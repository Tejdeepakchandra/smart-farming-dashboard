<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService — handles all communication with Google Gemini API
 * 
 * Features:
 * - Exponential backoff retry on 429 (rate limit) errors
 * - Response caching to reduce API calls
 * - Fallback model support
 */
class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $maxRetries = 3;

    /**
     * Models to try in order (primary → fallback)
     */
    protected array $models = [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-2.5-flash',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = $this->models[0];
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    }

    /**
     * Generate AI insight based on sensor readings for a specific crop
     */
    public function generateInsight(array $sensorData, string $cropName, array $idealRanges = []): array
    {
        // Build a cache key from sensor data to avoid duplicate calls
        $cacheKey = 'gemini_insight_' . md5($cropName . json_encode($sensorData));
        
        // Return cached response if available (cache for 5 minutes)
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $prompt = "You are an expert agricultural advisor. Analyze these REAL-TIME sensor readings for a {$cropName} crop and provide actionable advice.\n\n";
        $prompt .= "📊 CURRENT SENSOR DATA:\n";
        $prompt .= "• Temperature: {$sensorData['temperature']}°C\n";
        $prompt .= "• Soil Moisture: {$sensorData['soil_moisture']}%\n";
        $prompt .= "• Humidity: {$sensorData['humidity']}%\n";
        $prompt .= "• Light Intensity: {$sensorData['light_intensity']} lux\n";
        $prompt .= "• Rainfall: {$sensorData['rainfall']}mm\n\n";

        if (!empty($idealRanges)) {
            $prompt .= "🎯 IDEAL RANGES FOR {$cropName}:\n";
            foreach ($idealRanges as $sensor => $range) {
                $label = ucwords(str_replace('_', ' ', $sensor));
                $prompt .= "• {$label}: {$range['ideal_min']} - {$range['ideal_max']}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Based on this data, provide EXACTLY 3 short, actionable recommendations. Format each as a numbered point. Keep each under 2 sentences. Focus on what the farmer should do RIGHT NOW.";

        $response = $this->callGeminiWithRetry($prompt);

        $result = [
            'prompt' => $prompt,
            'response' => $response,
        ];

        // Cache successful responses for 5 minutes
        if (!str_starts_with($response, 'AI service') && !str_starts_with($response, 'Failed') && !str_starts_with($response, 'Gemini API')) {
            Cache::put($cacheKey, $result, 300);
        }

        return $result;
    }

    /**
     * Handle a chat message from the farmer
     */
    public function chat(string $userMessage): string
    {
        $prompt = "You are a smart farming assistant called 'FarmAI'. Rules:\n"
            . "1. Answer ONLY farming-related questions (crops, soil, pests, irrigation, weather, livestock, farming techniques)\n"
            . "2. Use simple language a farmer can understand\n"
            . "3. Keep answers concise but helpful (max 3-4 paragraphs)\n"
            . "4. If the question is NOT about farming, say: 'I specialize in farming topics. Try asking about crops, soil, irrigation, or pest control!'\n\n"
            . "Farmer's question: " . $userMessage;

        return $this->callGeminiWithRetry($prompt);
    }

    /**
     * Call Gemini with automatic retry on rate limit (429) errors
     * Uses exponential backoff: 2s → 4s → 8s
     */
    protected function callGeminiWithRetry(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return 'Gemini API key is not configured. Please add GEMINI_API_KEY to your .env file.';
        }

        // Check if we're in a cooldown period from a recent 429
        $cooldownUntil = Cache::get('gemini_cooldown');
        if ($cooldownUntil && now()->timestamp < $cooldownUntil) {
            $waitSeconds = $cooldownUntil - now()->timestamp;
            Log::info("Gemini API: In cooldown period, waiting {$waitSeconds}s");
            sleep(min($waitSeconds, 10));
        }

        // Try each model in order
        foreach ($this->models as $modelName) {
            $url = $this->baseUrl . $modelName . ':generateContent?key=' . $this->apiKey;

            for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
                try {
                    Log::info("Gemini API: Attempt {$attempt}/{$this->maxRetries} with model {$modelName}");

                    $response = Http::timeout(45)->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'topK' => 40,
                            'topP' => 0.95,
                            'maxOutputTokens' => 1024,
                        ],
                    ]);

                    // Success!
                    if ($response->successful()) {
                        $data = $response->json();
                        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                        if ($text) {
                            // Clear any cooldown on success
                            Cache::forget('gemini_cooldown');
                            Log::info("Gemini API: Success with {$modelName} on attempt {$attempt}");
                            return $text;
                        }

                        return 'No response generated. Please try again.';
                    }

                    $status = $response->status();
                    $body = $response->json() ?? [];
                    $errorMessage = $body['error']['message'] ?? $response->body();

                    // Rate limited (429) — retry with exponential backoff
                    if ($status === 429) {
                        $waitSeconds = pow(2, $attempt + 1); // 4s, 8s, 16s
                        
                        // Check for Retry-After header
                        $retryAfter = $response->header('Retry-After');
                        if ($retryAfter && is_numeric($retryAfter)) {
                            $waitSeconds = min((int)$retryAfter, 30);
                        }

                        Log::warning("Gemini API: Rate limited (429) on {$modelName}. Waiting {$waitSeconds}s before retry {$attempt}/{$this->maxRetries}");
                        
                        // Set a cooldown to prevent hammering
                        Cache::put('gemini_cooldown', now()->timestamp + $waitSeconds, $waitSeconds + 10);

                        if ($attempt < $this->maxRetries) {
                            sleep($waitSeconds);
                            continue; // Retry with same model
                        }

                        // Exhausted retries for this model, try next model
                        Log::warning("Gemini API: Exhausted retries for {$modelName}, trying fallback model...");
                        break;
                    }

                    // API key invalid (403) — don't retry, report immediately
                    if ($status === 403) {
                        Log::error("Gemini API: Forbidden (403). API key may be invalid or the API may not be enabled.", [
                            'error' => $errorMessage,
                        ]);
                        return "API key error: Your Gemini API key may be invalid or expired. Please check your GEMINI_API_KEY in .env and ensure the Generative Language API is enabled in Google Cloud Console.";
                    }

                    // Other errors (500, 503, etc.) — retry
                    if ($status >= 500) {
                        Log::warning("Gemini API: Server error ({$status}). Retrying...");
                        if ($attempt < $this->maxRetries) {
                            sleep(pow(2, $attempt));
                            continue;
                        }
                        break;
                    }

                    // Other client errors — don't retry
                    Log::error('Gemini API Error', [
                        'status' => $status,
                        'body' => $errorMessage,
                        'model' => $modelName,
                    ]);
                    return "AI service error (HTTP {$status}): " . substr($errorMessage, 0, 200);

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::warning("Gemini API: Connection timeout on attempt {$attempt}. " . $e->getMessage());
                    if ($attempt < $this->maxRetries) {
                        sleep(pow(2, $attempt));
                        continue;
                    }
                    break;
                } catch (\Exception $e) {
                    Log::error('Gemini API Exception: ' . $e->getMessage());
                    return 'Failed to connect to AI service: ' . $e->getMessage();
                }
            }
        }

        // All models and retries exhausted
        return 'AI service is temporarily busy (rate limited). Please wait 30 seconds and try again. The free Gemini API allows 15 requests per minute.';
    }
}
