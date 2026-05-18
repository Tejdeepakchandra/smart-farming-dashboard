<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Crop;
use App\Models\SensorReading;

/**
 * SensorSimulatorService — generates crop-specific IoT sensor data
 * 
 * Each crop type has its own ideal sensor ranges. The simulator generates
 * values that hover around ideal ranges with realistic variation.
 * Can be triggered from web (no terminal needed for deployment).
 */
class SensorSimulatorService
{
    /**
     * Crop-specific ideal sensor profiles
     * Each crop has ideal ranges and alert thresholds
     */
    private array $cropProfiles = [
        'rice' => [
            'temperature'     => ['min' => 20, 'max' => 35, 'ideal_min' => 25, 'ideal_max' => 30],
            'soil_moisture'   => ['min' => 60, 'max' => 95, 'ideal_min' => 70, 'ideal_max' => 85],
            'humidity'        => ['min' => 60, 'max' => 90, 'ideal_min' => 70, 'ideal_max' => 80],
            'light_intensity' => ['min' => 10000, 'max' => 50000, 'ideal_min' => 20000, 'ideal_max' => 40000],
            'rainfall'        => ['min' => 5, 'max' => 80, 'ideal_min' => 15, 'ideal_max' => 40],
        ],
        'wheat' => [
            'temperature'     => ['min' => 12, 'max' => 30, 'ideal_min' => 18, 'ideal_max' => 24],
            'soil_moisture'   => ['min' => 30, 'max' => 70, 'ideal_min' => 40, 'ideal_max' => 55],
            'humidity'        => ['min' => 35, 'max' => 75, 'ideal_min' => 45, 'ideal_max' => 60],
            'light_intensity' => ['min' => 15000, 'max' => 60000, 'ideal_min' => 25000, 'ideal_max' => 45000],
            'rainfall'        => ['min' => 0, 'max' => 50, 'ideal_min' => 5, 'ideal_max' => 20],
        ],
        'tomato' => [
            'temperature'     => ['min' => 18, 'max' => 35, 'ideal_min' => 21, 'ideal_max' => 27],
            'soil_moisture'   => ['min' => 40, 'max' => 80, 'ideal_min' => 50, 'ideal_max' => 65],
            'humidity'        => ['min' => 40, 'max' => 80, 'ideal_min' => 50, 'ideal_max' => 65],
            'light_intensity' => ['min' => 20000, 'max' => 70000, 'ideal_min' => 30000, 'ideal_max' => 55000],
            'rainfall'        => ['min' => 0, 'max' => 40, 'ideal_min' => 5, 'ideal_max' => 15],
        ],
        'corn' => [
            'temperature'     => ['min' => 18, 'max' => 38, 'ideal_min' => 24, 'ideal_max' => 32],
            'soil_moisture'   => ['min' => 40, 'max' => 85, 'ideal_min' => 50, 'ideal_max' => 70],
            'humidity'        => ['min' => 45, 'max' => 85, 'ideal_min' => 55, 'ideal_max' => 70],
            'light_intensity' => ['min' => 20000, 'max' => 65000, 'ideal_min' => 30000, 'ideal_max' => 50000],
            'rainfall'        => ['min' => 0, 'max' => 60, 'ideal_min' => 10, 'ideal_max' => 25],
        ],
        'potato' => [
            'temperature'     => ['min' => 10, 'max' => 30, 'ideal_min' => 15, 'ideal_max' => 22],
            'soil_moisture'   => ['min' => 50, 'max' => 85, 'ideal_min' => 60, 'ideal_max' => 75],
            'humidity'        => ['min' => 60, 'max' => 90, 'ideal_min' => 65, 'ideal_max' => 80],
            'light_intensity' => ['min' => 12000, 'max' => 45000, 'ideal_min' => 18000, 'ideal_max' => 35000],
            'rainfall'        => ['min' => 0, 'max' => 50, 'ideal_min' => 8, 'ideal_max' => 20],
        ],
        'sugarcane' => [
            'temperature'     => ['min' => 20, 'max' => 40, 'ideal_min' => 27, 'ideal_max' => 35],
            'soil_moisture'   => ['min' => 55, 'max' => 95, 'ideal_min' => 65, 'ideal_max' => 80],
            'humidity'        => ['min' => 55, 'max' => 90, 'ideal_min' => 65, 'ideal_max' => 80],
            'light_intensity' => ['min' => 25000, 'max' => 75000, 'ideal_min' => 35000, 'ideal_max' => 60000],
            'rainfall'        => ['min' => 5, 'max' => 70, 'ideal_min' => 15, 'ideal_max' => 35],
        ],
        'cotton' => [
            'temperature'     => ['min' => 20, 'max' => 42, 'ideal_min' => 25, 'ideal_max' => 35],
            'soil_moisture'   => ['min' => 30, 'max' => 70, 'ideal_min' => 40, 'ideal_max' => 55],
            'humidity'        => ['min' => 30, 'max' => 70, 'ideal_min' => 40, 'ideal_max' => 55],
            'light_intensity' => ['min' => 25000, 'max' => 80000, 'ideal_min' => 40000, 'ideal_max' => 65000],
            'rainfall'        => ['min' => 0, 'max' => 50, 'ideal_min' => 5, 'ideal_max' => 20],
        ],
        'soybean' => [
            'temperature'     => ['min' => 15, 'max' => 35, 'ideal_min' => 22, 'ideal_max' => 30],
            'soil_moisture'   => ['min' => 40, 'max' => 80, 'ideal_min' => 50, 'ideal_max' => 65],
            'humidity'        => ['min' => 45, 'max' => 80, 'ideal_min' => 55, 'ideal_max' => 70],
            'light_intensity' => ['min' => 15000, 'max' => 55000, 'ideal_min' => 25000, 'ideal_max' => 40000],
            'rainfall'        => ['min' => 0, 'max' => 55, 'ideal_min' => 8, 'ideal_max' => 25],
        ],
    ];

