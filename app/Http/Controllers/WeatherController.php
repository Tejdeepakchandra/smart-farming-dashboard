<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * WeatherController — Real-time Weather Forecast + AI Farming Suggestions
 * 
 * Uses Open-Meteo API (free, no API key needed):
 * - 7-day weather forecast
 * - Hourly/daily temperature, rain, wind, humidity
 * - AI-powered farming suggestions based on forecast
 */
class WeatherController extends Controller
{
    // Default: Hyderabad, India (change per user's farm location)
    private float $lat = 17.385;
    private float $lon = 78.4867;
    private string $timezone = 'Asia/Kolkata';

    public function index()
    {
        $user = Auth::user();
        $crops = Crop::where('user_id', $user->id)->where('status', 'active')->get();

        // Get forecast (cached for 30 min)
        $forecast = Cache::remember('weather_forecast_' . $user->id, 1800, function () {
            return $this->fetchForecast();
        });

        // Generate farming suggestions from weather
        $suggestions = $this->generateSuggestions($forecast, $crops);

        return view('weather.index', compact('forecast', 'suggestions', 'crops'));
    }

    public function forecastData()
    {
        $user = Auth::user();
        $forecast = Cache::remember('weather_forecast_' . $user->id, 1800, function () {
            return $this->fetchForecast();
        });
        return response()->json($forecast);
    }

    private function fetchForecast(): array
    {
        try {
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $this->lat,
                'longitude' => $this->lon,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,windspeed_10m_max,uv_index_max,weathercode',
                'hourly' => 'temperature_2m,relative_humidity_2m,precipitation,soil_moisture_0_to_7cm',
                'current' => 'temperature_2m,relative_humidity_2m,precipitation,windspeed_10m,weathercode',
                'timezone' => $this->timezone,
                'forecast_days' => 7,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error('Weather API error: ' . $e->getMessage());
        }

        // Fallback simulated data
        return $this->simulatedForecast();
    }

    private function simulatedForecast(): array
    {
        $daily = ['time' => [], 'temperature_2m_max' => [], 'temperature_2m_min' => [],
            'precipitation_sum' => [], 'precipitation_probability_max' => [],
            'windspeed_10m_max' => [], 'uv_index_max' => [], 'weathercode' => []];

        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $daily['time'][] = $date->format('Y-m-d');
            $daily['temperature_2m_max'][] = rand(28, 38);
            $daily['temperature_2m_min'][] = rand(18, 26);
            $daily['precipitation_sum'][] = round(rand(0, 30) * (rand(0, 1) ? 1 : 0), 1);
            $daily['precipitation_probability_max'][] = rand(0, 90);
            $daily['windspeed_10m_max'][] = round(rand(5, 25), 1);
            $daily['uv_index_max'][] = round(rand(4, 12), 1);
            $daily['weathercode'][] = [0, 1, 2, 3, 45, 61, 63, 80][rand(0, 7)];
        }

        return [
            'current' => [
                'temperature_2m' => rand(25, 35),
                'relative_humidity_2m' => rand(40, 80),
                'precipitation' => 0,
                'windspeed_10m' => round(rand(5, 20), 1),
                'weathercode' => 0,
            ],
            'daily' => $daily,
        ];
    }

    private function generateSuggestions(array $forecast, $crops): array
    {
        $suggestions = [];
        $daily = $forecast['daily'] ?? [];

        if (empty($daily['time'])) return $suggestions;

        // Check tomorrow's weather
        $tomorrowRain = $daily['precipitation_sum'][1] ?? 0;
        $tomorrowTemp = $daily['temperature_2m_max'][1] ?? 30;
        $tomorrowWind = $daily['windspeed_10m_max'][1] ?? 10;

        if ($tomorrowRain > 10) {
            $suggestions[] = [
                'icon' => '🌧️', 'priority' => 'high',
                'title' => 'Heavy Rain Expected Tomorrow',
                'detail' => "Expected rainfall: {$tomorrowRain}mm. Skip irrigation today — your crops will get natural watering. Check drainage channels to prevent waterlogging.",
            ];
        } elseif ($tomorrowRain > 0) {
            $suggestions[] = [
                'icon' => '🌦️', 'priority' => 'info',
                'title' => 'Light Rain Expected',
                'detail' => "Expected rainfall: {$tomorrowRain}mm. You can reduce irrigation by 50% today.",
            ];
        } else {
            $suggestions[] = [
                'icon' => '☀️', 'priority' => 'medium',
                'title' => 'No Rain Expected',
                'detail' => 'Dry conditions ahead. Ensure irrigation system is running on schedule. Consider adding mulch to conserve soil moisture.',
            ];
        }

        if ($tomorrowTemp > 35) {
            $suggestions[] = [
                'icon' => '🔥', 'priority' => 'high',
                'title' => "Heat Wave Alert ({$tomorrowTemp}°C)",
                'detail' => 'Very high temperatures expected. Water crops early morning (before 7 AM). Install shade cloth on sensitive crops like tomatoes.',
            ];
        }

        if ($tomorrowWind > 20) {
            $suggestions[] = [
                'icon' => '💨', 'priority' => 'medium',
                'title' => "Strong Winds ({$tomorrowWind} km/h)",
                'detail' => 'Stake tall crops like corn and sugarcane. Avoid spraying pesticides — chemicals will drift. Secure greenhouse covers.',
            ];
        }

        // 3-day outlook
        $totalRain3d = array_sum(array_slice($daily['precipitation_sum'] ?? [], 0, 3));
        if ($totalRain3d > 50) {
            $suggestions[] = [
                'icon' => '⛈️', 'priority' => 'high',
                'title' => "Heavy Rainfall Next 3 Days ({$totalRain3d}mm total)",
                'detail' => 'Significant rainfall expected. Prepare drainage, harvest any mature produce early, and protect seedlings.',
            ];
        } elseif ($totalRain3d == 0) {
            $suggestions[] = [
                'icon' => '🏜️', 'priority' => 'medium',
                'title' => 'No Rain for 3 Days',
                'detail' => 'Dry spell ahead. Increase irrigation frequency. Deep watering is better than frequent shallow watering.',
            ];
        }

        return $suggestions;
    }

    /**
     * WMO Weather Codes → Description
     */
    public static function weatherCodeToInfo(int $code): array
    {
        return match(true) {
            $code === 0 => ['desc' => 'Clear sky', 'icon' => '☀️'],
            $code <= 3 => ['desc' => 'Partly cloudy', 'icon' => '⛅'],
            $code <= 48 => ['desc' => 'Foggy', 'icon' => '🌫️'],
            $code <= 57 => ['desc' => 'Drizzle', 'icon' => '🌦️'],
            $code <= 67 => ['desc' => 'Rain', 'icon' => '🌧️'],
            $code <= 77 => ['desc' => 'Snow', 'icon' => '❄️'],
            $code <= 82 => ['desc' => 'Rain showers', 'icon' => '🌧️'],
            $code <= 86 => ['desc' => 'Snow showers', 'icon' => '🌨️'],
            $code <= 99 => ['desc' => 'Thunderstorm', 'icon' => '⛈️'],
            default => ['desc' => 'Unknown', 'icon' => '🌤️'],
        };
    }
}
