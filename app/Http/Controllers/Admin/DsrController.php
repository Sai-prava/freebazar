<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminWalletExport;
use App\Exports\MonthlySalesExport;
use App\Exports\MsrExport;
use App\Exports\WalletExport;
use App\Http\Controllers\Controller;
use App\Imports\WalletImport;
use App\Models\PosModel;
use App\Models\sponcer;
use App\Models\Sponsor;
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

        $wallets = $query->with('user','getPos')->orderBy('id', 'desc')
        ->simplePaginate(15);
        // dd($wallets);
        $wallets->appends($request->only(['search', 'start_date', 'end_date']));

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
        $pos = PosModel::with('user')->get()->map(function ($item) {
            $data = $item->toArray();
            $data['pos_id'] = $item->user ? $item->user->user_id : null;
            unset($data['user']);
            return $data;
        });

        // $query = Wallet::select(
        //     'pos_id',
        //     'user_id',
        //     'mobilenumber',
        //     DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
        //     DB::raw('SUM(billing_amount) as total_billing_amount')
        // )
        //     ->whereNotNull('transaction_date')
        //     ->groupBy('user_id', 'pos_id', 'mobilenumber', 'transaction_month');

        $query = Wallet::select(
            'user_id',
            'mobilenumber',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
            DB::raw('SUM(billing_amount) as total_billing_amount')
        )->whereNotNull(['transaction_date', 'user_id'])
            ->with(['user', 'getPos'])
            ->whereNotNull('transaction_date')
            ->orderBy('id', 'desc')
            ->groupBy(['user_id', 'mobilenumber',  DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")')]);

        if ($request->has('search') && !empty($request->search)) {
            $userId = $request->search;
            $query->where('pos_id', $userId);
        }
        if ($request->has('month') && !empty($request->month)) {
            $selectedMonth = $request->month;
            $query->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth]);
        }
        $monthlySales = $query->simplePaginate(15)->through(function ($item) {
            // Log::info($item->user_id);
            $billing_amount = 0;
            $check_sponser = Sponsor::with('user')->where('sponsor_id', $item->user_id)->get();
            if (!$check_sponser->isEmpty()) {
                $check_sponser->map(function ($items) use (&$billing_amount) {
                    $billing_amount += (Wallet::where('user_id', $items->user_id)->sum('billing_amount'));
                    // dd($billing_amount);
                });
            }
            $item->sponsor_expenditure = $billing_amount;
            return $item;
        });


        // $monthlySales = $query->orderBy('id', 'desc')->simplePaginate(15);
        $monthlySales->appends($request->only(['search', 'month']));

        return view('admin.msr.index', compact('pos', 'monthlySales'));
    }


    public function exportMsr(Request $request)
    {
        $query = Wallet::select(
            'pos_id',
            'user_id',
            'mobilenumber',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
            DB::raw('SUM(billing_amount) as total_billing_amount')
        )
            ->whereNotNull('transaction_date')
            ->groupBy('user_id', 'pos_id', 'mobilenumber', 'transaction_month');

        if ($request->has('search') && !empty($request->search)) {
            $userId = $request->search;
            $query->where('pos_id', $userId);
        }

        if ($request->has('month') && !empty($request->month)) {
            $selectedMonth = $request->month;
            $query->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth]);
        }

        $filteredData = $query->get();

        return Excel::download(new MsrExport($filteredData), 'MonthlySalesReport.csv');
    }
}
