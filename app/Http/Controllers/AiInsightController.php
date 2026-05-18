<?php

namespace App\Http\Controllers;

use App\Models\AiInsight;
use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\GeminiService;
use App\Services\SensorSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiInsightController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Generate a new AI insight based on latest sensor data
     */
    public function generate(Request $request)
    {
        $user = Auth::user();
        $cropId = $request->input('crop_id');

        // Find crop
        $query = Crop::where('user_id', $user->id)->where('status', 'active');
        if ($cropId) {
            $query->where('_id', $cropId);
        }
        $crop = $query->first();

        if (!$crop) {
            return response()->json([
                'success' => false,
                'message' => 'No active crop found. Please add a crop first.',
            ]);
        }

        // Get latest sensor reading
        $reading = SensorReading::where('user_id', $user->id)
            ->where('crop_id', $crop->id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (!$reading) {
            return response()->json([
                'success' => false,
                'message' => 'No sensor data available. Click "Generate Sensor Data" first!',
            ]);
        }

        // Build sensor data snapshot
        $sensorData = [
            'temperature' => $reading->temperature,
            'soil_moisture' => $reading->soil_moisture,
            'humidity' => $reading->humidity,
            'light_intensity' => $reading->light_intensity,
            'rainfall' => $reading->rainfall,
        ];

        // Get ideal ranges for this crop
        $simulator = new SensorSimulatorService();
        $idealRanges = $simulator->getProfileForCrop($crop->name);

        // Call Gemini API with ideal ranges for context
        $result = $this->gemini->generateInsight($sensorData, $crop->name, $idealRanges);

        // Store the insight
        $insight = AiInsight::create([
            'user_id' => $user->id,
            'crop_id' => $crop->id,
            'sensor_snapshot' => $sensorData,
            'prompt_sent' => $result['prompt'],
            'ai_response' => $result['response'],
        ]);

        return response()->json([
            'success' => true,
            'insight' => $insight->ai_response,
            'crop_name' => $crop->name,
            'created_at' => $insight->created_at->format('d M Y, H:i'),
        ]);
    }
}
