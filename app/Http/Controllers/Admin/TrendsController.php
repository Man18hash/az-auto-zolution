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
        // 1) Get filters from request
        $period = $request->input('period', 'day'); // day, week, month
        $search = trim($request->input('search', '')); // case-insensitive substring match

        // 2) Calculate start & end dates based on period
        $now = Carbon::now();
        if ($period === 'day') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : $now->copy()->startOfDay();
            $endDate   = $request->filled('end_date')   ? Carbon::parse($request->input('end_date'))->endOfDay()   : $now->copy()->endOfDay();
        } elseif ($period === 'week') {
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();
        } elseif ($period === 'month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
        } else {
            $startDate = $now->copy()->subMonth()->startOfDay();
            $endDate   = $now->copy()->endOfDay();
        }

        // 3) Detect the "name" column on inventories
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

        // 4) Query top 30 materials for bar chart/table (filtered if search is provided)
        $materialsQuery = DB::table('invoice_items')
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
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->groupBy('material_name')
            ->orderByDesc('total_quantity');

        // Case-insensitive "contains" filter (substring match, no special key required)
        if ($search !== '') {
            $materialsQuery->havingRaw('LOWER(material_name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        $materials = $materialsQuery->limit(30)->get();

        // 5) Compute total sales for current search filter (₱)
        $filteredSales = null;
        if ($search !== '') {
            $filteredSales = DB::table('invoice_items')
                ->selectRaw("SUM(invoice_items.line_total) as sum_sales")
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->leftJoin('inventories', 'invoice_items.part_id', '=', 'inventories.id')
                ->where('invoices.status', 'paid')
                ->whereBetween('invoices.created_at', [$startDate, $endDate])
                ->whereRaw("
                    LOWER(
                        CASE
                            WHEN invoice_items.part_id IS NULL
                                THEN invoice_items.manual_part_name
                            ELSE inventories.`{$nameCol}`
                        END
                    ) LIKE ?
                ", ['%' . strtolower($search) . '%'])
                ->value('sum_sales') ?? 0;
        }

        // 6) For dropdown: get ALL unique material names used in period
        $allMaterialNames = DB::table('invoice_items')
            ->selectRaw("DISTINCT
                CASE
                  WHEN invoice_items.part_id IS NULL
                    THEN invoice_items.manual_part_name
                  ELSE inventories.`{$nameCol}`
                END AS material_name
            ")
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('inventories', 'invoice_items.part_id', '=', 'inventories.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->whereNotNull(DB::raw(
                "CASE
                    WHEN invoice_items.part_id IS NULL
                        THEN invoice_items.manual_part_name
                    ELSE inventories.`{$nameCol}`
                END"
            ))
            ->orderBy('material_name')
            ->pluck('material_name')
            ->filter()
            ->values();

        // 7) Pass variables to Blade
        return view('admin.trends', compact(
            'materials', 'startDate', 'endDate', 'period', 'search', 'filteredSales', 'allMaterialNames'
        ));
    }
}
