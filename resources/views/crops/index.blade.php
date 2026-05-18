@extends('layouts.app')
@section('title', 'My Crops')
@section('page-title', '🌱 My Crops')

@section('content')
<div class="space-y-5 fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">My Crops</h1>
            <p class="text-gray-400 text-sm">Manage your fields and monitor health</p>
        </div>
        <a href="{{ route('crops.create') }}" class="btn-primary px-5 py-2.5 text-sm flex items-center gap-2 w-fit">➕ Add New Crop</a>
    </div>

    @if($crops->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($crops as $crop)
                @php
                    $em = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘'];
                    $emoji = $em[strtolower($crop->name)] ?? '🌱';
                @endphp
                <div class="card p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">{{ $emoji }}</span>
                            <div>
                                <h3 class="text-lg font-extrabold text-white">{{ $crop->name }}</h3>
                                <p class="text-xs text-gray-500">📍 {{ $crop->field_name }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $crop->status === 'active' ? 'text-green-400' : 'text-amber-400' }}" style="background: {{ $crop->status === 'active' ? 'rgba(34,197,94,0.15)' : 'rgba(245,158,11,0.15)' }}; border: 1px solid {{ $crop->status === 'active' ? 'rgba(34,197,94,0.3)' : 'rgba(245,158,11,0.3)' }};">
                            {{ $crop->status === 'active' ? '🟢 Growing' : '🟡 Harvested' }}
                        </span>
                    </div>

                    @if($crop->latestReading)
                        <div class="grid grid-cols-3 gap-2 mb-4 p-3 rounded-xl" style="background: rgba(255,255,255,0.03);">
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">🌡️ Temp</p>
                                <p class="text-sm font-extrabold text-orange-400">{{ round($crop->latestReading->temperature,1) }}°</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">💧 Water</p>
                                <p class="text-sm font-extrabold text-blue-400">{{ round($crop->latestReading->soil_moisture,1) }}%</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">☁️ Air</p>
                                <p class="text-sm font-extrabold text-purple-400">{{ round($crop->latestReading->humidity,1) }}%</p>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl mb-4 text-center text-xs text-gray-500" style="background: rgba(255,255,255,0.03);">
                            No readings yet
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-[11px] text-gray-500 mb-4">
                        <span>📊 {{ $crop->readingsCount }} readings</span>
                        <span>🌱 {{ $crop->planting_date ? $crop->planting_date->format('d M') : '' }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('crops.show', $crop->id) }}" class="flex-1 text-center text-sm py-2 rounded-xl font-bold text-green-300" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.2);">📋 View</a>
                        <a href="{{ route('crops.edit', $crop->id) }}" class="flex-1 text-center text-sm py-2 rounded-xl font-bold text-blue-300" style="background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.2);">✏️ Edit</a>
                        <form method="POST" action="{{ route('crops.destroy', $crop->id) }}" class="flex-1" onsubmit="return confirm('Delete this crop?')">@csrf @method('DELETE')
                            <button class="w-full text-sm py-2 rounded-xl font-bold text-red-300" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.2);">🗑️</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card p-12 text-center">
            <p class="text-6xl mb-4">🌾</p>
            <h3 class="text-xl font-extrabold text-white mb-2">No Crops Yet!</h3>
            <p class="text-gray-400 mb-6">Start monitoring your farm by adding your first crop</p>
            <a href="{{ route('crops.create') }}" class="btn-primary px-6 py-3 text-sm inline-flex items-center gap-2">🌱 Add First Crop</a>
        </div>
    @endif
</div>
@endsection
