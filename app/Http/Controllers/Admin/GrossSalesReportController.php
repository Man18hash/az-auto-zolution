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

        $dates = collect()
            ->merge($data['sales']->pluck('date'))
            ->merge($data['arCollections']->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')))
            ->merge($data['expenses']->pluck('date')->map(fn($d)=> Carbon::parse($d)->format('Y-m-d')))
            ->merge($data['cashDeposits']->pluck('date')->map(fn($d)=> Carbon::parse($d)->format('Y-m-d')))
            ->unique()
            ->sort()
            ->values();

        $report = [];
        $grand = [
            'sales'     => 0,
            'ar'        => 0,
            'expenses'  => 0,
            'deposits'  => 0,
            'discounts' => 0,
            'gross'     => 0,
        ];

        foreach ($dates as $date) {
            $salesForDay    = $data['sales']->filter(fn($s)  => $s['date'] === $date);
            $arForDay       = $data['arCollections']->filter(fn($ar)=> Carbon::parse($ar->date)->format('Y-m-d') === $date);
            $expensesForDay = $data['expenses']->filter(fn($ex)=> Carbon::parse($ex->date)->format('Y-m-d') === $date);
            $depositsForDay = $data['cashDeposits']->filter(fn($d)=> Carbon::parse($d->date)->format('Y-m-d') === $date);

            $totalSales     = $salesForDay->sum('line_total');
            $totalAR        = $arForDay->sum('amount');
            $totalExpenses  = $expensesForDay->sum('amount');
            $totalDeposits  = $depositsForDay->sum('amount');
            $totalDiscounts = $salesForDay->sum(fn($s) => ($s['discount_value'] ?? 0) + ($s['invoice_discount'] ?? 0));

            $grossTotal = ($totalSales + $totalAR)
                        - ($totalExpenses + $totalDeposits)
                        - $totalDiscounts;

            $grand['sales']     += $totalSales;
            $grand['ar']        += $totalAR;
            $grand['expenses']  += $totalExpenses;
            $grand['deposits']  += $totalDeposits;
            $grand['discounts'] += $totalDiscounts;
            $grand['gross']     += $grossTotal;

            $report[] = [
                'date'            => $date,
                'sales'           => $salesForDay,
                'total_sales'     => $totalSales,
                'ar'              => $arForDay,
                'total_ar'        => $totalAR,
                'expenses'        => $expensesForDay,
                'total_expenses'  => $totalExpenses,
                'deposits'        => $depositsForDay,
                'total_deposits'  => $totalDeposits,
                'total_discounts' => $totalDiscounts,
                'gross'           => $grossTotal,
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

        $rows = collect();

        foreach ($data['sales'] as $s) {
            $rows->push([
                'date'                 => $s['date'],
                'invoice_no'           => $s['invoice_no'],
                'customer'             => $s['customer'],
                'vehicle_manufacturer' => $s['vehicle_manufacturer'],
                'vehicle_model'        => $s['vehicle_model'],
                'vehicle_year'         => $s['vehicle_year'],
                'vehicle_plate'        => $s['vehicle_plate'],
                'description'          => $s['service'],
                'quantity'             => $s['quantity'],
                'acquisition_price'    => $s['acquisition_price'],
                'selling_price'        => $s['original_price'],
                'discount'             => ($s['discount_value'] ?? 0) + ($s['invoice_discount'] ?? 0),
                'line_total'           => $s['line_total'],
                'amount'               => $s['line_total'],
                'remarks'              => $s['remarks'],
                'payment'              => $s['payment'],
                'payment_type'         => $s['payment_type'],
                'type'                 => 'Sales',
            ]);
        }

        foreach ($data['arCollections'] as $ar) {
            $rows->push([
                'date'        => Carbon::parse($ar->date)->format('Y-m-d'),
                'customer'    => 'A/R Collections',
                'description' => $ar->description ?? '-',
                'quantity'    => '',
                'amount'      => $ar->amount,
                'type'        => 'A/R',
            ]);
        }

        foreach ($data['expenses'] as $ex) {
            $rows->push([
                'date'        => Carbon::parse($ex->date)->format('Y-m-d'),
                'customer'    => 'Expenses',
                'description' => $ex->title ?? '-',
                'quantity'    => '',
                'amount'      => $ex->amount,
                'type'        => 'Expense',
            ]);
        }

        foreach ($data['cashDeposits'] as $dep) {
            $rows->push([
                'date'        => Carbon::parse($dep->date)->format('Y-m-d'),
                'customer'    => 'Cash Deposits',
                'description' => $dep->description ?? '-',
                'quantity'    => '',
                'amount'      => $dep->amount,
                'type'        => 'Deposit',
            ]);
        }

        return Excel::download(new GrossSalesExport($rows), 'gross_sales_' . $startDate . '_to_' . $endDate . '.xlsx');
    }

    protected function generateReportData($startDate, $endDate)
    {
        $invoices = Invoice::with(['items.part', 'client', 'vehicle', 'jobs'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at')
            ->get();

        $sales = collect();

        foreach ($invoices as $invoice) {
            $customer = $invoice->client->name ?? $invoice->customer_name ?? '-';

            foreach ($invoice->items as $item) {
                $acqPrice = $item->manual_acquisition_price
                           ?? ($item->part->acquisition_price ?? 0);

                $sales->push([
                    'invoice_no'           => $invoice->invoice_no,
                    'date'                 => $invoice->created_at->format('Y-m-d'),
                    'customer'             => $customer,
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'        => $invoice->vehicle?->model ?? '',
                    'vehicle_year'         => $invoice->vehicle?->year ?? '',
                    'vehicle_plate'        => $invoice->vehicle?->plate_number ?? '',
                    'service'              => $item->manual_part_name ?? ($item->part->item_name ?? 'Part'),
                    'quantity'             => $item->quantity,
                    'acquisition_price'    => $acqPrice,
                    'original_price'       => $item->original_price ?? 0,
                    'discount_value'       => $item->discount_value ?? 0,
                    'invoice_discount'     => $invoice->total_discount ?? 0,
                    'line_total'           => $item->line_total ?? 0,
                    'remarks'              => $invoice->remarks ?? '',
                    'payment'              => $invoice->grand_total ?? 0,
                    'payment_type'         => $invoice->payment_type ?? 'cash',
                ]);
            }

            foreach ($invoice->jobs as $job) {
                $sales->push([
                    'invoice_no'           => $invoice->invoice_no,
                    'date'                 => $invoice->created_at->format('Y-m-d'),
                    'customer'             => $customer,
                    'vehicle_manufacturer' => $invoice->vehicle?->manufacturer ?? '',
                    'vehicle_model'        => $invoice->vehicle?->model ?? '',
                    'vehicle_year'         => $invoice->vehicle?->year ?? '',
                    'vehicle_plate'        => $invoice->vehicle?->plate_number ?? '',
                    'service'              => $job->job_description ?? 'Labor',
                    'quantity'             => 1,
                    'acquisition_price'    => 0,
                    'original_price'       => $job->total ?? 0,
                    'discount_value'       => 0,
                    'invoice_discount'     => $invoice->total_discount ?? 0,
                    'line_total'           => $job->total ?? 0,
                    'remarks'              => 'Labor',
                    'payment'              => $invoice->grand_total ?? 0,
                    'payment_type'         => $invoice->payment_type ?? 'cash',
                ]);
            }
        }

        return [
            'sales'         => $sales,
            'arCollections' => ARCollection::whereBetween('date', [$startDate, $endDate])->get(),
            'expenses'      => Expense::whereBetween('date', [$startDate, $endDate])->get(),
            'cashDeposits'  => CashDeposit::whereBetween('date', [$startDate, $endDate])->get(),
        ];
    }
}
