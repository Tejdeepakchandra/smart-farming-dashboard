<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Crop Model — represents a crop/field managed by a farmer
 * 
 * Fields: user_id, name, field_name, planting_date, expected_harvest_date, status
 */
class Crop extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'crops';

    protected $fillable = [
        'user_id',
        'name',
        'field_name',
        'planting_date',
        'expected_harvest_date',
        'status', // active or harvested
    ];

    protected function casts(): array
    {
        return [
            'planting_date' => 'datetime',
            'expected_harvest_date' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this crop.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all sensor readings for this crop.
     */
    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * Get all AI insights for this crop.
     */
    public function aiInsights()
    {
        return $this->hasMany(AiInsight::class);
    }
}
