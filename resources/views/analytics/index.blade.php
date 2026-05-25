@extends('layouts.app')
@section('title', 'Predictive Analytics')
@section('breadcrumb', 'analytics')

@section('content')
@php $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">📊</span> Predictive Analytics
            </h1>
            <p class="mt-1 text-gray-500">AI-powered yield predictions, risk assessment, irrigation scheduling, and resource economics.</p>
        </div>
        <span class="chip-muted rounded-full px-3 py-1 text-xs font-semibold">🧠 Formula-Based Predictions</span>
    </div>

    @if(count($analytics) === 0)
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center">
            <div class="text-6xl mb-3">📊</div>
            <h3 class="font-display text-xl text-gray-900">No data for analytics</h3>
            <p class="mt-1 text-sm text-gray-500 mb-4">Add crops and generate sensor readings to see predictions.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary inline-flex">🏠 Go to Dashboard</a>
        </div>
    @else

    <!-- ═══════════ FARM FINANCIAL SUMMARY ═══════════ -->
    <div class="animate-fade-up">
        <h2 class="font-display text-xl text-gray-900 mb-4">💰 Farm Financial Overview</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">💧</span>
                <span class="font-display text-lg text-blue-600">{{ number_format($farmSummary['totalWaterUsage']) }}L</span>
                <p class="text-[10px] text-gray-400">Total Water Used</p>
            </div>
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">💸</span>
                <span class="font-display text-lg text-red-500">₹{{ number_format($farmSummary['totalCost']) }}</span>
                <p class="text-[10px] text-gray-400">Investment</p>
            </div>
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">💰</span>
                <span class="font-display text-lg text-green-600">₹{{ number_format($farmSummary['totalRevenue']) }}</span>
                <p class="text-[10px] text-gray-400">Expected Revenue</p>
            </div>
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">📈</span>
                <span class="font-display text-lg {{ $farmSummary['totalProfit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">₹{{ number_format($farmSummary['totalProfit']) }}</span>
                <p class="text-[10px] text-gray-400">Net Profit</p>
            </div>
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">💧</span>
                <span class="font-display text-lg text-cyan-500">{{ $farmSummary['avgWaterSavings'] }}%</span>
                <p class="text-[10px] text-gray-400">Water Savings</p>
            </div>
            <div class="hiq-card p-4 text-center">
                <span class="text-2xl block mb-1">🤖</span>
                <span class="font-display text-lg text-purple-600">{{ $farmSummary['aiOptimizationScore'] }}%</span>
                <p class="text-[10px] text-gray-400">AI Score</p>
            </div>
        </div>
    </div>

    <!-- ═══════════ PER-CROP ANALYTICS ═══════════ -->
    @foreach($analytics as $idx => $a)
        @php $emoji = $em[strtolower($a['crop']->name)] ?? '🌱'; @endphp
        <div class="hiq-card p-6 animate-fade-up" style="animation-delay: {{ $idx * 80 + 100 }}ms;">
            <!-- Crop Header -->
            <div class="flex items-center justify-between mb-5 pb-4" style="border-bottom: 1px solid var(--border);">
                <div class="flex items-center gap-3">
                    <span class="text-4xl">{{ $emoji }}</span>
                    <div>
                        <h2 class="font-display text-lg text-gray-900">{{ $a['crop']->name }}</h2>
                        <p class="text-xs text-gray-400">📍 {{ $a['crop']->field_name }} · {{ $a['areaAcres'] }} acre{{ $a['areaAcres'] != 1 ? 's' : '' }} · {{ $a['readingsCount'] }} readings</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative w-14 h-14">
                        <svg class="w-14 h-14 -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" stroke="#f1f5f9" stroke-width="7" fill="none"/>
                            <circle cx="50" cy="50" r="42" stroke="{{ $a['healthScore'] >= 80 ? '#22c55e' : ($a['healthScore'] >= 50 ? '#f59e0b' : '#ef4444') }}" stroke-width="7" fill="none" stroke-dasharray="{{ 2 * 3.14159 * 42 }}" stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $a['healthScore'] / 100) }}" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="font-display text-sm {{ $a['healthScore'] >= 80 ? 'text-green-600' : ($a['healthScore'] >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $a['healthScore'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- 1. YIELD PREDICTION -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">🌾 Yield Prediction</h3>
                    <div class="rounded-2xl p-4" style="background: rgba(34,197,94,0.04); border: 1px solid rgba(34,197,94,0.12);">
                        <div class="font-display text-3xl text-green-600 tabular-nums">{{ number_format($a['predictedYield']) }}<span class="text-sm text-gray-400 ml-1">kg</span></div>
                        <p class="text-[11px] text-gray-400 mt-1">Base: {{ number_format($a['economics']['yield_kg']) }}kg/acre × {{ $a['areaAcres'] }} acre{{ $a['areaAcres'] != 1 ? 's' : '' }}</p>
                        <div class="mt-2">
                            <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                                <span>Yield Factor</span><span>{{ $a['yieldMultiplier'] }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100"><div class="h-full rounded-full bg-green-500" style="width: {{ $a['yieldMultiplier'] }}%;"></div></div>
                        </div>
                        <div class="mt-2">
                            <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                                <span>Growth Progress</span><span>{{ $a['growthProgress'] }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100"><div class="h-full rounded-full bg-blue-400" style="width: {{ $a['growthProgress'] }}%;"></div></div>
                        </div>
                    </div>
                </div>

                <!-- 2. RISK ASSESSMENT -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">⚠️ Risk Assessment</h3>
                    <div class="space-y-2">
                        @php
                            $riskMeta = [
                                'drought'=>['🏜️','Drought','Soil too dry for roots'],
                                'fungal'=>['🍄','Fungal Disease','High humidity breeding fungi'],
                                'heat'=>['🔥','Heat Stress','Temperature above ideal'],
                                'flood'=>['🌊','Flood','Excessive rainfall/water'],
                            ];
                        @endphp
                        @foreach($a['risks'] as $rKey => $rVal)
                            @php $rm = $riskMeta[$rKey]; @endphp
                            <div class="rounded-xl p-2.5" style="background: {{ $rVal > 50 ? 'rgba(239,68,68,0.06)' : ($rVal > 20 ? 'rgba(245,158,11,0.06)' : 'rgba(34,197,94,0.04)') }}; border: 1px solid {{ $rVal > 50 ? 'rgba(239,68,68,0.12)' : ($rVal > 20 ? 'rgba(245,158,11,0.12)' : 'rgba(34,197,94,0.08)') }};">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-gray-700">{{ $rm[0] }} {{ $rm[1] }}</span>
                                    <span class="text-xs font-bold {{ $rVal > 50 ? 'text-red-500' : ($rVal > 20 ? 'text-amber-500' : 'text-green-500') }}">{{ $rVal }}%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100"><div class="h-full rounded-full transition-all" style="width: {{ $rVal }}%; background: {{ $rVal > 50 ? '#ef4444' : ($rVal > 20 ? '#f59e0b' : '#22c55e') }};"></div></div>
                                <p class="text-[10px] text-gray-400 mt-1">{{ $rm[2] }}</p>
                            </div>
                        @endforeach
                        @if(empty($a['risks']))
                            <div class="rounded-xl p-3 text-center text-xs text-gray-400" style="background: var(--muted);">No risk data — generate sensor readings first</div>
                        @endif
                    </div>
                </div>

                <!-- 3. IRRIGATION PREDICTION -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">💧 Irrigation Schedule</h3>
                    <div class="rounded-2xl p-4" style="background: rgba(59,130,246,0.04); border: 1px solid rgba(59,130,246,0.12);">
                        <div class="text-center mb-3">
                            <span class="text-3xl">{{ $a['nextIrrigationHours'] === 0 ? '🚿' : '⏰' }}</span>
                            <div class="font-display text-2xl text-blue-600 mt-1">
                                @if($a['nextIrrigationHours'] === 0 || $a['nextIrrigationHours'] === 'N/A')
                                    NOW
                                @else
                                    {{ $a['nextIrrigationHours'] }}h
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-400">{{ $a['nextIrrigationHours'] === 0 ? 'Irrigation needed now!' : 'Until next watering' }}</p>
                        </div>
                        <div class="space-y-1.5 text-[11px]">
                            <div class="flex justify-between"><span class="text-gray-400">Daily water need</span><span class="font-semibold text-gray-700">{{ number_format($a['waterPerDay']) }}L</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Total consumed</span><span class="font-semibold text-blue-600">{{ number_format($a['waterUsed']) }}L</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Water savings</span><span class="font-semibold text-green-600">{{ $a['waterSavings'] }}% ✅</span></div>
                        </div>
                    </div>
                </div>

                <!-- 4. ECONOMICS -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">💰 Financial Estimate</h3>
                    <div class="rounded-2xl p-4" style="background: rgba(168,85,247,0.04); border: 1px solid rgba(168,85,247,0.12);">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-400">💸 Investment <span class="text-[9px] px-1 py-0.5 rounded bg-gray-100">{{ $a['costSource'] }}</span></span>
                                <span class="font-display text-sm text-red-500">₹{{ number_format($a['costEstimate']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-400">💰 Exp. Revenue</span>
                                <span class="font-display text-sm text-green-600">₹{{ number_format($a['revenueEstimate']) }}</span>
                            </div>
                            <div class="pt-2" style="border-top: 1px solid rgba(168,85,247,0.12);">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-gray-700">📈 Net Profit</span>
                                    <span class="font-display text-lg {{ $a['profit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">₹{{ number_format($a['profit']) }}</span>
                                </div>
                            </div>
                            <div class="rounded-lg p-2 mt-2 text-center" style="background: rgba(34,197,94,0.06);">
                                <span class="text-xs font-semibold text-green-700">ROI: {{ $a['economics']['cost_per_acre'] > 0 ? round(($a['profit'] / $a['economics']['cost_per_acre']) * 100) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Revenue Comparison Chart -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 300ms;">
        <h2 class="font-display text-lg text-gray-900 mb-4">📊 Revenue vs Cost Comparison</h2>
        <div style="height: 300px;"><canvas id="revenueChart"></canvas></div>
    </div>

    @endif
</div>
@endsection

@if(count($analytics ?? []) > 0)
@php
    $chartData = [];
    foreach ($analytics as $a) {
        $chartData[] = [
            'name' => $a['crop']->name,
            'cost' => $a['economics']['cost_per_acre'],
            'revenue' => $a['revenueEstimate'],
            'profit' => $a['profit'],
        ];
    }
@endphp
@push('scripts')
<script>
const analyticsData = @json($chartData);
new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: analyticsData.map(a => a.name),
        datasets: [
            { label: '💸 Cost (₹)', data: analyticsData.map(a => a.cost), backgroundColor: 'rgba(239,68,68,0.2)', borderColor: '#ef4444', borderWidth: 1, borderRadius: 8 },
            { label: '💰 Revenue (₹)', data: analyticsData.map(a => a.revenue), backgroundColor: 'rgba(34,197,94,0.2)', borderColor: '#22c55e', borderWidth: 1, borderRadius: 8 },
            { label: '📈 Profit (₹)', data: analyticsData.map(a => a.profit), backgroundColor: 'rgba(139,92,246,0.2)', borderColor: '#8b5cf6', borderWidth: 1, borderRadius: 8 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#64748b', font: { size: 11, family: 'Inter' }, usePointStyle: true, padding: 16 } } },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { display: false } },
            y: { ticks: { color: '#94a3b8', callback: v => '₹' + (v/1000).toFixed(0) + 'k' }, grid: { color: 'rgba(0,0,0,0.04)' } }
        }
    }
});
</script>
@endpush
@endif

