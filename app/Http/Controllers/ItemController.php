<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Controller;
use App\Models\Item;
use App\Models\Notification;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::paginate(10);
        return view('items.index', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        \Log::info('Gelen request:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|in:adet,kg,lt,paket',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'stock_tracking_type' => 'required|string|in:manuel,otomatik',
            'weekly_consumption' => 'required_if:stock_tracking_type,otomatik|integer|min:0'
        ]);

        \Log::info('Validasyon sonrası:', $validated);

        // Manuel stok takibi seçildiğinde haftalık tüketimi 0 yap
        if ($validated['stock_tracking_type'] == 'manuel') {
            $validated['weekly_consumption'] = 0;
        }

        $item = Item::create($validated);
        \Log::info('Oluşturulan kayıt:', $item->toArray());

        // İlk stok hareketini kaydet
        if ($validated['current_stock'] > 0) {
            StockMovement::create([
                'item_id' => $item->id,
                'type' => 'in',
                'quantity' => $validated['current_stock'],
                'description' => 'İlk stok girişi'
            ]);
        }

        return redirect()->route('items.index')
            ->with('success', 'Stok kalemi başarıyla oluşturuldu.');
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    private function checkAndCreateStockAlert(Item $item)
    {
        if ($item->current_stock <= $item->minimum_stock) {
            Notification::create([
                'title' => 'Kritik Stok Seviyesi',
                'message' => "{$item->name} ürününün stok seviyesi kritik durumda. Mevcut stok: {$item->current_stock} {$item->unit}",
                'type' => 'stock_alert',
                'is_read' => false
            ]);
        }
    }

    public function consume(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $item->current_stock],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($item, $request) {
            // Stok miktarını azalt
            $item->current_stock -= $request->quantity;
            $item->save();

            // Stok hareketini kaydet
            $item->stockMovements()->create([
                'type' => 'out',
                'quantity' => $request->quantity,
                'description' => $request->description ?? 'Stok tüketimi',
            ]);

            // Stok seviyesi kontrolü
            $this->checkAndCreateStockAlert($item);
        });

        return redirect()->route('items.index')
            ->with('success', 'Stok tüketimi başarıyla kaydedildi.');
    }

    public function add(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($item, $request) {
            // Stok miktarını artır
            $item->current_stock += $request->quantity;
            $item->save();

            // Stok hareketini kaydet
            $item->stockMovements()->create([
                'type' => 'in',
                'quantity' => $request->quantity,
                'description' => $request->description ?? 'Stok artışı',
            ]);
        });

        return redirect()->route('items.index')
            ->with('success', 'Stok artışı başarıyla kaydedildi.');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|in:adet,kg,lt,paket',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'stock_tracking_type' => 'required|string|in:manuel,otomatik',
            'weekly_consumption' => 'required_if:stock_tracking_type,otomatik|integer|min:0'
        ]);

        // Manuel stok takibi seçildiğinde haftalık tüketimi 0 yap
        if ($validated['stock_tracking_type'] == 'manuel') {
            $validated['weekly_consumption'] = 0;
        }

        $item->update($validated);

        // Stok seviyesi kontrolü
        $this->checkAndCreateStockAlert($item);

        return redirect()->route('items.index')
            ->with('success', 'Stok kalemi başarıyla güncellendi.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Stok kalemi başarıyla silindi.');
    }

    public function addStock(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255'
        ]);

        $item->current_stock += $validated['quantity'];
        $item->save();

        StockMovement::create([
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'description' => $validated['description'] ?? 'Stok girişi'
        ]);

        return redirect()->back()->with('success', 'Stok başarıyla eklendi.');
    }

    public function removeStock(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0|max:' . $item->current_stock,
            'description' => 'nullable|string|max:255'
        ]);

        $item->current_stock -= $validated['quantity'];
        $item->save();

        StockMovement::create([
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'description' => $validated['description'] ?? 'Stok çıkışı'
        ]);

        return redirect()->back()->with('success', 'Stok başarıyla çıkarıldı.');
    }
}
