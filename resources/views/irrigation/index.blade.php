@extends('layouts.app')
@section('title', 'Smart Irrigation')
@section('breadcrumb', 'irrigation')

@section('content')
@php $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">💧</span> Smart Irrigation Control
            </h1>
            <p class="mt-1 text-gray-500">Automated precision irrigation — manage pumps, valves, and water flow across all crop zones.</p>
        </div>
    </div>

    @if(count($zones) === 0)
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center">
            <div class="text-6xl mb-3">💧</div>
            <h3 class="font-display text-xl text-gray-900">No active crops</h3>
            <p class="mt-1 text-sm text-gray-500 mb-4">Add crops to manage irrigation.</p>
            <a href="{{ route('crops.create') }}" class="btn-primary inline-flex">🌱 Add Crop</a>
        </div>
    @else

    <!-- ═══════════ SYSTEM OVERVIEW ═══════════ -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 animate-fade-up">
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">⚙️</span>
            <span class="font-display text-base text-gray-900 capitalize">{{ $systemStats['irrigationMode'] }}</span>
            <p class="text-[10px] text-gray-400">Mode</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">🔌</span>
            <span class="font-display text-lg {{ $systemStats['activePumps'] > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ $systemStats['activePumps'] }}/{{ $systemStats['totalZones'] }}</span>
            <p class="text-[10px] text-gray-400">Active Pumps</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">💧</span>
            <span class="font-display text-lg text-blue-600">{{ number_format($systemStats['totalWaterToday']) }}L</span>
            <p class="text-[10px] text-gray-400">Water Today</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">📊</span>
            <span class="font-display text-lg text-purple-600">{{ $systemStats['waterPressure'] }} bar</span>
            <p class="text-[10px] text-gray-400">Pressure</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">⚡</span>
            <span class="font-display text-lg text-green-600">{{ $systemStats['systemEfficiency'] }}%</span>
            <p class="text-[10px] text-gray-400">Efficiency</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <span class="text-2xl block mb-1">🕐</span>
            <span class="font-display text-sm text-gray-900">{{ now()->format('g:i A') }}</span>
            <p class="text-[10px] text-gray-400">System Time</p>
        </div>
    </div>

    <!-- ═══════════ MODE SELECTOR ═══════════ -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 60ms;">
        <h2 class="font-display text-base text-gray-900 mb-4">⚙️ Irrigation Mode</h2>
        <div class="grid grid-cols-3 gap-3">
            @php $modes = [
                'manual' => ['icon'=>'✋','title'=>'Manual','desc'=>'Full control. Toggle each pump individually.','color'=>'blue'],
                'automatic' => ['icon'=>'🤖','title'=>'Automatic','desc'=>'Pumps activate when soil moisture drops below ideal.','color'=>'green'],
                'ai' => ['icon'=>'🧠','title'=>'AI Controlled','desc'=>'AI considers weather, crop stage, and soil data.','color'=>'purple'],
            ]; @endphp
            @foreach($modes as $mKey => $m)
                <button onclick="setMode('{{ $mKey }}')" class="rounded-2xl border-2 p-4 text-left transition-all {{ $irrigationMode === $mKey ? 'border-' . $m['color'] . '-400 bg-' . $m['color'] . '-50/50 ring-2 ring-' . $m['color'] . '-200' : 'border-gray-200 hover:border-gray-300' }}" id="mode-{{ $mKey }}">
                    <div class="text-3xl mb-2">{{ $m['icon'] }}</div>
                    <h3 class="font-display text-sm text-gray-900">{{ $m['title'] }}</h3>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $m['desc'] }}</p>
                    @if($irrigationMode === $mKey)
                        <span class="mt-2 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold chip-success">● Active</span>
                    @endif
                </button>
            @endforeach
        </div>
        <span id="mode-status" class="hidden mt-3 block text-sm"></span>
    </div>

    <!-- ═══════════ ZONE CONTROL PANELS ═══════════ -->
    <div class="animate-fade-up" style="animation-delay: 120ms;">
        <h2 class="font-display text-xl text-gray-900 mb-4">🗺️ Zone Control Panels</h2>
        <div class="grid md:grid-cols-2 gap-5">
            @foreach($zones as $i => $zone)
                @php
                    $emoji = $em[strtolower($zone['crop']->name)] ?? '🌱';
                    $moistPct = min(100, max(0, ($zone['currentMoisture'] / 100) * 100));
                    $moistColor = $zone['needsWater'] ? '#ef4444' : ($zone['isOverWatered'] ? '#3b82f6' : '#22c55e');
                @endphp
                <div class="hiq-card p-5 animate-fade-up" style="animation-delay: {{ $i * 60 + 180 }}ms;">
                    <!-- Zone Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ $emoji }}</span>
                            <div>
                                <h3 class="font-display text-base text-gray-900">{{ $zone['crop']->name }} Zone</h3>
                                <p class="text-[11px] text-gray-400">📍 {{ $zone['crop']->field_name }}</p>
                            </div>
                        </div>
                        <!-- Pump Toggle -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">Pump</span>
                            <button onclick="togglePump('{{ $zone['crop']->id }}', {{ $zone['pumpOn'] ? 'false' : 'true' }}, this)" class="relative w-14 h-7 rounded-full transition-colors {{ $zone['pumpOn'] ? 'bg-green-500' : 'bg-gray-300' }}" id="pump-{{ $zone['crop']->id }}">
                                <div class="absolute top-0.5 {{ $zone['pumpOn'] ? 'left-7.5' : 'left-0.5' }} w-6 h-6 rounded-full bg-white shadow-md transition-all" style="{{ $zone['pumpOn'] ? 'transform: translateX(28px);' : '' }}"></div>
                            </button>
                        </div>
                    </div>

                    <!-- Animated Water Flow -->
                    <div class="relative rounded-xl p-4 mb-4" style="background: linear-gradient(90deg, rgba(59,130,246,0.03), rgba(59,130,246,{{ $zone['pumpOn'] ? '0.08' : '0.02' }}));">
                        <!-- Pipeline SVG -->
                        <svg width="100%" height="40" class="mb-2">
                            <!-- Main pipe -->
                            <rect x="0" y="15" width="100%" height="10" rx="5" fill="#e2e8f0"/>
                            @if($zone['pumpOn'])
                                <!-- Water flow animation -->
                                <rect x="0" y="15" width="100%" height="10" rx="5" fill="url(#flow-{{ $i }})" opacity="0.7"/>
                                <defs>
                                    <linearGradient id="flow-{{ $i }}" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.1"/>
                                        <stop offset="50%" stop-color="#3b82f6" stop-opacity="0.6"/>
                                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.1"/>
                                        <animate attributeName="x1" from="-1" to="1" dur="2s" repeatCount="indefinite"/>
                                        <animate attributeName="x2" from="0" to="2" dur="2s" repeatCount="indefinite"/>
                                    </linearGradient>
                                </defs>
                                <!-- Flow dots -->
                                <circle r="4" fill="#3b82f6">
                                    <animateMotion dur="2s" repeatCount="indefinite" path="M0,20 L300,20"/>
                                </circle>
                                <circle r="3" fill="#60a5fa">
                                    <animateMotion dur="2s" repeatCount="indefinite" path="M0,20 L300,20" begin="0.5s"/>
                                </circle>
                            @endif
                            <!-- Valve -->
                            <rect x="48%" y="8" width="16" height="24" rx="4" fill="{{ $zone['pumpOn'] ? '#22c55e' : '#94a3b8' }}" stroke="white" stroke-width="2"/>
                            <text x="50%" y="24" text-anchor="middle" fill="white" font-size="8" font-weight="bold">{{ $zone['pumpOn'] ? 'ON' : 'OFF' }}</text>
                        </svg>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div>
                                <span class="text-xs text-gray-400">Flow Rate</span>
                                <div class="font-display text-sm {{ $zone['pumpOn'] ? 'text-blue-600' : 'text-gray-400' }}">{{ $zone['flowRate'] }} L/min</div>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400">Valve</span>
                                <div class="font-display text-sm {{ $zone['pumpOn'] ? 'text-green-600' : 'text-gray-400' }}">{{ $zone['valveOpen'] }}% Open</div>
                            </div>
                            <div>
                                <span class="text-xs text-gray-400">Today</span>
                                <div class="font-display text-sm text-blue-600">{{ number_format($zone['waterToday']) }}L</div>
                            </div>
                        </div>
                    </div>

                    <!-- Soil Moisture Gauge -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-400">Soil Moisture</span>
                            <span class="font-bold" style="color: {{ $moistColor }};">{{ $zone['currentMoisture'] }}%</span>
                        </div>
                        <div class="relative h-4 rounded-full bg-gray-100 overflow-hidden">
                            <!-- Ideal range zone -->
                            <div class="absolute top-0 h-full bg-green-100" style="left: {{ $zone['idealMin'] }}%; width: {{ $zone['idealMax'] - $zone['idealMin'] }}%;"></div>
                            <!-- Current level -->
                            <div class="absolute top-0 h-full rounded-full transition-all" style="width: {{ $moistPct }}%; background: {{ $moistColor }};"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                            <span>0%</span>
                            <span class="text-green-600 font-semibold">Ideal: {{ $zone['idealMin'] }}–{{ $zone['idealMax'] }}%</span>
                            <span>100%</span>
                        </div>
                    </div>

                    <!-- Status + Next Irrigation -->
                    <div class="flex items-center justify-between pt-3" style="border-top: 1px solid var(--border);">
                        <div class="flex items-center gap-2">
                            @if($zone['needsWater'])
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold chip-destructive animate-pulse-soft">🚿 Needs Water</span>
                            @elseif($zone['isOverWatered'])
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold chip-warning">💦 Over Watered</span>
                            @else
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold chip-success">✅ Optimal</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-400">Next irrigation</span>
                            <div class="text-xs font-semibold {{ $zone['hoursUntilDry'] <= 2 ? 'text-red-500' : 'text-gray-700' }}">{{ $zone['nextIrrigation'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

async function setMode(mode) {
    const st = document.getElementById('mode-status');
    try {
        const r = await fetch('{{ route("irrigation.toggle") }}', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ action: 'mode', value: mode })
        });
        const d = await r.json();
        if (d.success) {
            st.className = 'mt-3 block text-sm text-green-600';
            st.textContent = '✅ ' + d.message;
            setTimeout(() => location.reload(), 600);
        }
    } catch(e) { st.className = 'mt-3 block text-sm text-red-500'; st.textContent = '❌ Connection error'; }
}

async function togglePump(cropId, on, btn) {
    try {
        const r = await fetch('{{ route("irrigation.toggle") }}', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ action: 'pump', crop_id: cropId, value: on })
        });
        const d = await r.json();
        if (d.success) setTimeout(() => location.reload(), 400);
    } catch(e) {}
}
</script>
@endpush
