<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with('item')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('stock-movements.index', compact('movements'));
    }

    public function show($id)
    {
        $movement = StockMovement::with('item')->findOrFail($id);
        return view('stock-movements.show', compact('movement'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'type' => 'required|in:in,out',
            'description' => 'required|string'
        ]);

        $movement = StockMovement::create($validated);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla kaydedildi.');
    }

    public function update(Request $request, $id)
    {
        $movement = StockMovement::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'numeric|min:0',
            'type' => 'in:in,out',
            'description' => 'string'
        ]);

        $movement->update($validated);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $movement = StockMovement::findOrFail($id);
        $movement->delete();

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla silindi.');
    }
}
