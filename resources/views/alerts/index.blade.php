@extends('layouts.app')
@section('title', 'Alerts')
@section('breadcrumb', 'alerts')

@section('content')
@php
    $unread = $alerts->where('is_read', false);
    $read = $alerts->where('is_read', true);
    $dangers = $unread->where('type', 'danger');
    $warnings = $unread->where('type', 'warning');
    $infos = $unread->where('type', 'info');
@endphp
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="flex items-center gap-3 font-display text-3xl tracking-tight text-gray-900">
                <span class="text-3xl">🚨</span> Alerts & Notifications
            </h1>
            <p class="mt-1 text-gray-500">
                Smart threshold alerts generated from your IoT sensor readings.
            </p>
        </div>
        @if($unread->count() > 0)
            <form method="POST" action="{{ route('alerts.markAllRead') }}">
                @csrf
                <button type="submit" class="btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mark all read
                </button>
            </form>
        @endif
    </div>

    <!-- Stats bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-fade-up">
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">📬</span>
            <p class="font-display text-lg text-gray-900">{{ $unread->count() }}</p>
            <p class="text-[11px] text-gray-400">Unread</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">🚨</span>
            <p class="font-display text-lg text-red-500">{{ $dangers->count() }}</p>
            <p class="text-[11px] text-gray-400">Critical</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">⚠️</span>
            <p class="font-display text-lg text-amber-500">{{ $warnings->count() }}</p>
            <p class="text-[11px] text-gray-400">Warnings</p>
        </div>
        <div class="hiq-card p-3 text-center">
            <span class="text-2xl">ℹ️</span>
            <p class="font-display text-lg text-blue-500">{{ $infos->count() }}</p>
            <p class="text-[11px] text-gray-400">Info</p>
        </div>
    </div>

    <!-- How alerts work -->
    <div class="hiq-card p-5 animate-fade-up" style="animation-delay: 60ms;">
        <div class="flex items-start gap-3">
            <span class="text-2xl">🤖</span>
            <div>
                <h3 class="font-display text-sm text-gray-900 mb-1">How Smart Alerts Work</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Every time your IoT sensors record new data, our system compares each reading against the <strong class="text-gray-700">ideal range for your specific crop type</strong>.
                    If temperature, soil moisture, humidity, or rainfall goes outside safe limits, an alert is automatically generated with a specific recommendation.
                    <span class="text-red-500 font-semibold">🚨 Critical</span> = immediate action needed.
                    <span class="text-amber-500 font-semibold">⚠️ Warning</span> = monitor closely.
                    <span class="text-blue-500 font-semibold">ℹ️ Info</span> = FYI, check when convenient.
                </p>
            </div>
        </div>
    </div>

    @if($alerts->count() === 0)
        <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-16 text-center">
            <div class="text-6xl mb-3">🎉</div>
            <div class="font-display text-xl text-gray-900">No alerts yet</div>
            <p class="mt-1 text-sm text-gray-500 mb-4">Your fields are looking healthy. Alerts will appear here when sensor readings go outside ideal ranges.</p>
            <a href="{{ route('dashboard') }}" class="btn-primary inline-flex">🏠 Go to Dashboard</a>
        </div>
    @else
        <!-- Unread alerts -->
        @if($unread->count() > 0)
        <div>
            <h2 class="font-display text-lg text-gray-900 mb-3 flex items-center gap-2">
                📬 Unread
                <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse-soft"></span>
            </h2>
            <div class="space-y-3">
                @foreach($unread->sortByDesc('created_at') as $i => $a)
                    @php
                        $icon = $a->type === 'danger' ? '🚨' : ($a->type === 'warning' ? '⚠️' : 'ℹ️');
                        $tone = $a->type === 'danger' ? 'border-red-100 bg-red-50/50'
                            : ($a->type === 'warning' ? 'border-amber-100 bg-amber-50/50' : 'border-blue-100 bg-blue-50/50');
                        $title = $a->type === 'danger' ? 'Critical Alert' : ($a->type === 'warning' ? 'Warning' : 'Info');
                        $actionHint = '';
                        if (str_contains($a->message, 'moisture')) $actionHint = '💡 Check irrigation schedule';
                        elseif (str_contains($a->message, 'Heat') || str_contains($a->message, 'Temperature')) $actionHint = '💡 Consider shade netting';
                        elseif (str_contains($a->message, 'Humidity') || str_contains($a->message, 'Fungal')) $actionHint = '💡 Improve plant ventilation';
                        elseif (str_contains($a->message, 'rain') || str_contains($a->message, 'Rain')) $actionHint = '💡 Check drainage channels';
                    @endphp
                    <div class="rounded-2xl border p-4 shadow-soft animate-fade-up {{ $tone }}" style="animation-delay: {{ $i * 40 }}ms;">
                        <div class="flex items-start gap-4">
                            <div class="grid h-11 w-11 place-items-center rounded-xl bg-white text-2xl flex-shrink-0 shadow-sm">{{ $icon }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-display text-sm text-gray-900">{{ $title }}</h3>
                                    <span class="h-2 w-2 rounded-full" style="background: var(--primary);"></span>
                                </div>
                                <p class="mt-0.5 text-sm text-gray-600">{{ $a->message }}</p>
                                @if($actionHint)
                                    <p class="mt-1.5 text-xs font-medium text-green-700">{{ $actionHint }}</p>
                                @endif
                                <div class="mt-2 text-xs text-gray-400">{{ $a->created_at->diffForHumans() }} · {{ $a->created_at->format('d M Y, H:i') }}</div>
                            </div>
                            <button onclick="markRead('{{ $a->id ?? $a->_id }}', this)" class="btn-ghost text-xs flex-shrink-0">✓ Read</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Read alerts -->
        @if($read->count() > 0)
        <div>
            <h2 class="font-display text-base text-gray-400 mb-3">📭 Earlier (read)</h2>
            <div class="space-y-2">
                @foreach($read->sortByDesc('created_at')->take(10) as $a)
                    @php
                        $icon = $a->type === 'danger' ? '🚨' : ($a->type === 'warning' ? '⚠️' : 'ℹ️');
                    @endphp
                    <div class="rounded-xl border border-gray-100 p-3 opacity-60">
                        <div class="flex items-start gap-3">
                            <span class="text-lg">{{ $icon }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-500">{{ $a->message }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $a->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
async function markRead(id, btn) {
    try {
        const r = await fetch(`/alerts/${id}/mark-read`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const d = await r.json();
        if (d.success) {
            const card = btn.closest('.rounded-2xl');
            card.style.opacity = '0.3';
            card.style.transition = 'opacity 0.3s';
            setTimeout(() => card.remove(), 300);
        }
    } catch (e) {}
}
</script>
@endpush
