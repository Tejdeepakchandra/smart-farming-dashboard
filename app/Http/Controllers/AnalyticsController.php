<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Support\Facades\Auth;

/**
 * AnalyticsController — Predictive Analytics & Resource Intelligence
 * 
 * Provides data-science-driven insights:
 * - Yield prediction based on sensor history
 * - Irrigation scheduling prediction
 * - Disease/risk probability assessment
 * - Resource consumption analytics (water, cost, revenue)
 */
class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $simulator = new SensorSimulatorService();
        $crops = Crop::where('user_id', $user->id)->where('status', 'active')->get();

        $analytics = [];
        $totalWaterUsage = 0;
        $totalCost = 0;
        $totalRevenue = 0;

        // Revenue/cost per crop (INR) — realistic Indian farming estimates
        $cropEconomics = [
            'rice'      => ['cost_per_acre' => 25000, 'revenue_per_acre' => 55000, 'yield_kg' => 2500, 'water_liters_day' => 8000],
            'wheat'     => ['cost_per_acre' => 18000, 'revenue_per_acre' => 42000, 'yield_kg' => 2000, 'water_liters_day' => 3500],
            'tomato'    => ['cost_per_acre' => 40000, 'revenue_per_acre' => 120000, 'yield_kg' => 15000, 'water_liters_day' => 4000],
            'corn'      => ['cost_per_acre' => 15000, 'revenue_per_acre' => 35000, 'yield_kg' => 3500, 'water_liters_day' => 5000],
            'potato'    => ['cost_per_acre' => 35000, 'revenue_per_acre' => 80000, 'yield_kg' => 12000, 'water_liters_day' => 4500],
            'sugarcane' => ['cost_per_acre' => 30000, 'revenue_per_acre' => 90000, 'yield_kg' => 35000, 'water_liters_day' => 7000],
            'cotton'    => ['cost_per_acre' => 22000, 'revenue_per_acre' => 50000, 'yield_kg' => 800, 'water_liters_day' => 3000],
            'soybean'   => ['cost_per_acre' => 12000, 'revenue_per_acre' => 32000, 'yield_kg' => 1200, 'water_liters_day' => 3500],
        ];

        foreach ($crops as $crop) {
            $profile = $simulator->getProfileForCrop($crop->name);
            $readings = SensorReading::where('crop_id', $crop->id)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get();

            $latest = $readings->first();
            $readingsCount = $readings->count();

            // ─── Health Score ────────────────────
            $healthScore = 0;
            if ($latest) {
                $total = 0; $cnt = 0;
                foreach ($profile as $key => $range) {
                    $val = $latest->{$key};
                    if ($val >= $range['ideal_min'] && $val <= $range['ideal_max']) {
                        $total += 100;
                    } else {
                        $dist = $val < $range['ideal_min'] ? $range['ideal_min'] - $val : $val - $range['ideal_max'];
                        $maxD = $val < $range['ideal_min'] ? $range['ideal_min'] - $range['min'] : $range['max'] - $range['ideal_max'];
                        $total += max(0, round(100 * (1 - ($maxD > 0 ? $dist / $maxD : 1))));
                    }
                    $cnt++;
                }
                $healthScore = $cnt > 0 ? round($total / $cnt) : 0;
            }

            // ─── Yield Prediction ────────────────
            $econ = $cropEconomics[strtolower($crop->name)] ?? $cropEconomics['rice'];
            $areaAcres = $crop->area_acres ?? 1;
            $baseYield = $econ['yield_kg'] * $areaAcres;
            $yieldMultiplier = $healthScore / 100;
            // Factor in days remaining
            $daysTotal = $crop->planting_date && $crop->expected_harvest_date
                ? max(1, $crop->planting_date->diffInDays($crop->expected_harvest_date)) : 120;
            $daysPassed = $crop->planting_date ? $crop->planting_date->diffInDays(now()) : 0;
            $growthProgress = min(1, max(0, $daysPassed / $daysTotal));
            $predictedYield = round($baseYield * $yieldMultiplier * (0.7 + 0.3 * $growthProgress));

            // ─── Risk Assessment ─────────────────
            $risks = [];
            if ($latest) {
                // Drought risk
                $moistDef = max(0, $profile['soil_moisture']['ideal_min'] - ($latest->soil_moisture ?? 50));
                $droughtRisk = min(100, round($moistDef * 3));

                // Fungal risk (high humidity + high moisture)
                $humidExcess = max(0, ($latest->humidity ?? 50) - $profile['humidity']['ideal_max']);
                $fungalRisk = min(100, round($humidExcess * 4));

                // Heat stress
                $tempExcess = max(0, ($latest->temperature ?? 25) - $profile['temperature']['ideal_max']);
                $heatRisk = min(100, round($tempExcess * 8));

                // Flood risk
                $rainExcess = max(0, ($latest->rainfall ?? 0) - $profile['rainfall']['ideal_max']);
                $floodRisk = min(100, round($rainExcess * 3));

                $risks = [
                    'drought' => $droughtRisk,
                    'fungal' => $fungalRisk,
                    'heat' => $heatRisk,
                    'flood' => $floodRisk,
                ];
            }

            // ─── Irrigation Prediction ───────────
            $nextIrrigationHours = 'N/A';
            if ($latest) {
                $currentMoisture = $latest->soil_moisture ?? 50;
                $idealMin = $profile['soil_moisture']['ideal_min'] ?? 40;
                $moistureAboveMin = $currentMoisture - $idealMin;
                // Assume moisture drops ~2% per hour in dry conditions
                $nextIrrigationHours = $moistureAboveMin > 0 ? round($moistureAboveMin / 2) : 0;
            }

            // ─── Resource Analytics (uses user-entered data when available) ──
            $waterPerDay = round($econ['water_liters_day'] * $areaAcres);
            $daysActive = $daysPassed > 0 ? $daysPassed : 1;
            $waterUsed = $waterPerDay * $daysActive;
            $waterSavings = $healthScore >= 70 ? rand(18, 35) : rand(5, 15);

            // Use user-entered investment if available, else fallback to crop default
            $userInvestment = $crop->estimated_investment;
            $costPerAcre = $userInvestment ? round($userInvestment / $areaAcres) : $econ['cost_per_acre'];
            $totalInvestment = $userInvestment ?: round($econ['cost_per_acre'] * $areaAcres);
            $costSource = $userInvestment ? 'Your input' : 'Estimated';

            $revenueEstimate = round($econ['revenue_per_acre'] * $areaAcres * $yieldMultiplier);
            $profit = $revenueEstimate - $totalInvestment;

            $totalWaterUsage += $waterUsed;
            $totalCost += $totalInvestment;
            $totalRevenue += $revenueEstimate;

            $analytics[] = [
                'crop' => $crop,
                'healthScore' => $healthScore,
                'predictedYield' => $predictedYield,
                'baseYield' => $baseYield,
                'yieldMultiplier' => round($yieldMultiplier * 100),
                'growthProgress' => round($growthProgress * 100),
                'risks' => $risks,
                'nextIrrigationHours' => $nextIrrigationHours,
                'waterPerDay' => $waterPerDay,
                'waterUsed' => $waterUsed,
                'waterSavings' => $waterSavings,
                'costEstimate' => $totalInvestment,
                'revenueEstimate' => $revenueEstimate,
                'profit' => $profit,
                'readingsCount' => $readingsCount,
                'areaAcres' => $areaAcres,
                'costSource' => $costSource,
                'economics' => [
                    'cost_per_acre' => $costPerAcre,
                    'revenue_per_acre' => $econ['revenue_per_acre'],
                    'yield_kg' => $econ['yield_kg'],
                    'water_liters_day' => $econ['water_liters_day'],
                ],
            ];
        }

        $farmSummary = [
            'totalWaterUsage' => $totalWaterUsage,
            'totalCost' => $totalCost,
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalRevenue - $totalCost,
            'avgWaterSavings' => count($analytics) > 0
                ? round(collect($analytics)->avg('waterSavings')) : 0,
            'aiOptimizationScore' => count($analytics) > 0
                ? round(collect($analytics)->avg('healthScore') * 0.9 + 10) : 0,
        ];

        return view('analytics.index', compact('analytics', 'farmSummary', 'crops'));
    }
}
