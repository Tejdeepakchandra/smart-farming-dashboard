@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', '🏠 Farm Dashboard')

@section('content')
<div class="space-y-5 fade-up">

    <!-- Welcome -->
    <div class="card p-5" style="background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(5,150,105,0.1));">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white">Hello, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-gray-400 text-sm mt-1">{{ auth()->user()->farm_name ?? 'My Farm' }} • {{ now()->format('l, d M Y') }}</p>
            </div>
            <div class="flex gap-3">
                <div class="text-center px-4 py-2 rounded-xl" style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.2);">
                    <p class="text-2xl font-black text-green-400">{{ $activeCropCount }}</p>
                    <p class="text-[11px] text-gray-400">Active Crops</p>
                </div>
                <div class="text-center px-4 py-2 rounded-xl" style="background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.2);">
                    <p class="text-2xl font-black text-blue-400">{{ $totalReadings }}</p>
                    <p class="text-[11px] text-gray-400">Readings</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-white/5">
            @if($activeCropCount > 0)
                <button onclick="simulateSensor()" id="sim-btn" class="btn-primary px-4 py-2 text-sm flex items-center gap-2">
                    ⚡ New Reading
                </button>
                <button onclick="simulateBatch()" id="batch-btn" class="px-4 py-2 text-sm rounded-xl font-bold text-blue-300 flex items-center gap-2" style="background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.2);">
                    📊 Fill 24h Data
                </button>
            @else
                <a href="{{ route('crops.create') }}" class="btn-primary px-5 py-2.5 text-sm flex items-center gap-2">🌱 Add Your First Crop</a>
            @endif
            <span id="sim-status" class="text-sm text-gray-500 self-center hidden"></span>
        </div>
    </div>

    <!-- Sensor Health Cards -->
    @if($latestReading)
    <div>
        <h2 class="text-white font-bold text-base mb-3">🌡️ Crop Health Status {{ $activeCrop ? '— ' . $activeCrop->name : '' }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @php
                $items = [
                    ['key'=>'temperature','label'=>'Temperature','unit'=>'°C','emoji'=>'🌡️','good'=>'Perfect','low'=>'Too Cold','high'=>'Too Hot',
                     'colors'=>['good'=>['bg'=>'rgba(34,197,94,0.12)','border'=>'rgba(34,197,94,0.25)','text'=>'#4ade80','bar'=>'#22c55e'],
                                'low'=>['bg'=>'rgba(59,130,246,0.12)','border'=>'rgba(59,130,246,0.25)','text'=>'#60a5fa','bar'=>'#3b82f6'],
                                'high'=>['bg'=>'rgba(239,68,68,0.12)','border'=>'rgba(239,68,68,0.25)','text'=>'#f87171','bar'=>'#ef4444']]],
                    ['key'=>'soil_moisture','label'=>'Soil Water','unit'=>'%','emoji'=>'💧','good'=>'Good','low'=>'Needs Water!','high'=>'Too Wet',
                     'colors'=>['good'=>['bg'=>'rgba(34,197,94,0.12)','border'=>'rgba(34,197,94,0.25)','text'=>'#4ade80','bar'=>'#22c55e'],
                                'low'=>['bg'=>'rgba(245,158,11,0.12)','border'=>'rgba(245,158,11,0.25)','text'=>'#fbbf24','bar'=>'#f59e0b'],
                                'high'=>['bg'=>'rgba(59,130,246,0.12)','border'=>'rgba(59,130,246,0.25)','text'=>'#60a5fa','bar'=>'#3b82f6']]],
                    ['key'=>'humidity','label'=>'Air Humidity','unit'=>'%','emoji'=>'☁️','good'=>'Good','low'=>'Too Dry','high'=>'Too Humid',
                     'colors'=>['good'=>['bg'=>'rgba(34,197,94,0.12)','border'=>'rgba(34,197,94,0.25)','text'=>'#4ade80','bar'=>'#22c55e'],
                                'low'=>['bg'=>'rgba(245,158,11,0.12)','border'=>'rgba(245,158,11,0.25)','text'=>'#fbbf24','bar'=>'#f59e0b'],
                                'high'=>['bg'=>'rgba(139,92,246,0.12)','border'=>'rgba(139,92,246,0.25)','text'=>'#a78bfa','bar'=>'#8b5cf6']]],
                    ['key'=>'light_intensity','label'=>'Sunlight','unit'=>'lux','emoji'=>'☀️','good'=>'Good','low'=>'Low Light','high'=>'Intense',
                     'colors'=>['good'=>['bg'=>'rgba(34,197,94,0.12)','border'=>'rgba(34,197,94,0.25)','text'=>'#4ade80','bar'=>'#22c55e'],
                                'low'=>['bg'=>'rgba(107,114,128,0.12)','border'=>'rgba(107,114,128,0.25)','text'=>'#9ca3af','bar'=>'#6b7280'],
                                'high'=>['bg'=>'rgba(245,158,11,0.12)','border'=>'rgba(245,158,11,0.25)','text'=>'#fbbf24','bar'=>'#f59e0b']]],
                    ['key'=>'rainfall','label'=>'Rain','unit'=>'mm','emoji'=>'🌧️','good'=>'Normal','low'=>'No Rain','high'=>'Heavy Rain',
                     'colors'=>['good'=>['bg'=>'rgba(34,197,94,0.12)','border'=>'rgba(34,197,94,0.25)','text'=>'#4ade80','bar'=>'#22c55e'],
                                'low'=>['bg'=>'rgba(107,114,128,0.12)','border'=>'rgba(107,114,128,0.25)','text'=>'#9ca3af','bar'=>'#6b7280'],
                                'high'=>['bg'=>'rgba(59,130,246,0.12)','border'=>'rgba(59,130,246,0.25)','text'=>'#60a5fa','bar'=>'#3b82f6']]],
                ];
            @endphp

            @foreach($items as $s)
                @php
                    $val = $latestReading->{$s['key']};
                    $ideal = $idealRanges[$s['key']] ?? null;
                    $status = 'good';
                    if ($ideal) {
                        if ($val < $ideal['ideal_min']) $status = 'low';
                        elseif ($val > $ideal['ideal_max']) $status = 'high';
                    }
                    $c = $s['colors'][$status];
                    $displayVal = $s['key'] === 'light_intensity' ? number_format($val) : round($val, 1);
                    // Calculate gauge percentage
                    $pct = $ideal ? min(100, max(0, (($val - $ideal['min']) / max(1, $ideal['max'] - $ideal['min'])) * 100)) : 50;
                @endphp
                <div class="card p-4" style="background: {{ $c['bg'] }}; border-color: {{ $c['border'] }};">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">{{ $s['emoji'] }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background: {{ $c['bg'] }}; color: {{ $c['text'] }}; border: 1px solid {{ $c['border'] }};">
                            {{ $s[$status] }}
                        </span>
                    </div>
                    <p class="text-2xl font-black" style="color: {{ $c['text'] }};" id="{{ $s['key'] }}-value">{{ $displayVal }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $s['label'] }}</p>
                    <!-- Gauge bar -->
                    <div class="gauge-bar mt-2">
                        <div class="gauge-fill" style="width: {{ $pct }}%; background: {{ $c['bar'] }};"></div>
                    </div>
                    @if($ideal)
                        <p class="text-[10px] text-gray-600 mt-1">Best: {{ $ideal['ideal_min'] }}–{{ $ideal['ideal_max'] }} {{ $s['unit'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @elseif($activeCropCount > 0)
    <div class="card p-8 text-center">
        <p class="text-4xl mb-3">📡</p>
        <p class="text-gray-400">No sensor readings yet. Click <strong class="text-green-400">"New Reading"</strong> above to simulate IoT data!</p>
    </div>
    @endif

    <!-- Chart + AI Insight -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-white font-bold">📈 Sensor Trends</h3>
                <select id="chart-crop-filter" class="input-field text-sm py-1.5 px-3" style="max-width: 150px;">
                    <option value="">All Crops</option>
                    @foreach($crops as $crop)
                        <option value="{{ $crop->id }}" {{ $activeCrop && $activeCrop->id == $crop->id ? 'selected' : '' }}>{{ $crop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="height: 260px;"><canvas id="sensorChart"></canvas></div>
            <div id="no-chart" class="hidden text-center py-10">
                <p class="text-3xl mb-2">📊</p>
                <p class="text-gray-500 text-sm">Click "Fill 24h Data" to see trends here</p>
            </div>
        </div>

        <!-- AI Insight -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-white font-bold">🤖 AI Advice</h3>
                <button onclick="generateInsight()" id="ai-btn" class="text-xs px-3 py-1.5 rounded-lg font-bold text-green-300 disabled:opacity-50" style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.2);">
                    ✨ Ask AI
                </button>
            </div>
            <div id="insight-content" class="text-sm text-gray-300 leading-relaxed overflow-y-auto" style="max-height: 240px;">
                @if($latestInsight)
                    <span class="text-[11px] text-green-400 font-bold">{{ $latestInsight->crop->name ?? '' }}</span>
                    <span class="text-[11px] text-gray-600 ml-1">{{ $latestInsight->created_at->diffForHumans() }}</span>
                    <div class="mt-2 whitespace-pre-line">{{ $latestInsight->ai_response }}</div>
                @else
                    <div class="text-center py-8">
                        <p class="text-3xl mb-2">💡</p>
                        <p class="text-gray-500 text-sm">Add crop → Generate data → Click "Ask AI"</p>
                    </div>
                @endif
            </div>
            <div id="insight-loading" class="hidden text-center py-8">
                <div class="inline-block w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-gray-500 mt-2 text-sm">Thinking...</p>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if($alerts->where('is_read', false)->count() > 0)
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-white font-bold">🔔 Alerts</h3>
            <a href="{{ route('alerts.index') }}" class="text-sm text-green-400 font-bold">View All →</a>
        </div>
        <div class="space-y-2">
            @foreach($alerts->where('is_read', false)->take(4) as $alert)
                <div class="flex items-start gap-3 p-3 rounded-xl" style="background: {{ $alert->type === 'danger' ? 'rgba(239,68,68,0.1)' : ($alert->type === 'warning' ? 'rgba(245,158,11,0.1)' : 'rgba(59,130,246,0.1)') }}; border: 1px solid {{ $alert->type === 'danger' ? 'rgba(239,68,68,0.2)' : ($alert->type === 'warning' ? 'rgba(245,158,11,0.2)' : 'rgba(59,130,246,0.2)') }};">
                    <span class="text-lg mt-0.5">{{ $alert->type === 'danger' ? '🚨' : ($alert->type === 'warning' ? '⚠️' : 'ℹ️') }}</span>
                    <div>
                        <p class="text-sm text-gray-300">{{ $alert->message }}</p>
                        <p class="text-[11px] text-gray-600 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
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
let chart;

async function simulateSensor() {
    const btn = document.getElementById('sim-btn'), st = document.getElementById('sim-status');
    btn.disabled=true; btn.textContent='⏳ Reading...'; st.classList.remove('hidden'); st.textContent='Simulating...';
    try {
        const r = await fetch('{{ route("sensors.simulate") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:'{}'});
        const d = await r.json();
        st.textContent = d.success ? '✅ '+d.message : '❌ '+d.message;
        if(d.success) { location.reload(); }
    } catch(e){ st.textContent='❌ Error'; }
    btn.disabled=false; btn.textContent='⚡ New Reading';
    setTimeout(()=>st.classList.add('hidden'),4000);
}

async function simulateBatch() {
    const btn = document.getElementById('batch-btn'), st = document.getElementById('sim-status');
    btn.disabled=true; btn.textContent='⏳ Generating...'; st.classList.remove('hidden'); st.textContent='Creating data...';
    try {
        const r = await fetch('{{ route("sensors.simulateBatch") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({count:20})});
        const d = await r.json();
        st.textContent = d.success ? '✅ '+d.message : '❌ '+d.message;
        if(d.success) { location.reload(); }
    } catch(e){ st.textContent='❌ Error'; }
    btn.disabled=false; btn.textContent='📊 Fill 24h Data';
    setTimeout(()=>st.classList.add('hidden'),4000);
}

async function loadChart(cropId='') {
    try {
        const r = await fetch(`{{ route('sensors.data') }}?crop_id=${cropId}`);
        const d = await r.json();
        if(!d.labels||d.labels.length===0){document.getElementById('sensorChart').style.display='none';document.getElementById('no-chart').classList.remove('hidden');return;}
        document.getElementById('sensorChart').style.display='block';document.getElementById('no-chart').classList.add('hidden');
        if(chart) chart.destroy();
        chart = new Chart(document.getElementById('sensorChart').getContext('2d'),{
            type:'line',data:{labels:d.labels,datasets:[
                {label:'🌡️ Temp',data:d.temperature,borderColor:'#f97316',backgroundColor:'rgba(249,115,22,0.1)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
                {label:'💧 Moisture',data:d.soil_moisture,borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.1)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
                {label:'☁️ Humidity',data:d.humidity,borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,0.1)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
                {label:'🌧️ Rain',data:d.rainfall,borderColor:'#06b6d4',backgroundColor:'rgba(6,182,212,0.1)',tension:0.4,fill:true,pointRadius:3,borderWidth:2.5},
            ]},
            options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
                plugins:{legend:{labels:{color:'#9ca3af',font:{size:11,family:'Nunito'},usePointStyle:true,pointStyle:'circle'}}},
                scales:{x:{ticks:{color:'#6b7280',maxTicksLimit:8,font:{size:10}},grid:{color:'rgba(255,255,255,0.03)'}},y:{ticks:{color:'#6b7280',font:{size:10}},grid:{color:'rgba(255,255,255,0.03)'}}}
            }
        });
    } catch(e){}
}
loadChart('{{ $activeCrop ? $activeCrop->id : '' }}');
document.getElementById('chart-crop-filter').addEventListener('change',function(){loadChart(this.value);});

async function generateInsight() {
    const c=document.getElementById('insight-content'),l=document.getElementById('insight-loading'),b=document.getElementById('ai-btn');
    c.classList.add('hidden');l.classList.remove('hidden');b.disabled=true;
    try {
        const r = await fetch('{{ route("ai-insights.generate") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:'{}'});
        const d = await r.json();
        l.classList.add('hidden');c.classList.remove('hidden');
        c.innerHTML = d.success
            ? `<span class="text-[11px] text-green-400 font-bold">${d.crop_name}</span><span class="text-[11px] text-gray-600 ml-1">${d.created_at}</span><div class="mt-2 whitespace-pre-line">${d.insight}</div>`
            : `<p class="text-amber-400 text-sm">${d.message}</p>`;
    } catch(e){l.classList.add('hidden');c.classList.remove('hidden');c.innerHTML='<p class="text-red-400 text-sm">Connection error. Try again.</p>';}
    b.disabled=false;
}
</script>
@endpush
