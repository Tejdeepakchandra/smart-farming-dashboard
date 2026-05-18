<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AlertController — list alerts and mark as read
 */
class AlertController extends Controller
{
    /**
     * Display a listing of all alerts.
     */
    public function index()
    {
        $alerts = Alert::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alerts.index', compact('alerts'));
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllRead()
    {
        Alert::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->route('alerts.index')
            ->with('success', 'All alerts marked as read.');
    }

    /**
     * Mark a single alert as read.
     */
    public function markRead(string $id)
    {
        $alert = Alert::where('_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alert->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread alert count (for AJAX bell icon refresh)
     */
    public function unreadCount()
    {
        $count = Alert::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
