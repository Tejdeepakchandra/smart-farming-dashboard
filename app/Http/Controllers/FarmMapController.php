<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Support\Facades\Auth;

/**
 * FarmMapController — Interactive Digital Twin Farm Map
 * 
 * Provides a spatial visualization of the farm:
 * - Top-down SVG grid showing crop zones
 * - Color-coded health status per zone
 * - Sensor overlay (temperature, moisture, humidity heatmaps)
 * - Animated irrigation lines and sensor nodes
 */
class FarmMapController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $simulator = new SensorSimulatorService();
        $crops = Crop::where('user_id', $user->id)->get();

        $zones = [];
        foreach ($crops as $crop) {
            $latest = SensorReading::where('crop_id', $crop->id)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $profile = $simulator->getProfileForCrop($crop->name);
            $healthScore = null;
            $sensorStatus = [];

            if ($latest) {
                $total = 0; $cnt = 0;
                foreach ($profile as $key => $range) {
                    $val = $latest->{$key};
                    $ok = $val >= $range['ideal_min'] && $val <= $range['ideal_max'];
                    if ($ok) { $score = 100; }
                    else {
                        $dist = $val < $range['ideal_min'] ? $range['ideal_min'] - $val : $val - $range['ideal_max'];
                        $maxD = $val < $range['ideal_min'] ? $range['ideal_min'] - $range['min'] : $range['max'] - $range['ideal_max'];
                        $score = max(0, round(100 * (1 - ($maxD > 0 ? $dist / $maxD : 1))));
                    }
                    $sensorStatus[$key] = [
                        'value' => $val,
                        'score' => $score,
                        'ok' => $ok,
                        'ideal_min' => $range['ideal_min'],
                        'ideal_max' => $range['ideal_max'],
                    ];
                    $total += $score;
                    $cnt++;
                }
                $healthScore = $cnt > 0 ? round($total / $cnt) : 0;
            }

            // Risk assessment
            $risks = [];
            if ($latest) {
                if (($sensorStatus['soil_moisture']['value'] ?? 100) < ($profile['soil_moisture']['ideal_min'] ?? 40))
                    $risks[] = ['type' => 'drought', 'label' => '🏜️ Drought Risk', 'severity' => 'high'];
                if (($sensorStatus['humidity']['value'] ?? 0) > ($profile['humidity']['ideal_max'] ?? 80))
                    $risks[] = ['type' => 'fungal', 'label' => '🍄 Fungal Risk', 'severity' => 'medium'];
                if (($sensorStatus['temperature']['value'] ?? 0) > ($profile['temperature']['ideal_max'] ?? 35))
                    $risks[] = ['type' => 'heat', 'label' => '🔥 Heat Stress', 'severity' => 'high'];
                if (($sensorStatus['rainfall']['value'] ?? 0) > ($profile['rainfall']['ideal_max'] ?? 40))
                    $risks[] = ['type' => 'flood', 'label' => '🌊 Flood Risk', 'severity' => 'medium'];
            }

            $zones[] = [
                'crop' => $crop,
                'latest' => $latest,
                'healthScore' => $healthScore,
                'sensorStatus' => $sensorStatus,
                'risks' => $risks,
                'profile' => $profile,
                'needsWater' => $latest && ($latest->soil_moisture < ($profile['soil_moisture']['ideal_min'] ?? 40)),
            ];
        }

        return view('farm-map.index', compact('zones', 'crops'));
    }
}
