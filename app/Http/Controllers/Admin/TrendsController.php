<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TrendsController extends Controller
{
    public function index(Request $request)
    {
        // 1) Parse date range (default: last 30 days)
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // 2) Detect the “name” column on inventories
        $nameCol = null;
        foreach (['name','item_name','description','particular'] as $col) {
            if (Schema::hasColumn('inventories', $col)) {
                $nameCol = $col;
                break;
            }
        }
        if (! $nameCol) {
            abort(500, 'No suitable name column found in inventories table.');
        }

        // 3) Top 10 materials by total quantity, filtered by invoice_items.created_at
        $materials = DB::table('invoice_items')
            ->selectRaw("
                CASE
                  WHEN invoice_items.part_id IS NULL
                    THEN invoice_items.manual_part_name
                  ELSE inventories.`{$nameCol}`
                END AS material_name,
                SUM(invoice_items.quantity) AS total_quantity
            ")
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('inventories', 'invoice_items.part_id', '=', 'inventories.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoice_items.created_at', [$startDate, $endDate])
            ->groupBy('material_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return view('admin.trends', compact('materials','startDate','endDate'));
    }
}
