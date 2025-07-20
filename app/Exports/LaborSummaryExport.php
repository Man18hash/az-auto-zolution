<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaborSummaryExport implements FromView
{
    public $jobs, $startDate, $endDate, $grouped;

    public function __construct($jobs, $startDate, $endDate, $grouped)
    {
        $this->jobs = $jobs;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->grouped = $grouped;
    }

    public function view(): View
    {
        return view('admin.labor-summary-export', [
            'jobs' => $this->jobs,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'grouped' => $this->grouped,
        ]);
    }
}
