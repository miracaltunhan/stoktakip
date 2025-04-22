<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use App\Models\Notification;

class AutoStockUpdate extends Command
{
    protected $signature = 'stock:auto-update';
    protected $description = 'Otomatik stok takibi yapılan ürünlerin stoklarını günceller';

    public function handle()
    {
        $items = Item::where('stock_tracking_type', 'otomatik')->get();

        foreach ($items as $item) {
            // Haftalık tüketim miktarını stoktan düş
            $item->current_stock -= $item->weekly_consumption;
            
            // Eğer stok minimum seviyenin altına düşerse bildirim oluştur
            if ($item->current_stock <= $item->minimum_stock) {
                $this->checkAndCreateStockAlert($item);
            }
            
            $item->save();
        }

        $this->info('Otomatik stok güncellemesi tamamlandı.');
    }

    private function checkAndCreateStockAlert(Item $item)
    {
        Notification::create([
            'title' => 'Kritik Stok Seviyesi',
            'message' => "{$item->name} ürününün stok seviyesi kritik durumda. Mevcut stok: {$item->current_stock} {$item->unit}",
            'type' => 'stock_alert',
            'is_read' => false
        ]);
    }
} 