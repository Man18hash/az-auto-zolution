<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArCollection;
use App\Models\CashDeposit;

class ARCashDepositController extends Controller
{
    public function index()
    {
        $arCollections = ArCollection::orderBy('date', 'desc')->get();
        $cashDeposits = CashDeposit::orderBy('date', 'desc')->get();
        return view('cashier.ar-cashdeposit', compact('arCollections', 'cashDeposits'));
    }

    public function storeAR(Request $request)
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
        ]);
        $validated['user_id'] = auth()->id();
        ArCollection::create($validated);
        return back()->with('success', 'A/R Collection recorded!');
    }

    public function storeCashDeposit(Request $request)
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
        ]);
        $validated['user_id'] = auth()->id();
        CashDeposit::create($validated);
        return back()->with('success', 'Cash Deposit recorded!');
    }

    // Add edit/update/destroy as needed
}
