@extends('layouts.app')
@section('title', 'Edit Crop')
@section('breadcrumb', 'crops / edit')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">✏️</span> Edit — {{ $crop->name }}
            </h1>
            <p class="mt-1 text-gray-500">Update crop details and growing status.</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('crops.update', $crop->id) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="hiq-card p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Crop Name</label>
                    <input type="text" name="name" value="{{ old('name', $crop->name) }}" required class="input-field">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">📍 Field Name</label>
                    <input type="text" name="field_name" value="{{ old('field_name', $crop->field_name) }}" required class="input-field">
                    @error('field_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">🌱 Planting Date</label>
                        <input type="date" name="planting_date" value="{{ old('planting_date', $crop->planting_date?->format('Y-m-d')) }}" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">🌾 Harvest Date</label>
                        <input type="date" name="expected_harvest_date" value="{{ old('expected_harvest_date', $crop->expected_harvest_date?->format('Y-m-d')) }}" required class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">📐 Area (Acres)</label>
                        <input type="number" step="0.1" min="0.1" name="area_acres" value="{{ old('area_acres', $crop->area_acres ?? 1) }}" required class="input-field">
                        <p class="text-[10px] text-gray-400 mt-1">Used for yield & cost predictions</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">💰 Investment (₹)</label>
                        <input type="number" step="100" min="0" name="estimated_investment" value="{{ old('estimated_investment', $crop->estimated_investment ?? '') }}" class="input-field" placeholder="e.g., 25000">
                        <p class="text-[10px] text-gray-400 mt-1">Your total spend (seeds, labor, etc.)</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" required class="input-field">
                        <option value="active" {{ $crop->status === 'active' ? 'selected' : '' }}>● Active (Growing)</option>
                        <option value="harvested" {{ $crop->status === 'harvested' ? 'selected' : '' }}>● Harvested</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary px-6 py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Update Crop
                </button>
                <a href="{{ route('crops.index') }}" class="btn-ghost px-4 py-2.5">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
