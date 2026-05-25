<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Harvest IQ') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-weight: 700; letter-spacing: -0.02em; }
        .bg-gradient-primary { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .shadow-glow { box-shadow: 0 4px 14px rgba(22,163,74,0.25); }
        .input-field {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            border-radius: 0.5rem;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem;
            padding: 0.625rem 1.5rem;
            box-shadow: 0 4px 14px rgba(22,163,74,0.25);
            border: none; cursor: pointer; width: 100%;
            transition: all 0.2s;
        }
        .btn-primary:hover { opacity: 0.92; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(15px)} to{opacity:1;transform:translateY(0)} }
        .animate-fade-up { animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 25%, #f0f9ff 50%, #eff6ff 100%);">
    <div class="w-full max-w-md px-6 animate-fade-up">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2.5">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-primary text-white shadow-glow">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <span class="font-display text-xl text-gray-900">Harvest IQ</span>
            </a>
        </div>
        <!-- Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg p-8">
            {{ $slot }}
        </div>
        <p class="text-center mt-6 text-xs text-gray-400">Harvest IQ — Smart Farming Dashboard</p>
    </div>
</body>
</html>
