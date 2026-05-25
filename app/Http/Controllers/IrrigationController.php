<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * IrrigationController — Smart Irrigation Automation Panel
 * 
 * Provides:
 * - Manual/Automatic/AI-Controlled irrigation modes
 * - Per-zone pump status and valve control
 * - Water usage tracking and scheduling
 * - Animated irrigation flow visualization data
 */
class IrrigationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $simulator = new SensorSimulatorService();
        $crops = Crop::where('user_id', $user->id)->where('status', 'active')->get();

        // Get irrigation state from cache (simulated)
        $irrigationMode = Cache::get("irrigation_mode_{$user->id}", 'automatic');

        $zones = [];
        $totalWaterToday = 0;

        foreach ($crops as $crop) {
            $profile = $simulator->getProfileForCrop($crop->name);
            $latest = SensorReading::where('crop_id', $crop->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $currentMoisture = $latest->soil_moisture ?? 50;
            $idealMin = $profile['soil_moisture']['ideal_min'];
            $idealMax = $profile['soil_moisture']['ideal_max'];

            // Determine if irrigation is needed
            $needsWater = $currentMoisture < $idealMin;
            $isOverWatered = $currentMoisture > $idealMax;

            // Pump status (auto = based on moisture)
            $pumpOn = false;
            if ($irrigationMode === 'automatic' || $irrigationMode === 'ai') {
                $pumpOn = $needsWater;
            } else {
                $pumpOn = Cache::get("pump_{$user->id}_{$crop->id}", false);
            }

            // Water usage calculation
            $cropEconomics = [
                'rice' => 8000, 'wheat' => 3500, 'tomato' => 4000, 'corn' => 5000,
                'potato' => 4500, 'sugarcane' => 7000, 'cotton' => 3000, 'soybean' => 3500,
            ];
            $waterPerDay = $cropEconomics[strtolower($crop->name)] ?? 4000;
            $waterToday = $pumpOn ? round($waterPerDay * (now()->hour / 24)) : round($waterPerDay * 0.3);
            $totalWaterToday += $waterToday;

            // Next irrigation time
            $hoursUntilDry = $currentMoisture > $idealMin
                ? round(($currentMoisture - $idealMin) / 2, 1)
                : 0;
            $nextIrrigation = $hoursUntilDry > 0
                ? now()->addHours($hoursUntilDry)->format('g:i A')
                : 'NOW — needs water!';

            // Flow rate (liters per minute)
            $flowRate = $pumpOn ? round($waterPerDay / 480, 1) : 0; // 8 hour pumping day

            $zones[] = [
                'crop' => $crop,
                'latest' => $latest,
                'currentMoisture' => round($currentMoisture, 1),
                'idealMin' => $idealMin,
                'idealMax' => $idealMax,
                'needsWater' => $needsWater,
                'isOverWatered' => $isOverWatered,
                'pumpOn' => $pumpOn,
                'waterToday' => $waterToday,
                'waterPerDay' => $waterPerDay,
                'nextIrrigation' => $nextIrrigation,
                'hoursUntilDry' => $hoursUntilDry,
                'flowRate' => $flowRate,
                'valveOpen' => $pumpOn ? 100 : 0,
            ];
        }

        $systemStats = [
            'totalWaterToday' => $totalWaterToday,
            'activePumps' => collect($zones)->where('pumpOn', true)->count(),
            'totalZones' => count($zones),
            'waterPressure' => rand(25, 45) / 10, // bar
            'systemEfficiency' => rand(82, 96),
            'irrigationMode' => $irrigationMode,
        ];

        return view('irrigation.index', compact('zones', 'systemStats', 'irrigationMode'));
    }

    public function toggle(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action'); // 'mode' or 'pump'
        $value = $request->input('value');

        if ($action === 'mode') {
            Cache::put("irrigation_mode_{$user->id}", $value, 86400);
            return response()->json(['success' => true, 'mode' => $value, 'message' => "Irrigation mode set to: {$value}"]);
        }

        if ($action === 'pump') {
            $cropId = $request->input('crop_id');
            $on = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            Cache::put("pump_{$user->id}_{$cropId}", $on, 86400);

            // When pump is turned ON, create a new sensor reading with increased moisture
            if ($on) {
                $crop = Crop::where('id', $cropId)->where('user_id', $user->id)->first();
                if ($crop) {
                    $simulator = new SensorSimulatorService();
                    $profile = $simulator->getProfileForCrop($crop->name);
                    $latest = SensorReading::where('crop_id', $cropId)
                        ->orderBy('recorded_at', 'desc')
                        ->first();

                    if ($latest) {
                        // Boost moisture by 15%, cap at ideal_max + 5
                        $newMoisture = min(
                            ($profile['soil_moisture']['ideal_max'] ?? 85) + 5,
                            ($latest->soil_moisture ?? 50) + 15
                        );
                        SensorReading::create([
                            'user_id' => $user->id,
                            'crop_id' => $cropId,
                            'temperature' => $latest->temperature,
                            'soil_moisture' => round($newMoisture, 1),
                            'humidity' => $latest->humidity,
                            'light_intensity' => $latest->light_intensity,
                            'rainfall' => $latest->rainfall,
                            'recorded_at' => now(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'pump' => $on,
                'message' => $on
                    ? '💧 Pump turned ON — soil moisture increasing'
                    : '🔴 Pump turned OFF',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action']);
    }
}
