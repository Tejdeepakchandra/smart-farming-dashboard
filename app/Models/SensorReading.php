<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * SensorReading Model — stores simulated IoT sensor data
 * 
 * Fields: user_id, crop_id, temperature, soil_moisture, humidity, 
 *         light_intensity, rainfall, recorded_at
 */
class SensorReading extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_readings';

    // Only created_at is needed, no updated_at for sensor data
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'crop_id',
        'temperature',
        'soil_moisture',
        'humidity',
        'light_intensity',
        'rainfall',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'soil_moisture' => 'float',
            'humidity' => 'float',
            'light_intensity' => 'float',
            'rainfall' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this reading.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the crop associated with this reading.
     */
    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}
