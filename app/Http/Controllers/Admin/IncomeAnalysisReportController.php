<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\ARCollection;
use App\Models\Expense;
use App\Models\CashDeposit;

class IncomeAnalysisReportController extends Controller
{
    public function index(Request $request)
    {
        $periodType = $request->input('period', 'daily');
        $now = Carbon::now();

        // 1) Determine buckets
        switch ($periodType) {
            case 'weekly':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end   = $now->copy()->endOfDay();
                $increment = fn($dt)=> $dt->addDay();
                $format    = 'M d';
                break;
            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                $increment = fn($dt)=> $dt->addDay();
                $format    = 'M d';
                break;
            case 'yearly':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                $increment = fn($dt)=> $dt->addMonth();
                $format    = 'M Y';
                break;
            case 'daily':
            default:
                $periodType = 'daily';
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                $increment = fn($dt)=> $dt->addHour();
                $format    = 'H:00';
        }

        // 2) Build bucket boundaries + labels
        $buckets    = [];
        $labels     = [];
        $cursor     = $start->copy();
        while ($cursor->lte($end)) {
            $buckets[] = $cursor->copy();
            $labels[]  = $cursor->format($format);
            $increment($cursor);
        }
        $bucketEnds = [];
        foreach ($buckets as $b) {
            $e = $b->copy();
            $increment($e);
            $bucketEnds[] = $e;
        }

        // 3) Initialize series arrays
        $count = count($buckets);
        $series = [
            'Sales'    => array_fill(0, $count, 0),
            'A/R'      => array_fill(0, $count, 0),
            'Expenses' => array_fill(0, $count, 0),
            'Deposits' => array_fill(0, $count, 0),
        ];

        // 4) Load data (with items for discount computation)
        $invoices = Invoice::with('items','jobs')
            ->where('status','paid')
            ->whereBetween('created_at', [$start, $end])
            ->get();
        $ars      = ARCollection::whereBetween('date',    [$start->toDateString(), $end->toDateString()])->get();
        $expenses = Expense::whereBetween('date',        [$start->toDateString(), $end->toDateString()])->get();
        $deposits = CashDeposit::whereBetween('date',    [$start->toDateString(), $end->toDateString()])->get();

        // 5) Bucket sums
        foreach ($invoices as $inv) {
            $ts = $inv->created_at;
            foreach ($buckets as $i => $bstart) {
                if ($ts->gte($bstart) && $ts->lt($bucketEnds[$i])) {
                    $series['Sales'][$i] += $inv->items->sum('line_total') + $inv->jobs->sum('total');
                    break;
                }
            }
        }
        foreach ($ars as $a) {
            $ts = Carbon::parse($a->date);
            foreach ($buckets as $i => $bstart) {
                if ($ts->gte($bstart) && $ts->lt($bucketEnds[$i])) {
                    $series['A/R'][$i] += $a->amount;
                    break;
                }
            }
        }
        foreach ($expenses as $e) {
            $ts = Carbon::parse($e->date);
            foreach ($buckets as $i => $bstart) {
                if ($ts->gte($bstart) && $ts->lt($bucketEnds[$i])) {
                    $series['Expenses'][$i] += $e->amount;
                    break;
                }
            }
        }
        foreach ($deposits as $d) {
            $ts = Carbon::parse($d->date);
            foreach ($buckets as $i => $bstart) {
                if ($ts->gte($bstart) && $ts->lt($bucketEnds[$i])) {
                    $series['Deposits'][$i] += $d->amount;
                    break;
                }
            }
        }

        // 6) Net Income series
        $net = [];
        for ($i=0; $i<$count; $i++) {
            $net[] = $series['Sales'][$i]
                   + $series['A/R'][$i]
                   - $series['Expenses'][$i]
                   - $series['Deposits'][$i];
        }
        $series['Net Income'] = $net;

        // 7) Totals (with guard)
        $totals = [];
        foreach ($series as $k => $arr) {
            $totals[$k] = is_array($arr)
                ? array_sum($arr)
                : $arr;
        }

        // 8) Discounts (sum BOTH invoice-level and item-level discounts)
        $totalInvoiceDiscount = $invoices->sum('total_discount');
        $totalItemDiscount    = $invoices->sum(function($inv){
            return $inv->items->sum('discount_value');
        });
        $totalDiscount = $totalInvoiceDiscount + $totalItemDiscount;

        // 9) Payment split
        $cashPayments = $invoices
            ->filter(fn($inv)=> $inv->payment_type==='cash')
            ->sum(fn($inv)=> $inv->items->sum('line_total') + $inv->jobs->sum('total'));
        $nonCashPayments  = $totals['Sales'] - $cashPayments;

        return view('admin.income-analysis-report', [
            'periodType'      => $periodType,
            'labels'          => $labels,
            'series'          => $series,
            'totals'          => $totals,
            'totalDiscount'   => $totalDiscount,
            'cashPayments'    => $cashPayments,
            'nonCashPayments' => $nonCashPayments,
        ]);
    }
}