    /** Default profile for any crop not in the list */
    private array $defaultProfile = [
        'temperature'     => ['min' => 18, 'max' => 38, 'ideal_min' => 22, 'ideal_max' => 30],
        'soil_moisture'   => ['min' => 30, 'max' => 85, 'ideal_min' => 45, 'ideal_max' => 65],
        'humidity'        => ['min' => 35, 'max' => 85, 'ideal_min' => 50, 'ideal_max' => 70],
        'light_intensity' => ['min' => 10000, 'max' => 60000, 'ideal_min' => 20000, 'ideal_max' => 40000],
        'rainfall'        => ['min' => 0, 'max' => 60, 'ideal_min' => 5, 'ideal_max' => 20],
    ];

    /**
     * Get the sensor profile for a crop (by name lookup)
     */
    public function getProfileForCrop(string $cropName): array
    {
        $key = strtolower(trim($cropName));
        return $this->cropProfiles[$key] ?? $this->defaultProfile;
    }

    /**
     * Get all available crop profiles (for dropdowns)
     */
    public function getAvailableCrops(): array
    {
        return array_keys($this->cropProfiles);
    }

    /**
     * Simulate sensor data for a single crop
     * Returns the generated values
     */
    public function simulateForCrop(string $userId, Crop $crop): array
    {
        $profile = $this->getProfileForCrop($crop->name);
        $sensorData = $this->generateValues($profile);

        // Insert sensor reading
        SensorReading::create([
            'user_id' => $userId,
            'crop_id' => $crop->id,
            'temperature' => $sensorData['temperature'],
            'soil_moisture' => $sensorData['soil_moisture'],
            'humidity' => $sensorData['humidity'],
            'light_intensity' => $sensorData['light_intensity'],
            'rainfall' => $sensorData['rainfall'],
            'recorded_at' => now(),
        ]);

        // Check alert thresholds
        $this->checkAlerts($userId, $crop, $sensorData, $profile);

        return $sensorData;
    }

    /**
     * Simulate data for ALL active crops of a user
     */
    public function simulateForUser(string $userId): int
    {
        $crops = Crop::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($crops as $crop) {
            $this->simulateForCrop($userId, $crop);
            $count++;
        }

        return $count;
    }

    /**
     * Generate sensor values that hover around ideal ranges with variation
     */
    private function generateValues(array $profile): array
    {
        $values = [];
        foreach ($profile as $sensor => $range) {
            // 70% chance values are near ideal, 30% chance they drift to extremes
            $nearIdeal = (mt_rand(1, 100) <= 70);

            if ($nearIdeal) {
                $min = $range['ideal_min'];
                $max = $range['ideal_max'];
            } else {
                $min = $range['min'];
                $max = $range['max'];
            }

            if ($sensor === 'light_intensity') {
                $values[$sensor] = rand($min, $max);
            } else {
                $values[$sensor] = round(
                    $min + (mt_rand() / mt_getrandmax()) * ($max - $min), 1
                );
            }
        }
        return $values;
    }

    /**
     * Check sensor values against crop-specific thresholds and create alerts
     */
    private function checkAlerts(string $userId, Crop $crop, array $data, array $profile): void
    {
        $twoHoursAgo = now()->subHours(2);

        // Low soil moisture — below crop's minimum ideal
        if ($data['soil_moisture'] < $profile['soil_moisture']['ideal_min'] * 0.6) {
            if ($this->shouldTrigger($userId, 'low_moisture', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $userId, 'type' => 'danger',
                    'message' => "🚿 {$crop->name}: Irrigation needed! Soil moisture at {$data['soil_moisture']}% (ideal: {$profile['soil_moisture']['ideal_min']}-{$profile['soil_moisture']['ideal_max']}%)",
                    'is_read' => false, 'triggered_at' => now(),
                ]);
            }
        }

        // High temperature — above crop's maximum
        if ($data['temperature'] > $profile['temperature']['ideal_max'] * 1.15) {
            if ($this->shouldTrigger($userId, 'high_temp', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $userId, 'type' => 'warning',
                    'message' => "🌡️ {$crop->name}: Heat stress! Temperature at {$data['temperature']}°C (ideal: {$profile['temperature']['ideal_min']}-{$profile['temperature']['ideal_max']}°C)",
                    'is_read' => false, 'triggered_at' => now(),
                ]);
            }
        }

        // High humidity — fungal risk
        if ($data['humidity'] > $profile['humidity']['ideal_max'] * 1.1) {
            if ($this->shouldTrigger($userId, 'high_humidity', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $userId, 'type' => 'warning',
                    'message' => "💧 {$crop->name}: Fungal disease risk! Humidity at {$data['humidity']}% (ideal max: {$profile['humidity']['ideal_max']}%)",
                    'is_read' => false, 'triggered_at' => now(),
                ]);
            }
        }

        // Heavy rainfall
        if ($data['rainfall'] > $profile['rainfall']['ideal_max'] * 2) {
            if ($this->shouldTrigger($userId, 'heavy_rain', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $userId, 'type' => 'info',
                    'message' => "🌧️ {$crop->name}: Heavy rainfall at {$data['rainfall']}mm — check drainage",
                    'is_read' => false, 'triggered_at' => now(),
                ]);
            }
        }
    }

    private function shouldTrigger(string $userId, string $alertKey, $since): bool
    {
        return !Alert::where('user_id', $userId)
            ->where('message', 'like', '%' . explode('_', $alertKey)[0] . '%')
            ->where('triggered_at', '>=', $since)
            ->exists();
    }
}
