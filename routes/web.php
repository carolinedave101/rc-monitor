<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\JourneyController as AdminJourneyController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\JourneyController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/journey', JourneyController::class)->name('journey.index');

    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::get('/devices/{device}', [DeviceController::class, 'show'])->name('devices.show');
    Route::patch('/devices/{device}/status', [DeviceController::class, 'updateStatus'])->name('devices.status');
    Route::post('/devices/{device}/consent', [DeviceController::class, 'markConsented'])->name('devices.consent');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/rules', [AlertController::class, 'rules'])->name('alerts.rules');
    Route::post('/alerts/rules', [AlertController::class, 'storeRule'])->name('alerts.rules.store');
    Route::delete('/alerts/rules/{rule}', [AlertController::class, 'destroyRule'])->name('alerts.rules.destroy');
    Route::post('/alerts/rules/{rule}/toggle', [AlertController::class, 'toggleRule'])->name('alerts.rules.toggle');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/features', [AdminFeatureController::class, 'index'])->name('features.index');
    Route::patch('/features/{feature}', [AdminFeatureController::class, 'update'])->name('features.update');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');

    Route::post('/users/{user}/steps', [AdminJourneyController::class, 'store'])->name('users.steps.store');
    Route::post('/steps/{step}/start', [AdminJourneyController::class, 'start'])->name('steps.start');
    Route::post('/steps/{step}/advance', [AdminJourneyController::class, 'advance'])->name('steps.advance');
    Route::post('/steps/{step}/pause', [AdminJourneyController::class, 'pause'])->name('steps.pause');
    Route::post('/steps/{step}/resume', [AdminJourneyController::class, 'resume'])->name('steps.resume');
    Route::delete('/steps/{step}', [AdminJourneyController::class, 'destroy'])->name('steps.destroy');

    Route::get('/devices', [AdminDeviceController::class, 'index'])->name('devices.index');
    Route::patch('/devices/{device}', [AdminDeviceController::class, 'updateStatus'])->name('devices.update-status');
    Route::post('/devices/{device}/rotate-token', [AdminDeviceController::class, 'rotateToken'])->name('devices.rotate-token');
});
