@extends('layouts.app')
@section('title', 'Add Crop')
@section('breadcrumb', 'crops / new')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">🌱</span> Add New Crop
            </h1>
            <p class="mt-1 text-gray-500">Pick a crop type and we'll set up IoT monitoring with ideal sensor ranges.</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('crops.store') }}" class="space-y-6">
            @csrf

            <!-- Crop Type Picker -->
            <div class="hiq-card p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-3">What are you growing?</label>
                <div class="grid grid-cols-4 gap-3">
                    @php $emojis = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘']; @endphp
                    @foreach($cropTypes as $type)
                        <button type="button" onclick="pickCrop('{{ $type }}')" data-type="{{ $type }}"
                            class="crop-btn p-4 rounded-xl text-center transition-all border-2 border-gray-100 hover:border-green-300 hover:bg-green-50/50 cursor-pointer group" style="background: var(--muted);">
                            <span class="text-3xl block group-hover:scale-110 transition-transform">{{ $emojis[$type] ?? '🌱' }}</span>
                            <span class="text-xs text-gray-500 mt-1.5 block capitalize font-semibold">{{ $type }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Form fields -->
            <div class="hiq-card p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Crop Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input-field" placeholder="Click a crop above or type name">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">📍 Field / Plot Name</label>
                    <input type="text" name="field_name" value="{{ old('field_name') }}" required class="input-field" placeholder="e.g., North Field, Plot A">
                    @error('field_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">🌱 Planting Date</label>
                        <input type="date" name="planting_date" value="{{ old('planting_date', now()->format('Y-m-d')) }}" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">🌾 Harvest Date</label>
                        <input type="date" name="expected_harvest_date" value="{{ old('expected_harvest_date', now()->addMonths(3)->format('Y-m-d')) }}" required class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">📐 Area (Acres)</label>
                        <input type="number" step="0.1" min="0.1" name="area_acres" value="{{ old('area_acres', 1) }}" required class="input-field" placeholder="e.g., 2.5">
                        <p class="text-[10px] text-gray-400 mt-1">Used for yield & cost predictions in Analytics</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">💰 Investment (₹)</label>
                        <input type="number" step="100" min="0" name="estimated_investment" value="{{ old('estimated_investment', 25000) }}" class="input-field" placeholder="e.g., 25000">
                        <p class="text-[10px] text-gray-400 mt-1">Your total spend on this crop (seeds, labor, etc.)</p>
                    </div>
                </div>
                <input type="hidden" name="status" value="active">
            </div>

            <!-- Info -->
            <div class="rounded-xl p-4 text-sm flex items-start gap-2" style="background: rgba(34,197,94,0.06); border: 1px solid rgba(34,197,94,0.15); color: #15803d;">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>Auto Setup:</strong> When you save, we'll automatically create 20 sensor readings based on this crop's ideal growing conditions!</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="btn-primary px-6 py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save & Start Monitoring
                </button>
                <a href="{{ route('crops.index') }}" class="btn-ghost px-4 py-2.5">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pickCrop(type) {
    document.querySelectorAll('.crop-btn').forEach(b => {
        b.style.borderColor = '#f1f5f9';
        b.style.background = 'var(--muted)';
    });
    const btn = document.querySelector(`[data-type="${type}"]`);
    btn.style.borderColor = '#22c55e';
    btn.style.background = 'rgba(34,197,94,0.08)';
    document.getElementById('name').value = type.charAt(0).toUpperCase() + type.slice(1);
}
</script>
@endpush
