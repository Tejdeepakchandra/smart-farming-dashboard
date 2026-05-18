<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService — handles all communication with Google Gemini API
 * Uses gemini-2.0-flash model for AI-powered farming insights
 */
class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    }

    /**
     * Generate AI insight based on sensor readings for a specific crop
     */
    public function generateInsight(array $sensorData, string $cropName, array $idealRanges = []): array
    {
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

        $response = $this->callGemini($prompt);

        return [
            'prompt' => $prompt,
            'response' => $response,
        ];
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

        return $this->callGemini($prompt);
    }

    /**
     * Make the actual HTTP call to Gemini API
     */
    protected function callGemini(string $prompt): string
    {
        if (empty($this->apiKey)) {
            return 'Gemini API key is not configured. Please add GEMINI_API_KEY to your .env file.';
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
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

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'AI service returned error (HTTP ' . $response->status() . '). Please try again.';

        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return 'Failed to connect to AI service: ' . $e->getMessage();
        }
    }
}
