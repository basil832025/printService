<?php

use App\Http\Controllers\Web\AgentController;
use App\Http\Controllers\Web\ApiKeyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\JobController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

Route::middleware(['auth', 'tenant.access'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/cabinet/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/cabinet/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/cabinet/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/cabinet/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::post('/cabinet/api-keys/site', [ApiKeyController::class, 'storeSite'])->name('api-keys.store-site');
    Route::delete('/cabinet/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
});

Route::middleware(['auth', 'tenant.access'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
