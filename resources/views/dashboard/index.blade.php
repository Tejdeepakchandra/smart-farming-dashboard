@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'dashboard')

@section('content')
@php
    $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘'];

    // Action item display data
    $actionMeta = [
        'temperature' => [
            'emoji'=>'🌡️','label'=>'Temperature',
            'low_action'=>'Provide mulch or row covers to retain warmth',
            'high_action'=>'Install shade netting or increase irrigation for evaporative cooling',
            'low_icon'=>'❄️','high_icon'=>'🔥',
        ],
        'soil_moisture' => [
            'emoji'=>'💧','label'=>'Soil Moisture',
            'low_action'=>'Irrigate the field immediately — drip irrigation recommended',
            'high_action'=>'Improve drainage channels and reduce watering frequency',
            'low_icon'=>'🚿','high_icon'=>'🌊',
        ],
        'humidity' => [
            'emoji'=>'☁️','label'=>'Air Humidity',
            'low_action'=>'Use misting systems or mulch to conserve ground moisture',
            'high_action'=>'Improve ventilation and spacing to reduce fungal disease risk',
            'low_icon'=>'🏜️','high_icon'=>'🍄',
        ],
        'light_intensity' => [
            'emoji'=>'☀️','label'=>'Sunlight',
            'low_action'=>'Prune nearby shading plants or consider reflective mulch',
            'high_action'=>'Install 40% shade cloth to reduce leaf burn risk',
            'low_icon'=>'🌑','high_icon'=>'😎',
        ],
        'rainfall' => [
            'emoji'=>'🌧️','label'=>'Rainfall',
            'low_action'=>'Supplement with manual irrigation — check soil moisture levels',
            'high_action'=>'Check drainage systems and protect seedlings from waterlogging',
            'low_icon'=>'☀️','high_icon'=>'⛈️',
        ],
    ];

    // Crop care quick tips (shown on per-crop cards)
    $careTips = [
        'rice'=>'Keep paddies flooded 2-5cm. Ideal pH 5.5-6.5.',
        'wheat'=>'Water at crown root stage. Avoid waterlogging.',
        'tomato'=>'Stake plants early. Water at base, not leaves.',
        'corn'=>'Space 30cm apart. Heavy nitrogen feeder.',
        'potato'=>'Hill soil around stems. Harvest when leaves yellow.',
        'sugarcane'=>'Deep furrow planting. Ratoon for 2nd harvest.',
        'cotton'=>'Thin seedlings early. Watch for bollworm.',
        'soybean'=>'Inoculate seeds with rhizobium before sowing.',
    ];
