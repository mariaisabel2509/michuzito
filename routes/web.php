<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/2fa/challenge',  [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/verify',    [TwoFactorController::class, 'verify']);
    Route::post('/2fa/resend',    [TwoFactorController::class, 'resend'])->name('2fa.resend');
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'show'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.send');
    Route::get('/reset-password',   [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',  [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');

    Route::get('/perfil',            [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil',            [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/contrasena', [ProfileController::class, 'changePassword'])->name('profile.password');

    Route::get('/pagos',                       [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/pagos',                      [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/invoice/{invoice}',  [PaymentController::class, 'invoice'])->name('payments.invoice');
    Route::get('/payments/pending/{payment}',  [PaymentController::class, 'pending'])->name('payments.pending');

    Route::middleware('role:administrador')->group(function () {
        Route::get('/admin/users',                        [UserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users',                       [UserController::class, 'store']);
        Route::patch('/admin/users/{user}/role',          [UserController::class, 'assignRole']);
        Route::patch('/admin/users/{user}/deactivate',    [UserController::class, 'deactivate']);
        Route::get('/admin/payments',                     [PaymentController::class, 'index'])->name('admin.payments');
        Route::patch('/admin/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    });
});