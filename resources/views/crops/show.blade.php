@extends('layouts.app')
@section('title', $crop->name . ' — Crop Detail')
@section('breadcrumb', 'crops / ' . $crop->name)

@php
    $emojis = ['rice'=>'🌾','wheat'=>'🌿','tomato'=>'🍅','corn'=>'🌽','potato'=>'🥔','sugarcane'=>'🎋','cotton'=>'☁️','soybean'=>'🫘'];
    $emoji = $emojis[strtolower($crop->name)] ?? '🌱';

    // ─── Growth Stages ─────────────────────────────
    $growthStages = [
        ['name'=>'Planting','icon'=>'🌱','desc'=>'Seeds sown into prepared soil'],
        ['name'=>'Germination','icon'=>'🌿','desc'=>'First shoots emerge from soil'],
        ['name'=>'Vegetative','icon'=>'🪴','desc'=>'Leaves and stems grow rapidly'],
        ['name'=>'Flowering','icon'=>'🌸','desc'=>'Flowers/heads appear on plant'],
        ['name'=>'Ripening','icon'=>'🌾','desc'=>'Fruits/grains mature and ripen'],
        ['name'=>'Harvest','icon'=>'🚜','desc'=>'Crop is ready to be harvested'],
    ];
    // Calculate current stage based on time progress
    $plantDate = $crop->planting_date;
    $harvestDate = $crop->expected_harvest_date;
    $currentStage = 0;
    if ($plantDate && $harvestDate && $harvestDate->gt($plantDate)) {
        $totalDays = $plantDate->diffInDays($harvestDate);
        $daysPassed = $plantDate->diffInDays(now());
        $progress = min(1, max(0, $daysPassed / max(1, $totalDays)));
        $currentStage = min(5, floor($progress * 6));
    }

    // ─── Health Score ──────────────────────────────
    $healthScore = 0;
    $sensorAnalysis = [];
    if ($latestReading) {
        $totalScore = 0; $cnt = 0;
        foreach ($idealRanges as $key => $range) {
            $val = $latestReading->{$key};
            if ($val >= $range['ideal_min'] && $val <= $range['ideal_max']) {
                $score = 100;
                $status = 'perfect';
            } elseif ($val < $range['ideal_min']) {
                $dist = $range['ideal_min'] - $val;
                $maxD = $range['ideal_min'] - $range['min'];
                $score = max(0, round(100 * (1 - ($maxD > 0 ? $dist / $maxD : 1))));
                $status = $score > 50 ? 'low_mild' : 'low_bad';
            } else {
                $dist = $val - $range['ideal_max'];
                $maxD = $range['max'] - $range['ideal_max'];
                $score = max(0, round(100 * (1 - ($maxD > 0 ? $dist / $maxD : 1))));
                $status = $score > 50 ? 'high_mild' : 'high_bad';
            }
            $sensorAnalysis[$key] = ['val'=>$val, 'score'=>$score, 'status'=>$status, 'range'=>$range];
            $totalScore += $score;
            $cnt++;
        }
        $healthScore = $cnt > 0 ? round($totalScore / $cnt) : 0;
    }

    // ─── Sensor Meta (farmer-friendly) ─────────────
    $sensorMeta = [
        'temperature' => [
            'label'=>'Temperature','unit'=>'°C','emoji'=>'🌡️','color'=>'#f97316',
            'what_it_is'=>'Air temperature around your crop canopy.',
            'perfect'=>'Your crop is at the ideal temperature. This promotes healthy growth and good photosynthesis.',
            'low_mild'=>'Slightly cool for this crop. Growth may slow down.',
            'low_bad'=>'Too cold! Risk of frost damage and stunted growth. Use mulch or row covers.',
            'high_mild'=>'Getting warm. The plant may wilt during peak hours.',
            'high_bad'=>'Heat stress! Leaves may burn. Install shade netting and increase irrigation.',
            'actions_low'=>['Apply straw mulch around plant base to retain warmth','Use transparent plastic row covers at night','Water in the morning so soil stores daytime heat'],
            'actions_high'=>['Install 40% shade cloth over the crop','Increase drip irrigation frequency to cool roots','Apply reflective mulch (white/silver) to lower soil temp'],
        ],
        'soil_moisture' => [
            'label'=>'Soil Moisture','unit'=>'%','emoji'=>'💧','color'=>'#3b82f6',
            'what_it_is'=>'How much water is available in the soil for roots.',
            'perfect'=>'Soil moisture is ideal. Roots have the right amount of water for nutrient uptake.',
            'low_mild'=>'Soil is drying out. Your crop will start feeling thirsty soon.',
            'low_bad'=>'Drought stress! Roots can\'t absorb enough water. Irrigate immediately.',
            'high_mild'=>'Soil is wetter than ideal. Reduce watering frequency.',
            'high_bad'=>'Waterlogged! Root rot risk. Improve drainage immediately.',
            'actions_low'=>['Start drip irrigation immediately','Apply organic mulch (2-4 inches) to reduce evaporation','Water deeply but less frequently to encourage deep root growth'],
            'actions_high'=>['Stop irrigation for 24-48 hours','Create drainage channels between rows','Loosen compacted soil to improve water flow'],
        ],
        'humidity' => [
            'label'=>'Air Humidity','unit'=>'%','emoji'=>'☁️','color'=>'#8b5cf6',
            'what_it_is'=>'Moisture level in the air surrounding your crops.',
            'perfect'=>'Air humidity is perfect. Low disease risk and good transpiration.',
            'low_mild'=>'Air is drier than ideal. Leaf edges may curl.',
            'low_bad'=>'Very dry air! Rapid water loss through leaves. Use misting if possible.',
            'high_mild'=>'Air is humid. Watch for early signs of fungal disease.',
            'high_bad'=>'Fungal disease risk is HIGH! Improve airflow between plants immediately.',
            'actions_low'=>['Use misting systems or sprinklers in early morning','Apply mulch to create micro-humidity at soil level','Group plants closer to retain humidity (temporary)'],
            'actions_high'=>['Prune lower leaves to improve air circulation','Increase row spacing if possible','Apply preventive fungicide (neem-based recommended)','Avoid evening watering — water in morning only'],
        ],
        'light_intensity' => [
            'label'=>'Sunlight','unit'=>'lux','emoji'=>'☀️','color'=>'#eab308',
            'what_it_is'=>'Amount of sunlight reaching your crop leaves.',
            'perfect'=>'Light levels are ideal for photosynthesis and fruit/grain development.',
            'low_mild'=>'Slightly less sunlight than ideal. Growth may be slower.',
            'low_bad'=>'Insufficient light! Plants may become leggy and weak.',
            'high_mild'=>'Intense sunlight. Adequate watering will prevent leaf burn.',
            'high_bad'=>'Extreme light! Risk of leaf scorch and sunburn on fruits.',
            'actions_low'=>['Prune nearby trees or structures causing shade','Use reflective mulch to redirect light to lower canopy','Consider relocating seedlings to sunnier position'],
            'actions_high'=>['Install 30-40% shade cloth during peak hours (11am-3pm)','Increase watering to compensate for higher evaporation','Spray kaolin clay on leaves as natural sunscreen'],
        ],
        'rainfall' => [
            'label'=>'Rainfall','unit'=>'mm','emoji'=>'🌧️','color'=>'#06b6d4',
            'what_it_is'=>'Amount of natural rain your crops received.',
            'perfect'=>'Rain is within normal range. Natural watering supplementing irrigation.',
            'low_mild'=>'Low rainfall. Your irrigation plan should compensate.',
            'low_bad'=>'No significant rain. Rely entirely on irrigation system.',
            'high_mild'=>'Moderate rain. Reduce irrigation to avoid over-watering.',
            'high_bad'=>'Heavy rainfall! Check for waterlogging, erosion, and crop damage.',
            'actions_low'=>['Ensure drip irrigation is running on schedule','Check soil moisture sensors — manual watering may be needed','Apply extra mulch to conserve any ground moisture'],
            'actions_high'=>['Inspect drainage channels for blockages','Prop up lodged (fallen) plants with stakes','Spray preventive fungicide within 48h of heavy rain','Reduce or skip scheduled irrigation'],
        ],
    ];

    // ─── Crop-Specific Care Guide ──────────────────
    $careGuides = [
        'rice' => [
            'overview'=>'Rice is a warm-season cereal grown in flooded paddies. It needs abundant water and high humidity.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'Maintain 2-5cm standing water in paddies throughout growth. Drain fields 2 weeks before harvest to let soil firm up.','frequency'=>'Continuous flooding'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Prefer clay or clay-loam soil with good water retention. Optimal pH: 5.5-6.5. Add organic matter before planting.','type'=>'Clay-loam'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun, 6-8 hours daily. Short-day varieties flower when daylight shortens.','hours'=>'6-8 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Apply nitrogen in 3 splits: basal dose at transplanting, 2nd at tillering (25 days), 3rd at panicle initiation (50 days).','schedule'=>'3-split NPK'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Watch for stem borers, leaf folders, and brown plant hoppers. Use neem-based IPM. Blast disease in humid weather.','common'=>'Stem borers, leaf blast'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Harvest when 80% of grains turn golden-brown. Grain moisture should be 20-25%. Dry to 14% before storage.','indicator'=>'80% golden grains'],
        ],
        'wheat' => [
            'overview'=>'Wheat is a cool-season crop that thrives in well-drained loamy soils with moderate temperatures.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'Wheat needs 4-6 irrigations. Critical stages: crown root initiation (21d), tillering (45d), flowering, and grain filling.','frequency'=>'4-6 irrigations'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Well-drained loamy or clay-loam soil. Ideal pH: 6.0-7.5. Prepare seedbed with fine tilth.','type'=>'Loamy soil'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun required. Wheat is a long-day plant — needs 10-14 hours of light for proper heading.','hours'=>'10-14 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Basal NPK at sowing. Top-dress nitrogen at first irrigation (21 days). Phosphorus is critical for root development.','schedule'=>'2-split N + P basal'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Yellow rust in cool-wet weather. Loose smut from contaminated seeds. Aphids in warm spells. Use resistant varieties.','common'=>'Yellow rust, aphids'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Harvest when stems turn golden and grain is hard. Thumb-nail test: grain should crack, not dent. Moisture ~14%.','indicator'=>'Golden stems, hard grain'],
        ],
        'tomato' => [
            'overview'=>'Tomatoes are warm-season fruits needing consistent warmth, moderate moisture, and plenty of sunlight.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'Water at the base, not on leaves (prevents blight). 1-2 inches per week. Consistent moisture prevents blossom end rot.','frequency'=>'1-2 inches/week'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Rich, well-drained loamy soil. pH 6.0-6.8. Add compost before planting. Calcium-rich soil prevents blossom end rot.','type'=>'Rich loamy'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun, minimum 6-8 hours daily. Tomatoes love warmth but fruits may sunscald above 35°C.','hours'=>'6-8 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Balanced 10-10-10 at transplant. Switch to high-potassium (0-0-60) when fruiting begins for better fruit quality.','schedule'=>'NPK → high-K at fruiting'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Tomato hornworm, whitefly, and late blight are common. Stake plants for airflow. Copper spray for blight prevention.','common'=>'Hornworm, late blight'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Pick when fully colored (red/orange). Gentle twist-and-pull. Green tomatoes ripen in paper bags at room temp.','indicator'=>'Full color development'],
        ],
        'corn' => [
            'overview'=>'Corn is a heavy-feeding warm-season crop that needs deep soil, full sun, and consistent moisture during tasseling.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'1.5-2 inches per week. Critical moisture period: tasseling to early grain fill. Drought here = up to 50% yield loss.','frequency'=>'1.5-2 inches/week'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Deep, fertile loam with good drainage. pH 5.8-7.0. Corn is a heavy nitrogen feeder — rotate with legumes.','type'=>'Deep fertile loam'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun essential, 8-10 hours. Corn is a C4 plant — very efficient at using sunlight for rapid growth.','hours'=>'8-10 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Heavy nitrogen requirements. Side-dress N at V6 stage (6-leaf). Zinc and sulfur may be needed in deficient soils.','schedule'=>'Heavy N + side-dress'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Corn earworm and fall armyworm are major pests. Northern corn leaf blight in humid weather. Bt varieties help.','common'=>'Earworm, armyworm'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Sweet corn: when silks turn brown and kernels release milky sap. Grain corn: when husks dry and kernels dent.','indicator'=>'Brown silks, milky kernels'],
        ],
        'potato' => [
            'overview'=>'Potatoes are cool-season tuber crops grown in loose, well-drained soil. Hilling soil around stems is essential.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'1-2 inches per week. Keep soil consistently moist but not wet. Irregular watering causes knobby or cracked tubers.','frequency'=>'1-2 inches/week'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Loose, well-drained sandy-loam. pH 5.0-6.0 (acidic). Avoid alkaline soil — causes scab disease. Add compost generously.','type'=>'Sandy-loam (acidic)'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun for foliage growth, but tubers grow underground in darkness. Hill soil to prevent greening (solanine toxin).','hours'=>'6+ hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'High potassium for tuber development. Apply balanced NPK at planting, side-dress potassium at hilling stage.','schedule'=>'NPK basal + K side-dress'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Colorado potato beetle (hand-pick or Bt). Late blight is devastating — use resistant varieties and copper fungicide.','common'=>'Beetle, late blight'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'New potatoes: 2-3 weeks after flowering. Main crop: when foliage dies back. Cure in dark, cool space for 2 weeks.','indicator'=>'Yellowed/dead foliage'],
        ],
        'sugarcane' => [
            'overview'=>'Sugarcane is a tropical perennial grass grown for sugar. It needs warmth, water, and a long growing season (10-18 months).',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'Requires 1500-2500mm of water over its lifecycle. Furrow irrigation is most common. Reduce water before harvest.','frequency'=>'Furrow irrigation'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Deep, well-drained loamy to clay-loam. pH 5.0-8.5. Sugarcane tolerates a wide pH range but prefers 6.5.','type'=>'Deep clay-loam'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun, 8-10 hours. As a C4 grass, sugarcane is extremely efficient at converting sunlight to biomass.','hours'=>'8-10 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Heavy nitrogen feeder. Apply N in 3 splits. Potassium improves juice quality. Add press-mud and bagasse as organic matter.','schedule'=>'Heavy N 3-split + K'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Sugarcane borer and woolly aphid. Red rot disease in waterlogged fields. Use hot water treated setts for planting.','common'=>'Borer, red rot'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Harvest at 10-18 months when Brix reading reaches 18-20%. Cut close to ground — the lower stem has highest sugar.','indicator'=>'Brix 18-20%'],
        ],
        'cotton' => [
            'overview'=>'Cotton is a warm-season fiber crop that needs a long frost-free period, full sun, and moderate water.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'Moderate water needs. Critical stages: flowering and boll formation. Excess water = boll rot. Deficit = small bolls.','frequency'=>'Moderate, drip preferred'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Well-drained black cotton soil (vertisol) or sandy-loam. pH 6.0-7.5. Good drainage is essential to prevent root rot.','type'=>'Black cotton soil'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun, 6-8 hours minimum. Cotton is very heat-tolerant and thrives in hot, sunny conditions.','hours'=>'6-8 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Moderate nitrogen (excess = vegetative growth over bolls). High potassium for fiber quality. Boron for boll retention.','schedule'=>'Moderate N + high K'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'American bollworm is the #1 pest. Whitefly and jassids suck sap. Use Bt cotton varieties and IPM.','common'=>'Bollworm, whitefly'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Pick when bolls fully open and fiber is fluffy white. Multiple pickings over 6-8 weeks as bolls open progressively.','indicator'=>'Open white bolls'],
        ],
        'soybean' => [
            'overview'=>'Soybean is a warm-season legume that fixes nitrogen in soil. It is moderately drought-tolerant once established.',
            'water'=>['title'=>'Water Management','icon'=>'💧','tip'=>'1-1.5 inches per week. Critical moisture: flowering (R1) to pod fill (R5). Drought here causes flower/pod abortion.','frequency'=>'1-1.5 inches/week'],
            'soil'=>['title'=>'Soil Requirements','icon'=>'🪱','tip'=>'Well-drained loamy soil. pH 6.0-7.0. Inoculate seeds with Bradyrhizobium for nitrogen fixation. Avoid waterlogging.','type'=>'Well-drained loam'],
            'sun'=>['title'=>'Sunlight Needs','icon'=>'☀️','tip'=>'Full sun, 6-8 hours. Soybean is a short-day plant — flowering triggered by shorter days (depends on variety group).','hours'=>'6-8 hrs/day'],
            'fertilizer'=>['title'=>'Fertilization','icon'=>'🧪','tip'=>'Low nitrogen needed (fixes its own). Apply phosphorus and potassium based on soil test. Molybdenum for nodulation.','schedule'=>'Low N, P+K basal'],
            'pests'=>['title'=>'Pest & Disease','icon'=>'🐛','tip'=>'Soybean aphid and bean leaf beetle. Soybean cyst nematode in repeated fields. Rotate with corn for best results.','common'=>'Aphid, cyst nematode'],
            'harvest'=>['title'=>'When to Harvest','icon'=>'🚜','tip'=>'Harvest when 95% of pods are brown and beans rattle inside. Grain moisture should be 13-14%.','indicator'=>'Brown pods, 13% moisture'],
        ],
    ];

    $guide = $careGuides[strtolower($crop->name)] ?? [
        'overview'=>'Monitor this crop regularly and maintain ideal growing conditions.',
        'water'=>['title'=>'Water','icon'=>'💧','tip'=>'Maintain consistent soil moisture.','frequency'=>'As needed'],
        'soil'=>['title'=>'Soil','icon'=>'🪱','tip'=>'Use well-drained soil with organic matter.','type'=>'General'],
        'sun'=>['title'=>'Sunlight','icon'=>'☀️','tip'=>'Provide adequate sunlight.','hours'=>'6+ hrs'],
        'fertilizer'=>['title'=>'Fertilizer','icon'=>'🧪','tip'=>'Apply balanced fertilizer as needed.','schedule'=>'Regular'],
        'pests'=>['title'=>'Pests','icon'=>'🐛','tip'=>'Monitor for common pests.','common'=>'Varies'],
        'harvest'=>['title'=>'Harvest','icon'=>'🚜','tip'=>'Harvest when mature.','indicator'=>'Visual maturity'],
    ];
