<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MaterialSummaryExport implements FromView
{
    public $grouped;
    public $startDate;
    public $endDate;

    public function __construct($grouped, $startDate, $endDate)
    {
        $this->grouped = $grouped;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('admin.material-summary-export', [
            'grouped' => $this->grouped,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
