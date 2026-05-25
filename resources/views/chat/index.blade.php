@extends('layouts.app')
@section('title', 'AI Chat')
@section('breadcrumb', 'chat')

@section('content')
<div class="mx-auto max-w-3xl flex flex-col rounded-3xl border border-gray-200 bg-white shadow-card overflow-hidden" style="height: calc(100vh - 8rem);">

    <!-- Header -->
    <div class="flex items-center gap-3 border-b px-5 py-3 bg-gradient-card" style="border-color: var(--border);">
        <div class="grid h-10 w-10 place-items-center rounded-full bg-gradient-primary text-white shadow-glow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <div>
            <div class="font-display text-sm text-gray-900 flex items-center gap-2">
                FarmAI Assistant <span class="text-base">🤖</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                Powered by Google Gemini
                <span class="ml-2 inline-flex items-center gap-1 text-green-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse-soft"></span> Online
                </span>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div id="chat-messages" class="flex-1 overflow-y-auto p-5" style="background: radial-gradient(at top, #f0fdf4 0%, #fafbfc 60%);">
        @if(count($messages) === 0)
            <!-- Welcome -->
            <div class="space-y-4">
                <div class="flex gap-2 items-start">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-gradient-primary text-white text-xs flex-shrink-0">🤖</div>
                    <div class="max-w-[80%] rounded-2xl rounded-tl-sm border border-gray-200 bg-white p-3.5 text-sm shadow-soft text-gray-700">
                        Hi there! 👋 I'm your AI farming assistant. Ask me anything about soil, crops, pests, or watering schedules.
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 pl-10">
                    <button onclick="quickAsk('What is the best soil pH for rice?')" class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs hover:border-green-400 hover:bg-green-50 transition">🌾 Best soil pH for rice?</button>
                    <button onclick="quickAsk('How to control pests in wheat naturally?')" class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs hover:border-green-400 hover:bg-green-50 transition">🐛 How to control pests in wheat?</button>
                    <button onclick="quickAsk('When should I water my tomato plants?')" class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs hover:border-green-400 hover:bg-green-50 transition">💧 When to water tomatoes?</button>
                </div>
            </div>
        @endif

        <div class="space-y-3" id="chat-list">
            @foreach($messages as $msg)
                <!-- User message -->
                <div class="flex gap-2 items-end justify-end animate-fade-up">
                    <div class="max-w-[80%] rounded-2xl rounded-br-sm p-3 text-sm shadow-soft text-white bg-gradient-primary">
                        <div class="whitespace-pre-wrap">{{ $msg->message }}</div>
                        <div class="mt-1 text-[10px] text-white/70">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
                <!-- AI response -->
                <div class="flex gap-2 items-end justify-start animate-fade-up">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-gradient-primary text-white text-xs flex-shrink-0">🤖</div>
                    <div class="max-w-[80%] rounded-2xl rounded-tl-sm border border-gray-200 bg-white p-3 text-sm shadow-soft text-gray-700">
                        <div class="whitespace-pre-wrap">{{ $msg->response }}</div>
                        <div class="mt-1 text-[10px] text-gray-400">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Input -->
    <form id="chat-form" class="flex items-center gap-2 border-t bg-white p-3" style="border-color: var(--border);">
        <input type="text" id="chat-input" placeholder="Ask FarmAI anything…" required autocomplete="off" maxlength="1000" class="input-field flex-1">
        <button type="submit" id="send-btn" class="grid h-10 w-10 place-items-center rounded-lg bg-gradient-primary text-white shadow-glow flex-shrink-0 hover:opacity-92 transition disabled:opacity-50" disabled>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
const cm = document.getElementById('chat-messages');
const cl = document.getElementById('chat-list');
const ci = document.getElementById('chat-input');
const sb = document.getElementById('send-btn');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const ui = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}';
cm.scrollTop = cm.scrollHeight;

// Enable send button when input has text
ci.addEventListener('input', () => { sb.disabled = !ci.value.trim(); });

function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

function quickAsk(q) {
    ci.value = q;
    sb.disabled = false;
    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
}

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = ci.value.trim();
    if (!msg) return;

    // Add user message
    cl.innerHTML += `<div class="flex gap-2 items-end justify-end animate-fade-up">
        <div class="max-w-[80%] rounded-2xl rounded-br-sm p-3 text-sm shadow-soft text-white bg-gradient-primary">
            <div class="whitespace-pre-wrap">${esc(msg)}</div>
        </div>
    </div>`;

    // Add typing indicator
    const tid = 't' + Date.now();
    cl.innerHTML += `<div id="${tid}" class="flex gap-2 items-end">
        <div class="grid h-8 w-8 place-items-center rounded-full bg-gradient-primary text-white text-xs flex-shrink-0">🤖</div>
        <div class="rounded-2xl rounded-tl-sm border border-gray-200 bg-white p-3 shadow-soft">
            <div class="flex gap-1">
                <span class="h-2 w-2 rounded-full bg-gray-300 animate-bounce-dot" style="animation-delay:0ms"></span>
                <span class="h-2 w-2 rounded-full bg-gray-300 animate-bounce-dot" style="animation-delay:160ms"></span>
                <span class="h-2 w-2 rounded-full bg-gray-300 animate-bounce-dot" style="animation-delay:320ms"></span>
            </div>
        </div>
    </div>`;
    cm.scrollTop = cm.scrollHeight;
    ci.value = '';
    sb.disabled = true;

    try {
        const r = await fetch('{{ route("chat.send") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ message: msg })
        });
        const d = await r.json();
        document.getElementById(tid)?.remove();
        if (d.success) {
            cl.innerHTML += `<div class="flex gap-2 items-end animate-fade-up">
                <div class="grid h-8 w-8 place-items-center rounded-full bg-gradient-primary text-white text-xs flex-shrink-0">🤖</div>
                <div class="max-w-[80%] rounded-2xl rounded-tl-sm border border-gray-200 bg-white p-3 text-sm shadow-soft text-gray-700">
                    <div class="whitespace-pre-wrap">${esc(d.response)}</div>
                </div>
            </div>`;
        } else {
            cl.innerHTML += `<div class="text-center text-amber-600 text-sm py-2">${d.message || 'Error'}</div>`;
        }
    } catch (e) {
        document.getElementById(tid)?.remove();
        cl.innerHTML += `<div class="text-center text-red-500 text-sm py-2">Connection error. Try again.</div>`;
    }

    sb.disabled = !ci.value.trim();
    ci.focus();
    cm.scrollTop = cm.scrollHeight;
});
</script>
@endpush
