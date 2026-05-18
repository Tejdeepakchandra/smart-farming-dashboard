<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * User Model — extends MongoDB Authenticatable for auth support
 * 
 * Fields: name, email, password, farm_name, location
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'farm_name',
        'location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all crops belonging to this user.
     */
    public function crops()
    {
        return $this->hasMany(Crop::class);
    }

    /**
     * Get all sensor readings for this user.
     */
    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * Get all alerts for this user.
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get all AI insights for this user.
     */
    public function aiInsights()
    {
        return $this->hasMany(AiInsight::class);
    }

    /**
     * Get all chat messages for this user.
     */
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
