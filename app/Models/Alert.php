<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Alert Model — stores system-generated farming alerts
 * 
 * Fields: user_id, type (warning/danger/info), message, is_read, triggered_at
 */
class Alert extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'alerts';

    protected $fillable = [
        'user_id',
        'type',       // warning, danger, info
        'message',
        'is_read',
        'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'triggered_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this alert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only unread alerts.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
