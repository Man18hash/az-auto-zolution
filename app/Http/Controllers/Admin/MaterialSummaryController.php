<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MaterialSummaryController extends Controller
{
    public function index(Request $request)
    {
        [$materials, $startDate, $endDate, $grouped] = $this->getSummaryData($request);

        return view('admin.material-summary', [
            'materials'  => $materials,
            'grouped'    => $grouped,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ]);
    }

    public function exportPDF(Request $request)
    {
        [$materials, $startDate, $endDate, $grouped] = $this->getSummaryData($request);

        $pdf = Pdf::loadView('admin.material-summary-export', [
            'materials' => $materials,
            'grouped'   => $grouped,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('material-summary-' . now()->format('Ymd_His') . '.pdf');
    }

    private function getSummaryData(Request $request)
    {
        // Date range
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Detect name and cost column in inventories
        $nameCol = collect(['name', 'item_name', 'description', 'particular'])
            ->first(fn($col) => Schema::hasColumn('inventories', $col));

        if (! $nameCol) {
            abort(500, 'No valid material name column found in inventories table.');
        }

        $costCol = collect(['cost_price', 'acquisition_price', 'price'])
            ->first(fn($col) => Schema::hasColumn('inventories', $col));

        // Query
        $materials = DB::table('invoice_items')
            ->selectRaw("
                DATE(invoices.created_at) AS day,
                COALESCE(clients.name, invoices.customer_name) AS customer_name,
                CASE
                    WHEN invoice_items.part_id IS NULL THEN invoice_items.manual_part_name
                    ELSE inventories.`$nameCol`
                END AS material,
                invoice_items.original_price AS price,
                CASE
                    WHEN invoice_items.part_id IS NULL THEN invoice_items.manual_acquisition_price
                    ELSE " . ($costCol ? "inventories.`$costCol`" : "invoice_items.manual_acquisition_price") . "
                END AS cost,
                (invoice_items.original_price - 
                    CASE
                        WHEN invoice_items.part_id IS NULL THEN invoice_items.manual_acquisition_price
                        ELSE " . ($costCol ? "inventories.`$costCol`" : "invoice_items.manual_acquisition_price") . "
                    END
                ) * invoice_items.quantity AS gross_profit
            ")
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('inventories', 'invoice_items.part_id', '=', 'inventories.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->orderBy('day', 'asc')
            ->get();

        $grouped = $materials->groupBy('day');

        return [$materials, $startDate, $endDate, $grouped];
    }
}
