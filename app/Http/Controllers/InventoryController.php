<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::orderBy('created_at', 'desc')->get();
        return view('cashier.inventory', compact('inventories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name'         => 'required|string|max:255',
            'part_number'       => 'required|string|max:255',
            'quantity'          => 'required|integer|min:0',
            'selling'           => 'required|numeric|min:0',
            'acquisition_price' => 'nullable|numeric|min:0',
            'supplier'          => 'nullable|string|max:255',
        ]);
        $inv = Inventory::create($data);
        return response()->json($inv);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'item_name'         => 'required|string|max:255',
            'part_number'       => 'required|string|max:255',
            'quantity'          => 'required|integer|min:0',
            'selling'           => 'required|numeric|min:0',
            'acquisition_price' => 'nullable|numeric|min:0',
            'supplier'          => 'nullable|string|max:255',
        ]);
        $inventory->update($data);
        return response()->json($inventory);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return response()->json(['deleted' => true]);
    }
}
