<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AiInsightController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Crops — full CRUD
    Route::resource('crops', CropController::class);

    // Sensor Data
    Route::get('/sensors/data', [SensorController::class, 'data'])->name('sensors.data');
    Route::get('/sensors/latest', [SensorController::class, 'latest'])->name('sensors.latest');
    Route::get('/sensors/history', [SensorController::class, 'history'])->name('sensors.history');

    // Web-based Sensor Simulation (no terminal needed!)
    Route::post('/sensors/simulate', [SensorController::class, 'simulate'])->name('sensors.simulate');
    Route::post('/sensors/simulate-batch', [SensorController::class, 'simulateBatch'])->name('sensors.simulateBatch');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/mark-all-read', [AlertController::class, 'markAllRead'])->name('alerts.markAllRead');
    Route::post('/alerts/{id}/mark-read', [AlertController::class, 'markRead'])->name('alerts.markRead');
    Route::get('/alerts/unread-count', [AlertController::class, 'unreadCount'])->name('alerts.unreadCount');

    // AI Insights
    Route::post('/ai-insights/generate', [AiInsightController::class, 'generate'])->name('ai-insights.generate');

    // AI Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

    // Profile (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
