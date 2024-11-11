<?php

namespace App\Exports;

use App\Models\Wallet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MsrExport implements FromCollection, WithHeadings
{
    protected $filteredData;

    public function __construct($filteredData)
    {
        $this->filteredData = $filteredData;
    }

    public function collection()
    {
        return $this->filteredData->map(function ($wallet) {
            return [
                'month' => Carbon::parse($wallet->transaction_month)->format('F Y'), 
                'mobilenumber' => $wallet->mobilenumber,
                'name' => $wallet->user->name ?? 'N/A', 
                'billing_amount' => $wallet->total_billing_amount,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Month',
            'Mobile Number',
            'Name',
            'Billing Amount',
        ];
    }
}
