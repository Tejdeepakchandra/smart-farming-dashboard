@extends('layouts.app')
@section('title', 'Add Crop')
@section('page-title', '🌱 Add New Crop')

@section('content')
<div class="max-w-2xl mx-auto fade-up">
    <div class="card p-6">
        <h2 class="text-xl font-extrabold text-white mb-1">🌱 Add New Crop</h2>
        <p class="text-gray-400 text-sm mb-5">Pick a crop type and we'll set up IoT monitoring with ideal sensor ranges</p>

        <form method="POST" action="{{ route('crops.store') }}" class="space-y-5">
            @csrf

            <!-- Crop Type Picker -->
            <div>
                <label class="block text-sm font-bold text-gray-300 mb-2">🌾 What are you growing?</label>
                <div class="grid grid-cols-4 gap-2">
                    @php $emojis = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
                    @foreach($cropTypes as $type)
                        <button type="button" onclick="pickCrop('{{ $type }}')" data-type="{{ $type }}"
                            class="crop-btn p-3 rounded-xl text-center transition-all" style="background: rgba(255,255,255,0.03); border: 2px solid rgba(255,255,255,0.06);">
                            <span class="text-3xl block">{{ $emojis[$type] ?? '🌱' }}</span>
                            <span class="text-[11px] text-gray-400 mt-1 block capitalize font-bold">{{ $type }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-1">Crop Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input-field w-full" placeholder="Click a crop above or type name">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-300 mb-1">📍 Field / Plot Name</label>
                <input type="text" name="field_name" value="{{ old('field_name') }}" required class="input-field w-full" placeholder="e.g., North Field, Plot A">
                @error('field_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-1">🌱 Planting Date</label>
                    <input type="date" name="planting_date" value="{{ old('planting_date', now()->format('Y-m-d')) }}" required class="input-field w-full">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-1">🌾 Harvest Date</label>
                    <input type="date" name="expected_harvest_date" value="{{ old('expected_harvest_date', now()->addMonths(3)->format('Y-m-d')) }}" required class="input-field w-full">
                </div>
            </div>

            <input type="hidden" name="status" value="active">

            <div class="p-4 rounded-xl text-sm" style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #86efac;">
                💡 <strong>Auto Setup:</strong> When you save, we'll automatically create 20 sensor readings based on this crop's ideal growing conditions!
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm">✅ Save & Start Monitoring</button>
                <a href="{{ route('crops.index') }}" class="px-4 py-2.5 text-gray-400 hover:text-white text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pickCrop(type) {
    document.querySelectorAll('.crop-btn').forEach(b => { b.style.borderColor = 'rgba(255,255,255,0.06)'; b.style.background = 'rgba(255,255,255,0.03)'; });
    const btn = document.querySelector(`[data-type="${type}"]`);
    btn.style.borderColor = '#22c55e'; btn.style.background = 'rgba(34,197,94,0.12)';
    document.getElementById('name').value = type.charAt(0).toUpperCase() + type.slice(1);
}
</script>
@endpush
