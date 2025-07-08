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
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

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
                    'date'                   => $invoice->created_at->format('Y-m-d'),
                    'customer_name'          => $invoice->client->name ?? $invoice->customer_name ?? '-',
                    // these four vehicle fields get sent to the Blade
                    'vehicle_plate'          => $invoice->vehicle?->plate_number    ?? '',
                    'vehicle_manufacturer'   => $invoice->vehicle?->manufacturer    ?? '',
                    'vehicle_model'          => $invoice->vehicle?->model           ?? '',
                    'vehicle_year'           => $invoice->vehicle?->year            ?? '',

                    'item_name'              => $item->manual_part_name
                                                 ?? ($item->part->item_name      ?? '-'),
                    'acquisition_price'      => $item->manual_acquisition_price
                                                 ?? ($item->part->acquisition_price ?? 0),
                    'selling_price'          => $item->manual_selling_price
                                                 ?? (
                                                     $item->discounted_price > 0
                                                       ? $item->discounted_price
                                                       : $item->original_price
                                                   ),
                    'quantity'               => $item->quantity,
                    'line_total'             => $item->line_total ?? 0,
                    'remarks'                => $invoice->remarks ?? '',
                ];
            }

            foreach ($invoice->jobs as $job) {
                $allItems[] = [
                    'date'                   => $invoice->created_at->format('Y-m-d'),
                    'customer_name'          => $invoice->client->name ?? $invoice->customer_name ?? '-',
                    'vehicle_plate'          => $invoice->vehicle?->plate_number    ?? '',
                    'vehicle_manufacturer'   => $invoice->vehicle?->manufacturer    ?? '',
                    'vehicle_model'          => $invoice->vehicle?->model           ?? '',
                    'vehicle_year'           => $invoice->vehicle?->year            ?? '',

                    'item_name'              => $job->job_description ?? '-',
                    'acquisition_price'      => 0,
                    'selling_price'          => $job->total ?? 0,
                    'quantity'               => 1,
                    'line_total'             => $job->total ?? 0,
                    'remarks'                => $invoice->remarks ?? '',
                ];
            }
        }

        $totalSales = collect($allItems)->sum('line_total');
        $totalCost  = collect($allItems)->sum(function ($item) {
            return ($item['acquisition_price'] ?? 0) * ($item['quantity'] ?? 1);
        });
        $totalProfit = $totalSales - $totalCost;

        return view('admin.sales-report', [
            'allItems'    => $allItems,
            'totalSales'  => $totalSales,
            'totalCost'   => $totalCost,
            'totalProfit' => $totalProfit,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());

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
            // items
            foreach ($invoice->items as $item) {
                $allItems[] = [
                    'date'                   => $invoice->created_at->format('Y-m-d'),
                    'customer_name'          => $invoice->client->name ?? $invoice->customer_name ?? '-',
                    'vehicle_plate'          => $invoice->vehicle?->plate_number    ?? '',
                    'vehicle_manufacturer'   => $invoice->vehicle?->manufacturer    ?? '',
                    'vehicle_model'          => $invoice->vehicle?->model           ?? '',
                    'vehicle_year'           => $invoice->vehicle?->year            ?? '',

                    'item_name'              => $item->manual_part_name
                                                 ?? ($item->part->item_name      ?? '-'),
                    'acquisition_price'      => $item->manual_acquisition_price
                                                 ?? ($item->part->acquisition_price ?? 0),
                    'selling_price'          => $item->manual_selling_price
                                                 ?? (
                                                     $item->discounted_price > 0
                                                       ? $item->discounted_price
                                                       : $item->original_price
                                                   ),
                    'quantity'               => $item->quantity,
                    'line_total'             => $item->line_total ?? 0,
                    'remarks'                => $invoice->remarks ?? '',
                ];
            }

            // jobs
            foreach ($invoice->jobs as $job) {
                $allItems[] = [
                    'date'                   => $invoice->created_at->format('Y-m-d'),
                    'customer_name'          => $invoice->client->name ?? $invoice->customer_name ?? '-',
                    'vehicle_plate'          => $invoice->vehicle?->plate_number    ?? '',
                    'vehicle_manufacturer'   => $invoice->vehicle?->manufacturer    ?? '',
                    'vehicle_model'          => $invoice->vehicle?->model           ?? '',
                    'vehicle_year'           => $invoice->vehicle?->year            ?? '',

                    'item_name'              => $job->job_description ?? '-',
                    'acquisition_price'      => 0,
                    'selling_price'          => $job->total ?? 0,
                    'quantity'               => 1,
                    'line_total'             => $job->total ?? 0,
                    'remarks'                => $invoice->remarks ?? '',
                ];
            }
        }

        return Excel::download(
            new SalesReportExport($allItems, $startDate, $endDate),
            'Sales_Report_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }
}
