<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWalletExport;
use App\Exports\MonthlySalesExport;
use App\Exports\WalletExport;
use App\Http\Controllers\Controller;
use App\Imports\WalletImport;
use App\Models\PosModel;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DsrController extends Controller
{
    public function dsr(Request $request)
    {
        $query = Wallet::query();

        if (
            $request->has('start_date') && !empty($request->start_date) &&
            $request->has('end_date') && !empty($request->end_date)
        ) {

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } else {
            $query->whereDate('insert_date', now()->toDateString());

            if ($query->count() == 0) {
                $previousDate = DB::table('wallets')
                    ->whereDate('insert_date', '<', now()->toDateString())
                    ->max('insert_date');

                if ($previousDate) {
                    $query->whereDate('insert_date', $previousDate);
                }
            }
        }
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('mobilenumber', 'LIKE', "%{$searchTerm}%");
        }

        $wallets = $query->simplePaginate(15);

        return view('admin.dsr.index', compact('wallets'));
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
        // dd($request->file('file'));
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
    public function msr(Request $request)
    {
        Log::info($request->all());
        // Fetch all POS with associated users for the dropdown
        $pos = PosModel::with('user')->get()->map(function ($item) {
            // dd($item->user->user_id);
            $data = $item->toArray();
            $data['pos_id'] = $item->user ? $item->user->user_id : null; // Use user_id from user relationship
            unset($data['user']);
            return $data;
        });
        // Initialize the query for Wallet transactions
        $query = Wallet::select(
            'user_id',
            'mobilenumber',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
            DB::raw('SUM(billing_amount) as total_billing_amount')
        )
            ->whereNotNull('transaction_date')
            ->groupBy('user_id', 'mobilenumber', 'transaction_month');
        // dd($query);    

        if ($request->has('search') && !empty($request->search)) {
            $userId = $request->search;
            $query->where('pos_id', $userId);
        }
        // Apply month filter if selected
        if ($request->has('month') && !empty($request->month)) {
            $selectedMonth = $request->month;
            $query->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth]);
        }

        // Get the paginated results
        $monthlySales = $query->simplePaginate(15);

        // Return the view with data
        return view('admin.msr.index', compact('pos', 'monthlySales'));
    }





    public function exportMsr(Request $request)
    {
        $searchTerm = $request->input('search');
        return Excel::download(new MonthlySalesExport($searchTerm), 'MonthlySalesReport.csv');
    }
}
