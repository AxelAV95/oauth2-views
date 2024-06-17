<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/test', function () {
    return view('welcome');
});

Route::get('login', function(){
    return view('auth.login');
})->name('login');

Route::get('register', function(){
    return view('auth.register');
})->name('register');

Route::get('dashboard', function(){
    $user = session('user');
    $accessToken = session('access_token');
    $refreshToken = session('refresh_token');

    // Puedes hacer lo que necesites con los datos del usuario y los tokens de acceso
    // Por ejemplo, podrías pasarlos a la vista del dashboard para mostrar información del usuario
    return view('welcome', [
        'user' => $user,
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
    ]);
})->name('dashboard')->middleware('auth');

Route::post('login', [AuthController::class, 'loginOauth2'])->name('login.post');
Route::post('register', [AuthController::class, 'loginOauth2'])->name('register.post');

// Oauth callback
Route::get('oauth/callback', [AuthController::class, 'oauthCallback'])->name('oauth.callback');