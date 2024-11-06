<?php

namespace App\Http\Controllers;

use App\Exports\WalletExport;
use App\Imports\WalletImport;
use App\Models\PosModel;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class WalletController extends Controller
{
    public function walletManage(Request $request, $id)
    {
        $user = User::find($id);
        $userId = Auth::user()->id;
        // dd($userId);
        $pos = PosModel::where('user_id', $userId)->first();

        $walletBalanceQuery = Wallet::where('user_id', $user->id);
        $transactionQuery = Wallet::where('user_id', $user->id);

        if ($request->has('balance_date') && $request->balance_date != null) {
            $walletBalanceQuery->whereDate('transaction_date', $request->balance_date);
        }

        if ($request->has('transaction_date') && $request->transaction_date != null) {
            $transactionQuery->whereDate('transaction_date', $request->transaction_date);
        }

        $walletBalance = $walletBalanceQuery->first();
        $walletList = $transactionQuery->orderBy('transaction_date', 'desc')->get();
        // dd($walletList);

        $balanceNotFound = $request->has('balance_date') && !$walletBalance;
        $transactionsNotFound = $request->has('transaction_date') && $walletList->isEmpty();

        $userId = $user->id;
        $posId = $pos->id;

        $userwallet = UserWallet::where('user_id', $userId)->first();
        $totalusedAmount = UserWallet::where('user_id', $userId)->sum('used_amount');

        if ($userwallet) {
            $walletamount = $userwallet->wallet_amount;
            $walletBalance = $walletamount - $totalusedAmount;
        } else {
            $walletBalance = 0;
        }

        if ($walletBalance < 0) {
            $walletBalance = 0;
        }

        return view('pos.wallet_manage', compact('user', 'walletList', 'walletBalance', 'balanceNotFound', 'transactionsNotFound'));
    }

    public function dsr(Request $request)
    {
        $query = Wallet::query();

        // Check for date filters
        if (
            $request->has('start_date') && !empty($request->start_date) &&
            $request->has('end_date') && !empty($request->end_date)
        ) {
            // Apply date filter and paginate the results
            $wallets = $query->whereBetween('transaction_date', [$request->start_date, $request->end_date])
                ->orderBy('id', 'desc') // You might want to order by id or another field
                ->paginate(15); // Use paginate here instead of get
        } else {
            // If no filters, just get the latest records
            $wallets = $query->orderBy('id', 'desc')->simplepaginate(15);
        }

        return view('pos.dsr', compact('wallets'));
    }



    public function export(Request $request)
    {
        if (
            $request->has('start_date') && !empty($request->start_date) &&
            $request->has('end_date') && !empty($request->end_date)
        ) {
            return Excel::download(new WalletExport($request->start_date, $request->end_date), 'daily_sales_report.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    public function import(Request $request)
    {

        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt',
            ]);

            Excel::import(new WalletImport, $request->file('file'));

            return redirect()->back()->with('success', 'DSR File Uploaded Successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function dsrList(Request $request)
    {
        $request->validate([
            'amount' => 'required',
        ]);

        $userId = Auth::user()->id;
        $pos = PosModel::where('user_id', $userId)->first();
        $randomNumber = mt_rand(1, 99999);
        $invoiceNumber = $randomNumber + 1;
        $invoice = 'S' . str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT);
        $amount = $request->amount;
        $mobilenumber = $request->mobilenumber;
        $transaction_date = $request->transaction_date;
        $user_id = $request->user_id;
        $pay_by = $request->pay_by;
        $alt_pay_by = $request->alternative_pay_by;

        // dd($pos->transaction_charge);
        $transaction_charge = $pos ? $pos->transaction_charge : 0;
        $transaction_amount = $amount * ($transaction_charge / 100);

        $walletUsedAmount = 0;

        if ($pay_by == 'wallet') {
            $userWallet = UserWallet::where('user_id', $user_id)->first();
            $walletBalance = $userWallet ? $userWallet->wallet_amount - UserWallet::where('user_id', $user_id)->sum('used_amount') : 0;

            if ($walletBalance <= 0) {
                return redirect()->back()->with('error', 'Wallet is empty. Payment cannot be processed.');
            }

            if ($walletBalance > 0) {
                $walletUsedAmount = min($amount, $walletBalance);
                $remainingAmount = $amount - $walletUsedAmount;

                $userWalletEntry = new UserWallet();
                $userWalletEntry->invoice = $invoice;
                $userWalletEntry->used_amount = $walletUsedAmount;
                $userWalletEntry->user_id = $user_id;
                $userWalletEntry->mobilenumber = $mobilenumber;
                $userWalletEntry->transaction_date = $transaction_date;
                $userWalletEntry->pay_by = 'wallet';
                $userWalletEntry->trans_type = 'debit';
                $userWalletEntry->pos_id = $pos->id ?? null;
                $userWalletEntry->save();

                $walletEntry = new Wallet();
                $walletEntry->user_id = $user_id;
                $walletEntry->invoice = $invoice;
                $walletEntry->mobilenumber = $mobilenumber;
                $walletEntry->transaction_date = $transaction_date;
                $walletEntry->billing_amount = $amount;
                $walletEntry->transaction_amount = $transaction_amount;

                if ($walletBalance >= $amount) {
                    $walletEntry->amount_wallet = $amount;
                    $walletEntry->pay_by = 'wallet';
                    $walletEntry->tran_type = 'debit';
                    $walletEntry->amount = 0;
                } else {
                    $walletEntry->amount_wallet = $walletUsedAmount;
                    $walletEntry->pay_by = $alt_pay_by;
                    $walletEntry->tran_type = 'credit';
                    $walletEntry->amount = $remainingAmount;
                }

                $walletEntry->pos_id = $pos->id ?? null;
                $walletEntry->insert_date = now();
                $walletEntry->save();
            }
        } else {
            $remainingAmount = $amount;
        }

        if ($pay_by == 'cash' || $pay_by == 'upi') {
            $dsrlist = new Wallet();
            $dsrlist->invoice = $invoice;
            $dsrlist->user_id = $user_id;
            $dsrlist->amount = $remainingAmount;
            $dsrlist->pay_by = $pay_by;
            $dsrlist->mobilenumber = $mobilenumber;
            $dsrlist->transaction_date = $transaction_date;
            $dsrlist->tran_type = 'credit';
            $dsrlist->billing_amount = $amount;
            $dsrlist->amount_wallet = 0;
            $dsrlist->transaction_amount = $transaction_amount;
            $dsrlist->pos_id = $pos->id ?? null;
            $dsrlist->insert_date = now();
            $dsrlist->save();
        }

        return redirect()->back()->with('success', 'Payment successfully.');
    }


    public function journal(Request $request)
    {
        if ($request->has(['start_date', 'end_date'])) {

            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $transactions = Wallet::whereBetween('insert_date', [$request->start_date, $request->end_date])->get();
        } else {
            $transactions = collect();
        }
        $notransactions = $transactions->isEmpty();
        return view('pos.journal', compact('transactions', 'notransactions'));
    }
}
