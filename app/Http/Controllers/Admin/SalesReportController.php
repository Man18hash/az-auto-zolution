<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Import for export
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate   = $request->input('end_date',   Carbon::now()->toDateString());

        $invoices = Invoice::with(['items.part', 'client', 'vehicle', 'jobs'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $allItems = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $allItems[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_no'        => $invoice->invoice_no,
                    'date'              => $invoice->created_at->format('Y-m-d'),
                    'customer_name'     => $invoice->client->name
                                               ?? $invoice->customer_name 
                                               ?? '-',
                    'vehicle_plate'     => $invoice->vehicle?->plate_number   ?? '',
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'     => $invoice->vehicle?->model          ?? '',
                    'vehicle_year'      => $invoice->vehicle?->year           ?? '',
                    'item_name'         => $item->manual_part_name
                                               ?? ($item->part->item_name      ?? '-'),
                    'acquisition_price' => $item->manual_acquisition_price
                                               ?? ($item->part->acquisition_price ?? 0),
                    'selling_price'     => $item->original_price,
                    'discount_value'    => $item->discount_value,
                    'quantity'          => $item->quantity,
                    'line_total'        => $item->line_total,
                    'remarks'           => $invoice->remarks ?? '',
                ];
            }

            foreach ($invoice->jobs as $job) {
                $allItems[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_no'        => $invoice->invoice_no,
                    'date'              => $invoice->created_at->format('Y-m-d'),
                    'customer_name'     => $invoice->client->name         ?? $invoice->customer_name ?? '-',
                    'vehicle_plate'     => $invoice->vehicle?->plate_number   ?? '',
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'     => $invoice->vehicle?->model          ?? '',
                    'vehicle_year'      => $invoice->vehicle?->year           ?? '',
                    'item_name'         => $job->job_description              ?? '-',
                    'acquisition_price' => 0,
                    'selling_price'     => $job->total                       ?? 0,
                    'discount_value'    => 0,
                    'quantity'          => 1,
                    'line_total'        => $job->total                       ?? 0,
                    'remarks'           => $invoice->remarks                ?? '',
                ];
            }
        }

        // ─── AGGREGATES ───────────────────────────────────────────────────────
        $totalSales    = collect($allItems)->sum('line_total');
        $totalCost     = collect($allItems)
                             ->sum(fn($item) =>
                                  ($item['acquisition_price'] ?? 0)
                                * ($item['quantity']          ?? 1)
                             );

        $totalItemDiscount    = $invoices->sum(fn($inv) => $inv->items->sum('discount_value'));
        $invoiceLevelDiscount = $invoices->sum('total_discount');

        $totalDiscount = $totalItemDiscount + $invoiceLevelDiscount;
        $totalProfit   = $totalSales - $totalCost - $totalDiscount;

        $cashSales = $invoices
            ->filter(fn($inv) => $inv->payment_type === 'cash')
            ->sum(fn($inv) =>
                $inv->items->sum('line_total')
              + $inv->jobs->sum('total')
            );

        $nonCashSales = $totalSales - $cashSales;

        return view('admin.sales-report', [
            'invoices'      => $invoices,
            'allItems'      => $allItems,
            'totalSales'    => $totalSales,
            'totalCost'     => $totalCost,
            'totalDiscount' => $totalDiscount,
            'totalProfit'   => $totalProfit,
            'cashSales'     => $cashSales,
            'nonCashSales'  => $nonCashSales,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate   = $request->input('end_date',   Carbon::now()->toDateString());

        $invoices = Invoice::with(['items.part', 'client', 'vehicle', 'jobs'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $allItems = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $allItems[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_no'        => $invoice->invoice_no,
                    'date'              => $invoice->created_at->format('Y-m-d'),
                    'customer_name'     => $invoice->client->name
                                               ?? $invoice->customer_name 
                                               ?? '-',
                    'vehicle_plate'     => $invoice->vehicle?->plate_number   ?? '',
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'     => $invoice->vehicle?->model          ?? '',
                    'vehicle_year'      => $invoice->vehicle?->year           ?? '',
                    'item_name'         => $item->manual_part_name
                                               ?? ($item->part->item_name      ?? '-'),
                    'acquisition_price' => $item->manual_acquisition_price
                                               ?? ($item->part->acquisition_price ?? 0),
                    'selling_price'     => $item->original_price,
                    'discount_value'    => $item->discount_value,
                    'quantity'          => $item->quantity,
                    'line_total'        => $item->line_total,
                    'remarks'           => $invoice->remarks ?? '',
                ];
            }

            foreach ($invoice->jobs as $job) {
                $allItems[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_no'        => $invoice->invoice_no,
                    'date'              => $invoice->created_at->format('Y-m-d'),
                    'customer_name'     => $invoice->client->name         ?? $invoice->customer_name ?? '-',
                    'vehicle_plate'     => $invoice->vehicle?->plate_number   ?? '',
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'     => $invoice->vehicle?->model          ?? '',
                    'vehicle_year'      => $invoice->vehicle?->year           ?? '',
                    'item_name'         => $job->job_description              ?? '-',
                    'acquisition_price' => 0,
                    'selling_price'     => $job->total                       ?? 0,
                    'discount_value'    => 0,
                    'quantity'          => 1,
                    'line_total'        => $job->total                       ?? 0,
                    'remarks'           => $invoice->remarks                ?? '',
                ];
            }
        }

        return Excel::download(
            new SalesReportExport($allItems, $startDate, $endDate, $invoices),
            'Sales_Report_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }
}
