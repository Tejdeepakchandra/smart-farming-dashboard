<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ChatController — handles AI chat messages with Gemini
 */
class ChatController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Display the chat interface with message history.
     */
    public function index()
    {
        $messages = ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.index', compact('messages'));
    }

    /**
     * Send a message to Gemini and store the conversation.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Call Gemini API for chat response
        $response = $this->gemini->chat($validated['message']);

        // Store the conversation
        $chatMessage = ChatMessage::create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'response' => $response,
        ]);

        return response()->json([
            'success' => true,
            'message' => $chatMessage->message,
            'response' => $chatMessage->response,
            'created_at' => $chatMessage->created_at->format('H:i'),
        ]);
    }
}
