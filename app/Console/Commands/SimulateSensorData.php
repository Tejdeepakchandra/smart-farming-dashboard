<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Crop;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * SimulateSensorData — generates realistic random IoT sensor values
 * per user, per active crop, and inserts into sensor_readings collection.
 * Also triggers alert logic based on threshold values.
 * 
 * Scheduled to run every minute via Laravel Scheduler.
 */
class SimulateSensorData extends Command
{
    protected $signature = 'sensor:simulate';
    protected $description = 'Simulate IoT sensor data for all active crops and trigger alerts';

    /**
     * Sensor value ranges (realistic farming values)
     */
    private array $ranges = [
        'temperature'     => ['min' => 18, 'max' => 42],    // °C
        'soil_moisture'   => ['min' => 15, 'max' => 95],    // %
        'humidity'        => ['min' => 30, 'max' => 90],    // %
        'light_intensity' => ['min' => 1000, 'max' => 80000], // lux
        'rainfall'        => ['min' => 0, 'max' => 100],    // mm
    ];

    public function handle()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('No users found. Register a user first.');
            return;
        }

        $totalReadings = 0;
        $totalAlerts = 0;

        foreach ($users as $user) {
            $crops = Crop::where('user_id', $user->id)
                ->where('status', 'active')
                ->get();

            if ($crops->isEmpty()) {
                $this->info("User {$user->name}: No active crops, skipping.");
                continue;
            }

            foreach ($crops as $crop) {
                // Generate realistic sensor values with some random variation
                $sensorData = $this->generateSensorValues();

                // Insert sensor reading
                SensorReading::create([
                    'user_id' => $user->id,
                    'crop_id' => $crop->id,
                    'temperature' => $sensorData['temperature'],
                    'soil_moisture' => $sensorData['soil_moisture'],
                    'humidity' => $sensorData['humidity'],
                    'light_intensity' => $sensorData['light_intensity'],
                    'rainfall' => $sensorData['rainfall'],
                    'recorded_at' => now(),
                ]);

                $totalReadings++;

                // Check alert thresholds
                $alertsTriggered = $this->checkAlerts($user, $crop, $sensorData);
                $totalAlerts += $alertsTriggered;
            }
        }

        $this->info("✅ Simulated {$totalReadings} sensor readings, triggered {$totalAlerts} alerts.");
    }

    /**
     * Generate random sensor values within realistic ranges
     */
    private function generateSensorValues(): array
    {
        $values = [];
        foreach ($this->ranges as $sensor => $range) {
            if ($sensor === 'light_intensity') {
                // Light intensity uses integer values
                $values[$sensor] = rand($range['min'], $range['max']);
            } else {
                // Use one decimal place for other sensors
                $values[$sensor] = round(
                    $range['min'] + (mt_rand() / mt_getrandmax()) * ($range['max'] - $range['min']),
                    1
                );
            }
        }
        return $values;
    }

    /**
     * Check sensor values against alert thresholds
     * Only insert alert if the same type wasn't triggered in the last 2 hours
     */
    private function checkAlerts($user, $crop, array $data): int
    {
        $alertsTriggered = 0;
        $twoHoursAgo = now()->subHours(2);

        // Rule 1: Low soil moisture — irrigation needed
        if ($data['soil_moisture'] < 25) {
            if ($this->shouldTriggerAlert($user->id, 'danger', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $user->id,
                    'type' => 'danger',
                    'message' => "🚿 Irrigation needed urgently for {$crop->name} (Soil moisture: {$data['soil_moisture']}%)",
                    'is_read' => false,
                    'triggered_at' => now(),
                ]);
                $alertsTriggered++;
            }
        }

        // Rule 2: High temperature — heat stress
        if ($data['temperature'] > 40) {
            if ($this->shouldTriggerAlert($user->id, 'warning', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $user->id,
                    'type' => 'warning',
                    'message' => "🌡️ Heat stress warning for {$crop->name} (Temperature: {$data['temperature']}°C)",
                    'is_read' => false,
                    'triggered_at' => now(),
                ]);
                $alertsTriggered++;
            }
        }

        // Rule 3: High humidity — fungal disease risk
        if ($data['humidity'] > 85) {
            if ($this->shouldTriggerAlert($user->id, 'warning', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $user->id,
                    'type' => 'warning',
                    'message' => "💧 High humidity — risk of fungal disease ({$data['humidity']}%)",
                    'is_read' => false,
                    'triggered_at' => now(),
                ]);
                $alertsTriggered++;
            }
        }

        // Rule 4: Heavy rainfall — drainage check
        if ($data['rainfall'] > 80) {
            if ($this->shouldTriggerAlert($user->id, 'info', $twoHoursAgo)) {
                Alert::create([
                    'user_id' => $user->id,
                    'type' => 'info',
                    'message' => "🌧️ Heavy rainfall detected — check drainage ({$data['rainfall']}mm)",
                    'is_read' => false,
                    'triggered_at' => now(),
                ]);
                $alertsTriggered++;
            }
        }

        return $alertsTriggered;
    }

    /**
     * Check if an alert of this type was already triggered within the cooldown period
     */
    private function shouldTriggerAlert(string $userId, string $type, $since): bool
    {
        return !Alert::where('user_id', $userId)
            ->where('type', $type)
            ->where('triggered_at', '>=', $since)
            ->exists();
    }
}
