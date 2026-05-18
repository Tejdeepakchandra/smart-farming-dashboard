<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\SensorReading;
use App\Services\SensorSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropController extends Controller
{
    public function index()
    {
        $crops = Crop::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Add latest reading for each crop
        foreach ($crops as $crop) {
            $crop->latestReading = SensorReading::where('crop_id', $crop->id)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $crop->readingsCount = SensorReading::where('crop_id', $crop->id)->count();
        }

        return view('crops.index', compact('crops'));
    }

    public function create()
    {
        $simulator = new SensorSimulatorService();
        $cropTypes = $simulator->getAvailableCrops();
        return view('crops.create', compact('cropTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'required|date|after:planting_date',
            'status' => 'required|in:active,harvested',
        ]);

        $validated['user_id'] = Auth::id();

        $crop = Crop::create($validated);

        // Auto-generate initial sensor data batch for the new crop (20 readings)
        if ($validated['status'] === 'active') {
            $simulator = new SensorSimulatorService();
            $profile = $simulator->getProfileForCrop($validated['name']);

            for ($i = 20; $i >= 1; $i--) {
                $data = $this->generateFromProfile($profile);
                SensorReading::create([
                    'user_id' => Auth::id(),
                    'crop_id' => $crop->id,
                    'temperature' => $data['temperature'],
                    'soil_moisture' => $data['soil_moisture'],
                    'humidity' => $data['humidity'],
                    'light_intensity' => $data['light_intensity'],
                    'rainfall' => $data['rainfall'],
                    'recorded_at' => now()->subMinutes($i * 15),
                ]);
            }
        }

        return redirect()->route('crops.index')
            ->with('success', "Crop '{$crop->name}' added with 20 initial sensor readings!");
    }

    public function show(string $id)
    {
        $crop = Crop::where('_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $sensorReadings = SensorReading::where('crop_id', $crop->id)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at', 'asc')
            ->get();

        $simulator = new SensorSimulatorService();
        $idealRanges = $simulator->getProfileForCrop($crop->name);

        $latestReading = SensorReading::where('crop_id', $crop->id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        return view('crops.show', compact('crop', 'sensorReadings', 'idealRanges', 'latestReading'));
    }

    public function edit(string $id)
    {
        $crop = Crop::where('_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $simulator = new SensorSimulatorService();
        $cropTypes = $simulator->getAvailableCrops();

        return view('crops.edit', compact('crop', 'cropTypes'));
    }

    public function update(Request $request, string $id)
    {
        $crop = Crop::where('_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_name' => 'required|string|max:255',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'required|date|after:planting_date',
            'status' => 'required|in:active,harvested',
        ]);

        $crop->update($validated);

        return redirect()->route('crops.index')
            ->with('success', 'Crop updated successfully!');
    }

    public function destroy(string $id)
    {
        $crop = Crop::where('_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        SensorReading::where('crop_id', $crop->id)->delete();
        $crop->delete();

        return redirect()->route('crops.index')
            ->with('success', 'Crop deleted successfully!');
    }

    private function generateFromProfile(array $profile): array
    {
        $values = [];
        foreach ($profile as $sensor => $range) {
            $nearIdeal = (mt_rand(1, 100) <= 70);
            $min = $nearIdeal ? $range['ideal_min'] : $range['min'];
            $max = $nearIdeal ? $range['ideal_max'] : $range['max'];
            $values[$sensor] = ($sensor === 'light_intensity')
                ? rand($min, $max)
                : round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 1);
        }
        return $values;
    }
}
