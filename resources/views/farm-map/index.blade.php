@extends('layouts.app')
@section('title', 'Digital Farm Map')
@section('breadcrumb', 'farm map')

@section('content')
@php $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">🗺️</span> Digital Farm Map
            </h1>
            <p class="mt-1 text-gray-500">Interactive top-view of your farm — a <strong class="text-gray-900">Digital Twin</strong> of your fields with real-time sensor data.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="setView('health')" id="btn-health" class="view-tab active">🌿 Health</button>
            <button onclick="setView('temperature')" id="btn-temperature" class="view-tab">🌡️ Temperature</button>
            <button onclick="setView('moisture')" id="btn-moisture" class="view-tab">💧 Moisture</button>
            <button onclick="setView('risk')" id="btn-risk" class="view-tab">⚠️ Risk</button>
        </div>
    </div>

    @if(count($zones) === 0)
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center">
            <div class="text-6xl mb-3">🗺️</div>
            <h3 class="font-display text-xl text-gray-900">No crops to display</h3>
            <p class="mt-1 text-sm text-gray-500 mb-4">Add crops to see your farm visualized here.</p>
            <a href="{{ route('crops.create') }}" class="btn-primary inline-flex">🌱 Add Crop</a>
        </div>
    @else
    <!-- Farm Grid -->
    <div class="hiq-card p-6 animate-fade-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-base text-gray-900" id="view-title">🌿 Health View — Crop Zone Overview</h2>
            <div class="flex items-center gap-3 text-[11px] text-gray-400">
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-green-400"></span> Healthy</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-amber-400"></span> Warning</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-red-400"></span> Critical</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-blue-400"></span> Water Flow</span>
            </div>
        </div>

        <!-- SVG Farm Map -->
        <div class="relative rounded-2xl overflow-hidden border border-gray-200" style="background: linear-gradient(135deg, #f0fdf4, #ecfdf5);">
            <svg id="farm-svg" viewBox="0 0 800 {{ ceil(count($zones) / 2) * 220 + 60 }}" class="w-full" style="min-height: 400px;">
                <!-- Background pattern -->
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
                    </pattern>
                    <filter id="glow"><feGaussianBlur stdDeviation="3" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    <linearGradient id="waterGrad" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3"/><stop offset="50%" stop-color="#3b82f6" stop-opacity="0.8"/><stop offset="100%" stop-color="#3b82f6" stop-opacity="0.3"/></linearGradient>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>

                <!-- Main irrigation line -->
                <line x1="400" y1="20" x2="400" y2="{{ ceil(count($zones) / 2) * 220 + 40 }}" stroke="url(#waterGrad)" stroke-width="4" stroke-dasharray="10 5">
                    <animate attributeName="stroke-dashoffset" from="0" to="-30" dur="1.5s" repeatCount="indefinite"/>
                </line>
                <text x="400" y="15" text-anchor="middle" fill="#3b82f6" font-size="10" font-weight="bold">💧 MAIN PIPELINE</text>

                @foreach($zones as $i => $zone)
                    @php
                        $col = $i % 2;
                        $row = floor($i / 2);
                        $x = $col === 0 ? 30 : 420;
                        $y = $row * 220 + 40;
                        $w = 350; $h = 190;
                        $hs = $zone['healthScore'] ?? 0;
                        $cropName = $zone['crop']->name;
                        $emoji = $em[strtolower($cropName)] ?? '🌱';
                        $latest = $zone['latest'];

                        // Colors based on health
                        $fill = $hs >= 80 ? 'rgba(34,197,94,0.08)' : ($hs >= 50 ? 'rgba(245,158,11,0.08)' : 'rgba(239,68,68,0.08)');
                        $stroke = $hs >= 80 ? 'rgba(34,197,94,0.4)' : ($hs >= 50 ? 'rgba(245,158,11,0.4)' : 'rgba(239,68,68,0.4)');
                        $dotColor = $hs >= 80 ? '#22c55e' : ($hs >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp

                    <!-- Zone rectangle -->
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $w }}" height="{{ $h }}" rx="16" fill="{{ $fill }}" stroke="{{ $stroke }}" stroke-width="2" class="farm-zone" data-index="{{ $i }}" data-health="{{ $hs }}" data-temp="{{ $latest->temperature ?? 0 }}" data-moisture="{{ $latest->soil_moisture ?? 0 }}" data-risk="{{ count($zone['risks']) }}"/>

                    <!-- Branch irrigation line -->
                    <line x1="400" y1="{{ $y + $h/2 }}" x2="{{ $col === 0 ? $x + $w : $x }}" y2="{{ $y + $h/2 }}" stroke="{{ $zone['needsWater'] ? '#3b82f6' : 'rgba(59,130,246,0.15)' }}" stroke-width="{{ $zone['needsWater'] ? 3 : 1 }}" stroke-dasharray="6 4">
                        @if($zone['needsWater'])
                            <animate attributeName="stroke-dashoffset" from="0" to="{{ $col === 0 ? '20' : '-20' }}" dur="1s" repeatCount="indefinite"/>
                        @endif
                    </line>
                    @if($zone['needsWater'])
                        <text x="{{ $col === 0 ? $x + $w - 10 : $x + 10 }}" y="{{ $y + $h/2 - 6 }}" text-anchor="{{ $col === 0 ? 'end' : 'start' }}" fill="#3b82f6" font-size="9" font-weight="bold">💧 IRRIGATING</text>
                    @endif

                    <!-- Zone header -->
                    <text x="{{ $x + 20 }}" y="{{ $y + 28 }}" fill="#0f172a" font-size="15" font-weight="bold">{{ $emoji }} {{ $cropName }} Zone</text>
                    <text x="{{ $x + 20 }}" y="{{ $y + 44 }}" fill="#94a3b8" font-size="10">📍 {{ $zone['crop']->field_name }}</text>

                    <!-- Health score badge -->
                    <rect x="{{ $x + $w - 70 }}" y="{{ $y + 12 }}" width="55" height="24" rx="12" fill="{{ $dotColor }}"/>
                    <text x="{{ $x + $w - 42 }}" y="{{ $y + 29 }}" text-anchor="middle" fill="white" font-size="11" font-weight="bold">{{ $hs }}%</text>

                    @if($latest)
                        <!-- Sensor readings grid -->
                        <text x="{{ $x + 20 }}" y="{{ $y + 72 }}" fill="#64748b" font-size="10">🌡️ {{ round($latest->temperature, 1) }}°C</text>
                        <text x="{{ $x + 110 }}" y="{{ $y + 72 }}" fill="#64748b" font-size="10">💧 {{ round($latest->soil_moisture, 1) }}%</text>
                        <text x="{{ $x + 200 }}" y="{{ $y + 72 }}" fill="#64748b" font-size="10">☁️ {{ round($latest->humidity, 1) }}%</text>

                        <!-- Moisture bar -->
                        <rect x="{{ $x + 20 }}" y="{{ $y + 85 }}" width="{{ $w - 40 }}" height="8" rx="4" fill="#f1f5f9"/>
                        <rect x="{{ $x + 20 }}" y="{{ $y + 85 }}" width="{{ ($w - 40) * min(1, ($latest->soil_moisture ?? 0) / 100) }}" height="8" rx="4" fill="{{ ($latest->soil_moisture ?? 0) >= ($zone['sensorStatus']['soil_moisture']['ideal_min'] ?? 40) ? '#22c55e' : '#ef4444' }}"/>
                        <text x="{{ $x + 20 }}" y="{{ $y + 107 }}" fill="#94a3b8" font-size="9">Soil Moisture Level</text>

                        <!-- Sensor dots (animated) -->
                        @for($s = 0; $s < 3; $s++)
                            @php $sx = $x + 50 + $s * 120; $sy = $y + 130; @endphp
                            <circle cx="{{ $sx }}" cy="{{ $sy }}" r="6" fill="{{ $dotColor }}" opacity="0.3">
                                <animate attributeName="r" values="6;10;6" dur="2s" begin="{{ $s * 0.5 }}s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" begin="{{ $s * 0.5 }}s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="{{ $sx }}" cy="{{ $sy }}" r="4" fill="{{ $dotColor }}" filter="url(#glow)"/>
                            <text x="{{ $sx }}" y="{{ $sy + 16 }}" text-anchor="middle" fill="#94a3b8" font-size="8">Sensor {{ $s + 1 }}</text>
                        @endfor

                        <!-- Risk badges -->
                        @foreach($zone['risks'] as $ri => $risk)
                            <rect x="{{ $x + 20 + $ri * 100 }}" y="{{ $y + 155 }}" width="90" height="22" rx="11" fill="{{ $risk['severity'] === 'high' ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }}"/>
                            <text x="{{ $x + 65 + $ri * 100 }}" y="{{ $y + 170 }}" text-anchor="middle" fill="{{ $risk['severity'] === 'high' ? '#ef4444' : '#f59e0b' }}" font-size="9" font-weight="bold">{{ $risk['label'] }}</text>
                        @endforeach

                        @if(count($zone['risks']) === 0)
                            <rect x="{{ $x + 20 }}" y="{{ $y + 155 }}" width="90" height="22" rx="11" fill="rgba(34,197,94,0.08)"/>
                            <text x="{{ $x + 65 }}" y="{{ $y + 170 }}" text-anchor="middle" fill="#22c55e" font-size="9" font-weight="bold">✅ All Good</text>
                        @endif
                    @else
                        <text x="{{ $x + $w/2 }}" y="{{ $y + $h/2 + 10 }}" text-anchor="middle" fill="#94a3b8" font-size="12">No sensor data yet</text>
                    @endif
                @endforeach

                <!-- Compass -->
                <g transform="translate(750, {{ ceil(count($zones) / 2) * 220 + 30 }})">
                    <circle r="18" fill="white" stroke="#e2e8f0" stroke-width="1"/>
                    <text y="-6" text-anchor="middle" fill="#0f172a" font-size="10" font-weight="bold">N</text>
                    <text y="12" text-anchor="middle" fill="#94a3b8" font-size="8">S</text>
                    <text x="-10" y="3" fill="#94a3b8" font-size="8">W</text>
                    <text x="8" y="3" fill="#94a3b8" font-size="8">E</text>
                </g>
            </svg>
        </div>
    </div>

    <!-- Zone Detail Cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 animate-fade-up" style="animation-delay: 100ms;">
        @foreach($zones as $i => $zone)
            @php $emoji = $em[strtolower($zone['crop']->name)] ?? '🌱'; $hs = $zone['healthScore'] ?? 0; @endphp
            <div class="hiq-card p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $emoji }}</span>
                        <div>
                            <h3 class="font-display text-sm text-gray-900">{{ $zone['crop']->name }}</h3>
                            <p class="text-[10px] text-gray-400">{{ $zone['crop']->field_name }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $hs >= 80 ? 'chip-success' : ($hs >= 50 ? 'chip-warning' : 'chip-destructive') }}">{{ $hs }}%</span>
                </div>
                @if($zone['latest'])
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        @foreach(['temperature'=>['🌡','°C'], 'soil_moisture'=>['💧','%'], 'humidity'=>['☁','%']] as $k => $m)
                            @php $st = $zone['sensorStatus'][$k] ?? null; @endphp
                            <div class="text-center p-1.5 rounded-lg {{ $st && $st['ok'] ? 'bg-green-50' : 'bg-red-50' }}">
                                <span class="text-[10px]">{{ $m[0] }}</span>
                                <span class="block text-xs font-bold tabular-nums {{ $st && $st['ok'] ? 'text-green-700' : 'text-red-600' }}">{{ round($zone['latest']->{$k}, 1) }}{{ $m[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @foreach($zone['risks'] as $risk)
                        <div class="text-[11px] font-semibold {{ $risk['severity'] === 'high' ? 'text-red-500' : 'text-amber-500' }}">{{ $risk['label'] }}</div>
                    @endforeach
                    @if($zone['needsWater'])
                        <div class="text-[11px] font-semibold text-blue-500 animate-pulse-soft">💧 Irrigation Active</div>
                    @endif
                @else
                    <p class="text-xs text-gray-400">No readings yet</p>
                @endif
                <a href="{{ route('crops.show', $zone['crop']->id) }}" class="mt-2 block text-center text-xs font-semibold text-green-600 hover:underline">View Details →</a>
            </div>
        @endforeach
    </div>
    @endif
</div>

<style>
.view-tab { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); background: white; color: #64748b; cursor: pointer; transition: all 0.2s; }
.view-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
.view-tab:hover:not(.active) { background: var(--muted); }
</style>
@endsection

@push('scripts')
<script>
const viewTitles = {
    health: '🌿 Health View — Crop Zone Overview',
    temperature: '🌡️ Temperature Heatmap — Thermal Distribution',
    moisture: '💧 Moisture Map — Soil Water Levels',
    risk: '⚠️ Risk Map — Problem Zone Identification',
};
const viewColors = {
    health: z => z.dataset.health >= 80 ? 'rgba(34,197,94,0.12)' : (z.dataset.health >= 50 ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)'),
    temperature: z => { const t = z.dataset.temp; return t > 35 ? 'rgba(239,68,68,0.15)' : (t > 28 ? 'rgba(249,115,22,0.12)' : 'rgba(59,130,246,0.1)'); },
    moisture: z => { const m = z.dataset.moisture; return m < 40 ? 'rgba(239,68,68,0.15)' : (m < 60 ? 'rgba(245,158,11,0.1)' : 'rgba(59,130,246,0.12)'); },
    risk: z => z.dataset.risk > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(34,197,94,0.08)',
};
function setView(v) {
    document.querySelectorAll('.view-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + v).classList.add('active');
    document.getElementById('view-title').textContent = viewTitles[v];
    document.querySelectorAll('.farm-zone').forEach(z => { z.setAttribute('fill', viewColors[v](z)); });
}
</script>
@endpush
