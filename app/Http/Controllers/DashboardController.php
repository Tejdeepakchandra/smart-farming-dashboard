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
        $simulator = new SensorSimulatorService();

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
            $idealRanges = $simulator->getProfileForCrop($activeCrop->name);
        }

        // ─── Per-Crop Health Data ─────────────────────────
        $actionItems = [];
        $totalHealthScore = 0;
        $healthCropCount = 0;

        foreach ($crops as $crop) {
            $crop->latestReading = SensorReading::where('crop_id', $crop->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $crop->readingsCount = SensorReading::where('crop_id', $crop->id)->count();

            if ($crop->latestReading) {
                $profile = $simulator->getProfileForCrop($crop->name);
                $crop->idealRanges = $profile;

                // Calculate health score (0-100)
                $score = 0;
                $count = 0;
                foreach ($profile as $key => $range) {
                    $val = $crop->latestReading->{$key};
                    if ($val >= $range['ideal_min'] && $val <= $range['ideal_max']) {
                        $score += 100;
                    } else {
                        // Calculate how far off the value is from ideal
                        if ($val < $range['ideal_min']) {
                            $dist = $range['ideal_min'] - $val;
                            $maxDist = $range['ideal_min'] - $range['min'];
                        } else {
                            $dist = $val - $range['ideal_max'];
                            $maxDist = $range['max'] - $range['ideal_max'];
                        }
                        $pctOff = $maxDist > 0 ? ($dist / $maxDist) : 1;
                        $score += max(0, round(100 * (1 - $pctOff)));
                    }
                    $count++;

                    // Generate action items for problematic sensors
                    if ($val < $range['ideal_min'] || $val > $range['ideal_max']) {
                        $actionItems[] = [
                            'crop_id' => $crop->id,
                            'crop_name' => $crop->name,
                            'field_name' => $crop->field_name,
                            'sensor' => $key,
                            'value' => $val,
                            'ideal_min' => $range['ideal_min'],
                            'ideal_max' => $range['ideal_max'],
                            'direction' => $val < $range['ideal_min'] ? 'low' : 'high',
                            'severity' => ($val < $range['min'] * 1.1 || $val > $range['max'] * 0.9) ? 'critical' : 'warning',
                        ];
                    }
                }
                $crop->healthScore = $count > 0 ? round($score / $count) : 0;
                $totalHealthScore += $crop->healthScore;
                $healthCropCount++;
            } else {
                $crop->healthScore = null;
                $crop->idealRanges = [];
            }
        }

        $farmHealthScore = $healthCropCount > 0 ? round($totalHealthScore / $healthCropCount) : null;

        // Sort action items by severity (critical first), limit to 6
        usort($actionItems, fn($a, $b) => ($a['severity'] === 'critical' ? 0 : 1) - ($b['severity'] === 'critical' ? 0 : 1));
        $actionItems = array_slice($actionItems, 0, 6);

        return view('dashboard.index', compact(
            'latestReading', 'alerts', 'unreadAlertCount', 'latestInsight',
            'activeCrop', 'crops', 'activeCropCount', 'totalCropCount',
            'totalReadings', 'idealRanges', 'farmHealthScore', 'actionItems'
        ));
    }
}
