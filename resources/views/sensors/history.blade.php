@extends('layouts.app')
@section('title', 'Sensor History')
@section('breadcrumb', 'sensors')

@section('content')
@php $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">📡</span> Sensor History
            </h1>
            <p class="mt-1 text-gray-500">Browse, filter, and analyze raw IoT sensor data across all your crops.</p>
        </div>
    </div>

    <!-- How sensors work -->
    <div class="hiq-card p-5 animate-fade-up">
        <div class="flex items-start gap-3">
            <span class="text-2xl">🔬</span>
            <div>
                <h3 class="font-display text-sm text-gray-900 mb-1">Understanding Sensor Data</h3>
                <div class="grid md:grid-cols-5 gap-3 mt-2">
                    <div class="rounded-lg p-2 text-center text-xs" style="background: rgba(249,115,22,0.06);">
                        <span class="text-lg block">🌡️</span>
                        <span class="font-semibold text-orange-600">Temperature</span>
                        <p class="text-gray-400 mt-0.5">Air warmth in °C around your crop canopy</p>
                    </div>
                    <div class="rounded-lg p-2 text-center text-xs" style="background: rgba(59,130,246,0.06);">
                        <span class="text-lg block">💧</span>
                        <span class="font-semibold text-blue-600">Soil Moisture</span>
                        <p class="text-gray-400 mt-0.5">Water available in soil for roots (%)</p>
                    </div>
                    <div class="rounded-lg p-2 text-center text-xs" style="background: rgba(139,92,246,0.06);">
                        <span class="text-lg block">☁️</span>
                        <span class="font-semibold text-purple-600">Humidity</span>
                        <p class="text-gray-400 mt-0.5">Air moisture level (%) — fungal risk if high</p>
                    </div>
                    <div class="rounded-lg p-2 text-center text-xs" style="background: rgba(234,179,8,0.06);">
                        <span class="text-lg block">☀️</span>
                        <span class="font-semibold text-amber-600">Sunlight</span>
                        <p class="text-gray-400 mt-0.5">Light intensity in lux reaching leaves</p>
                    </div>
                    <div class="rounded-lg p-2 text-center text-xs" style="background: rgba(6,182,212,0.06);">
                        <span class="text-lg block">🌧️</span>
                        <span class="font-semibold text-cyan-600">Rainfall</span>
                        <p class="text-gray-400 mt-0.5">Precipitation in mm — check drainage if high</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="hiq-card p-5 animate-fade-up" style="animation-delay: 60ms;">
        <h3 class="font-display text-sm text-gray-900 mb-3">🔍 Filter Readings</h3>
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">📅 Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="input-field">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">📅 Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="input-field">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">🌱 Crop</label>
                <div class="flex gap-2">
                    <select name="crop_id" class="input-field flex-1">
                        <option value="">All crops</option>
                        @foreach($crops as $c)
                            <option value="{{ $c->id }}" {{ $cropId == $c->id ? 'selected' : '' }}>
                                {{ $em[strtolower($c->name)] ?? '🌱' }} {{ $c->name }} — {{ $c->field_name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary px-5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($readings->count() > 0)
    @php
        // Quick stats from filtered readings
        $avgTemp = round($readings->avg('temperature'), 1);
        $avgMoist = round($readings->avg('soil_moisture'), 1);
        $avgHumid = round($readings->avg('humidity'), 1);
        $avgRain = round($readings->avg('rainfall'), 1);
        $maxTemp = round($readings->max('temperature'), 1);
        $minTemp = round($readings->min('temperature'), 1);
    @endphp

    <!-- Quick stats -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 animate-fade-up" style="animation-delay: 120ms;">
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">📊 Readings</p>
            <p class="font-display text-lg text-gray-900">{{ $readings->count() }}</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">🌡️ Avg Temp</p>
            <p class="font-display text-lg text-orange-500">{{ $avgTemp }}°C</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">🌡️ Min–Max</p>
            <p class="font-display text-sm text-gray-700">{{ $minTemp }}° — {{ $maxTemp }}°</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">💧 Avg Moisture</p>
            <p class="font-display text-lg text-blue-500">{{ $avgMoist }}%</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">☁️ Avg Humidity</p>
            <p class="font-display text-lg text-purple-500">{{ $avgHumid }}%</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <p class="text-[10px] text-gray-400">🌧️ Avg Rain</p>
            <p class="font-display text-lg text-cyan-500">{{ $avgRain }}mm</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 180ms;">
        <h2 class="font-display text-lg text-gray-900 mb-4">📈 Sensor Trends</h2>
        <div style="height: 300px;"><canvas id="historyChart"></canvas></div>
        <div class="mt-4 rounded-xl p-3 text-[11px] text-gray-400" style="background: var(--muted);">
            <strong class="text-gray-500">📖 Tip:</strong> Look for sudden spikes or drops — these indicate weather events or sensor issues. Flat, consistent lines mean your growing conditions are stable.
        </div>
    </div>

    <!-- Data Table -->
    <div class="hiq-card shadow-card overflow-hidden animate-fade-up" style="animation-delay: 240ms;">
        <div class="p-6 pb-3 flex items-center justify-between">
            <div>
                <h2 class="font-display text-lg text-gray-900">📋 Raw Readings</h2>
                <p class="text-xs text-gray-400">Sorted by most recent first</p>
            </div>
            <span class="chip-muted rounded-full px-3 py-1 text-xs font-semibold">{{ $readings->count() }} records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-400" style="background: var(--muted);">
                        <th class="px-6 py-3 font-semibold">#</th>
                        <th class="px-3 py-3 font-semibold">Time</th>
                        <th class="px-3 py-3 font-semibold">Crop</th>
                        <th class="px-3 py-3 font-semibold">🌡️ Temp</th>
                        <th class="px-3 py-3 font-semibold">💧 Moisture</th>
                        <th class="px-3 py-3 font-semibold">☁️ Humidity</th>
                        <th class="px-3 py-3 font-semibold">☀️ Light</th>
                        <th class="px-3 py-3 font-semibold">🌧️ Rain</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border);">
                    @foreach($readings->take(50) as $idx => $r)
                        @php $crop = $crops->firstWhere('id', $r->crop_id); @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-2.5 text-xs text-gray-300 tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2.5 text-xs text-gray-400 tabular-nums">{{ $r->recorded_at->format('d M, H:i') }}</td>
                            <td class="px-3 py-2.5">
                                <span class="inline-flex items-center gap-1.5 text-xs">
                                    {{ $em[strtolower($crop->name ?? '')] ?? '🌱' }}
                                    <span class="text-gray-700 font-medium">{{ $crop->name ?? '' }}</span>
                                    <span class="text-gray-400">({{ $crop->field_name ?? '' }})</span>
                                </span>
                            </td>
                            <td class="px-3 py-2.5 font-semibold text-orange-500 tabular-nums">{{ round($r->temperature, 1) }}°C</td>
                            <td class="px-3 py-2.5 font-semibold text-blue-500 tabular-nums">{{ round($r->soil_moisture, 1) }}%</td>
                            <td class="px-3 py-2.5 font-semibold text-purple-500 tabular-nums">{{ round($r->humidity, 1) }}%</td>
                            <td class="px-3 py-2.5 font-semibold text-amber-500 tabular-nums">{{ number_format($r->light_intensity) }} lux</td>
                            <td class="px-3 py-2.5 font-semibold text-cyan-500 tabular-nums">{{ round($r->rainfall, 1) }}mm</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($readings->count() > 50)
            <div class="px-6 py-3 text-xs text-gray-400" style="border-top: 1px solid var(--border);">Showing 50 of {{ $readings->count() }} readings. Use filters to narrow down results.</div>
        @endif
    </div>
    @else
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center animate-fade-up" style="animation-delay: 120ms;">
            <div class="text-5xl mb-3">📡</div>
            <h3 class="font-display text-xl text-gray-900">No Data Yet</h3>
            <p class="mt-1 text-sm text-gray-500 mb-5">Go to Dashboard → Click "New Reading" to generate IoT sensor data for your crops.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary inline-flex">🏠 Go to Dashboard</a>
        </div>
    @endif
</div>
@endsection

@if($readings->count() > 0)
@push('scripts')
<script>
new Chart(document.getElementById('historyChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($readings->pluck('recorded_at')->map(fn($d) => $d->format('d/m H:i'))) !!},
        datasets: [
            { label: '🌡️ Temperature (°C)', data: {!! json_encode($readings->pluck('temperature')) !!}, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '💧 Moisture (%)', data: {!! json_encode($readings->pluck('soil_moisture')) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '☁️ Humidity (%)', data: {!! json_encode($readings->pluck('humidity')) !!}, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '🌧️ Rain (mm)', data: {!! json_encode($readings->pluck('rainfall')) !!}, borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#64748b', font: { size: 11, family: 'Inter' }, usePointStyle: true, padding: 16 } },
            tooltip: { backgroundColor: 'white', titleColor: '#0f172a', bodyColor: '#64748b', borderColor: '#e2e8f0', borderWidth: 1, padding: 12, cornerRadius: 12 }
        },
        scales: {
            x: { ticks: { color: '#94a3b8', maxTicksLimit: 10 }, grid: { color: 'rgba(0,0,0,0.04)' } },
            y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(0,0,0,0.04)' } }
        }
    }
});
</script>
@endpush
@endif
