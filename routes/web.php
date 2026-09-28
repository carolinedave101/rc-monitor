<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

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