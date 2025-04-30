<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Notification;
use App\Models\StockMovement;
use App\Services\StockReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $stockReportService;

    public function __construct(StockReportService $stockReportService)
    {
        $this->stockReportService = $stockReportService;
    }

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
        try {
            $item = Item::findOrFail($itemId);

            $movements = [
                'dates' => [],
                'in' => [],
                'out' => [],
                'stock' => [],
                'forecast' => [],
                'consumption_stats' => []
            ];

            // Başlangıç stok değeri
            $currentStock = $item->current_stock;
            $stockHistory = [];

            // Son 12 ayın verilerini topla
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $movements['dates'][] = $date->format('M Y');

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

                // Stok değişimini hesapla
                $stockChange = $in - $out;
                $currentStock += $stockChange;
                $stockHistory[] = $currentStock;
            }

            $movements['stock'] = $stockHistory;

            // Tüketim istatistiklerini hesapla
            $avgMonthlyConsumption = 0;
            $totalOut = array_sum($movements['out']);
            $nonZeroMonths = count(array_filter($movements['out'], function($value) { return $value > 0; }));

            if ($nonZeroMonths > 0) {
                $avgMonthlyConsumption = $totalOut / $nonZeroMonths;
            } else if ($item->stock_tracking_type == 'otomatik' && $item->weekly_consumption > 0) {
                $avgMonthlyConsumption = $item->weekly_consumption * 4.33;
            }

            // Kritik seviye kontrolü
            $hasMinimumStock = !is_null($item->minimum_stock) && $item->minimum_stock > 0;
            $monthsUntilCritical = 0;
            $isNearCritical = false;

            if ($hasMinimumStock && $avgMonthlyConsumption > 0) {
                $monthsUntilCritical = ($currentStock - $item->minimum_stock) / $avgMonthlyConsumption;
                $isNearCritical = $monthsUntilCritical <= 2;
            }

            $movements['consumption_stats'] = [
                'avg_monthly' => round($avgMonthlyConsumption, 2),
                'months_until_critical' => round($monthsUntilCritical, 1),
                'current_stock' => $currentStock,
                'minimum_stock' => $item->minimum_stock,
                'has_minimum_stock' => $hasMinimumStock,
                'has_movements' => !empty($stockHistory),
                'is_near_critical' => $isNearCritical,
                'has_weekly_consumption' => $item->stock_tracking_type == 'otomatik' && $item->weekly_consumption > 0
            ];

            // Gelecek 6 ay için tahmin hesapla
            $forecastDates = [];
            $forecastValues = [];
            $lastStock = end($stockHistory);

            // Mevsimsel dalgalanmaları hesapla
            $seasonalFactors = [];
            $monthlyAverages = array_fill(0, 12, 0);
            $monthlyCounts = array_fill(0, 12, 0);

            foreach ($movements['out'] as $index => $out) {
                $monthIndex = (11 - $index) % 12;
                $monthlyAverages[$monthIndex] += $out;
                $monthlyCounts[$monthIndex]++;
            }

            for ($i = 0; $i < 12; $i++) {
                if ($monthlyCounts[$i] > 0) {
                    $monthlyAverages[$i] /= $monthlyCounts[$i];
                }
            }

            $overallAverage = 0;
            $validAverages = array_filter($monthlyAverages);
            if (!empty($validAverages)) {
                $overallAverage = array_sum($validAverages) / count($validAverages);
            }

            for ($i = 0; $i < 12; $i++) {
                if ($overallAverage > 0) {
                    $seasonalFactors[$i] = $monthlyAverages[$i] / $overallAverage;
                } else {
                    $seasonalFactors[$i] = 1;
                }
            }

            // Tahmin hesapla
            for ($i = 1; $i <= 6; $i++) {
                $forecastDate = Carbon::now()->addMonths($i);
                $forecastDates[] = $forecastDate->format('M Y');

                $monthIndex = ($forecastDate->month - 1) % 12;
                $seasonalFactor = $seasonalFactors[$monthIndex] ?? 1;

                // Tahmin = Son stok - (Ortalama tüketim * Mevsimsel faktör * Ay sayısı)
                $forecastStock = $lastStock - ($avgMonthlyConsumption * $seasonalFactor * $i);
                $forecastValues[] = max(0, $forecastStock);
            }

            $movements['forecast'] = [
                'dates' => $forecastDates,
                'values' => $forecastValues
            ];

            return response()->json([
                'item' => [
                    'name' => $item->name,
                    'unit' => $item->unit
                ],
                'movements' => $movements
            ]);
        } catch (\Exception $e) {
            \Log::error('Stok hareketleri hatası: ' . $e->getMessage());
            return response()->json([
                'error' => 'Stok hareketleri alınırken bir hata oluştu'
            ], 500);
        }
    }

    public function downloadReport($itemId)
    {
        try {
            $item = Item::findOrFail($itemId);
            $stockData = $this->getItemMovementsData($item);

            $pdf = $this->stockReportService->generateReport($item, $stockData);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $item->name . '_stok_raporu.pdf"')
                ->header('Content-Length', strlen($pdf));
        } catch (\Exception $e) {
            \Log::error('PDF oluşturma hatası: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    private function getItemMovementsData($item)
    {
        $movements = [
            'dates' => [],
            'in' => [],
            'out' => [],
            'stock' => [],
            'forecast' => [],
            'consumption_stats' => []
        ];

        // Başlangıç stok değeri
        $currentStock = $item->current_stock;
        $stockHistory = [];

        // Son 12 ayın verilerini topla
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $movements['dates'][] = $date->format('M Y');

            $in = StockMovement::where('item_id', $item->id)
                ->where('type', 'in')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('quantity');
            $movements['in'][] = $in;

            $out = StockMovement::where('item_id', $item->id)
                ->where('type', 'out')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('quantity');
            $movements['out'][] = $out;

            // Stok değişimini hesapla
            $stockChange = $in - $out;
            $currentStock += $stockChange;
            $stockHistory[] = $currentStock;
        }

        $movements['stock'] = $stockHistory;

        // Tüketim istatistiklerini hesapla
        $avgMonthlyConsumption = 0;
        $totalOut = array_sum($movements['out']);
        $nonZeroMonths = count(array_filter($movements['out'], function($value) { return $value > 0; }));

        if ($nonZeroMonths > 0) {
            $avgMonthlyConsumption = $totalOut / $nonZeroMonths;
        } else if ($item->stock_tracking_type == 'otomatik' && $item->weekly_consumption > 0) {
            $avgMonthlyConsumption = $item->weekly_consumption * 4.33;
        }

        // Kritik seviye kontrolü
        $hasMinimumStock = !is_null($item->minimum_stock) && $item->minimum_stock > 0;
        $monthsUntilCritical = 0;
        $isNearCritical = false;

        if ($hasMinimumStock && $avgMonthlyConsumption > 0) {
            $monthsUntilCritical = ($currentStock - $item->minimum_stock) / $avgMonthlyConsumption;
            $isNearCritical = $monthsUntilCritical <= 2;
        }

        $movements['consumption_stats'] = [
            'avg_monthly' => round($avgMonthlyConsumption, 2),
            'months_until_critical' => round($monthsUntilCritical, 1),
            'current_stock' => $currentStock,
            'minimum_stock' => $item->minimum_stock,
            'has_minimum_stock' => $hasMinimumStock,
            'has_movements' => !empty($stockHistory),
            'is_near_critical' => $isNearCritical,
            'has_weekly_consumption' => $item->stock_tracking_type == 'otomatik' && $item->weekly_consumption > 0
        ];

        // Gelecek 6 ay için tahmin hesapla
        $forecastDates = [];
        $forecastValues = [];
        $lastStock = end($stockHistory);

        // Mevsimsel dalgalanmaları hesapla
        $seasonalFactors = [];
        $monthlyAverages = array_fill(0, 12, 0);
        $monthlyCounts = array_fill(0, 12, 0);

        foreach ($movements['out'] as $index => $out) {
            $monthIndex = (11 - $index) % 12;
            $monthlyAverages[$monthIndex] += $out;
            $monthlyCounts[$monthIndex]++;
        }

        for ($i = 0; $i < 12; $i++) {
            if ($monthlyCounts[$i] > 0) {
                $monthlyAverages[$i] /= $monthlyCounts[$i];
            }
        }

        $overallAverage = 0;
        $validAverages = array_filter($monthlyAverages);
        if (!empty($validAverages)) {
            $overallAverage = array_sum($validAverages) / count($validAverages);
        }

        for ($i = 0; $i < 12; $i++) {
            if ($overallAverage > 0) {
                $seasonalFactors[$i] = $monthlyAverages[$i] / $overallAverage;
            } else {
                $seasonalFactors[$i] = 1;
            }
        }

        // Tahmin hesapla
        for ($i = 1; $i <= 6; $i++) {
            $forecastDate = Carbon::now()->addMonths($i);
            $forecastDates[] = $forecastDate->format('M Y');

            $monthIndex = ($forecastDate->month - 1) % 12;
            $seasonalFactor = $seasonalFactors[$monthIndex] ?? 1;

            // Tahmin = Son stok - (Ortalama tüketim * Mevsimsel faktör * Ay sayısı)
            $forecastStock = $lastStock - ($avgMonthlyConsumption * $seasonalFactor * $i);
            $forecastValues[] = max(0, $forecastStock);
        }

        $movements['forecast'] = [
            'dates' => $forecastDates,
            'values' => $forecastValues
        ];

        return [
            'item' => [
                'name' => $item->name,
                'unit' => $item->unit
            ],
            'movements' => $movements
        ];
    }
}
