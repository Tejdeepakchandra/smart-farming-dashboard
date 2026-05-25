<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Harvest IQ — Smart Farming Dashboard with IoT + AI</title>
    <meta name="description" content="Monitor crops in real time with IoT sensors, get AI-powered farming advice, and never miss a critical alert. Built for modern farmers.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-weight: 700; letter-spacing: -0.02em; }
        .bg-gradient-primary { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .shadow-glow { box-shadow: 0 4px 14px rgba(22,163,74,0.25); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) both; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        @keyframes pulseSoft { 0%,100%{opacity:1} 50%{opacity:.5} }
        .animate-pulse-soft { animation: pulseSoft 2s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .animate-float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased bg-white min-h-screen">

    <!-- Nav -->
    <header class="sticky top-0 z-20 border-b border-gray-100/60" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="/" class="flex items-center gap-2.5">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-primary text-white shadow-glow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <span class="font-display text-lg">Harvest IQ</span>
            </a>
            <nav class="hidden md:flex gap-8 text-sm text-gray-500">
                <a href="#features" class="hover:text-gray-900 transition">Features</a>
                <a href="#crops" class="hover:text-gray-900 transition">Crops</a>
                <a href="#stack" class="hover:text-gray-900 transition">Stack</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-primary text-white text-sm font-semibold px-4 py-2 shadow-glow hover:opacity-92 transition">
                        Dashboard
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 px-3 py-2 transition">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-primary text-white text-sm font-semibold px-4 py-2 shadow-glow hover:opacity-92 transition">Get Started</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 25%, #f0f9ff 50%, #eff6ff 100%);">
        <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 50%, rgba(34,197,94,0.15) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(59,130,246,0.1) 0%, transparent 50%);"></div>
        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-6 py-24 md:grid-cols-2 md:py-32">
            <!-- Left -->
            <div class="animate-fade-up">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white/60 px-3 py-1 text-xs font-medium text-gray-500 backdrop-blur">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse-soft"></span>
                    IoT sensors live • AI advice ready
                </div>
                <h1 class="font-display text-5xl leading-[1.05] tracking-tight text-gray-900 md:text-6xl" style="text-wrap: balance;">
                    Grow more, <span class="bg-gradient-to-r from-green-600 to-emerald-500 bg-clip-text text-transparent">guess less.</span>
                </h1>
                <p class="mt-5 max-w-lg text-lg text-gray-500" style="text-wrap: balance;">
                    Real-time IoT sensor data, smart threshold alerts, and AI-powered crop recommendations — all in one beautiful dashboard farmers actually want to open.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-primary text-white font-semibold px-6 py-3 shadow-glow hover:opacity-92 transition">
                        Start for free
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white text-gray-700 font-semibold px-6 py-3 hover:bg-gray-50 transition">
                        View live demo
                    </a>
                </div>
                <div class="mt-8 flex items-center gap-6 text-xs text-gray-400">
                    <div><span class="font-semibold text-gray-700">3,200+</span> farmers</div>
                    <div><span class="font-semibold text-gray-700">12M</span> readings/day</div>
                    <div><span class="font-semibold text-gray-700">98%</span> uptime</div>
                </div>
            </div>

            <!-- Right — Preview cards -->
            <div class="relative animate-fade-up delay-200">
                <div class="absolute -inset-6 rounded-[2rem] bg-gradient-primary opacity-15 blur-3xl"></div>
                <div class="relative grid grid-cols-2 gap-4">
                    <div class="col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-lg">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl">🌡️</span>
                            <span class="text-sm font-medium text-gray-500">Temperature</span>
                            <span class="ml-auto rounded-full bg-green-50 border border-green-200 px-2 py-0.5 text-[11px] font-semibold text-green-700">Perfect</span>
                        </div>
                        <div class="font-display text-4xl tracking-tight">26.4<span class="text-lg text-gray-400 ml-1">°C</span></div>
                        <div class="mt-3 h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-green-500 transition-all" style="width: 58%;"></div>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-400">Best: 18–30 °C</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-md">
                        <span class="text-2xl">💧</span>
                        <div class="mt-2 font-display text-2xl tracking-tight">62.1<span class="text-sm text-gray-400 ml-0.5">%</span></div>
                        <div class="text-xs text-gray-400 mt-0.5">Soil Water</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-md">
                        <span class="text-2xl">☀️</span>
                        <div class="mt-2 font-display text-2xl tracking-tight">28,400<span class="text-sm text-gray-400 ml-0.5">lux</span></div>
                        <div class="text-xs text-gray-400 mt-0.5">Sunlight</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="bg-white py-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl tracking-tight text-gray-900">Everything your farm needs</h2>
                <p class="mt-2 text-gray-500">Three pillars of smart agriculture, unified in one dashboard.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @php
                    $features = [
                        ['icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>', 'title' => 'IoT Sensors', 'desc' => 'Monitor temperature, soil moisture, humidity, light, and rainfall with simulated sensor data — no hardware needed.', 'color' => 'green'],
                        ['icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>', 'title' => 'AI Advice', 'desc' => 'Get personalized crop recommendations powered by Google Gemini AI, tailored to your current sensor readings.', 'color' => 'blue'],
                        ['icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>', 'title' => 'Smart Alerts', 'desc' => 'Automatic warnings for drought, heat stress, fungal risk, and heavy rainfall — never miss a critical moment.', 'color' => 'amber'],
                    ];
                @endphp
                @foreach($features as $i => $f)
                    <div class="animate-fade-up rounded-2xl border border-gray-100 bg-white p-8 hover:shadow-xl hover:border-gray-200 transition-all group" style="animation-delay: {{ $i * 100 }}ms;">
                        <div class="grid h-12 w-12 place-items-center rounded-xl mb-5 {{ $f['color'] === 'green' ? 'bg-green-50 text-green-600' : ($f['color'] === 'blue' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600') }}">
                            {!! $f['icon'] !!}
                        </div>
                        <h3 class="font-display text-lg text-gray-900 mb-2">{{ $f['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Crop Types -->
    <section id="crops" class="py-20" style="background: #f8fafc;">
        <div class="mx-auto max-w-6xl px-6 text-center">
            <h2 class="font-display text-3xl tracking-tight text-gray-900 mb-3">Supports 8+ crop types</h2>
            <p class="text-gray-500 mb-8">Each crop comes with pre-configured ideal sensor ranges for accurate monitoring.</p>
            <div class="flex flex-wrap justify-center gap-3">
                @php $crops = ['🌾 Rice','🌿 Wheat','🍅 Tomato','🌽 Corn','🥔 Potato','🎋 Sugarcane','☁️ Cotton','🫘 Soybean']; @endphp
                @foreach($crops as $c)
                    <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:shadow-md hover:border-green-200 hover:bg-green-50 transition-all cursor-default">{{ $c }}</div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section id="stack" class="bg-white py-20">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <h2 class="font-display text-3xl tracking-tight text-gray-900 mb-3">Built with modern tools</h2>
            <p class="text-gray-500 mb-8">Production-grade stack for reliability and performance.</p>
            <div class="flex flex-wrap justify-center gap-3">
                @php $tech = ['Laravel 12','MongoDB Atlas','Blade + Tailwind','Chart.js','Google Gemini AI','Laravel Breeze']; @endphp
                @foreach($tech as $t)
                    <span class="rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-500">{{ $t }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
        <div class="mx-auto max-w-2xl px-6 text-center">
            <h2 class="font-display text-3xl tracking-tight text-gray-900 mb-3">Ready to grow smarter?</h2>
            <p class="text-gray-500 mb-8">Join thousands of farmers making data-driven decisions.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-primary text-white font-semibold px-8 py-3.5 shadow-glow hover:opacity-92 transition text-base">
                Start for free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-100 py-8 text-center">
        <p class="text-sm text-gray-400">Harvest IQ — Smart Farming Dashboard © {{ date('Y') }}</p>
    </footer>

</body>
</html>
