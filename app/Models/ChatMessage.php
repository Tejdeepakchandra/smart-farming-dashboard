<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * ChatMessage Model — stores AI chat conversation history
 * 
 * Fields: user_id, message, response
 */
class ChatMessage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chat_messages';

    protected $fillable = [
        'user_id',
        'message',   // user's question
        'response',  // Gemini's response
    ];

    /**
     * Get the user that owns this chat message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
