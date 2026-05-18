@extends('layouts.app')
@section('title', 'Alerts')
@section('page-title', '🔔 Alerts')

@section('content')
<div class="space-y-5 fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Farm Alerts</h1>
            <p class="text-gray-400 text-sm">⚠️ Important notifications about your crops</p>
        </div>
        @if($alerts->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('alerts.markAllRead') }}">@csrf
            <button class="px-4 py-2 rounded-xl text-sm font-bold text-green-300" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.2);">✅ Mark All Read</button>
        </form>
        @endif
    </div>

    @if($alerts->count() > 0)
        <div class="space-y-2">
            @foreach($alerts as $alert)
                <div class="card p-4 flex items-start gap-3 {{ $alert->is_read ? 'opacity-50' : '' }}">
                    <span class="text-xl mt-0.5">{{ $alert->type === 'danger' ? '🚨' : ($alert->type === 'warning' ? '⚠️' : 'ℹ️') }}</span>
                    <div class="flex-1">
                        <p class="text-sm text-gray-300 {{ !$alert->is_read ? 'font-bold' : '' }}">{{ $alert->message }}</p>
                        <p class="text-[11px] text-gray-600 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$alert->is_read)
                    <form method="POST" action="{{ route('alerts.markRead', $alert->id) }}">@csrf
                        <button class="text-[11px] px-3 py-1 rounded-lg text-green-400 font-bold hover:text-green-300" style="background: rgba(34,197,94,0.1);">✓ Read</button>
                    </form>
                    @else
                        <span class="text-[11px] text-gray-600">Read ✓</span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="card p-12 text-center">
            <p class="text-5xl mb-3">🎉</p>
            <h3 class="text-xl font-extrabold text-white mb-2">All Clear!</h3>
            <p class="text-gray-400">No alerts. Your farm is running great!</p>
        </div>
    @endif
</div>
@endsection
