<?php

namespace App\Imports;

use App\Models\UserWallet;
use Maatwebsite\Excel\Concerns\ToModel;


class AdminWalletImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new UserWallet([
            'user_id' => $row[0],
            'wallet_amount' => $row[1],
            'trans_type' => $row[2],
            'mobilenumber' => $row[3],
        ]);
    }
}
