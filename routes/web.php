<?php

use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\ItemController;
use Illuminate\Support\Facades\Route;

// Ana sayfa ve dashboard rotaları
Route::get('/', [App\Http\Controllers\Api\DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index'])->name('dashboard');
Route::get('/charts', [App\Http\Controllers\Api\DashboardController::class, 'charts'])->name('charts');
Route::get('/api/items/{item}/movements', [App\Http\Controllers\Api\DashboardController::class, 'getItemMovements'])->name('api.items.movements');

// Resource rotaları
Route::resource('items', App\Http\Controllers\ItemController::class);
Route::resource('stock-movements', App\Http\Controllers\Api\StockMovementController::class);
Route::resource('notifications', NotificationController::class);

// Bildirim işlemleri
Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
    ->name('notifications.mark-as-read');
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.mark-all-read');

// Chatbot rotaları
Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
Route::post('/chatbot/send', [ChatbotController::class, 'send'])->name('chatbot.send');

// Stok işlemleri
Route::post('items/{item}/consume', [ItemController::class, 'consume'])->name('items.consume');
Route::post('items/{item}/add-stock', [ItemController::class, 'addStock'])->name('items.add-stock');

// Dashboard ek özellikleri
Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('dashboard.charts');
Route::get('/dashboard/download-report/{itemId}', [DashboardController::class, 'downloadReport'])->name('dashboard.download-report');
