<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\DailySheet;
use App\Exports\Sheets\SummarySheet;

class LaporanExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new DailySheet($this->data['start_date'], $this->data['end_date']),
            new SummarySheet($this->data),
        ];
    }
}

