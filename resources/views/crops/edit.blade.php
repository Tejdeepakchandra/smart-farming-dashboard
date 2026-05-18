@extends('layouts.app')
@section('title', 'Edit Crop')
@section('page-title', '✏️ Edit Crop')

@section('content')
<div class="max-w-2xl mx-auto fade-up">
    <div class="card p-6">
        <h2 class="text-xl font-extrabold text-white mb-5">✏️ Edit — {{ $crop->name }}</h2>
        <form method="POST" action="{{ route('crops.update', $crop->id) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-bold text-gray-300 mb-1">Crop Name</label>
                <input type="text" name="name" value="{{ old('name', $crop->name) }}" required class="input-field w-full">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-300 mb-1">📍 Field Name</label>
                <input type="text" name="field_name" value="{{ old('field_name', $crop->field_name) }}" required class="input-field w-full">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-1">🌱 Planting Date</label>
                    <input type="date" name="planting_date" value="{{ old('planting_date', $crop->planting_date?->format('Y-m-d')) }}" required class="input-field w-full">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-1">🌾 Harvest Date</label>
                    <input type="date" name="expected_harvest_date" value="{{ old('expected_harvest_date', $crop->expected_harvest_date?->format('Y-m-d')) }}" required class="input-field w-full">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-300 mb-1">Status</label>
                <select name="status" required class="input-field w-full">
                    <option value="active" {{ $crop->status==='active'?'selected':'' }}>🟢 Active (Growing)</option>
                    <option value="harvested" {{ $crop->status==='harvested'?'selected':'' }}>🟡 Harvested</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm">✅ Update Crop</button>
                <a href="{{ route('crops.index') }}" class="px-4 py-2.5 text-gray-400 hover:text-white text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
