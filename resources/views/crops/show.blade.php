@extends('layouts.app')
@section('title', $crop->name)
@section('page-title', ($emojis[strtolower($crop->name)] ?? '🌱') . ' ' . $crop->name)

@php $emojis = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp

@section('content')
<div class="space-y-5 fade-up">

    <div class="card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <span class="text-5xl">{{ $emojis[strtolower($crop->name)] ?? '🌱' }}</span>
                <div>
                    <h1 class="text-2xl font-extrabold text-white">{{ $crop->name }}</h1>
                    <p class="text-gray-400 text-sm">📍 {{ $crop->field_name }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('crops.edit', $crop->id) }}" class="px-4 py-2 rounded-xl text-sm font-bold text-blue-300" style="background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.2);">✏️ Edit</a>
                <a href="{{ route('crops.index') }}" class="px-4 py-2 rounded-xl text-sm text-gray-400 hover:text-white" style="background: rgba(255,255,255,0.05);">← Back</a>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-xl text-center" style="background: rgba(255,255,255,0.03);">
                <p class="text-[10px] text-gray-500">🌱 Planted</p>
                <p class="text-sm font-bold text-white">{{ $crop->planting_date?->format('d M Y') ?? 'N/A' }}</p>
            </div>
            <div class="p-3 rounded-xl text-center" style="background: rgba(255,255,255,0.03);">
                <p class="text-[10px] text-gray-500">🌾 Harvest</p>
                <p class="text-sm font-bold text-white">{{ $crop->expected_harvest_date?->format('d M Y') ?? 'N/A' }}</p>
            </div>
            <div class="p-3 rounded-xl text-center" style="background: rgba(255,255,255,0.03);">
                <p class="text-[10px] text-gray-500">⏳ Days Left</p>
                <p class="text-sm font-bold {{ $crop->expected_harvest_date?->isFuture() ? 'text-green-400' : 'text-amber-400' }}">{{ $crop->expected_harvest_date ? ($crop->expected_harvest_date->isFuture() ? $crop->expected_harvest_date->diffInDays(now()).'d' : 'Done') : '—' }}</p>
            </div>
            <div class="p-3 rounded-xl text-center" style="background: rgba(255,255,255,0.03);">
                <p class="text-[10px] text-gray-500">📊 Readings</p>
                <p class="text-sm font-bold text-white">{{ $sensorReadings->count() }}</p>
            </div>
        </div>
    </div>

    @if($latestReading)
    <div class="card p-5">
        <h3 class="text-white font-bold mb-4">🎯 How is your {{ $crop->name }} doing?</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @php
                $meta = [
                    'temperature'=>['label'=>'Temperature','unit'=>'°C','emoji'=>'🌡️','good'=>'✅ Perfect','low'=>'🥶 Too Cold','high'=>'🔥 Too Hot'],
                    'soil_moisture'=>['label'=>'Soil Water','unit'=>'%','emoji'=>'💧','good'=>'✅ Good','low'=>'⚠️ Needs Water','high'=>'💦 Too Wet'],
                    'humidity'=>['label'=>'Humidity','unit'=>'%','emoji'=>'☁️','good'=>'✅ Good','low'=>'🏜️ Too Dry','high'=>'😓 Too Humid'],
                    'light_intensity'=>['label'=>'Sunlight','unit'=>'lux','emoji'=>'☀️','good'=>'✅ Good','low'=>'🌑 Low Light','high'=>'☀️ Very Bright'],
                    'rainfall'=>['label'=>'Rainfall','unit'=>'mm','emoji'=>'🌧️','good'=>'✅ Normal','low'=>'☀️ No Rain','high'=>'🌊 Heavy Rain'],
                ];
            @endphp
            @foreach($meta as $key => $m)
                @php
                    $v = $latestReading->{$key};
                    $ir = $idealRanges[$key] ?? null;
                    $st = 'good';
                    if($ir){ if($v < $ir['ideal_min']) $st='low'; elseif($v > $ir['ideal_max']) $st='high'; }
                    $dv = $key==='light_intensity' ? number_format($v) : round($v,1);
                @endphp
                <div class="p-4 rounded-xl text-center" style="background: {{ $st==='good'?'rgba(34,197,94,0.1)':($st==='low'?'rgba(245,158,11,0.1)':'rgba(239,68,68,0.1)') }}; border: 1px solid {{ $st==='good'?'rgba(34,197,94,0.2)':($st==='low'?'rgba(245,158,11,0.2)':'rgba(239,68,68,0.2)') }};">
                    <p class="text-2xl mb-1">{{ $m['emoji'] }}</p>
                    <p class="text-xl font-black {{ $st==='good'?'text-green-400':($st==='low'?'text-amber-400':'text-red-400') }}">{{ $dv }}</p>
                    <p class="text-[10px] text-gray-500">{{ $m['label'] }} ({{ $m['unit'] }})</p>
                    <p class="text-[11px] font-bold mt-1 {{ $st==='good'?'text-green-400':($st==='low'?'text-amber-400':'text-red-400') }}">{{ $m[$st] }}</p>
                    @if($ir)<p class="text-[9px] text-gray-600 mt-1">Best: {{ $ir['ideal_min'] }}–{{ $ir['ideal_max'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card p-5">
        <h3 class="text-white font-bold mb-3">📈 Sensor Trends — Last 24 Hours</h3>
        @if($sensorReadings->count() > 0)
            <div style="height:260px;"><canvas id="cropChart"></canvas></div>
        @else
            <div class="text-center py-8"><p class="text-3xl mb-2">📡</p><p class="text-gray-500 text-sm">No recent data. Go to Dashboard → "New Reading"</p></div>
        @endif
    </div>
</div>
@endsection

@if($sensorReadings->count() > 0)
@push('scripts')
<script>
new Chart(document.getElementById('cropChart').getContext('2d'),{type:'line',data:{
    labels:{!! json_encode($sensorReadings->pluck('recorded_at')->map(fn($d)=>$d->format('H:i'))) !!},
    datasets:[
        {label:'🌡️ Temp',data:{!! json_encode($sensorReadings->pluck('temperature')) !!},borderColor:'#f97316',backgroundColor:'rgba(249,115,22,0.08)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
        {label:'💧 Moisture',data:{!! json_encode($sensorReadings->pluck('soil_moisture')) !!},borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.08)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
        {label:'☁️ Humidity',data:{!! json_encode($sensorReadings->pluck('humidity')) !!},borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,0.08)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
        {label:'🌧️ Rain',data:{!! json_encode($sensorReadings->pluck('rainfall')) !!},borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.08)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
    ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
    plugins:{legend:{labels:{color:'#9ca3af',font:{size:11,family:'Nunito'},usePointStyle:true}}},
    scales:{x:{ticks:{color:'#6b7280',maxTicksLimit:8},grid:{color:'rgba(255,255,255,0.03)'}},y:{ticks:{color:'#6b7280'},grid:{color:'rgba(255,255,255,0.03)'}}}}});
</script>
@endpush
@endif
