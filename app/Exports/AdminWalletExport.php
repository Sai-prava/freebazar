<?php

namespace App\Exports;

use App\Models\UserWallet;
use Maatwebsite\Excel\Concerns\FromCollection;

class AdminWalletExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Select only the fields you want to export
        return UserWallet::whereNotNull('wallet_amount')->select('user_id', 'wallet_amount', 'trans_type', 'mobilenumber')->get();
    }
}
