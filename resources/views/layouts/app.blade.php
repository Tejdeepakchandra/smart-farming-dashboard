<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🌾 {{ config('app.name', 'Harvest IQ') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg: #fafbfc;
            --fg: #0f172a;
            --card: #ffffff;
            --card-fg: #0f172a;
            --border: #e2e8f0;
            --muted: #f1f5f9;
            --muted-fg: #64748b;
            --primary: #16a34a;
            --primary-light: #22c55e;
            --primary-fg: #ffffff;
            --accent: #f0fdf4;
            --accent-fg: #0f172a;
            --success: #22c55e;
            --warning: #f59e0b;
            --warning-fg: #92400e;
            --destructive: #ef4444;
            --destructive-fg: #ffffff;
            --info: #3b82f6;
            --sidebar-w: 256px;
            --header-h: 56px;
        }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--fg); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.45s cubic-bezier(0.16,1,0.3,1) both; }

        @keyframes pulseSoft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-soft { animation: pulseSoft 2s ease-in-out infinite; }

        @keyframes bounceDot {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }
        .animate-bounce-dot { animation: bounceDot 1.4s ease-in-out infinite; }

        /* Gradients */
        .bg-gradient-primary { background: linear-gradient(135deg, var(--primary), var(--primary-light)); }
        .bg-gradient-card { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); }
        .bg-gradient-hero {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 25%, #f0f9ff 50%, #eff6ff 100%);
        }

        /* Shadows */
        .shadow-glow { box-shadow: 0 4px 14px rgba(22,163,74,0.25), 0 1px 3px rgba(22,163,74,0.1); }
        .shadow-card { box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02); }
        .shadow-elegant { box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04); }
        .shadow-soft { box-shadow: 0 1px 2px rgba(0,0,0,0.04); }

        /* Cards */
        .hiq-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .hiq-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--primary-fg);
            border-radius: 0.625rem;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(22,163,74,0.25);
            border: none;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.375rem;
        }
        .btn-primary:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(22,163,74,0.3); }
        .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .btn-outline {
            background: transparent;
            color: var(--fg);
            border: 1px solid var(--border);
            border-radius: 0.625rem;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.375rem;
        }
        .btn-outline:hover { background: var(--muted); border-color: #cbd5e1; }

        .btn-ghost {
            background: transparent;
            color: var(--muted-fg);
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .btn-ghost:hover { background: var(--muted); color: var(--fg); }

        /* Input */
        .input-field {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--fg);
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
        }
        .input-field::placeholder { color: #94a3b8; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width 0.25s cubic-bezier(0.16,1,0.3,1);
            overflow: hidden;
            z-index: 30;
        }
        .sidebar.collapsed { width: 64px; }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted-fg);
            transition: all 0.15s;
            text-decoration: none;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar-item:hover { background: var(--muted); color: var(--fg); }
        .sidebar-item.active {
            background: var(--accent);
            color: var(--primary);
            font-weight: 600;
        }
        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--primary);
            border-radius: 0 3px 3px 0;
        }
        .sidebar-item svg, .sidebar-item .icon-placeholder {
            width: 18px; height: 18px; flex-shrink: 0;
        }
        .sidebar .item-text { transition: opacity 0.15s; }
        .sidebar.collapsed .item-text { opacity: 0; pointer-events: none; }
        .sidebar.collapsed .sidebar-footer-full { display: none; }
        .sidebar.collapsed .sidebar-footer-mini { display: grid !important; }

        /* Font display */
        .font-display { font-weight: 700; letter-spacing: -0.02em; }

        /* Status chips */
        .chip-success { background: rgba(34,197,94,0.08); color: #16a34a; border: 1px solid rgba(34,197,94,0.2); }
        .chip-warning { background: rgba(245,158,11,0.08); color: #92400e; border: 1px solid rgba(245,158,11,0.2); }
        .chip-destructive { background: rgba(239,68,68,0.08); color: #dc2626; border: 1px solid rgba(239,68,68,0.2); }
        .chip-info { background: rgba(59,130,246,0.08); color: #2563eb; border: 1px solid rgba(59,130,246,0.2); }
        .chip-muted { background: var(--muted); color: var(--muted-fg); border: 1px solid var(--border); }

        /* Gauge */
        .gauge-bar {
            height: 10px;
            border-radius: 5px;
            background: var(--muted);
            overflow: hidden;
            position: relative;
        }
        .gauge-ideal {
            position: absolute;
            inset: 0;
            border-radius: 5px;
            background: rgba(34,197,94,0.12);
        }
        .gauge-fill {
            position: absolute;
            top: 0; bottom: 0; left: 0;
            border-radius: 5px;
            transition: width 0.8s cubic-bezier(0.16,1,0.3,1);
        }

        /* Tabular nums */
        .tabular-nums { font-variant-numeric: tabular-nums; }

        /* Ring colors for sensor cards */
        .ring-success { box-shadow: inset 0 0 0 1px rgba(34,197,94,0.2); }
        .ring-warning { box-shadow: inset 0 0 0 1px rgba(245,158,11,0.2); }
        .ring-destructive { box-shadow: inset 0 0 0 1px rgba(239,68,68,0.2); }
        .ring-info { box-shadow: inset 0 0 0 1px rgba(59,130,246,0.2); }

        /* Mobile overlay */
        .mobile-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            z-index: 40;
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s;
        }
        .mobile-overlay.open { opacity: 1; pointer-events: auto; }
        .mobile-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0;
            width: 280px;
            background: var(--card);
            border-right: 1px solid var(--border);
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .mobile-sidebar.open { transform: translateX(0); }

        @media (max-width: 1023px) {
            .sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="min-height: 100vh;">

<div class="flex h-screen overflow-hidden">

    <!-- ===== SIDEBAR (Desktop) ===== -->
    <aside id="sidebar" class="sidebar hidden lg:flex">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-4 border-b" style="border-color: var(--border);">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-primary text-white shadow-glow" style="font-size: 1rem;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div class="item-text leading-tight">
                    <div class="font-display text-sm">Harvest IQ</div>
                    <div class="text-xs" style="color: var(--muted-fg);">Smart Farming</div>
                </div>
            </a>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div class="text-[11px] font-semibold uppercase tracking-wider px-3 mb-2" style="color: var(--muted-fg);">
                <span class="item-text">Workspace</span>
            </div>

            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span class="item-text">Dashboard</span>
            </a>
            <a href="{{ route('crops.index') }}" class="sidebar-item {{ request()->routeIs('crops.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                <span class="item-text">My Crops</span>
            </a>
            <a href="{{ route('sensors.history') }}" class="sidebar-item {{ request()->routeIs('sensors.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span class="item-text">Sensors</span>
            </a>
            <a href="{{ route('alerts.index') }}" class="sidebar-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="item-text">Alerts</span>
                @php $uc = \App\Models\Alert::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                @if($uc > 0)
                    <span class="item-text ml-auto rounded-full px-2 py-0.5 text-[10px] font-semibold" style="background: var(--destructive); color: var(--destructive-fg);">{{ $uc }}</span>
                @endif
            </a>
            <a href="{{ route('chat.index') }}" class="sidebar-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="item-text">AI Chat</span>
            </a>

            <div class="pt-3 mt-3" style="border-top: 1px solid var(--border);"></div>
            <div class="text-[11px] font-semibold uppercase tracking-wider px-3 mb-2" style="color: var(--muted-fg);">
                <span class="item-text">Intelligence</span>
            </div>

            <a href="{{ route('farm-map') }}" class="sidebar-item {{ request()->routeIs('farm-map') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="item-text">Farm Map</span>
            </a>
            <a href="{{ route('weather.index') }}" class="sidebar-item {{ request()->routeIs('weather.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                <span class="item-text">Weather</span>
            </a>
            <a href="{{ route('analytics.index') }}" class="sidebar-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="item-text">Analytics</span>
            </a>
            <a href="{{ route('irrigation.index') }}" class="sidebar-item {{ request()->routeIs('irrigation.*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="item-text">Irrigation</span>
            </a>

            <div class="pt-4 mt-4" style="border-top: 1px solid var(--border);"></div>
            <a href="{{ route('profile.edit') }}" class="sidebar-item">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="item-text">Profile</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="sidebar-item w-full hover:!text-red-500">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="item-text">Logout</span>
                </button>
            </form>
        </nav>

        <!-- User footer -->
        <div class="border-t px-3 py-3" style="border-color: var(--border);">
            <div class="sidebar-footer-full flex items-center gap-3 px-2">
                <div class="grid h-9 w-9 place-items-center rounded-full text-sm font-semibold" style="background: var(--accent); color: var(--primary);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1 leading-tight item-text">
                    <div class="truncate text-sm font-medium">{{ auth()->user()->name ?? 'Farmer' }}</div>
                    <div class="truncate text-xs" style="color: var(--muted-fg);">{{ auth()->user()->farm_name ?? 'My Farm' }}</div>
                </div>
            </div>
            <div class="sidebar-footer-mini hidden place-items-center py-1">
                <div class="grid h-8 w-8 place-items-center rounded-full text-xs font-semibold" style="background: var(--accent); color: var(--primary);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <!-- Header -->
        <header class="sticky top-0 z-10 flex items-center gap-3 px-4 lg:px-6 border-b" style="height: var(--header-h); background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-color: var(--border);">
            <!-- Mobile toggle -->
            <button id="mobile-menu-btn" onclick="toggleMobile()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" style="color: var(--muted-fg);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Breadcrumb -->
            <div class="text-sm capitalize" style="color: var(--muted-fg);">@yield('breadcrumb', str_replace('-', ' ', request()->segment(1) ?? 'dashboard'))</div>

            <!-- Right side -->
            <div class="ml-auto flex items-center gap-2">
                <!-- Search (desktop only) -->
                <div class="hidden md:flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm w-64" style="background: var(--muted); color: var(--muted-fg); border: 1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <span>Search crops, fields…</span>
                </div>
                <!-- Alert bell -->
                <a href="{{ route('alerts.index') }}" class="relative grid h-9 w-9 place-items-center rounded-lg hover:bg-gray-100" style="border: 1px solid var(--border);">
                    <svg class="w-4 h-4" style="color: var(--fg);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if(($uc ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 grid h-4 min-w-[16px] place-items-center rounded-full px-1 text-[10px] font-bold" style="background: var(--destructive); color: var(--destructive-fg);">{{ $uc }}</span>
                    @endif
                </a>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            <div class="max-w-[1400px] mx-auto w-full">
                @if(session('success'))
                    <div class="mb-5 flex items-center gap-2 rounded-xl p-4 text-sm font-medium animate-fade-up" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2); color: #16a34a;">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>
</div>

<!-- ===== MOBILE SIDEBAR ===== -->
<div id="mobile-overlay" class="mobile-overlay lg:hidden" onclick="toggleMobile()"></div>
<aside id="mobile-sidebar" class="mobile-sidebar lg:hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border);">
        <div class="flex items-center gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-primary text-white shadow-glow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <span class="font-display text-sm">Harvest IQ</span>
        </div>
        <button onclick="toggleMobile()" class="p-1.5 rounded-lg hover:bg-gray-100" style="color: var(--muted-fg);">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="px-3 py-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('crops.index') }}" class="sidebar-item {{ request()->routeIs('crops.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
            <span>My Crops</span>
        </a>
        <a href="{{ route('sensors.history') }}" class="sidebar-item {{ request()->routeIs('sensors.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span>Sensors</span>
        </a>
        <a href="{{ route('alerts.index') }}" class="sidebar-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span>Alerts</span>
        </a>
        <a href="{{ route('chat.index') }}" class="sidebar-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>AI Chat</span>
        </a>
        <div class="pt-2 mt-2" style="border-top: 1px solid var(--border);"></div>
        <div class="text-[10px] font-semibold uppercase tracking-wider px-3 mb-1" style="color: var(--muted-fg);">Intelligence</div>
        <a href="{{ route('farm-map') }}" class="sidebar-item {{ request()->routeIs('farm-map') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            <span>Farm Map</span>
        </a>
        <a href="{{ route('weather.index') }}" class="sidebar-item {{ request()->routeIs('weather.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
            <span>Weather</span>
        </a>
        <a href="{{ route('analytics.index') }}" class="sidebar-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Analytics</span>
        </a>
        <a href="{{ route('irrigation.index') }}" class="sidebar-item {{ request()->routeIs('irrigation.*') ? 'active' : '' }}">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Irrigation</span>
        </a>
    </nav>
</aside>

<script>
function toggleMobile() {
    document.getElementById('mobile-sidebar').classList.toggle('open');
    document.getElementById('mobile-overlay').classList.toggle('open');
}
</script>
@stack('scripts')
</body>
</html>
