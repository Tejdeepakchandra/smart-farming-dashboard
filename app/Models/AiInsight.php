<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * AiInsight Model — stores Gemini AI-generated farming insights
 * 
 * Fields: user_id, crop_id, sensor_snapshot, prompt_sent, ai_response
 */
class AiInsight extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ai_insights';

    protected $fillable = [
        'user_id',
        'crop_id',
        'sensor_snapshot', // object storing sensor values at time of insight
        'prompt_sent',
        'ai_response',
    ];

    protected function casts(): array
    {
        return [
            'sensor_snapshot' => 'array',
        ];
    }

    /**
     * Get the user that owns this insight.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the crop associated with this insight.
     */
    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}
