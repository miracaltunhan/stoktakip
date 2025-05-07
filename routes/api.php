<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatbotController;

// API rotalarını 'api' prefix'i ile gruplama
Route::prefix('api')->middleware('api')->group(function () {

    // Test endpoint'i
    Route::get('test', function () {
        return response()->json([
            'message' => 'API çalışıyor'
        ]);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        // Route::get('/', [DashboardController::class, 'index']);
        // Route::get('/charts', [DashboardController::class, 'charts']);
        // Route::get('/items/{itemId}/movements', [DashboardController::class, 'getItemMovements']);
        // Route::get('/items/{itemId}/report', [DashboardController::class, 'downloadReport']);
    });

    // Ürün işlemleri
    Route::prefix('items')->group(function () {
        // Route::get('/', [ItemController::class, 'index']);
        // Route::get('/{id}', [ItemController::class, 'show']);
        // Route::post('/', [ItemController::class, 'store']);
        // Route::put('/{id}', [ItemController::class, 'update']);
        // Route::delete('/{id}', [ItemController::class, 'destroy']);
        // Route::post('/{id}/add-stock', [ItemController::class, 'addStock']);
        // Route::post('/{id}/consume', [ItemController::class, 'consume']);
        // Route::get('/{id}/stock-movements', [ItemController::class, 'getStockMovements']);
    });

    // Stok hareketleri
    Route::prefix('stock-movements')->group(function () {
        // Route::get('/', [StockMovementController::class, 'index']);
        // Route::get('/{id}', [StockMovementController::class, 'show']);
        // Route::post('/', [StockMovementController::class, 'store']);
        // Route::put('/{id}', [StockMovementController::class, 'update']);
        // Route::delete('/{id}', [StockMovementController::class, 'destroy']);
    });

    // Bildirimler
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // Chatbot
    Route::prefix('chatbot')->group(function () {
        Route::get('/', [ChatbotController::class, 'index']);
        Route::post('/send', [ChatbotController::class, 'send']);
    });
});
