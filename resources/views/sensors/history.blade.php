@extends('layouts.app')
@section('title', 'Sensor History')
@section('page-title', '📊 Sensor Data')

@section('content')
<div class="space-y-5 fade-up">
    <div>
        <h1 class="text-2xl font-extrabold text-white">Sensor History</h1>
        <p class="text-gray-400 text-sm">View past readings from your IoT sensors</p>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-[11px] font-bold text-gray-400 mb-1">🌱 Crop</label>
            <select name="crop_id" class="input-field text-sm py-2 px-3">
                <option value="">All Crops</option>
                @foreach($crops as $c)
                    <option value="{{ $c->id }}" {{ $cropId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 mb-1">📅 From</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="input-field text-sm py-2">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-gray-400 mb-1">📅 To</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="input-field text-sm py-2">
        </div>
        <button type="submit" class="btn-primary px-4 py-2 text-sm">🔍 Filter</button>
    </form>

    @if($readings->count() > 0)
    <!-- Chart -->
    <div class="card p-5">
        <h3 class="text-white font-bold mb-3">📈 Trend Graph</h3>
        <div style="height: 260px;"><canvas id="historyChart"></canvas></div>
    </div>

    <!-- Data Table -->
    <div class="card p-5 overflow-x-auto">
        <h3 class="text-white font-bold mb-3">📋 Data Table ({{ $readings->count() }} readings)</h3>
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="text-gray-500 text-[11px] border-b border-white/5">
                    <th class="py-2 px-3">Time</th>
                    <th class="py-2 px-3">🌡️ Temp</th>
                    <th class="py-2 px-3">💧 Moisture</th>
                    <th class="py-2 px-3">☁️ Humidity</th>
                    <th class="py-2 px-3">☀️ Light</th>
                    <th class="py-2 px-3">🌧️ Rain</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readings->take(50) as $r)
                <tr class="border-b border-white/3 hover:bg-white/3">
                    <td class="py-2 px-3 text-gray-400">{{ $r->recorded_at->format('d M, H:i') }}</td>
                    <td class="py-2 px-3 text-orange-400 font-bold">{{ round($r->temperature,1) }}°</td>
                    <td class="py-2 px-3 text-blue-400 font-bold">{{ round($r->soil_moisture,1) }}%</td>
                    <td class="py-2 px-3 text-purple-400 font-bold">{{ round($r->humidity,1) }}%</td>
                    <td class="py-2 px-3 text-yellow-400 font-bold">{{ number_format($r->light_intensity) }}</td>
                    <td class="py-2 px-3 text-cyan-400 font-bold">{{ round($r->rainfall,1) }}mm</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($readings->count() > 50)<p class="text-xs text-gray-600 mt-2">Showing 50 of {{ $readings->count() }}</p>@endif
    </div>
    @else
        <div class="card p-12 text-center">
            <p class="text-5xl mb-3">📡</p>
            <h3 class="text-xl font-extrabold text-white mb-2">No Data Yet</h3>
            <p class="text-gray-400 mb-4">Go to Dashboard → Click "New Reading" to generate sensor data</p>
            <a href="{{ route('dashboard') }}" class="btn-primary px-5 py-2.5 text-sm inline-block">🏠 Go to Dashboard</a>
        </div>
    @endif
</div>
@endsection

@if($readings->count() > 0)
@push('scripts')
<script>
new Chart(document.getElementById('historyChart').getContext('2d'),{type:'line',data:{
    labels:{!! json_encode($readings->pluck('recorded_at')->map(fn($d)=>$d->format('d/m H:i'))) !!},
    datasets:[
        {label:'🌡️ Temp',data:{!! json_encode($readings->pluck('temperature')) !!},borderColor:'#f97316',backgroundColor:'rgba(249,115,22,0.08)',tension:0.4,fill:true,pointRadius:2,borderWidth:2},
        {label:'💧 Moisture',data:{!! json_encode($readings->pluck('soil_moisture')) !!},borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.08)',tension:0.4,fill:true,pointRadius:2,borderWidth:2},
        {label:'☁️ Humidity',data:{!! json_encode($readings->pluck('humidity')) !!},borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,0.08)',tension:0.4,fill:true,pointRadius:2,borderWidth:2},
        {label:'🌧️ Rain',data:{!! json_encode($readings->pluck('rainfall')) !!},borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.08)',tension:0.4,fill:true,pointRadius:2,borderWidth:2},
    ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
    plugins:{legend:{labels:{color:'#9ca3af',font:{size:11,family:'Nunito'},usePointStyle:true}}},
    scales:{x:{ticks:{color:'#6b7280',maxTicksLimit:10},grid:{color:'rgba(255,255,255,0.03)'}},y:{ticks:{color:'#6b7280'},grid:{color:'rgba(255,255,255,0.03)'}}}}});
</script>
@endpush
@endif
