<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('items', ItemController::class);
Route::resource('stock-movements', StockMovementController::class);
Route::resource('notifications', NotificationController::class);

Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
