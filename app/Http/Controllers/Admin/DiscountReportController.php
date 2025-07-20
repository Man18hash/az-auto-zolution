<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

        // Get invoices with discount (either invoice-level or item-level)
        $invoices = Invoice::with(['client', 'vehicle', 'items' => function ($q) {
                $q->select('id', 'invoice_id', 'discount_value');
            }])
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Sum item-level + invoice-level discounts per invoice
        foreach ($invoices as $invoice) {
            $invoice->item_discount = $invoice->items->sum('discount_value');
            $invoice->combined_discount = $invoice->item_discount + $invoice->total_discount;
        }

        // Overall total discount
        $totalDiscount = $invoices->sum('combined_discount');

        // Breakdown of discount by date
        $discountByDate = $invoices
            ->groupBy(fn($inv) => $inv->created_at->format('Y-m-d'))
            ->map(fn($group, $date) => [
                'date'     => $date,
                'discount' => $group->sum('combined_discount'),
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
