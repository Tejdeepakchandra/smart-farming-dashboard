<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorController extends Controller
{
    /**
     * Return sensor readings as JSON for Chart.js (last 24 hours)
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        $cropId = $request->query('crop_id');

        $query = SensorReading::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at', 'asc');

        if ($cropId) {
            $query->where('crop_id', $cropId);
        }

        $readings = $query->get();

        return response()->json([
            'labels' => $readings->pluck('recorded_at')->map(fn($d) => $d->format('H:i')),
            'temperature' => $readings->pluck('temperature'),
            'soil_moisture' => $readings->pluck('soil_moisture'),
            'humidity' => $readings->pluck('humidity'),
            'light_intensity' => $readings->pluck('light_intensity'),
            'rainfall' => $readings->pluck('rainfall'),
        ]);
    }

    /**
     * Return the latest sensor reading (JSON for auto-refresh)
     */
    public function latest(Request $request)
    {
        $user = Auth::user();
        $cropId = $request->query('crop_id');

        $query = SensorReading::where('user_id', $user->id)
            ->orderBy('recorded_at', 'desc');

        if ($cropId) {
            $query->where('crop_id', $cropId);
        }

        $latest = $query->first();

        if (!$latest) {
            return response()->json([
                'temperature' => '--', 'soil_moisture' => '--',
                'humidity' => '--', 'light_intensity' => '--',
                'rainfall' => '--', 'recorded_at' => 'No data yet',
            ]);
        }

        return response()->json([
            'temperature' => round($latest->temperature, 1),
            'soil_moisture' => round($latest->soil_moisture, 1),
            'humidity' => round($latest->humidity, 1),
            'light_intensity' => round($latest->light_intensity, 0),
            'rainfall' => round($latest->rainfall, 1),
            'recorded_at' => $latest->recorded_at->format('d M Y, H:i:s'),
        ]);
    }

    /**
     * Sensor history page with filters
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $crops = Crop::where('user_id', $user->id)->get();

        $cropId = $request->query('crop_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = SensorReading::where('user_id', $user->id)
            ->orderBy('recorded_at', 'desc');

        if ($cropId) $query->where('crop_id', $cropId);
        if ($dateFrom) $query->where('recorded_at', '>=', $dateFrom);
        if ($dateTo) $query->where('recorded_at', '<=', $dateTo . ' 23:59:59');
        if (!$dateFrom && !$dateTo) $query->where('recorded_at', '>=', now()->subHours(24));

        $readings = $query->limit(500)->get();

        return view('sensors.history', compact('crops', 'readings', 'cropId', 'dateFrom', 'dateTo'));
    }

    /**
     * WEB-BASED SIMULATION — triggered from dashboard button (no terminal needed)
     * Generates sensor data for all active crops of the logged-in user
     */
    public function simulate(Request $request)
    {
        $user = Auth::user();
        $simulator = new SensorSimulatorService();

        $cropId = $request->input('crop_id');

        if ($cropId) {
            // Simulate for a specific crop
            $crop = Crop::where('_id', $cropId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$crop) {
                return response()->json(['success' => false, 'message' => 'Crop not found or not active.']);
            }

            $data = $simulator->simulateForCrop($user->id, $crop);

            return response()->json([
                'success' => true,
                'message' => "Sensor data generated for {$crop->name}",
                'count' => 1,
                'data' => $data,
            ]);
        } else {
            // Simulate for ALL active crops
            $count = $simulator->simulateForUser($user->id);

            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active crops found. Add a crop first!',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Sensor data generated for {$count} crop(s)",
                'count' => $count,
            ]);
        }
    }

    /**
     * Batch simulate — generate multiple readings at once (to fill chart)
     */
    public function simulateBatch(Request $request)
    {
        $user = Auth::user();
        $simulator = new SensorSimulatorService();
        $count = min((int)$request->input('count', 10), 50); // Max 50 at once

        $crops = Crop::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        if ($crops->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No active crops found.']);
        }

        $total = 0;
        // Spread readings over the past hours
        foreach ($crops as $crop) {
            $profile = $simulator->getProfileForCrop($crop->name);
            for ($i = $count; $i >= 1; $i--) {
                $data = $this->generateValuesFromProfile($profile);
                SensorReading::create([
                    'user_id' => $user->id,
                    'crop_id' => $crop->id,
                    'temperature' => $data['temperature'],
                    'soil_moisture' => $data['soil_moisture'],
                    'humidity' => $data['humidity'],
                    'light_intensity' => $data['light_intensity'],
                    'rainfall' => $data['rainfall'],
                    'recorded_at' => now()->subMinutes($i * 15),
                ]);
                $total++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$total} historical sensor readings across {$crops->count()} crop(s)",
            'count' => $total,
        ]);
    }

    private function generateValuesFromProfile(array $profile): array
    {
        $values = [];
        foreach ($profile as $sensor => $range) {
            $nearIdeal = (mt_rand(1, 100) <= 70);
            $min = $nearIdeal ? $range['ideal_min'] : $range['min'];
            $max = $nearIdeal ? $range['ideal_max'] : $range['max'];
            $values[$sensor] = ($sensor === 'light_intensity')
                ? rand($min, $max)
                : round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 1);
        }
        return $values;
    }
}
