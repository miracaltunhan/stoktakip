<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with('item')->latest()->paginate(10);
        return view('stock-movements.index', compact('movements'));
    }

    public function create()
    {
        return view('stock-movements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric',
            'type' => 'required|in:in,out',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        StockMovement::create($validated);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla oluşturuldu.');
    }

    public function show(StockMovement $stockMovement)
    {
        return view('stock-movements.show', compact('stockMovement'));
    }

    public function edit(StockMovement $stockMovement)
    {
        return view('stock-movements.edit', compact('stockMovement'));
    }

    public function update(Request $request, StockMovement $stockMovement)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric',
            'type' => 'required|in:in,out',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $stockMovement->update($validated);

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla güncellendi.');
    }

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla silindi.');
    }
} 