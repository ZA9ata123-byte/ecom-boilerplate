<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// use App\Http\Controllers\Auth\SimpleLoginController;

// Route::post('/login',  [SimpleLoginController::class,'login'])->name('login');
// Route::post('/logout', [SimpleLoginController::class,'logout']);
// Route::get('/api/user', [SimpleLoginController::class,'me']); // باش سكريبتك يتحقق من الأوث