@endphp
<div class="space-y-8">

    <!-- ═══════════ WELCOME BANNER ═══════════ -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 shadow-card animate-fade-up">
        <img src="/images/farm_hero.png" alt="Smart Farm" class="absolute inset-0 w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/60"></div>
        <div class="relative p-7">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <h1 class="font-display text-3xl tracking-tight text-gray-900">
                        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}! <span class="ml-1">👋</span>
                    </h1>
                    <p class="mt-1 text-gray-500">
                        <span class="font-medium text-gray-900">{{ auth()->user()->farm_name ?? 'My Farm' }}</span> · {{ now()->format('l, F j, Y') }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium chip-success">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse-soft"></span>
                            {{ $activeCropCount }} active crop{{ $activeCropCount !== 1 ? 's' : '' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium chip-muted">
                            📊 {{ $totalReadings }} readings
                        </span>
                        @if($unreadAlertCount > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium chip-destructive">
                                🔔 {{ $unreadAlertCount }} unread alert{{ $unreadAlertCount !== 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($activeCropCount > 0)
                        <button onclick="simulateSensor()" id="sim-btn" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            New Reading
                        </button>
                        <button onclick="simulateBatch()" id="batch-btn" class="btn-outline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Fill 24h Data
                        </button>
                    @else
                        <a href="{{ route('crops.create') }}" class="btn-primary">🌱 Add Your First Crop</a>
                    @endif
                </div>
            </div>
            <span id="sim-status" class="hidden mt-3 block text-sm text-gray-500"></span>
        </div>
    </div>

    <!-- ═══════════ FARM HEALTH OVERVIEW ═══════════ -->
    @if($farmHealthScore !== null)
    <div class="animate-fade-up" style="animation-delay: 80ms;">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="font-display text-xl text-gray-900">🏡 Farm Health Overview</h2>
            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $farmHealthScore >= 80 ? 'chip-success' : ($farmHealthScore >= 50 ? 'chip-warning' : 'chip-destructive') }}">
                {{ $farmHealthScore }}%
            </span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Overall Health Score with ring -->
            <div class="hiq-card p-5 flex flex-col items-center justify-center">
                <div class="relative w-24 h-24">
                    <svg class="w-24 h-24 -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="#f1f5f9" stroke-width="8" fill="none"/>
                        <circle cx="50" cy="50" r="42" stroke="{{ $farmHealthScore >= 80 ? '#22c55e' : ($farmHealthScore >= 50 ? '#f59e0b' : '#ef4444') }}" stroke-width="8" fill="none"
                            stroke-dasharray="{{ 2 * 3.14159 * 42 }}" stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $farmHealthScore / 100) }}" stroke-linecap="round" style="transition: stroke-dashoffset 1s ease-out;"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="font-display text-2xl {{ $farmHealthScore >= 80 ? 'text-green-600' : ($farmHealthScore >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $farmHealthScore }}%</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2 font-medium">Farm Health</p>
                <p class="text-[11px] text-gray-400">{{ $farmHealthScore >= 80 ? 'Excellent 🌟' : ($farmHealthScore >= 50 ? 'Needs attention ⚠️' : 'Critical 🚨') }}</p>
            </div>

            <!-- Active Crops -->
            <div class="hiq-card p-5 flex flex-col items-center justify-center text-center">
                <div class="grid h-12 w-12 place-items-center rounded-2xl mb-2" style="background: rgba(34,197,94,0.08);">
                    <span class="text-2xl">🌱</span>
                </div>
                <span class="font-display text-2xl text-gray-900">{{ $activeCropCount }}</span>
                <span class="text-xs text-gray-400 mt-0.5">Active Crops</span>
                <span class="text-[11px] text-gray-400">{{ $totalCropCount }} total</span>
            </div>

            <!-- Total Readings -->
            <div class="hiq-card p-5 flex flex-col items-center justify-center text-center">
                <div class="grid h-12 w-12 place-items-center rounded-2xl mb-2" style="background: rgba(59,130,246,0.08);">
                    <span class="text-2xl">📊</span>
                </div>
                <span class="font-display text-2xl text-gray-900">{{ number_format($totalReadings) }}</span>
                <span class="text-xs text-gray-400 mt-0.5">Sensor Readings</span>
                <span class="text-[11px] text-gray-400">IoT data points</span>
            </div>

            <!-- Unread Alerts -->
            <div class="hiq-card p-5 flex flex-col items-center justify-center text-center">
                <div class="grid h-12 w-12 place-items-center rounded-2xl mb-2" style="background: {{ $unreadAlertCount > 0 ? 'rgba(239,68,68,0.08)' : 'rgba(34,197,94,0.08)' }};">
                    <span class="text-2xl">{{ $unreadAlertCount > 0 ? '🔔' : '✅' }}</span>
                </div>
                <span class="font-display text-2xl text-gray-900">{{ $unreadAlertCount }}</span>
                <span class="text-xs text-gray-400 mt-0.5">Open Alerts</span>
                <span class="text-[11px] text-gray-400">{{ $unreadAlertCount === 0 ? 'All clear!' : 'Need attention' }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- ═══════════ TODAY'S ACTIONS (Recommendations) ═══════════ -->
    @if(count($actionItems) > 0)
    <div class="animate-fade-up" style="animation-delay: 140ms;">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="font-display text-xl text-gray-900">🎯 Today's Actions</h2>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold chip-warning">{{ count($actionItems) }} items</span>
        </div>
        <p class="text-sm text-gray-400 -mt-2 mb-4">Based on current sensor readings — things your crops need right now.</p>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($actionItems as $i => $item)
                @php
                    $meta = $actionMeta[$item['sensor']] ?? null;
                    if (!$meta) continue;
                    $icon = $item['direction'] === 'low' ? $meta['low_icon'] : $meta['high_icon'];
                    $action = $item['direction'] === 'low' ? $meta['low_action'] : $meta['high_action'];
                    $cropEmoji = $em[strtolower($item['crop_name'])] ?? '🌱';
                    $isCritical = $item['severity'] === 'critical';
                    $bg = $isCritical ? 'border-red-100 bg-red-50/40' : 'border-amber-100 bg-amber-50/40';
                    $badge = $isCritical ? 'chip-destructive' : 'chip-warning';
                    $unit = match($item['sensor']) {
                        'temperature' => '°C',
                        'soil_moisture', 'humidity' => '%',
                        'light_intensity' => ' lux',
                        'rainfall' => 'mm',
                        default => ''
                    };
                    $displayVal = $item['sensor'] === 'light_intensity' ? number_format($item['value']) : round($item['value'], 1);
                @endphp
                <div class="rounded-2xl border p-4 {{ $bg }} animate-fade-up" style="animation-delay: {{ $i * 60 + 200 }}ms;">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-white text-xl shadow-soft flex-shrink-0">{{ $icon }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $cropEmoji }} {{ ucfirst($item['crop_name']) }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">{{ $isCritical ? 'Urgent' : 'Action' }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $meta['label'] }}: <span class="font-semibold {{ $item['direction'] === 'low' ? 'text-blue-600' : 'text-red-500' }}">{{ $displayVal }}{{ $unit }}</span>
                                <span class="text-gray-400">(ideal: {{ $item['ideal_min'] }}–{{ $item['ideal_max'] }})</span>
                            </p>
                            <p class="text-xs text-gray-700 mt-2 leading-relaxed font-medium">💡 {{ $action }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @elseif($farmHealthScore !== null && $farmHealthScore >= 80)
    <div class="hiq-card p-6 text-center animate-fade-up" style="animation-delay: 140ms;">
        <div class="text-4xl mb-2">🎉</div>
        <h3 class="font-display text-lg text-gray-900">All sensors in optimal range!</h3>
        <p class="text-sm text-gray-400 mt-1">Your crops are happy. Keep monitoring and stay consistent.</p>
    </div>
    @endif

    <!-- ═══════════ PER-CROP HEALTH DASHBOARD ═══════════ -->
    @if($crops->count() > 0)
    <div class="animate-fade-up" style="animation-delay: 200ms;">
        <h2 class="font-display text-xl text-gray-900 mb-4">🌾 Crop Health Monitor</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($crops as $i => $crop)
                @php $cropEmoji = $em[strtolower($crop->name)] ?? '🌱'; @endphp
                <div class="hiq-card p-5 animate-fade-up" style="animation-delay: {{ $i * 60 + 260 }}ms;">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-3xl">{{ $cropEmoji }}</span>
                            <div>
                                <h3 class="font-display text-base text-gray-900">{{ $crop->name }}</h3>
                                <p class="text-[11px] text-gray-400">📍 {{ $crop->field_name }}</p>
                            </div>
                        </div>
                        @if($crop->healthScore !== null)
                            <div class="relative w-12 h-12">
                                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="42" stroke="#f1f5f9" stroke-width="8" fill="none"/>
                                    <circle cx="50" cy="50" r="42"
                                        stroke="{{ $crop->healthScore >= 80 ? '#22c55e' : ($crop->healthScore >= 50 ? '#f59e0b' : '#ef4444') }}"
                                        stroke-width="8" fill="none" stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                                        stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $crop->healthScore / 100) }}" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="font-bold text-xs {{ $crop->healthScore >= 80 ? 'text-green-600' : ($crop->healthScore >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $crop->healthScore }}%</span>
                                </div>
                            </div>
                        @else
                            <span class="chip-muted rounded-full px-2 py-1 text-[10px]">No data</span>
                        @endif
                    </div>

                    <!-- Sensor indicators -->
                    @if($crop->latestReading)
                        <div class="grid grid-cols-5 gap-1 mb-3">
                            @foreach(['temperature','soil_moisture','humidity','light_intensity','rainfall'] as $sKey)
                                @php
                                    $v = $crop->latestReading->{$sKey};
                                    $ir = $crop->idealRanges[$sKey] ?? null;
                                    $ok = !$ir || ($v >= $ir['ideal_min'] && $v <= $ir['ideal_max']);
                                    $sEmojis = ['temperature'=>'🌡','soil_moisture'=>'💧','humidity'=>'☁','light_intensity'=>'☀','rainfall'=>'🌧'];
                                    $sUnits = ['temperature'=>'°C','soil_moisture'=>'%','humidity'=>'%','light_intensity'=>'lux','rainfall'=>'mm'];
                                    $dv = $sKey === 'light_intensity' ? number_format($v) : round($v,1);
                                @endphp
                                <div class="text-center p-1.5 rounded-lg {{ $ok ? 'bg-green-50' : 'bg-red-50' }}">
                                    <div class="text-[10px]">{{ $sEmojis[$sKey] }}</div>
                                    <div class="text-[11px] font-bold tabular-nums {{ $ok ? 'text-green-700' : 'text-red-600' }}">{{ $dv }}</div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Sensor bar -->
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden mb-3">
                            <div class="h-full rounded-full transition-all" style="width: {{ $crop->healthScore ?? 0 }}%; background: {{ ($crop->healthScore ?? 0) >= 80 ? '#22c55e' : (($crop->healthScore ?? 0) >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                        </div>
                    @endif

                    <!-- Care tip -->
                    <p class="text-[11px] text-gray-400 leading-relaxed mb-3">
                        💡 {{ $careTips[strtolower($crop->name)] ?? 'Monitor regularly and maintain ideal conditions.' }}
                    </p>

                    <!-- Status & Actions -->
                    <div class="flex items-center justify-between pt-3" style="border-top: 1px solid var(--border);">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $crop->status === 'active' ? 'chip-success' : 'chip-warning' }}">{{ $crop->status === 'active' ? '● Growing' : '● Harvested' }}</span>
                        <div class="flex gap-1.5">
                            <a href="{{ route('crops.show', $crop->id) }}" class="text-[11px] font-semibold text-green-600 hover:underline">View Details →</a>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add New Crop CTA -->
            <a href="{{ route('crops.create') }}" class="rounded-3xl border-2 border-dashed border-gray-200 hover:border-green-300 hover:bg-green-50/30 p-5 flex flex-col items-center justify-center text-center transition-all group min-h-[220px]">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-gray-50 group-hover:bg-green-50 mb-3 transition">
                    <svg class="w-6 h-6 text-gray-300 group-hover:text-green-500 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-400 group-hover:text-green-600 transition">Add New Crop</span>
            </a>
        </div>
    </div>
    @endif

    <!-- ═══════════ SENSOR TRENDS + AI ADVICE ═══════════ -->
    <div class="grid gap-6 lg:grid-cols-3 animate-fade-up" style="animation-delay: 300ms;">
        <!-- Chart -->
        <div class="lg:col-span-2 hiq-card p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg text-gray-900">📈 Sensor Trends · Last 24h</h2>
                    <p class="text-xs text-gray-400">Temperature, moisture, humidity, and rainfall over time</p>
                </div>
                <select id="chart-crop-filter" class="input-field text-sm" style="max-width: 200px; width: auto;">
                    <option value="">All crops</option>
                    @foreach($crops as $crop)
                        @php $ce = $em[strtolower($crop->name)] ?? '🌱'; @endphp
                        <option value="{{ $crop->id }}" {{ $activeCrop && $activeCrop->id == $crop->id ? 'selected' : '' }}>
                            {{ $ce }} {{ $crop->name }} — {{ $crop->field_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="height: 300px;"><canvas id="sensorChart"></canvas></div>
            <div id="no-chart" class="hidden text-center py-12">
                <div class="text-4xl mb-2">📊</div>
                <p class="text-sm text-gray-400">Click <strong>"Fill 24h Data"</strong> above to see sensor trends here.</p>
                <p class="text-xs text-gray-400 mt-1">Charts help you spot patterns like overheating or drought stress.</p>
            </div>
            <!-- Chart legend explanation -->
            <div class="mt-4 rounded-xl p-3 text-[11px] text-gray-400 leading-relaxed" style="background: var(--muted);">
                <strong class="text-gray-500">📖 How to read:</strong>
                Flat lines = stable (good). Spikes = sudden changes (check alerts). Overlapping ideal zones appear as shaded regions. If a line stays outside ideal range, take action.
            </div>
        </div>

        <!-- AI Advice Panel -->
        <div class="hiq-card p-6 bg-gradient-card">
            <div class="flex items-center gap-2 mb-3">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-primary text-white shadow-glow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div>
                    <div class="font-display text-sm text-gray-900">AI Crop Advisor</div>
                    <div class="text-xs text-gray-400">Powered by Google Gemini</div>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-1">Get personalized recommendations based on your live sensor data, crop type, and growth stage.</p>

            <div class="rounded-xl p-3 text-[11px] text-gray-400 mb-3" style="background: var(--muted);">
                🧠 AI reads your sensors → compares with ideal ranges → gives 3 actionable tips
            </div>

            <button onclick="generateInsight()" id="ai-btn" class="btn-primary w-full justify-center mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                Ask AI for Advice
            </button>

            <div class="space-y-2" id="insight-content">
                @if($latestInsight)
                    <div class="text-xs text-gray-400 mb-2">
                        <span class="font-semibold text-green-600">{{ $latestInsight->crop->name ?? '' }}</span> · {{ $latestInsight->created_at->diffForHumans() }}
                    </div>
                    @foreach(explode("\n", $latestInsight->ai_response) as $tip)
                        @if(trim($tip))
                            <div class="rounded-xl border border-gray-100 bg-white p-3 text-sm text-gray-700 leading-relaxed">{{ trim($tip) }}</div>
                        @endif
                    @endforeach
                @else
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4 text-xs text-gray-400 text-center">
                        <div class="text-2xl mb-2">🤖</div>
                        Click "Ask AI" to get tailored crop advice.
                    </div>
                @endif
            </div>
            <div id="insight-loading" class="hidden text-center py-6">
                <div class="inline-block w-7 h-7 border-[3px] border-green-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-gray-400 mt-2 text-sm">Analyzing your sensor data…</p>
            </div>
        </div>
    </div>

    <!-- ═══════════ RECENT ALERTS ═══════════ -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 360ms;">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg text-gray-900">🔔 Recent Alerts</h2>
            <a href="{{ route('alerts.index') }}" class="text-sm font-medium text-green-600 hover:underline inline-flex items-center gap-1">
                View all <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
        @if($alerts->where('is_read', false)->count() === 0)
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/30 p-8 text-center text-sm text-gray-400">
                🎉 All clear — no unread alerts. Your farm is running smoothly.
            </div>
        @else
            <div class="space-y-2">
                @foreach($alerts->where('is_read', false)->take(4) as $alert)
                    @php
                        $icon = $alert->type === 'danger' ? '🚨' : ($alert->type === 'warning' ? '⚠️' : 'ℹ️');
                        $tone = $alert->type === 'danger' ? 'border-red-100 bg-red-50/50'
                            : ($alert->type === 'warning' ? 'border-amber-100 bg-amber-50/50' : 'border-blue-100 bg-blue-50/50');
                    @endphp
                    <div class="flex items-start gap-3 rounded-xl border p-3 {{ $tone }}">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-white text-xl flex-shrink-0 shadow-soft">{{ $icon }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-gray-900">{{ $alert->type === 'danger' ? 'Critical' : ($alert->type === 'warning' ? 'Warning' : 'Info') }}</div>
                            <div class="text-xs text-gray-500">{{ $alert->message }}</div>
                        </div>
                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ $alert->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let chart;

async function simulateSensor() {
    const btn = document.getElementById('sim-btn'), st = document.getElementById('sim-status');
    btn.disabled = true; btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Reading…';
    st.classList.remove('hidden');
    try {
        const r = await fetch('{{ route("sensors.simulate") }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:'{}' });
        const d = await r.json();
        st.textContent = d.success ? '✅ ' + d.message : '❌ ' + d.message;
        if (d.success) setTimeout(() => location.reload(), 500);
    } catch(e) { st.textContent = '❌ Connection error'; }
    btn.disabled = false; btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> New Reading';
    setTimeout(() => st.classList.add('hidden'), 4000);
}

async function simulateBatch() {
    const btn = document.getElementById('batch-btn'), st = document.getElementById('sim-status');
    btn.disabled = true; btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Generating…';
    st.classList.remove('hidden');
    try {
        const r = await fetch('{{ route("sensors.simulateBatch") }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({count:20}) });
        const d = await r.json();
        st.textContent = d.success ? '✅ ' + d.message : '❌ ' + d.message;
        if (d.success) setTimeout(() => location.reload(), 500);
    } catch(e) { st.textContent = '❌ Connection error'; }
    btn.disabled = false; btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg> Fill 24h Data';
    setTimeout(() => st.classList.add('hidden'), 4000);
}

async function loadChart(cropId = '') {
    try {
        const r = await fetch(`{{ route('sensors.data') }}?crop_id=${cropId}`);
        const d = await r.json();
        if (!d.labels || d.labels.length === 0) {
            document.getElementById('sensorChart').style.display = 'none';
            document.getElementById('no-chart').classList.remove('hidden');
            return;
        }
        document.getElementById('sensorChart').style.display = 'block';
        document.getElementById('no-chart').classList.add('hidden');
        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('sensorChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [
                    { label: '🌡️ Temperature (°C)', data: d.temperature, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
                    { label: '💧 Soil Moisture (%)', data: d.soil_moisture, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
                    { label: '☁️ Humidity (%)', data: d.humidity, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
                    { label: '🌧️ Rainfall (mm)', data: d.rainfall, borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: '#64748b', font: { size: 11, family: 'Inter' }, usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                    tooltip: {
                        backgroundColor: 'white', titleColor: '#0f172a', bodyColor: '#64748b',
                        borderColor: '#e2e8f0', borderWidth: 1, padding: 12, cornerRadius: 12,
                        bodyFont: { family: 'Inter', size: 12 }, titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                    }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8', maxTicksLimit: 8, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }
                }
            }
        });
    } catch (e) {}
}
loadChart('{{ $activeCrop ? $activeCrop->id : '' }}');
document.getElementById('chart-crop-filter').addEventListener('change', function() { loadChart(this.value); });

async function generateInsight() {
    const c = document.getElementById('insight-content'), l = document.getElementById('insight-loading'), b = document.getElementById('ai-btn');
    c.classList.add('hidden'); l.classList.remove('hidden'); b.disabled = true;
    try {
        const r = await fetch('{{ route("ai-insights.generate") }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:'{}' });
        const d = await r.json();
        l.classList.add('hidden'); c.classList.remove('hidden');
        if (d.success) {
            const tips = d.insight.split('\n').filter(t => t.trim());
            c.innerHTML = `<div class="text-xs text-gray-400 mb-2"><span class="font-semibold text-green-600">${d.crop_name || ''}</span> · just now</div>` +
                tips.map((t, i) => `<div class="rounded-xl border border-gray-100 bg-white p-3 text-sm text-gray-700 animate-fade-up" style="animation-delay:${i*80}ms">${t.replace(/</g,'&lt;')}</div>`).join('');
        } else {
            c.innerHTML = `<p class="text-amber-600 text-sm p-3">${d.message}</p>`;
        }
    } catch (e) {
        l.classList.add('hidden'); c.classList.remove('hidden');
        c.innerHTML = '<p class="text-red-500 text-sm p-3">Connection error. Try again.</p>';
    }
    b.disabled = false;
}
</script>
@endpush
