<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Inventory;

class HomeController extends Controller
{
    public function index()
    {
        $quotationCount    = Invoice::where('source_type', 'quotation')->count();
        $invoicingCount    = Invoice::where('source_type', 'invoicing')->count();
        $appointmentCount  = Invoice::where('source_type', 'appointment')->count();
        $serviceOrderCount = Invoice::where('source_type', 'service_order')->count();
        $historyCount      = Invoice::count(); // All invoice records (could be any status/type)
        $inventoryCount    = Inventory::count(); // Inventory items

        return view('cashier.home', compact(
            'quotationCount',
            'invoicingCount',
            'appointmentCount',
            'serviceOrderCount',
            'historyCount',
            'inventoryCount'
        ));
    }
}
