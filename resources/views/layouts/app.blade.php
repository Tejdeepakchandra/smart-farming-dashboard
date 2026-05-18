<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🌾 {{ config('app.name', 'Smart Farming') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Nunito', 'sans-serif'] } } }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Nunito', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1a1a2e; }
        ::-webkit-scrollbar-thumb { background: #4a4a6a; border-radius: 3px; }

        @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.4s ease-out forwards; }

        @keyframes pulse-soft { 0%,100%{opacity:1} 50%{opacity:.7} }
        .pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }

        .card {
            background: linear-gradient(135deg, rgba(30,30,50,0.9) 0%, rgba(20,20,40,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        .card:hover { border-color: rgba(255,255,255,0.12); transform: translateY(-1px); }

        .sidebar-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background: rgba(74, 222, 128, 0.08);
            border-left-color: #4ade80;
        }
        .sidebar-item.active { color: #4ade80; font-weight: 700; }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white; border-radius: 0.75rem; font-weight: 700;
            transition: all 0.2s; box-shadow: 0 4px 15px rgba(34,197,94,0.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(34,197,94,0.35); }

        .input-field {
            background: rgba(30,30,50,0.8); border: 1px solid rgba(255,255,255,0.1);
            color: white; border-radius: 0.75rem; padding: 0.625rem 1rem;
            transition: border-color 0.2s;
        }
        .input-field:focus { border-color: #4ade80; outline: none; box-shadow: 0 0 0 3px rgba(74,222,128,0.15); }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }

        /* Progress bar for sensor gauges */
        .gauge-bar { height: 8px; border-radius: 4px; background: rgba(255,255,255,0.08); overflow: hidden; }
        .gauge-fill { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased" style="background: linear-gradient(180deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%); min-height: 100vh;">

<div class="flex h-screen overflow-hidden">

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar" class="hidden lg:flex flex-col w-60 border-r border-white/5 flex-shrink-0" style="background: rgba(15,15,26,0.95);">
        <div class="flex items-center gap-3 px-5 py-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: linear-gradient(135deg, #22c55e, #059669);">🌾</div>
            <div>
                <h1 class="text-white font-extrabold text-sm">Smart Farm</h1>
                <p class="text-gray-500 text-[11px]">IoT Dashboard</p>
            </div>
        </div>

        <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'active text-green-400' : 'text-gray-400' }}">
                <span class="text-lg">🏠</span> Dashboard
            </a>
            <a href="{{ route('crops.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('crops.*') ? 'active text-green-400' : 'text-gray-400' }}">
                <span class="text-lg">🌱</span> My Crops
            </a>
            <a href="{{ route('sensors.history') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('sensors.*') ? 'active text-green-400' : 'text-gray-400' }}">
                <span class="text-lg">📊</span> Sensor Data
            </a>
            <a href="{{ route('alerts.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('alerts.*') ? 'active text-green-400' : 'text-gray-400' }}">
                <span class="text-lg">🔔</span> Alerts
                @php $uc = \App\Models\Alert::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                @if($uc > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full">{{ $uc }}</span>@endif
            </a>
            <a href="{{ route('chat.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('chat.*') ? 'active text-green-400' : 'text-gray-400' }}">
                <span class="text-lg">🤖</span> Ask AI
            </a>

            <div class="pt-4 mt-4 border-t border-white/5">
                <a href="{{ route('profile.edit') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400">
                    <span class="text-lg">👤</span> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 hover:text-red-400">
                        <span class="text-lg">🚪</span> Logout
                    </button>
                </form>
            </div>
        </nav>

        <div class="px-4 py-3 border-t border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #22c55e, #059669);">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'Farmer' }}</p>
                    <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->farm_name ?? 'My Farm' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header class="flex items-center justify-between px-4 lg:px-6 py-3 border-b border-white/5" style="background: rgba(15,15,26,0.8); backdrop-filter: blur(12px);">
            <button id="mobile-menu-btn" onclick="toggleMobile()" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h2 class="text-white font-bold text-lg">@yield('page-title', 'Dashboard')</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('alerts.index') }}" class="relative p-2 text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if(($uc ?? 0) > 0)<span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ $uc }}</span>@endif
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @if(session('success'))
                <div class="mb-4 p-4 rounded-xl text-sm font-semibold fade-up" style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #4ade80;">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>
</div>

<!-- Mobile sidebar -->
<div id="mobile-overlay" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden" onclick="toggleMobile()"></div>
<aside id="mobile-sidebar" class="fixed inset-y-0 left-0 w-60 z-50 transform -translate-x-full transition-transform duration-300 lg:hidden border-r border-white/5" style="background: rgba(15,15,26,0.98);">
    <div class="flex items-center justify-between px-5 py-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: linear-gradient(135deg, #22c55e, #059669);">🌾</div>
            <h1 class="text-white font-extrabold text-sm">Smart Farm</h1>
        </div>
        <button onclick="toggleMobile()" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>
    <nav class="px-3 py-2 space-y-0.5">
        <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'active text-green-400' : 'text-gray-400' }}"><span class="text-lg">🏠</span> Dashboard</a>
        <a href="{{ route('crops.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('crops.*') ? 'active text-green-400' : 'text-gray-400' }}"><span class="text-lg">🌱</span> My Crops</a>
        <a href="{{ route('sensors.history') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('sensors.*') ? 'active text-green-400' : 'text-gray-400' }}"><span class="text-lg">📊</span> Sensor Data</a>
        <a href="{{ route('alerts.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('alerts.*') ? 'active text-green-400' : 'text-gray-400' }}"><span class="text-lg">🔔</span> Alerts</a>
        <a href="{{ route('chat.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('chat.*') ? 'active text-green-400' : 'text-gray-400' }}"><span class="text-lg">🤖</span> Ask AI</a>
    </nav>
</aside>

<script>
function toggleMobile() {
    document.getElementById('mobile-sidebar').classList.toggle('-translate-x-full');
    document.getElementById('mobile-overlay').classList.toggle('hidden');
}
</script>
@stack('scripts')
</body>
</html>
