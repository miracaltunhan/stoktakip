<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\NotificationController;

Route::middleware('api')->group(function () {
    Route::apiResource('items', ItemController::class);
    Route::apiResource('stock-movements', StockMovementController::class);
    Route::apiResource('notifications', NotificationController::class);
}); 