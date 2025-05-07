<?php

namespace App\Services;

use TCPDF;
use App\Models\Item;
use Carbon\Carbon;

class StockReportService
{
    private $pdf;

    public function generateReport(Item $item, array $stockData)
    {
        try {
            // Geçici dosya dizinini belirle
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Geçici dosya dizini oluşturulamadı: ' . $tempDir);
                }
            }

            // PDF ayarları
            define('K_TCPDF_EXTERNAL_CONFIG', true);
            define('K_PATH_MAIN', dirname(__FILE__) . '/../../vendor/tecnickcom/tcpdf/');
            define('K_PATH_URL', dirname(__FILE__) . '/../../vendor/tecnickcom/tcpdf/');
            define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
            define('K_PATH_CACHE', $tempDir);
            define('K_PATH_URL_CACHE', '');
            define('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
            define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
            define('K_CELL_HEIGHT_RATIO', 1.25);
            define('K_TITLE_MAGNIFICATION', 1.3);
            define('K_SMALL_RATIO', 2/3);

            // TCPDF'i başlat
            $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            if (!$this->pdf) {
                throw new \Exception('TCPDF başlatılamadı');
            }

            // PDF ayarları
            $this->pdf->SetCreator(PDF_CREATOR);
            $this->pdf->SetAuthor('Stok Takip Sistemi');
            $this->pdf->SetTitle($item->name . ' Stok Raporu');

            // Varsayılan başlık ve altbilgi
            $this->pdf->setHeaderFont(Array('dejavusans', '', PDF_FONT_SIZE_MAIN));
            $this->pdf->setFooterFont(Array('dejavusans', '', PDF_FONT_SIZE_DATA));
            $this->pdf->SetDefaultMonospacedFont('dejavusansmono');
            $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Yeni sayfa ekle
            $this->pdf->AddPage();

            // Ürün bilgileri
            $this->pdf->SetFont('dejavusans', 'B', 16);
            $this->pdf->Cell(0, 10, $item->name . ' Stok Raporu', 0, 1, 'C');
            $this->pdf->Ln(12);

            $this->pdf->SetFont('dejavusans', '', 12);
            $this->pdf->Cell(0, 8, 'Birim: ' . $item->unit, 0, 1);
            $this->pdf->Cell(0, 8, 'Mevcut Stok: ' . $stockData['movements']['consumption_stats']['current_stock'] . ' ' . $item->unit, 0, 1);
            $this->pdf->Cell(0, 8, 'Minimum Stok: ' . ($item->minimum_stock ?? 'Belirlenmemiş') . ' ' . $item->unit, 0, 1);
            $this->pdf->Cell(0, 8, 'Stok Takip Tipi: ' . ($item->stock_tracking_type == 'otomatik' ? 'Otomatik' : 'Manuel'), 0, 1);
            
            if ($item->stock_tracking_type == 'otomatik') {
                $this->pdf->Cell(0, 8, 'Haftalık Tüketim: ' . $item->weekly_consumption . ' ' . $item->unit, 0, 1);
            }

            $this->pdf->Ln(18);

            // Stok durumu grafiği
            $this->pdf->SetFont('dejavusans', 'B', 14);
            $this->pdf->Cell(0, 9, 'Stok Durumu', 0, 1);
            $this->pdf->SetFont('dejavusans', '', 11);
            $this->pdf->MultiCell(0, 8, 'Aşağıdaki grafik son 12 aylık stok değişimini göstermektedir. Grafik, giriş ve çıkış hareketlerine göre stok seviyesinin nasıl değiştiğini gösterir.', 0, 'L');
            $this->pdf->Ln(6);

            // Stok grafiği
            $this->generateStockChart($stockData);
            $this->pdf->Ln(25);

            // Stok takip türü grafiği
            $this->pdf->SetFont('dejavusans', 'B', 14);
            $this->pdf->Cell(0, 9, 'Stok Takip Türü Analizi', 0, 1);
            $this->pdf->SetFont('dejavusans', '', 11);
            $this->pdf->MultiCell(0, 8, 'Aşağıdaki grafik, stok takip türüne göre giriş ve çıkış hareketlerinin dağılımını göstermektedir.', 0, 'L');
            $this->pdf->Ln(6);

            // Stok takip türü grafiği
            $this->generateStockTypeChart($stockData);
            $this->pdf->Ln(25);

            // Yeni sayfa ekle
            $this->pdf->AddPage();

            // Tüketim tahmini
            $this->pdf->SetFont('dejavusans', 'B', 14);
            $this->pdf->Cell(0, 9, 'Tüketim Tahmini', 0, 1);
            $this->pdf->SetFont('dejavusans', '', 11);
            $this->pdf->MultiCell(0, 8, 'Aşağıdaki grafik, mevcut tüketim hızı ve mevsimsel faktörler dikkate alınarak gelecek 6 ay için stok tahminini göstermektedir.', 0, 'L');
            $this->pdf->Ln(6);

            // Tahmin grafiği
            $this->generateForecastChart($stockData);
            $this->pdf->Ln(25);

            // Tüketim istatistikleri
            $this->pdf->SetFont('dejavusans', 'B', 14);
            $this->pdf->Cell(0, 9, 'Tüketim İstatistikleri', 0, 1);
            $this->pdf->SetFont('dejavusans', '', 11);

            $stats = $stockData['movements']['consumption_stats'];
            $this->pdf->Cell(0, 8, 'Ortalama Aylık Tüketim: ' . $stats['avg_monthly'] . ' ' . $item->unit, 0, 1);
            
            if ($stats['has_minimum_stock']) {
                $this->pdf->Cell(0, 8, 'Kritik Seviyeye Kalan Süre: ' . $stats['months_until_critical'] . ' ay', 0, 1);
                if ($stats['is_near_critical']) {
                    $this->pdf->SetTextColor(255, 0, 0);
                    $this->pdf->Cell(0, 8, 'UYARI: Stok seviyesi kritik seviyeye yaklaşmıştır!', 0, 1);
                    $this->pdf->SetTextColor(0, 0, 0);
                }
            }

            // PDF'i oluştur ve döndür
            return $this->pdf->Output('', 'S');
        } catch (\Exception $e) {
            \Log::error('PDF oluşturma hatası: ' . $e->getMessage());
            \Log::error('Hata detayı: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function generateStockChart($stockData)
    {
        $dates = $stockData['movements']['dates'];
        $stock = $stockData['movements']['stock'];
        $width = 180;
        $height = 100;
        $x = 15;
        $y = $this->pdf->GetY();
        $padding = 10;
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Rect($x, $y, $width, $height, 'F');
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Rect($x, $y, $width, $height);
        if (empty($stock) || count($stock) < 2) {
            $this->pdf->SetFont('dejavusans', '', 10);
            $this->pdf->SetTextColor(150, 0, 0);
            $this->pdf->Text($x + $padding, $y + $height / 2, 'Veri yok');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetY($y + $height + 15);
            return;
        }
        $maxValue = max($stock);
        $minValue = min($stock);
        $range = $maxValue - $minValue;
        if ($range == 0) {
            $range = $maxValue > 0 ? $maxValue : 1;
            $minValue = 0;
        }
        $pointCount = count($stock);
        $xStep = ($width - 2 * $padding) / ($pointCount - 1);
        $yStep = ($height - 2 * $padding) / 4;
        $this->pdf->SetDrawColor(75, 192, 192);
        $this->pdf->SetLineWidth(1);
        for ($i = 0; $i < $pointCount - 1; $i++) {
            $x1 = $x + $padding + ($i * $xStep);
            $y1 = $y + $height - $padding - (($stock[$i] - $minValue) / $range * ($height - 2 * $padding));
            $x2 = $x + $padding + (($i + 1) * $xStep);
            $y2 = $y + $height - $padding - (($stock[$i + 1] - $minValue) / $range * ($height - 2 * $padding));
            $this->pdf->Line($x1, $y1, $x2, $y2);
        }
        $this->pdf->SetFont('dejavusans', '', 8);
        $this->pdf->SetTextColor(0, 0, 0);
        for ($i = 0; $i < $pointCount; $i += 2) {
            $labelX = $x + $padding + ($i * $xStep);
            $labelY = $y + $height + 2;
            $this->pdf->Text($labelX, $labelY, $dates[$i]);
        }
        for ($i = 0; $i <= 4; $i++) {
            $value = $minValue + ($range * $i / 4);
            $labelY = $y + $height - $padding - ($i * $yStep);
            $this->pdf->Text($x, $labelY, round($value, 1));
        }
        $this->pdf->SetY($y + $height + 15);
    }

    private function generateForecastChart($stockData)
    {
        $dates = $stockData['movements']['forecast']['dates'];
        $values = $stockData['movements']['forecast']['values'];
        $width = 180;
        $height = 100;
        $x = 15;
        $y = $this->pdf->GetY();
        $padding = 10;
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Rect($x, $y, $width, $height, 'F');
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Rect($x, $y, $width, $height);
        if (empty($values) || count($values) < 2) {
            $this->pdf->SetFont('dejavusans', '', 10);
            $this->pdf->SetTextColor(150, 0, 0);
            $this->pdf->Text($x + $padding, $y + $height / 2, 'Veri yok');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetY($y + $height + 15);
            return;
        }
        $maxValue = max($values);
        $minValue = min($values);
        $range = $maxValue - $minValue;
        if ($range == 0) {
            $range = $maxValue > 0 ? $maxValue : 1;
            $minValue = 0;
        }
        $pointCount = count($values);
        $xStep = ($width - 2 * $padding) / ($pointCount - 1);
        $yStep = ($height - 2 * $padding) / 4;
        $this->pdf->SetDrawColor(255, 99, 132);
        $this->pdf->SetLineWidth(1);
        for ($i = 0; $i < $pointCount - 1; $i++) {
            $x1 = $x + $padding + ($i * $xStep);
            $y1 = $y + $height - $padding - (($values[$i] - $minValue) / $range * ($height - 2 * $padding));
            $x2 = $x + $padding + (($i + 1) * $xStep);
            $y2 = $y + $height - $padding - (($values[$i + 1] - $minValue) / $range * ($height - 2 * $padding));
            $this->pdf->Line($x1, $y1, $x2, $y2);
        }
        $this->pdf->SetFont('dejavusans', '', 8);
        $this->pdf->SetTextColor(0, 0, 0);
        for ($i = 0; $i < $pointCount; $i++) {
            $labelX = $x + $padding + ($i * $xStep);
            $labelY = $y + $height + 2;
            $this->pdf->Text($labelX, $labelY, $dates[$i]);
        }
        for ($i = 0; $i <= 4; $i++) {
            $value = $minValue + ($range * $i / 4);
            $labelY = $y + $height - $padding - ($i * $yStep);
            $this->pdf->Text($x, $labelY, round($value, 1));
        }
        $this->pdf->SetY($y + $height + 15);
    }

    private function generateStockTypeChart($stockData)
    {
        $inTotal = array_sum($stockData['movements']['in']);
        $outTotal = array_sum($stockData['movements']['out']);
        $width = 180;
        $height = 100;
        $x = 15;
        $y = $this->pdf->GetY();
        $padding = 10;
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Rect($x, $y, $width, $height, 'F');
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Rect($x, $y, $width, $height);
        $total = $inTotal + $outTotal;
        if ($total == 0) {
            $this->pdf->SetFont('dejavusans', '', 10);
            $this->pdf->SetTextColor(150, 0, 0);
            $this->pdf->Text($x + $padding, $y + $height / 2, 'Veri yok');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetY($y + $height + 15);
            return;
        }
        $inPercentage = ($inTotal / $total) * 100;
        $outPercentage = ($outTotal / $total) * 100;
        $barWidth = $width - 2 * $padding;
        $barHeight = 20;
        $this->pdf->SetFillColor(75, 192, 192);
        $this->pdf->Rect($x + $padding, $y + 20, $barWidth * ($inPercentage / 100), $barHeight, 'F');
        $this->pdf->SetFillColor(255, 99, 132);
        $this->pdf->Rect($x + $padding, $y + 50, $barWidth * ($outPercentage / 100), $barHeight, 'F');
        $this->pdf->SetFont('dejavusans', '', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Text($x + $padding, $y + 17, 'Giriş: ' . round($inPercentage, 1) . '% (' . $inTotal . ')');
        $this->pdf->Text($x + $padding, $y + 47, 'Çıkış: ' . round($outPercentage, 1) . '% (' . $outTotal . ')');
        $this->pdf->SetY($y + $height + 15);
    }

    private function cleanTempFiles($tempDir)
    {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function getStockData(Item $item)
    {
        try {
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

                $in = \App\Models\StockMovement::where('item_id', $item->id)
                    ->where('type', 'in')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('quantity');
                $movements['in'][] = $in;

                $out = \App\Models\StockMovement::where('item_id', $item->id)
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
        } catch (\Exception $e) {
            \Log::error('Stok verisi alınırken hata oluştu: ' . $e->getMessage());
            throw $e;
        }
    }
} 