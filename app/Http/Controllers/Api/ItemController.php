<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::with(['stockMovements', 'consumptionRecords'])->get();
        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'monthly_consumption' => 'required|integer|min:0'
        ]);

        $item = Item::create($validated);
        return response()->json($item, 201);
    }

    public function show(Item $item): JsonResponse
    {
        return response()->json($item->load(['stockMovements', 'consumptionRecords']));
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'monthly_consumption' => 'required|integer|min:0'
        ]);

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();
        return response()->json(null, 204);
    }
} 