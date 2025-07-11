<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;

class DiscountReportController extends Controller
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

        // Fetch only paid invoices with a discount in the given date range
        $invoices = Invoice::where('status', 'paid')
            ->where('total_discount', '>', 0)
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)  ->endOfDay(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Overall total discount
        $totalDiscount = $invoices->sum('total_discount');

        // Breakdown of discount by date
        $discountByDate = $invoices
            ->groupBy(fn($inv) => $inv->created_at->format('Y-m-d'))
            ->map(fn($group, $date) => [
                'date'     => $date,
                'discount' => $group->sum('total_discount'),
            ])
            ->values();

        return view('admin.discount-report', [
            'invoices'       => $invoices,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'totalDiscount'  => $totalDiscount,
            'discountByDate' => $discountByDate,
        ]);
    }
}
