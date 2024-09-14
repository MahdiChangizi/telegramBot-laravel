<?php

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WebAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->name('telegram.webhook');
Route::get('/telegram/handle', [TelegramController::class, 'handle']);

Route::get('/webapp', [WebAppController::class, 'index'])->name('webapp.index');
