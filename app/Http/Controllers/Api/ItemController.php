<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Notification;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::all();
        return response()->json($items);
    }

    public function show($id): JsonResponse
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'monthly_consumption' => 'required|numeric|min:0'
        ]);

        $item = Item::create($validated);
        return response()->json($item, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'unit' => 'string|max:50',
            'current_stock' => 'numeric|min:0',
            'minimum_stock' => 'numeric|min:0',
            'monthly_consumption' => 'numeric|min:0'
        ]);

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    public function addStock(Request $request, $id): JsonResponse
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'description' => 'required|string'
        ]);

        $item->current_stock += $validated['quantity'];
        $item->save();

        StockMovement::create([
            'item_id' => $id,
            'quantity' => $validated['quantity'],
            'type' => 'in',
            'description' => $validated['description']
        ]);

        return response()->json($item);
    }

    public function consume(Request $request, $id): JsonResponse
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'description' => 'required|string'
        ]);

        if ($item->current_stock < $validated['quantity']) {
            return response()->json([
                'message' => 'Yetersiz stok'
            ], 400);
        }

        $item->current_stock -= $validated['quantity'];
        $item->save();

        StockMovement::create([
            'item_id' => $id,
            'quantity' => $validated['quantity'],
            'type' => 'out',
            'description' => $validated['description']
        ]);

        return response()->json($item);
    }

    public function getStockMovements($id): JsonResponse
    {
        $movements = StockMovement::where('item_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($movements);
    }

    private function checkAndCreateStockAlert(Item $item): void
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
}
