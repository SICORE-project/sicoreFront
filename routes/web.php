<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;



Route::middleware('guest')->group(function (): void {

    Route::get('/', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.submit');


    // --- Réinitialisation du mot de passe (flux OTP en 3 étapes) ---

        // Étape 1 — email
        Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
            ->name('password.reset.form');
        Route::post('/forgot-password', [AuthController::class, 'sendOtp'])
            ->middleware('throttle:6,1')
            ->name('password.reset.send');

        // Étape 2 — code OTP
        Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])
            ->name('password.reset.otp');
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
            ->middleware('throttle:6,1')
            ->name('password.reset.otp.submit');
        Route::post('/verify-otp/resend', [AuthController::class, 'resendOtp'])
            ->middleware('throttle:3,1')
            ->name('password.reset.otp.resend');

        // Étape 3 — nouveau mot de passe
        Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])
            ->name('password.reset.newpassword');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:6,1')
            ->name('password.reset.submit');

    });




Route::middleware('sicore.auth')->group(function (): void {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

   Route::view('/dashboard', 'pages.dashboard.index')->name('dashboard');

});


require __DIR__.'/modules/administration.php';
require __DIR__.'/modules/parametrage.php';
require __DIR__.'/modules/paie.php';
require __DIR__.'/modules/indemnites.php';