<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/charts', [DashboardController::class, 'charts'])->name('charts');
Route::get('/api/items/{item}/movements', [DashboardController::class, 'getItemMovements'])->name('api.items.movements');

Route::resource('items', ItemController::class);
Route::resource('stock-movements', StockMovementController::class);
Route::resource('notifications', NotificationController::class);

Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
    ->name('notifications.mark-as-read');
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.mark-all-read');

Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
Route::post('/chatbot/send', [ChatbotController::class, 'send'])->name('chatbot.send');

Route::post('items/{item}/consume', [ItemController::class, 'consume'])->name('items.consume');
Route::post('items/{item}/add-stock', [ItemController::class, 'addStock'])->name('items.add-stock');
