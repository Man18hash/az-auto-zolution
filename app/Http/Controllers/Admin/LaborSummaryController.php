<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaborSummaryController extends Controller
{
    /**
     * Show the labor summary view.
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $labors = DB::table('invoice_jobs')
            ->selectRaw("
                DATE(invoices.created_at) AS day,
                COALESCE(clients.name, invoices.customer_name) AS customer_name,
                invoice_jobs.job_description,
                invoice_jobs.total AS labor_charge
            ")
            ->join('invoices', 'invoice_jobs.invoice_id', '=', 'invoices.id')
            ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->orderBy('day', 'asc')
            ->orderBy('customer_name', 'asc')
            ->get();

        $grouped = $labors->groupBy('day');

        return view('admin.labor-summary', compact('labors', 'startDate', 'endDate', 'grouped'));
    }

    /**
     * Export labor summary to PDF.
     */
    public function exportPDF(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $labors = DB::table('invoice_jobs')
            ->selectRaw("
                DATE(invoices.created_at) AS day,
                COALESCE(clients.name, invoices.customer_name) AS customer_name,
                invoice_jobs.job_description,
                invoice_jobs.total AS labor_charge
            ")
            ->join('invoices', 'invoice_jobs.invoice_id', '=', 'invoices.id')
            ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->orderBy('day', 'asc')
            ->orderBy('customer_name', 'asc')
            ->get();

        $grouped = $labors->groupBy('day');

        $pdf = Pdf::loadView('admin.labor-summary-export', [
            'labors' => $labors,
            'grouped' => $grouped,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('labor-summary-' . now()->format('Ymd_His') . '.pdf');
    }
}
