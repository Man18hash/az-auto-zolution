<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\ARCollection;
use App\Models\CashDeposit;
use App\Models\Expense;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrossSalesExport;

class GrossSalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate   = $request->input('end_date',   Carbon::now()->toDateString());

        $data = $this->generateReportData($startDate, $endDate);

        $sales         = $data['sales'];
        $arCollections = $data['arCollections'];
        $expenses      = $data['expenses'];
        $cashDeposits  = $data['cashDeposits'];

        // build list of all dates
        $dates = collect()
            ->merge($sales->pluck('date'))
            ->merge($arCollections->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')))
            ->merge($expenses->pluck('date')->map(fn($d)=> Carbon::parse($d)->format('Y-m-d')))
            ->merge($cashDeposits->pluck('date')->map(fn($d)=> Carbon::parse($d)->format('Y-m-d')))
            ->unique()
            ->sort()
            ->values();

        $report = [];
        $grand  = [
            'sales'    => 0,
            'ar'       => 0,
            'expenses' => 0,
            'deposits' => 0,
            'gross'    => 0,
        ];

        foreach ($dates as $date) {
            // filter each type for this date
            $salesForDay    = $sales->filter(fn($s)  => $s['date'] === $date);
            $arForDay       = $arCollections->filter(fn($ar)=> Carbon::parse($ar->date)->format('Y-m-d') === $date);
            $expensesForDay = $expenses->filter(fn($ex)=> Carbon::parse($ex->date)->format('Y-m-d') === $date);
            $depositsForDay = $cashDeposits->filter(fn($d)=> Carbon::parse($d->date)->format('Y-m-d') === $date);

            // totals
            $totalSales    = $salesForDay->sum('amount');
            $totalAR       = $arForDay->sum('amount');
            $totalExpenses = $expensesForDay->sum('amount');
            $totalDeposits = $depositsForDay->sum('amount');
            $grossTotal    = ($totalSales + $totalAR) - ($totalExpenses + $totalDeposits);

            // accumulate grand
            $grand['sales']    += $totalSales;
            $grand['ar']       += $totalAR;
            $grand['expenses'] += $totalExpenses;
            $grand['deposits'] += $totalDeposits;
            $grand['gross']    += $grossTotal;

            $report[] = [
                'date'           => $date,
                'sales'          => $salesForDay,
                'total_sales'    => $totalSales,
                'ar'             => $arForDay,
                'total_ar'       => $totalAR,
                'expenses'       => $expensesForDay,
                'total_expenses' => $totalExpenses,
                'deposits'       => $depositsForDay,
                'total_deposits' => $totalDeposits,
                'gross'          => $grossTotal,
            ];
        }

        return view('admin.gross-sales-report', [
            'report'    => $report,
            'grand'     => $grand,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate   = $request->input('end_date',   Carbon::now()->toDateString());

        $data = $this->generateReportData($startDate, $endDate);

        $allItems = collect();

        // ── Sales rows ───────────────────────────────
        foreach ($data['sales'] as $sale) {
            $allItems->push([
                'date'                 => $sale['date'],
                'customer'             => $sale['customer'],
                'vehicle_manufacturer' => $sale['vehicle_manufacturer'] ?? '',
                'vehicle_model'        => $sale['vehicle_model']        ?? '',
                'vehicle_year'         => $sale['vehicle_year']         ?? '',
                'vehicle_plate'        => $sale['vehicle_plate']        ?? '',
                'description'          => $sale['service'],
                'quantity'             => $sale['quantity'],
                'amount'               => $sale['amount'],
                'remarks'              => $sale['remarks'],
                'type'                 => 'Sales',
            ]);
        }

        // ── A/R Collections ──────────────────────────
        foreach ($data['arCollections'] as $ar) {
            $allItems->push([
                'date'        => Carbon::parse($ar->date)->format('Y-m-d'),
                'customer'    => 'A/R Collections',
                'vehicle_manufacturer' => '',
                'vehicle_model'        => '',
                'vehicle_year'         => '',
                'vehicle_plate'        => '',
                'description' => $ar->description ?? '-',
                'quantity'    => '',
                'amount'      => $ar->amount,
                'remarks'     => '',
                'type'        => 'A/R',
            ]);
        }

        // ── Expenses ─────────────────────────────────
        foreach ($data['expenses'] as $ex) {
            $allItems->push([
                'date'        => Carbon::parse($ex->date)->format('Y-m-d'),
                'customer'    => 'Expenses',
                'vehicle_manufacturer' => '',
                'vehicle_model'        => '',
                'vehicle_year'         => '',
                'vehicle_plate'        => '',
                'description' => $ex->title ?? '-',
                'quantity'    => '',
                'amount'      => $ex->amount,
                'remarks'     => '',
                'type'        => 'Expense',
            ]);
        }

        // ── Cash Deposits ────────────────────────────
        foreach ($data['cashDeposits'] as $dep) {
            $allItems->push([
                'date'        => Carbon::parse($dep->date)->format('Y-m-d'),
                'customer'    => 'Cash Deposits',
                'vehicle_manufacturer' => '',
                'vehicle_model'        => '',
                'vehicle_year'         => '',
                'vehicle_plate'        => '',
                'description' => $dep->description ?? '-',
                'quantity'    => '',
                'amount'      => $dep->amount,
                'remarks'     => '',
                'type'        => 'Deposit',
            ]);
        }

        return Excel::download(
            new GrossSalesExport($allItems),
            'gross_sales_report_'.$startDate.'_to_'.$endDate.'.xlsx'
        );
    }

    protected function generateReportData($startDate, $endDate)
    {
        $invoices = Invoice::with(['items.part', 'client', 'jobs'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)  ->endOfDay(),
            ])
            ->orderBy('created_at')
            ->get();

        $sales = collect();

        foreach ($invoices as $invoice) {
            $customer = $invoice->client->name ?? $invoice->customer_name ?? '-';

            // line‐items
            foreach ($invoice->items as $item) {
                $sales->push([
                    'invoice_id'     => $invoice->id,               // ← new
                    'invoice_no'     => $invoice->invoice_no,       // ← new
                    'date'                 => $invoice->created_at->format('Y-m-d'),
                    'customer'             => $customer,
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'        => $invoice->vehicle?->model        ?? '',
                    'vehicle_year'         => $invoice->vehicle?->year         ?? '',
                    'vehicle_plate'        => $invoice->vehicle?->plate_number ?? '',
                    'service'              => $item->manual_part_name
                                              ?? ($item->part->item_name ?? 'Service/Labor'),
                    'quantity'             => $item->quantity,
                    'amount'               => $item->line_total,
                    'remarks'              => $invoice->remarks ?? '',
                     // ← Newly added:
                    'payment_type'   => $invoice->payment_type,
                    'discount'       => $invoice->total_discount,
                    'payment'        => $invoice->grand_total,
                ]);
            }

            // jobs
            foreach ($invoice->jobs as $job) {
                $sales->push([
                    'invoice_id'     => $invoice->id,               // ← new
                    'invoice_no'     => $invoice->invoice_no,       // ← new
                    'date'                 => $invoice->created_at->format('Y-m-d'),
                    'customer'             => $customer,
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'        => $invoice->vehicle?->model        ?? '',
                    'vehicle_year'         => $invoice->vehicle?->year         ?? '',
                    'vehicle_plate'        => $invoice->vehicle?->plate_number ?? '',
                    'service'              => $job->job_description ?? 'Labor',
                    'quantity'             => 1,
                    'amount'               => $job->total ?? 0,
                    'remarks'              => 'Labor',
                     // ← Newly added:
                    'payment_type'   => $invoice->payment_type,
                    'discount'       => $invoice->total_discount,
                    'payment'        => $invoice->grand_total,
                ]);
            }
        }

        return [
            'sales'         => $sales,
            'arCollections' => ARCollection::whereBetween('date',   [$startDate, $endDate])->get(),
            'expenses'      => Expense::whereBetween('date',       [$startDate, $endDate])->get(),
            'cashDeposits'  => CashDeposit::whereBetween('date',   [$startDate, $endDate])->get(),
        ];
    }
}
