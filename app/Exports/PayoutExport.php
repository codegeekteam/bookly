<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class PayoutExport implements FromView, WithColumnWidths
{
    public function __construct(
        public $records
    ) {}

    public function view(): View
    {
        return view('exports.payouts', [
            'records' => $this->records
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 30,  // Service Provider
            'C' => 18,  // Amount
            'D' => 20,  // Due Date
            'E' => 20,  // Status
            'F' => 20,  // Transferred At
            'G' => 20,  // Created At
        ];
    }
}
