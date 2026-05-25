@extends('layouts.app')
@section('title', 'My Crops')
@section('breadcrumb', 'crops')

@section('content')
@php
    $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘'];
    $careTips = [
        'rice'=>'Water-loving crop. Keep paddies flooded 2-5cm.',
        'wheat'=>'Cool-season grain. Water at crown root stage.',
        'tomato'=>'Warm-season fruit. Stake early, water at base.',
        'corn'=>'Heavy nitrogen feeder. Space 30cm apart.',
        'potato'=>'Hill soil around stems as they grow.',
        'sugarcane'=>'Long-season crop (10-18 months). Deep furrows.',
        'cotton'=>'Heat-tolerant fiber crop. Thin seedlings early.',
        'soybean'=>'Nitrogen-fixing legume. Inoculate seeds before sowing.',
    ];
@endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">🌱</span> My Crops
            </h1>
            <p class="mt-1 text-gray-500">Manage your fields, track health scores, and monitor each crop individually.</p>
        </div>
        <a href="{{ route('crops.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add New Crop
        </a>
    </div>

    <!-- Summary bar -->
    @if($crops->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">🌾</span>
            <p class="font-display text-lg text-gray-900">{{ $crops->count() }}</p>
            <p class="text-[11px] text-gray-400">Total Crops</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">🟢</span>
            <p class="font-display text-lg text-green-600">{{ $crops->where('status', 'active')->count() }}</p>
            <p class="text-[11px] text-gray-400">Active</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">🟡</span>
            <p class="font-display text-lg text-amber-600">{{ $crops->where('status', 'harvested')->count() }}</p>
            <p class="text-[11px] text-gray-400">Harvested</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">📊</span>
            @php $avgHealth = $crops->whereNotNull('healthScore')->avg('healthScore'); @endphp
            <p class="font-display text-lg {{ ($avgHealth ?? 0) >= 80 ? 'text-green-600' : (($avgHealth ?? 0) >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $avgHealth ? round($avgHealth) . '%' : '--' }}</p>
            <p class="text-[11px] text-gray-400">Avg Health</p>
        </div>
    </div>
    @endif

    @if($crops->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($crops as $i => $crop)
                @php $emoji = $em[strtolower($crop->name)] ?? '🌱'; @endphp
                <div class="hiq-card p-5 animate-fade-up flex flex-col" style="animation-delay: {{ $i * 60 }}ms;">
                    <!-- Top -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-green-50 border border-green-100">
                                <span class="text-3xl">{{ $emoji }}</span>
                            </div>
                            <div>
                                <h3 class="font-display text-lg text-gray-900">{{ $crop->name }}</h3>
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    {{ $crop->field_name }}
                                </p>
                            </div>
                        </div>
                        <!-- Health Score Ring -->
                        @if($crop->healthScore !== null)
                        <div class="relative w-12 h-12 flex-shrink-0">
                            <svg class="w-12 h-12 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" stroke="#f1f5f9" stroke-width="8" fill="none"/>
                                <circle cx="50" cy="50" r="42"
                                    stroke="{{ $crop->healthScore >= 80 ? '#22c55e' : ($crop->healthScore >= 50 ? '#f59e0b' : '#ef4444') }}"
                                    stroke-width="8" fill="none" stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $crop->healthScore / 100) }}" stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="font-bold text-[11px] {{ $crop->healthScore >= 80 ? 'text-green-600' : ($crop->healthScore >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $crop->healthScore }}%</span>
                            </div>
                        </div>
                        @else
                        <span class="chip-muted rounded-full px-2 py-0.5 text-[10px]">No data</span>
                        @endif
                    </div>

                    <!-- Sensor mini-grid -->
                    @if($crop->latestReading)
                        <div class="grid grid-cols-5 gap-1.5 mb-3">
                            @foreach(['temperature'=>'🌡','soil_moisture'=>'💧','humidity'=>'☁','light_intensity'=>'☀','rainfall'=>'🌧'] as $sKey => $sEm)
                                @php
                                    $v = $crop->latestReading->{$sKey};
                                    $ir = $crop->idealRanges[$sKey] ?? null;
                                    $ok = !$ir || ($v >= $ir['ideal_min'] && $v <= $ir['ideal_max']);
                                    $dv = $sKey === 'light_intensity' ? number_format($v) : round($v,1);
                                @endphp
                                <div class="text-center p-1.5 rounded-lg {{ $ok ? 'bg-green-50' : 'bg-red-50' }}">
                                    <div class="text-[10px]">{{ $sEm }}</div>
                                    <div class="text-[11px] font-bold tabular-nums {{ $ok ? 'text-green-700' : 'text-red-600' }}">{{ $dv }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden mb-3">
                            <div class="h-full rounded-full" style="width: {{ $crop->healthScore ?? 0 }}%; background: {{ ($crop->healthScore ?? 0) >= 80 ? '#22c55e' : (($crop->healthScore ?? 0) >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl mb-3 text-center text-xs text-gray-400" style="background: var(--muted);">
                            No readings yet — click "New Reading" on Dashboard
                        </div>
                    @endif

                    <!-- Care tip -->
                    <p class="text-[11px] text-gray-400 leading-relaxed mb-3 flex-1">
                        💡 {{ $careTips[strtolower($crop->name)] ?? 'Monitor regularly and maintain ideal conditions.' }}
                    </p>

                    <!-- Meta -->
                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-3">
                        <span>📊 {{ $crop->readingsCount }} readings</span>
                        <span>🌱 {{ $crop->planting_date ? $crop->planting_date->format('d M Y') : '' }}</span>
                    </div>

                    <!-- Status & Actions -->
                    <div class="pt-3 flex gap-2" style="border-top: 1px solid var(--border);">
                        <a href="{{ route('crops.show', $crop->id) }}" class="flex-1 text-center text-sm py-2 rounded-lg font-semibold text-green-700 hover:bg-green-100 transition" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.15);">
                            View Details
                        </a>
                        <a href="{{ route('crops.edit', $crop->id) }}" class="text-center text-sm py-2 px-3 rounded-lg font-semibold text-blue-700 hover:bg-blue-100 transition" style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                            ✏️
                        </a>
                        <form method="POST" action="{{ route('crops.destroy', $crop->id) }}" onsubmit="return confirm('Delete this crop and all its sensor data?')">
                            @csrf @method('DELETE')
                            <button class="text-sm py-2 px-3 rounded-lg font-semibold text-red-600 hover:bg-red-100 transition" style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.12);">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            <!-- Add New Crop CTA -->
            <a href="{{ route('crops.create') }}" class="rounded-3xl border-2 border-dashed border-gray-200 hover:border-green-300 hover:bg-green-50/30 p-5 flex flex-col items-center justify-center text-center transition-all group min-h-[280px]">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-gray-50 group-hover:bg-green-50 mb-3 transition">
                    <svg class="w-7 h-7 text-gray-300 group-hover:text-green-500 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-400 group-hover:text-green-600 transition">Add New Crop</span>
                <span class="text-xs text-gray-300 group-hover:text-gray-400 mt-1 transition">We'll set up sensors automatically</span>
            </a>
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center">
            <img src="/images/crop_types.png" alt="Crop Types" class="w-64 h-auto mx-auto rounded-2xl mb-5 opacity-80">
            <h3 class="font-display text-xl text-gray-900">No crops yet</h3>
            <p class="mt-1 text-sm text-gray-500 mb-5">Start monitoring your farm by adding your first crop. We support Rice, Wheat, Tomato, Corn, Potato, Sugarcane, Cotton, and Soybean.</p>
            <a href="{{ route('crops.create') }}" class="btn-primary inline-flex">🌱 Add First Crop</a>
        </div>
    @endif
</div>
@endsection
