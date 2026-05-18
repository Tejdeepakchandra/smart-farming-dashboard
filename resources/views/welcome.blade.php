<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Farming Dashboard — IoT & AI for Modern Agriculture</title>
    <meta name="description" content="Monitor your crops with IoT sensors and AI-powered insights. Smart Farming Dashboard helps farmers make data-driven decisions.">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Nunito', sans-serif; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .float { animation: float 3s ease-in-out infinite; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease-out forwards; }
        .fade-d1 { animation-delay: 0.1s; opacity: 0; }
        .fade-d2 { animation-delay: 0.2s; opacity: 0; }
        .fade-d3 { animation-delay: 0.3s; opacity: 0; }
    </style>
</head>
<body class="antialiased" style="background: linear-gradient(180deg, #0a0a15 0%, #1a1a2e 40%, #16213e 100%); min-height: 100vh;">

    <!-- Nav -->
    <nav class="flex items-center justify-between px-6 lg:px-12 py-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background: linear-gradient(135deg, #22c55e, #059669);">🌾</div>
            <span class="text-white font-extrabold text-lg">Smart Farm</span>
        </div>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-xl text-sm font-bold text-white" style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 15px rgba(34,197,94,0.3);">Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="text-gray-400 hover:text-white text-sm font-bold px-4 py-2">Login</a>
                <a href="{{ route('register') }}" class="px-5 py-2 rounded-xl text-sm font-bold text-white" style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 15px rgba(34,197,94,0.3);">Get Started →</a>
            @endauth
        </div>
    </nav>

    <!-- Hero -->
    <section class="px-6 lg:px-12 py-16 lg:py-24 text-center max-w-4xl mx-auto">
        <div class="fade-up">
            <p class="text-6xl mb-4 float">🌾</p>
            <h1 class="text-4xl lg:text-6xl font-black text-white leading-tight mb-4">
                Smart Farming<br>
                <span class="bg-gradient-to-r from-green-400 to-emerald-500 bg-clip-text text-transparent">Dashboard</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-xl mx-auto mb-8">Monitor your crops with IoT sensors, get AI-powered farming advice, and make smarter decisions for better harvests.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="px-8 py-3 rounded-xl text-base font-bold text-white" style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 6px 20px rgba(34,197,94,0.3);">🚀 Start for Free</a>
                <a href="{{ route('login') }}" class="px-8 py-3 rounded-xl text-base font-bold text-gray-300" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">Login →</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="px-6 lg:px-12 py-12 max-w-5xl mx-auto">
        <h2 class="text-2xl font-extrabold text-white text-center mb-8">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="fade-up fade-d1 p-6 rounded-2xl text-center" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.15);">
                <p class="text-4xl mb-3">📡</p>
                <h3 class="text-lg font-extrabold text-white mb-2">IoT Sensors</h3>
                <p class="text-gray-400 text-sm">Monitor temperature, soil moisture, humidity, light, and rainfall in real-time</p>
            </div>
            <div class="fade-up fade-d2 p-6 rounded-2xl text-center" style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                <p class="text-4xl mb-3">🤖</p>
                <h3 class="text-lg font-extrabold text-white mb-2">AI Advice</h3>
                <p class="text-gray-400 text-sm">Get personalized crop recommendations powered by Google Gemini AI</p>
            </div>
            <div class="fade-up fade-d3 p-6 rounded-2xl text-center" style="background: rgba(139,92,246,0.08); border: 1px solid rgba(139,92,246,0.15);">
                <p class="text-4xl mb-3">🚨</p>
                <h3 class="text-lg font-extrabold text-white mb-2">Smart Alerts</h3>
                <p class="text-gray-400 text-sm">Automatic warnings for drought, heat stress, humidity, and heavy rain</p>
            </div>
        </div>
    </section>

    <!-- Crop Types -->
    <section class="px-6 lg:px-12 py-12 max-w-5xl mx-auto">
        <h2 class="text-2xl font-extrabold text-white text-center mb-8">Supports 8+ Crop Types</h2>
        <div class="flex flex-wrap justify-center gap-4">
            @php $crops = ['🌾 Rice','🌿 Wheat','🍅 Tomato','🌽 Corn','🥔 Potato','🎋 Sugarcane','☁️ Cotton','🫘 Soybean']; @endphp
            @foreach($crops as $c)
                <div class="px-4 py-2 rounded-xl text-sm font-bold text-gray-300" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">{{ $c }}</div>
            @endforeach
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="px-6 lg:px-12 py-12 max-w-4xl mx-auto">
        <h2 class="text-2xl font-extrabold text-white text-center mb-8">Built With</h2>
        <div class="flex flex-wrap justify-center gap-3">
            @php $tech = ['Laravel 12','MongoDB Atlas','Blade + Tailwind','Chart.js','Google Gemini AI','Laravel Breeze']; @endphp
            @foreach($tech as $t)
                <span class="px-4 py-2 rounded-full text-xs font-bold" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); color: #9ca3af;">{{ $t }}</span>
            @endforeach
        </div>
    </section>

    <footer class="text-center py-8 text-gray-600 text-sm">
        <p>Smart Farming Dashboard • Semester Project © {{ date('Y') }}</p>
    </footer>
</body>
</html>
