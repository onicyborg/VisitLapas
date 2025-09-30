<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InmatesController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\CountersController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Public display (no auth)
Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/api/v1/display', [DisplayController::class, 'data'])->name('display.data');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Inmates Management
    Route::get('/inmates', [InmatesController::class, 'index'])->name('inmates.index');
    Route::get('/inmates/{id}', [InmatesController::class, 'show'])->name('inmates.show');
    Route::post('/inmates', [InmatesController::class, 'store'])->name('inmates.store');
    Route::put('/inmates/{id}', [InmatesController::class, 'update'])->name('inmates.update');
    Route::delete('/inmates/{id}', [InmatesController::class, 'destroy'])->name('inmates.destroy');

    // Visitors Management
    Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
    Route::get('/visitors/{id}', [VisitorController::class, 'show'])->name('visitors.show');
    Route::post('/visitors', [VisitorController::class, 'store'])->name('visitors.store');
    Route::put('/visitors/{id}', [VisitorController::class, 'update'])->name('visitors.update');
    Route::delete('/visitors/{id}', [VisitorController::class, 'destroy'])->name('visitors.destroy');

    // Counters Management
    Route::resource('counters', CountersController::class)->except(['show']);
    Route::patch('counters/{counter}/toggle', [CountersController::class, 'toggle'])->name('counters.toggle');

    // Queues Management (explicit)
    Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
    Route::post('/queues', [QueueController::class, 'store'])->name('queues.store');
    Route::put('/queues/{queue}', [QueueController::class, 'update'])->name('queues.update');
    Route::post('/queues/{queue}/call', [QueueController::class, 'call'])->name('queues.call');
    Route::post('/queues/{queue}/start', [QueueController::class, 'start'])->name('queues.start');
    Route::post('/queues/{queue}/finish', [QueueController::class, 'finish'])->name('queues.finish');
    Route::post('/queues/{queue}/no-show', [QueueController::class, 'noShow'])->name('queues.no_show');
    Route::post('/queues/{queue}/cancel', [QueueController::class, 'cancel'])->name('queues.cancel');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto'])->name('profile.photo');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change_password');

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin-only: update display settings
    Route::patch('/display-settings', [DisplayController::class, 'updateSettings'])->name('display.settings.update');
});
