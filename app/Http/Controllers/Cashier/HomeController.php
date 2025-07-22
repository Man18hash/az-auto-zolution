<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Inventory;

class HomeController extends Controller
{
    public function index()
    {
        $quotationCount = Invoice::where('source_type', 'quotation')->count();
        $invoicingCount = Invoice::where('source_type', 'invoicing')->count();
        $appointmentCount = Invoice::where('source_type', 'appointment')->count();
        $serviceOrderCount = Invoice::where('source_type', 'service_order')->count();
        $historyCount = Invoice::count();
        $inventoryCount = Inventory::count();

        $appointments = Invoice::with('client')
            ->where('source_type', 'appointment')
            ->whereNotNull('appointment_date')
            ->get();

        $events = $appointments->map(function ($appointment) {
            return [
                'title' => $appointment->client->name ?? $appointment->customer_name,
                'start' => $appointment->appointment_date->format('Y-m-d'),
                'url' => route('cashier.appointment.edit', $appointment->id),
            ];
        });

        return view('cashier.home', compact(
            'quotationCount',
            'invoicingCount',
            'appointmentCount',
            'serviceOrderCount',
            'historyCount',
            'inventoryCount',
            'events' 
        ));
    }

}
