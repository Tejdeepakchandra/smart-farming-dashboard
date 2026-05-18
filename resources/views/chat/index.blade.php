@extends('layouts.app')
@section('title', 'AI Chat')
@section('page-title', '🤖 Ask AI')

@section('content')
<div class="max-w-3xl mx-auto fade-up">
    <div class="card overflow-hidden flex flex-col" style="height: calc(100vh - 140px);">

        <div class="px-5 py-3 border-b border-white/5 flex items-center gap-3 flex-shrink-0" style="background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(5,150,105,0.05));">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl" style="background: linear-gradient(135deg, #22c55e, #059669);">🤖</div>
            <div>
                <h3 class="text-sm font-extrabold text-white">FarmAI Assistant</h3>
                <p class="text-[11px] text-green-400">Powered by Google Gemini • Ask anything about farming</p>
            </div>
            <span class="ml-auto flex items-center gap-1.5 text-[11px] text-green-400">
                <span class="w-2 h-2 rounded-full bg-green-500 pulse-soft"></span> Online
            </span>
        </div>

        <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm" style="background: linear-gradient(135deg, #22c55e, #059669);">🤖</div>
                <div class="rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm" style="background: rgba(255,255,255,0.05);">
                    <p class="text-sm text-gray-300 font-bold">Hello! 🌾 I'm your farming AI assistant.</p>
                    <p class="text-sm text-gray-400 mt-2">Try asking me:</p>
                    <div class="mt-2 space-y-1">
                        <button onclick="quickAsk('What is the best soil pH for rice?')" class="block w-full text-left text-xs px-3 py-1.5 rounded-lg text-green-300 hover:text-green-200" style="background: rgba(34,197,94,0.1);">🌾 "Best soil pH for rice?"</button>
                        <button onclick="quickAsk('How to control pests in wheat naturally?')" class="block w-full text-left text-xs px-3 py-1.5 rounded-lg text-blue-300 hover:text-blue-200" style="background: rgba(59,130,246,0.1);">🐛 "How to control pests in wheat?"</button>
                        <button onclick="quickAsk('When should I water my tomato plants?')" class="block w-full text-left text-xs px-3 py-1.5 rounded-lg text-purple-300 hover:text-purple-200" style="background: rgba(139,92,246,0.1);">💧 "When to water tomatoes?"</button>
                    </div>
                </div>
            </div>

            @foreach($messages as $msg)
                <div class="flex items-start gap-3 justify-end">
                    <div class="rounded-2xl rounded-tr-sm px-4 py-3 max-w-sm" style="background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(5,150,105,0.15)); border: 1px solid rgba(34,197,94,0.2);">
                        <p class="text-sm text-white">{{ $msg->message }}</p>
                        <p class="text-[10px] text-green-400/50 mt-1">{{ $msg->created_at->format('H:i') }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold" style="background: rgba(255,255,255,0.1);">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm" style="background: linear-gradient(135deg, #22c55e, #059669);">🤖</div>
                    <div class="rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm" style="background: rgba(255,255,255,0.05);">
                        <p class="text-sm text-gray-300 whitespace-pre-line">{{ $msg->response }}</p>
                        <p class="text-[10px] text-gray-600 mt-1">{{ $msg->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="px-5 py-3 border-t border-white/5 flex-shrink-0">
            <form id="chat-form" class="flex gap-2">
                <input type="text" id="chat-input" placeholder="Ask about crops, soil, irrigation, pests..." required autocomplete="off" maxlength="1000" class="input-field flex-1 text-sm">
                <button type="submit" id="send-btn" class="btn-primary px-5 py-2.5 flex items-center gap-2 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cm = document.getElementById('chat-messages');
const ci = document.getElementById('chat-input');
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const ui = '{{ strtoupper(substr(auth()->user()->name,0,1)) }}';
cm.scrollTop = cm.scrollHeight;

function esc(t){const d=document.createElement('div');d.textContent=t;return d.innerHTML;}
function quickAsk(q){ci.value=q;document.getElementById('chat-form').dispatchEvent(new Event('submit'));}

document.getElementById('chat-form').addEventListener('submit',async(e)=>{
    e.preventDefault();
    const msg=ci.value.trim();if(!msg)return;

    cm.innerHTML+=`<div class="flex items-start gap-3 justify-end fade-up"><div class="rounded-2xl rounded-tr-sm px-4 py-3 max-w-sm" style="background:linear-gradient(135deg,rgba(34,197,94,0.2),rgba(5,150,105,0.15));border:1px solid rgba(34,197,94,0.2);"><p class="text-sm text-white">${esc(msg)}</p></div><div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold" style="background:rgba(255,255,255,0.1);">${ui}</div></div>`;

    const tid='t'+Date.now();
    cm.innerHTML+=`<div id="${tid}" class="flex items-start gap-3"><div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm" style="background:linear-gradient(135deg,#22c55e,#059669);">🤖</div><div class="rounded-2xl rounded-tl-sm px-4 py-3" style="background:rgba(255,255,255,0.05);"><div class="flex gap-1"><div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div><div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:.15s"></div><div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:.3s"></div></div></div></div>`;
    cm.scrollTop=cm.scrollHeight; ci.value=''; document.getElementById('send-btn').disabled=true;

    try{
        const r=await fetch('{{ route("chat.send") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({message:msg})});
        const d=await r.json(); document.getElementById(tid)?.remove();
        if(d.success){
            cm.innerHTML+=`<div class="flex items-start gap-3 fade-up"><div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm" style="background:linear-gradient(135deg,#22c55e,#059669);">🤖</div><div class="rounded-2xl rounded-tl-sm px-4 py-3 max-w-sm" style="background:rgba(255,255,255,0.05);"><p class="text-sm text-gray-300 whitespace-pre-line">${esc(d.response)}</p></div></div>`;
        } else {
            cm.innerHTML+=`<div class="text-center text-amber-400 text-sm py-2">${d.message||'Error'}</div>`;
        }
    }catch(e){document.getElementById(tid)?.remove();cm.innerHTML+=`<div class="text-center text-red-400 text-sm py-2">Connection error. Try again.</div>`;}

    document.getElementById('send-btn').disabled=false;ci.focus();cm.scrollTop=cm.scrollHeight;
});
</script>
@endpush
