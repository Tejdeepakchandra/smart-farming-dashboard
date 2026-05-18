<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AiInsight;
use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeCrop = Crop::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $latestReading = null;
        if ($activeCrop) {
            $latestReading = SensorReading::where('user_id', $user->id)
                ->where('crop_id', $activeCrop->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
        }

        if (!$latestReading) {
            $latestReading = SensorReading::where('user_id', $user->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
        }

        $alerts = Alert::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadAlertCount = Alert::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $latestInsight = AiInsight::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $crops = Crop::where('user_id', $user->id)->get();

        $activeCropCount = Crop::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $totalCropCount = $crops->count();

        // Total sensor readings count
        $totalReadings = SensorReading::where('user_id', $user->id)->count();

        // Get ideal ranges for active crop
        $idealRanges = [];
        if ($activeCrop) {
            $simulator = new SensorSimulatorService();
            $idealRanges = $simulator->getProfileForCrop($activeCrop->name);
        }

        return view('dashboard.index', compact(
            'latestReading', 'alerts', 'unreadAlertCount', 'latestInsight',
            'activeCrop', 'crops', 'activeCropCount', 'totalCropCount',
            'totalReadings', 'idealRanges'
        ));
    }
}