@endphp

@section('content')
<div class="space-y-8">

    <!-- ═══════════ CROP HEADER ═══════════ -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 shadow-card animate-fade-up">
        <div class="absolute inset-0 bg-gradient-to-r from-green-50/80 via-white to-white"></div>
        <div class="relative p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-center gap-5">
                    <div class="grid h-20 w-20 place-items-center rounded-2xl bg-white shadow-elegant border border-gray-200">
                        <span class="text-5xl">{{ $emoji }}</span>
                    </div>
                    <div>
                        <h1 class="font-display text-3xl tracking-tight text-gray-900">{{ $crop->name }}</h1>
                        <p class="text-gray-500 flex items-center gap-1.5 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $crop->field_name }}
                        </p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $crop->status === 'active' ? 'chip-success' : 'chip-warning' }}">
                                {{ $crop->status === 'active' ? '● Growing' : '● Harvested' }}
                            </span>
                            @if($latestReading)
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $healthScore >= 80 ? 'chip-success' : ($healthScore >= 50 ? 'chip-warning' : 'chip-destructive') }}">
                                    Health: {{ $healthScore }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('crops.edit', $crop->id) }}" class="btn-outline">✏️ Edit</a>
                    <a href="{{ route('crops.index') }}" class="btn-ghost">← Back</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════ STATS ROW ═══════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 animate-fade-up" style="animation-delay: 60ms;">
        <div class="hiq-card p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">🌱 Planted</p>
            <p class="text-sm font-semibold text-gray-900">{{ $crop->planting_date?->format('d M Y') ?? 'N/A' }}</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">🌾 Expected Harvest</p>
            <p class="text-sm font-semibold text-gray-900">{{ $crop->expected_harvest_date?->format('d M Y') ?? 'N/A' }}</p>
        </div>
        <div class="hiq-card p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">⏳ Days Left</p>
            <p class="text-sm font-semibold {{ $crop->expected_harvest_date?->isFuture() ? 'text-green-600' : 'text-amber-600' }}">
                {{ $crop->expected_harvest_date ? ($crop->expected_harvest_date->isFuture() ? $crop->expected_harvest_date->diffInDays(now()) . ' days' : 'Past due') : '—' }}
            </p>
        </div>
        <div class="hiq-card p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">📊 Readings</p>
            <p class="text-sm font-semibold text-gray-900">{{ $sensorReadings->count() }}</p>
        </div>
    </div>

    <!-- ═══════════ GROWTH STAGE TIMELINE ═══════════ -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 120ms;">
        <h2 class="font-display text-lg text-gray-900 mb-5">🌿 Growth Stage</h2>
        <div class="relative">
            <!-- Progress track -->
            <div class="absolute top-6 left-6 right-6 h-1 rounded-full" style="background: var(--muted);"></div>
            <div class="absolute top-6 left-6 h-1 rounded-full bg-gradient-primary transition-all" style="width: calc({{ $currentStage / 5 * 100 }}% - 12px);"></div>

            <!-- Stage dots -->
            <div class="relative grid grid-cols-6 text-center">
                @foreach($growthStages as $j => $stage)
                    @php
                        $isActive = $j <= $currentStage;
                        $isCurrent = $j === $currentStage;
                    @endphp
                    <div class="flex flex-col items-center">
                        <div class="relative z-10 grid h-12 w-12 place-items-center rounded-full border-2 mb-2 {{ $isCurrent ? 'bg-gradient-primary text-white shadow-glow border-green-500' : ($isActive ? 'bg-green-50 text-green-600 border-green-200' : 'bg-white text-gray-300 border-gray-200') }}">
                            <span class="text-lg">{{ $stage['icon'] }}</span>
                        </div>
                        <span class="text-xs font-semibold {{ $isCurrent ? 'text-green-600' : ($isActive ? 'text-gray-700' : 'text-gray-400') }}">{{ $stage['name'] }}</span>
                        <span class="text-[10px] text-gray-400 mt-0.5 hidden md:block">{{ $stage['desc'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ═══════════ HEALTH SCORE & SENSOR DEEP-DIVE ═══════════ -->
    @if($latestReading)
    <div class="animate-fade-up" style="animation-delay: 180ms;">
        <h2 class="font-display text-xl text-gray-900 mb-4">🎯 Sensor Analysis</h2>
        <p class="text-sm text-gray-400 -mt-2 mb-4">Detailed breakdown of each sensor — what it means for your {{ strtolower($crop->name) }} and what to do about it.</p>

        <div class="space-y-4">
            @foreach($sensorMeta as $key => $meta)
                @php
                    $sa = $sensorAnalysis[$key] ?? null;
                    if (!$sa) continue;
                    $val = $sa['val'];
                    $score = $sa['score'];
                    $status = $sa['status'];
                    $range = $sa['range'];
                    $displayVal = $key === 'light_intensity' ? number_format($val) : round($val, 1);

                    $statusLabel = match($status) {
                        'perfect' => '✅ Perfect',
                        'low_mild' => '⚠️ Slightly Low',
                        'low_bad' => '❌ Too Low',
                        'high_mild' => '⚠️ Slightly High',
                        'high_bad' => '❌ Too High',
                    };
                    $statusColor = $status === 'perfect' ? 'chip-success' : (str_contains($status, 'mild') ? 'chip-warning' : 'chip-destructive');
                    $barColor = $status === 'perfect' ? '#22c55e' : (str_contains($status, 'mild') ? '#f59e0b' : '#ef4444');
                    $explanation = $meta[$status] ?? 'Monitor this reading.';
                    $actions = ($status !== 'perfect') ? ($meta[str_contains($status, 'low') ? 'actions_low' : 'actions_high'] ?? []) : [];

                    // Gauge calculations
                    $absMin = $range['min'] * 0.8;
                    $absMax = $range['max'] * 1.2;
                    $totalRange = max(1, $absMax - $absMin);
                    $valPct = max(0, min(100, (($val - $absMin) / $totalRange) * 100));
                    $idealMinPct = (($range['ideal_min'] - $absMin) / $totalRange) * 100;
                    $idealMaxPct = (($range['ideal_max'] - $absMin) / $totalRange) * 100;
                @endphp
                <div class="hiq-card p-5">
                    <div class="flex flex-wrap items-start gap-4">
                        <!-- Left: Sensor info -->
                        <div class="flex-1 min-w-[280px]">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl">{{ $meta['emoji'] }}</span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-display text-base text-gray-900">{{ $meta['label'] }}</h3>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusColor }}">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400">{{ $meta['what_it_is'] }}</p>
                                </div>
                            </div>

                            <!-- Value + Gauge -->
                            <div class="flex items-center gap-4 mb-3">
                                <div class="font-display text-3xl tabular-nums" style="color: {{ $meta['color'] }};">{{ $displayVal }}<span class="text-sm text-gray-400 ml-1">{{ $meta['unit'] }}</span></div>
                                <div class="flex-1">
                                    <div class="gauge-bar">
                                        <div class="gauge-ideal" style="left: {{ $idealMinPct }}%; width: {{ $idealMaxPct - $idealMinPct }}%;"></div>
                                        <div class="gauge-fill" style="width: {{ $valPct }}%; background: {{ $barColor }};"></div>
                                    </div>
                                    <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                                        <span>{{ $range['min'] }}</span>
                                        <span class="text-green-600 font-semibold">Ideal: {{ $range['ideal_min'] }}–{{ $range['ideal_max'] }}</span>
                                        <span>{{ $range['max'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Explanation -->
                            <div class="rounded-xl p-3 text-sm leading-relaxed" style="background: {{ $status === 'perfect' ? 'rgba(34,197,94,0.05)' : (str_contains($status, 'mild') ? 'rgba(245,158,11,0.05)' : 'rgba(239,68,68,0.05)') }}; border: 1px solid {{ $status === 'perfect' ? 'rgba(34,197,94,0.12)' : (str_contains($status, 'mild') ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)') }};">
                                <span class="font-semibold text-gray-700">What this means:</span>
                                <span class="text-gray-600">{{ $explanation }}</span>
                            </div>
                        </div>

                        <!-- Right: Actions (only if not perfect) -->
                        @if(count($actions) > 0)
                        <div class="w-full lg:w-72 flex-shrink-0">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">💡 What to do</h4>
                            <div class="space-y-2">
                                @foreach($actions as $action)
                                    <div class="flex items-start gap-2 rounded-lg p-2.5 text-sm" style="background: var(--muted);">
                                        <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                                        <span class="text-gray-600 text-xs leading-relaxed">{{ $action }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @elseif($crop->status === 'active')
    <div class="rounded-3xl border border-dashed border-gray-200 bg-gray-50/30 p-12 text-center">
        <div class="text-5xl mb-3">📡</div>
        <h3 class="font-display text-lg text-gray-900">No sensor readings yet</h3>
        <p class="mt-1 text-sm text-gray-500">Go to <a href="{{ route('dashboard') }}" class="text-green-600 font-semibold hover:underline">Dashboard</a> → "New Reading" to start collecting data!</p>
    </div>
    @endif

    <!-- ═══════════ CARE GUIDE ═══════════ -->
    <div class="animate-fade-up" style="animation-delay: 240ms;">
        <h2 class="font-display text-xl text-gray-900 mb-2">📚 {{ ucfirst($crop->name) }} Care Guide</h2>
        <p class="text-sm text-gray-400 mb-4">{{ $guide['overview'] }}</p>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach(['water','soil','sun','fertilizer','pests','harvest'] as $gKey)
                @php $g = $guide[$gKey]; @endphp
                <div class="hiq-card p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-2xl">{{ $g['icon'] }}</span>
                        <h3 class="font-display text-sm text-gray-900">{{ $g['title'] }}</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $g['tip'] }}</p>
                    <div class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[11px] font-medium" style="background: var(--muted); color: var(--fg);">
                        @if($gKey === 'water') 📅 {{ $g['frequency'] }}
                        @elseif($gKey === 'soil') 🪨 {{ $g['type'] }}
                        @elseif($gKey === 'sun') ⏰ {{ $g['hours'] }}
                        @elseif($gKey === 'fertilizer') 📋 {{ $g['schedule'] }}
                        @elseif($gKey === 'pests') ⚠️ {{ $g['common'] }}
                        @elseif($gKey === 'harvest') 🎯 {{ $g['indicator'] }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ═══════════ SENSOR TRENDS CHART ═══════════ -->
    <div class="hiq-card p-6 animate-fade-up" style="animation-delay: 300ms;">
        <h2 class="font-display text-lg text-gray-900 mb-4">📈 Sensor Trends — Last 24 Hours</h2>
        @if($sensorReadings->count() > 0)
            <div style="height: 300px;"><canvas id="cropChart"></canvas></div>
            <div class="mt-4 rounded-xl p-3 text-[11px] text-gray-400" style="background: var(--muted);">
                <strong class="text-gray-500">📖 Reading the chart:</strong>
                Each line shows one sensor over time. Smooth, flat lines mean stable conditions (good!).
                Sudden spikes may trigger alerts. The shaded area under each line shows the trend direction.
            </div>
        @else
            <div class="text-center py-10">
                <div class="text-4xl mb-2">📊</div>
                <p class="text-sm text-gray-400">No recent data. Generate readings from the Dashboard to see trends.</p>
            </div>
        @endif
    </div>

</div>
@endsection

@if($sensorReadings->count() > 0)
@push('scripts')
<script>
new Chart(document.getElementById('cropChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($sensorReadings->pluck('recorded_at')->map(fn($d) => $d->format('H:i'))) !!},
        datasets: [
            { label: '🌡️ Temperature (°C)', data: {!! json_encode($sensorReadings->pluck('temperature')) !!}, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '💧 Soil Moisture (%)', data: {!! json_encode($sensorReadings->pluck('soil_moisture')) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '☁️ Humidity (%)', data: {!! json_encode($sensorReadings->pluck('humidity')) !!}, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
            { label: '🌧️ Rainfall (mm)', data: {!! json_encode($sensorReadings->pluck('rainfall')) !!}, borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.06)', tension: 0.4, fill: true, pointRadius: 2, borderWidth: 2 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#64748b', font: { size: 11, family: 'Inter' }, usePointStyle: true, padding: 16 } },
            tooltip: { backgroundColor: 'white', titleColor: '#0f172a', bodyColor: '#64748b', borderColor: '#e2e8f0', borderWidth: 1, padding: 12, cornerRadius: 12 }
        },
        scales: {
            x: { ticks: { color: '#94a3b8', maxTicksLimit: 8 }, grid: { color: 'rgba(0,0,0,0.04)' } },
            y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(0,0,0,0.04)' } }
        }
    }
});
</script>
@endpush
@endif
