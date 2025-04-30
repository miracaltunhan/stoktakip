<?php

namespace App\Services;

use TCPDF;
use App\Models\Item;

class StockReportService
{
    public function generateReport(Item $item, array $stockData)
    {
        // PDF ayarları
        define('K_TCPDF_EXTERNAL_CONFIG', true);
        define('K_PATH_MAIN', dirname(__FILE__) . '/../../vendor/tecnickcom/tcpdf/');
        define('K_PATH_URL', dirname(__FILE__) . '/../../vendor/tecnickcom/tcpdf/');
        define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
        define('K_PATH_CACHE', K_PATH_MAIN . 'cache/');
        define('K_PATH_URL_CACHE', K_PATH_URL . 'cache/');
        define('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
        define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
        define('K_CELL_HEIGHT_RATIO', 1.25);
        define('K_TITLE_MAGNIFICATION', 1.3);
        define('K_SMALL_RATIO', 2/3);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF ayarları
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Stok Takip Sistemi');
        $pdf->SetTitle($item->name . ' Stok Raporu');

        // Varsayılan başlık ve altbilgi
        $pdf->setHeaderFont(Array('dejavusans', '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array('dejavusans', '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont('dejavusansmono');
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Yeni sayfa ekle
        $pdf->AddPage();

        // Ürün bilgileri
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, $item->name . ' Stok Raporu', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(0, 10, 'Birim: ' . $item->unit, 0, 1);
        $pdf->Cell(0, 10, 'Mevcut Stok: ' . $stockData['movements']['consumption_stats']['current_stock'] . ' ' . $item->unit, 0, 1);
        $pdf->Cell(0, 10, 'Minimum Stok: ' . ($item->minimum_stock ?? 'Belirlenmemiş') . ' ' . $item->unit, 0, 1);
        $pdf->Cell(0, 10, 'Stok Takip Tipi: ' . ($item->stock_tracking_type == 'otomatik' ? 'Otomatik' : 'Manuel'), 0, 1);
        
        if ($item->stock_tracking_type == 'otomatik') {
            $pdf->Cell(0, 10, 'Haftalık Tüketim: ' . $item->weekly_consumption . ' ' . $item->unit, 0, 1);
        }

        $pdf->Ln(10);

        // Stok durumu grafiği
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Stok Durumu', 0, 1);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->MultiCell(0, 10, 'Aşağıdaki grafik son 12 aylık stok değişimini göstermektedir. Grafik, giriş ve çıkış hareketlerine göre stok seviyesinin nasıl değiştiğini gösterir.', 0, 'L');
        $pdf->Ln(5);

        // Stok grafiği resmi
        $chartImage = $this->generateStockChart($stockData);
        $pdf->Image('@' . $chartImage, 15, $pdf->GetY(), 180, 80);
        $pdf->Ln(90);

        // Tüketim tahmini
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Tüketim Tahmini', 0, 1);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->MultiCell(0, 10, 'Aşağıdaki grafik, mevcut tüketim hızı ve mevsimsel faktörler dikkate alınarak gelecek 6 ay için stok tahminini göstermektedir. Tahminler, ortalama aylık tüketim ve mevsimsel dalgalanmalar baz alınarak hesaplanmaktadır.', 0, 'L');
        $pdf->Ln(5);

        // Tahmin grafiği resmi
        $forecastImage = $this->generateForecastChart($stockData);
        $pdf->Image('@' . $forecastImage, 15, $pdf->GetY(), 180, 80);
        $pdf->Ln(90);

        // Tüketim istatistikleri
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Tüketim İstatistikleri', 0, 1);
        $pdf->SetFont('dejavusans', '', 12);

        $stats = $stockData['movements']['consumption_stats'];
        $pdf->Cell(0, 10, 'Ortalama Aylık Tüketim: ' . $stats['avg_monthly'] . ' ' . $item->unit, 0, 1);
        
        if ($stats['has_minimum_stock']) {
            $pdf->Cell(0, 10, 'Kritik Seviyeye Kalan Süre: ' . $stats['months_until_critical'] . ' ay', 0, 1);
            if ($stats['is_near_critical']) {
                $pdf->SetTextColor(255, 0, 0);
                $pdf->Cell(0, 10, 'UYARI: Stok seviyesi kritik seviyeye yaklaşmıştır!', 0, 1);
                $pdf->SetTextColor(0, 0, 0);
            }
        }

        return $pdf->Output('', 'S');
    }

    private function generateStockChart($stockData)
    {
        // Chart.js kullanarak grafik oluşturma
        $chart = new \stdClass();
        $chart->type = 'line';
        $chart->data = new \stdClass();
        $chart->data->labels = $stockData['movements']['dates'];
        $chart->data->datasets = [
            [
                'label' => 'Stok Seviyesi',
                'data' => $stockData['movements']['stock'],
                'borderColor' => 'rgb(75, 192, 192)',
                'tension' => 0.1
            ]
        ];
        $chart->options = new \stdClass();
        $chart->options->responsive = true;
        $chart->options->scales = new \stdClass();
        $chart->options->scales->y = new \stdClass();
        $chart->options->scales->y->beginAtZero = true;

        return base64_encode(json_encode($chart));
    }

    private function generateForecastChart($stockData)
    {
        // Chart.js kullanarak tahmin grafiği oluşturma
        $chart = new \stdClass();
        $chart->type = 'line';
        $chart->data = new \stdClass();
        $chart->data->labels = $stockData['movements']['forecast']['dates'];
        $chart->data->datasets = [
            [
                'label' => 'Tahmin',
                'data' => $stockData['movements']['forecast']['values'],
                'borderColor' => 'rgb(255, 99, 132)',
                'tension' => 0.1
            ]
        ];
        $chart->options = new \stdClass();
        $chart->options->responsive = true;
        $chart->options->scales = new \stdClass();
        $chart->options->scales->y = new \stdClass();
        $chart->options->scales->y->beginAtZero = true;

        return base64_encode(json_encode($chart));
    }
} 