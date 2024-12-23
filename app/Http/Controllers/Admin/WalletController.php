<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWalletExport;
use App\Http\Controllers\Controller;
use App\Imports\AdminWalletImport;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class WalletController extends Controller
{
    public function wallet()
    {
        $walletBalance = UserWallet::whereNotNull('wallet_amount')->orderBy('id', 'desc')->simplePaginate(15);
        //  dd($walletBalance);
        return view('admin.wallet.index', compact('walletBalance'));
    }
    public function exportWallet()
    {
        return Excel::download(new AdminWalletExport, 'admin_wallet_data.xlsx');
    }
    public function uploadWallet(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        Excel::import(new AdminWalletImport, $request->file('file'));

        return redirect()->back()->with('success', 'Wallet data imported successfully.');
    }
}
