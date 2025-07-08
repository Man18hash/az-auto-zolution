<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\ARCollection;
use App\Models\CashDeposit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class IncomeAnalysisReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input(
            'start_date',
            Carbon::now()->startOfMonth()->toDateString()
        );
        $endDate   = $request->input(
            'end_date',
            Carbon::now()->endOfMonth()->toDateString()
        );

        // --- your original data pulls ---
        $invoices    = Invoice::where('status','paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])->get();

        $expenses    = Expense::whereBetween('date', [$startDate, $endDate])->get();
        $collections = ARCollection::whereBetween('date', [$startDate, $endDate])->get();
        $deposits    = CashDeposit::whereBetween('date', [$startDate, $endDate])->get();

        // --- your original aggregates ---
        $totalSales    = $invoices->sum(function($inv){
            return $inv->items->sum('line_total')
                 + $inv->jobs->sum('total');
        });
        $totalAR        = $collections->sum('amount');
        $totalExpenses  = $expenses->sum('amount');
        $totalDeposits  = $deposits->sum('amount');
        $netIncome      = ($totalSales + $totalAR + $totalDeposits) - $totalExpenses;

        // --- NEW: build daily labels and series for charts ---
        $period = CarbonPeriod::create($startDate, $endDate);

        $labels         = [];
        $salesSeries    = [];
        $arSeries       = [];
        $depositSeries  = [];
        $expenseSeries  = [];

        foreach ($period as $day) {
            $d = $day->format('Y-m-d');
            $labels[] = $d;

            // daily paid‐invoice sales
            $dailyInv = Invoice::with('items','jobs')
                ->where('status','paid')
                ->whereDate('created_at', $d)
                ->get();

            $dailySales = $dailyInv->sum(function($inv){
                return $inv->items->sum('line_total')
                     + $inv->jobs->sum('total');
            });
            $salesSeries[]   = $dailySales;

            // daily A/R, deposits, expenses
            $arSeries[]      = ARCollection::whereDate('date',$d)->sum('amount');
            $depositSeries[] = CashDeposit  ::whereDate('date',$d)->sum('amount');
            $expenseSeries[] = Expense      ::whereDate('date',$d)->sum('amount');
        }

        // pass everything to the view
        return view('admin.income-analysis-report', [
            'labels'         => $labels,
            'salesSeries'    => $salesSeries,
            'arSeries'       => $arSeries,
            'depositSeries'  => $depositSeries,
            'expenseSeries'  => $expenseSeries,
            'totalSales'     => $totalSales,
            'totalAR'        => $totalAR,
            'totalExpenses'  => $totalExpenses,
            'totalDeposits'  => $totalDeposits,
            'netIncome'      => $netIncome,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ]);
    }
}
