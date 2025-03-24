<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Notification;
use Illuminate\Http\Request;

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
} 