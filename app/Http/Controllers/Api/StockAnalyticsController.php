<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;

class StockAnalyticsController extends Controller
{
    public function getStockAnalytics()
    {
        $totalItems = Product::count();
        $criticalItems = Product::where('stock_quantity', '<', 10)->count();

        // Stok takip türü dağılımı
        $stockTrackingDistribution = [
            'otomatik' => Product::where('tracking_type', 'automatic')->count(),
            'manuel' => Product::where('tracking_type', 'manual')->count()
        ];

        return response()->json([
            'total_items' => $totalItems,
            'critical_items' => $criticalItems,
            'stock_tracking_distribution' => $stockTrackingDistribution
        ]);
    }

    public function getProductMovements($productId)
    {
        $product = Product::findOrFail($productId);

        // Son 6 ayın hareketlerini al
        $movements = Stock::where('product_id', $productId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->orderBy('created_at')
            ->get();

        // Hareketleri aylara göre grupla
        $monthlyData = collect();
        foreach ($movements as $movement) {
            $month = Carbon::parse($movement->created_at)->format('Y-m');
            if (!$monthlyData->has($month)) {
                $monthlyData[$month] = 0;
            }
            $monthlyData[$month] += $movement->quantity;
        }

        return response()->json([
            'dates' => $monthlyData->keys()->toArray(),
            'stock' => $monthlyData->values()->toArray(),
            'item' => [
                'name' => $product->name,
                'unit' => $product->unit
            ]
        ]);
    }

    public function getStockMovements()
    {
        $movements = Stock::with('product')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'product_id' => $movement->product_id,
                    'product_name' => $movement->product->name,
                    'quantity' => $movement->quantity,
                    'description' => $movement->description,
                    'created_at' => $movement->created_at
                ];
            });

        return response()->json($movements);
    }
}
