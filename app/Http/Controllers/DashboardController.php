<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $criticalItems = Item::whereRaw('current_stock <= minimum_stock')->count();
        $totalValue = Item::sum('current_stock');
        $activeNotifications = Notification::where('is_read', false)->count();
        
        $recentMovements = StockMovement::with('item')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $criticalStockAlerts = Item::whereRaw('current_stock <= minimum_stock')
            ->orderBy('current_stock', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalItems',
            'criticalItems',
            'totalValue',
            'activeNotifications',
            'recentMovements',
            'criticalStockAlerts'
        ));
    }

    public function charts()
    {
        $totalItems = Item::count();
        $criticalItems = Item::whereRaw('current_stock <= minimum_stock')->count();

        // Stok takip türü dağılımı
        $stockTrackingDistribution = [
            'otomatik' => Item::where('stock_tracking_type', 'otomatik')->count(),
            'manuel' => Item::where('stock_tracking_type', 'manuel')->count()
        ];

        // Aktif ürünler
        $activeItems = Item::select('id', 'name')->get();

        return view('charts', compact(
            'totalItems',
            'criticalItems',
            'stockTrackingDistribution',
            'activeItems'
        ));
    }

    public function getItemMovements(Request $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        
        // Son 12 ayın verilerini al
        $movements = [
            'dates' => [],
            'in' => [],
            'out' => [],
            'stock' => []
        ];

        // Başlangıç stok değeri
        $currentStock = $item->current_stock;

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $movements['dates'][] = $date->format('M Y');
            
            // O ayki giriş ve çıkışları hesapla
            $in = StockMovement::where('item_id', $itemId)
                ->where('type', 'in')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('quantity');
            $movements['in'][] = $in;

            $out = StockMovement::where('item_id', $itemId)
                ->where('type', 'out')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('quantity');
            $movements['out'][] = $out;

            // Stok değerini hesapla (mevcut stok + giriş - çıkış)
            $currentStock = $currentStock + $in - $out;
            $movements['stock'][] = $currentStock;
        }

        return response()->json([
            'item' => [
                'name' => $item->name,
                'unit' => $item->unit
            ],
            'movements' => $movements
        ]);
    }
} 