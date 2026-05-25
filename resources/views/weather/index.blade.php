@extends('layouts.app')
@section('title', 'Weather Forecast')
@section('breadcrumb', 'weather')

@section('content')
@php
    $current = $forecast['current'] ?? [];
    $daily = $forecast['daily'] ?? [];
    $wCodes = [0=>'☀️ Clear',1=>'🌤️ Mostly Clear',2=>'⛅ Partly Cloudy',3=>'☁️ Overcast',45=>'🌫️ Fog',48=>'🌫️ Fog',51=>'🌦️ Drizzle',53=>'🌧️ Rain',55=>'🌧️ Heavy Rain',61=>'🌧️ Rain',63=>'🌧️ Moderate Rain',65=>'🌧️ Heavy Rain',80=>'🌦️ Showers',81=>'🌧️ Heavy Showers',82=>'⛈️ Violent Showers',95=>'⛈️ Thunderstorm',96=>'⛈️ Hailstorm',99=>'⛈️ Severe Storm'];
    $currentCode = $current['weathercode'] ?? 0;
    $currentDesc = $wCodes[$currentCode] ?? '🌤️ Unknown';
@endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">🌤️</span> Weather Forecast
            </h1>
            <p class="mt-1 text-gray-500">7-day weather forecast powered by <strong>Open-Meteo API</strong> with AI farming suggestions.</p>
        </div>
        <span class="chip-muted rounded-full px-3 py-1 text-xs font-semibold">📍 Hyderabad, India</span>
    </div>

    <!-- Current Weather Card -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 shadow-card animate-fade-up" style="background: linear-gradient(135deg, #ecfdf5, #eff6ff, #faf5ff);">
        <div class="p-8">
            <div class="flex flex-wrap items-center gap-8">
                <div class="text-center">
                    <div class="text-7xl mb-2">{{ explode(' ', $currentDesc)[0] }}</div>
                    <span class="font-display text-5xl text-gray-900">{{ round($current['temperature_2m'] ?? 0) }}°C</span>
                    <p class="text-sm text-gray-500 mt-1">{{ explode(' ', $currentDesc, 2)[1] ?? '' }}</p>
                </div>
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="hiq-card p-4 text-center">
                        <span class="text-2xl block mb-1">💧</span>
                        <span class="font-display text-xl text-gray-900">{{ round($current['relative_humidity_2m'] ?? 0) }}%</span>
                        <p class="text-[11px] text-gray-400">Humidity</p>
                    </div>
                    <div class="hiq-card p-4 text-center">
                        <span class="text-2xl block mb-1">💨</span>
                        <span class="font-display text-xl text-gray-900">{{ round($current['windspeed_10m'] ?? 0) }}</span>
                        <p class="text-[11px] text-gray-400">Wind (km/h)</p>
                    </div>
                    <div class="hiq-card p-4 text-center">
                        <span class="text-2xl block mb-1">🌧️</span>
                        <span class="font-display text-xl text-gray-900">{{ round($current['precipitation'] ?? 0, 1) }}</span>
                        <p class="text-[11px] text-gray-400">Rain (mm)</p>
                    </div>
                    <div class="hiq-card p-4 text-center">
                        <span class="text-2xl block mb-1">☀️</span>
                        <span class="font-display text-xl text-gray-900">{{ round($daily['uv_index_max'][0] ?? 0, 1) }}</span>
                        <p class="text-[11px] text-gray-400">UV Index</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Farming Suggestions -->
    @if(count($suggestions) > 0)
    <div class="animate-fade-up" style="animation-delay: 80ms;">
        <h2 class="font-display text-xl text-gray-900 mb-4">🤖 AI Farming Suggestions</h2>
        <p class="text-sm text-gray-400 -mt-2 mb-4">Smart recommendations based on upcoming weather patterns.</p>
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($suggestions as $i => $s)
                @php
                    $bg = $s['priority'] === 'high' ? 'border-red-100 bg-red-50/40' : ($s['priority'] === 'medium' ? 'border-amber-100 bg-amber-50/40' : 'border-blue-100 bg-blue-50/40');
                    $badge = $s['priority'] === 'high' ? 'chip-destructive' : ($s['priority'] === 'medium' ? 'chip-warning' : 'chip-muted');
                @endphp
                <div class="rounded-2xl border p-4 {{ $bg }} animate-fade-up" style="animation-delay: {{ $i * 60 + 140 }}ms;">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-white text-xl shadow-soft flex-shrink-0">{{ $s['icon'] }}</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-display text-sm text-gray-900">{{ $s['title'] }}</h3>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">{{ ucfirst($s['priority']) }}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $s['detail'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 7-Day Forecast -->
    <div class="animate-fade-up" style="animation-delay: 160ms;">
        <h2 class="font-display text-xl text-gray-900 mb-4">📅 7-Day Forecast</h2>
        <div class="grid gap-3">
            @for($d = 0; $d < min(7, count($daily['time'] ?? [])); $d++)
                @php
                    $date = \Carbon\Carbon::parse($daily['time'][$d]);
                    $maxT = round($daily['temperature_2m_max'][$d] ?? 0);
                    $minT = round($daily['temperature_2m_min'][$d] ?? 0);
                    $rain = round($daily['precipitation_sum'][$d] ?? 0, 1);
                    $rainProb = $daily['precipitation_probability_max'][$d] ?? 0;
                    $wind = round($daily['windspeed_10m_max'][$d] ?? 0);
                    $uv = round($daily['uv_index_max'][$d] ?? 0, 1);
                    $wc = $daily['weathercode'][$d] ?? 0;
                    $wInfo = $wCodes[$wc] ?? '🌤️ Unknown';
                    $wIcon = explode(' ', $wInfo)[0];
                    $wDesc = explode(' ', $wInfo, 2)[1] ?? '';
                    $isToday = $d === 0;
                @endphp
                <div class="hiq-card p-4 flex flex-wrap items-center gap-4 {{ $isToday ? 'ring-2 ring-green-200' : '' }}">
                    <!-- Day -->
                    <div class="w-24">
                        <span class="font-display text-sm text-gray-900">{{ $isToday ? 'Today' : $date->format('D') }}</span>
                        <p class="text-[11px] text-gray-400">{{ $date->format('M d') }}</p>
                    </div>
                    <!-- Icon -->
                    <div class="text-3xl w-12 text-center">{{ $wIcon }}</div>
                    <!-- Desc -->
                    <div class="flex-1 min-w-[100px]">
                        <span class="text-sm font-medium text-gray-700">{{ $wDesc }}</span>
                    </div>
                    <!-- Temp -->
                    <div class="w-28">
                        <div class="flex items-center gap-1">
                            <span class="font-display text-base text-orange-500">{{ $maxT }}°</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm text-blue-400">{{ $minT }}°</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 mt-1 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-400 via-green-400 to-orange-400" style="width: {{ min(100, $maxT * 2.5) }}%;"></div>
                        </div>
                    </div>
                    <!-- Rain -->
                    <div class="w-20 text-center">
                        <span class="text-sm font-semibold {{ $rain > 10 ? 'text-blue-500' : 'text-gray-500' }}">{{ $rain }}mm</span>
                        <p class="text-[10px] text-gray-400">{{ $rainProb }}% prob</p>
                    </div>
                    <!-- Wind -->
                    <div class="w-16 text-center">
                        <span class="text-sm text-gray-500">💨 {{ $wind }}</span>
                        <p class="text-[10px] text-gray-400">km/h</p>
                    </div>
                    <!-- UV -->
                    <div class="w-14 text-center">
                        <span class="text-sm font-semibold {{ $uv > 8 ? 'text-red-500' : ($uv > 5 ? 'text-amber-500' : 'text-green-500') }}">{{ $uv }}</span>
                        <p class="text-[10px] text-gray-400">UV</p>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Temperature Chart -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 240ms;">
        <h2 class="font-display text-lg text-gray-900 mb-4">📈 Temperature & Rainfall Trend</h2>
        <div style="height: 280px;"><canvas id="weatherChart"></canvas></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const daily = @json($forecast['daily'] ?? []);
if (daily.time && daily.time.length) {
    new Chart(document.getElementById('weatherChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: daily.time.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en', {weekday:'short',month:'short',day:'numeric'}); }),
            datasets: [
                { type: 'line', label: '🌡️ Max Temp (°C)', data: daily.temperature_2m_max, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.06)', tension: 0.4, fill: true, pointRadius: 4, borderWidth: 2, yAxisID: 'y' },
                { type: 'line', label: '🌡️ Min Temp (°C)', data: daily.temperature_2m_min, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.06)', tension: 0.4, fill: true, pointRadius: 4, borderWidth: 2, yAxisID: 'y' },
                { type: 'bar', label: '🌧️ Rainfall (mm)', data: daily.precipitation_sum, backgroundColor: 'rgba(6,182,212,0.3)', borderColor: '#06b6d4', borderWidth: 1, yAxisID: 'y1', borderRadius: 6 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { color: '#64748b', font: { size: 11, family: 'Inter' }, usePointStyle: true, padding: 16 } } },
            scales: {
                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y: { position: 'left', ticks: { color: '#94a3b8' }, grid: { color: 'rgba(0,0,0,0.04)' }, title: { display: true, text: 'Temperature (°C)', color: '#94a3b8' } },
                y1: { position: 'right', ticks: { color: '#06b6d4' }, grid: { display: false }, title: { display: true, text: 'Rainfall (mm)', color: '#06b6d4' } },
            }
        }
    });
}
</script>
@endpush
