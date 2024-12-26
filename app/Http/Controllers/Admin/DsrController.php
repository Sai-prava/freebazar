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

        $wallets = $query->with('user', 'getPos')->orderBy('id', 'desc')
            ->simplePaginate(15);
        // dd($wallets);
        $wallets->appends($request->only(['search', 'start_date', 'end_date']));

        return view('admin.dsr.index', compact('wallets'));
    }

    public function export(Request $request)
    {
        $startDate = $request->has('start_date') && !empty($request->start_date) ? $request->start_date : null;
        $endDate = $request->has('end_date') && !empty($request->end_date) ? $request->end_date : null;

        return Excel::download(new WalletExport($startDate, $endDate), 'daily_sales_report.csv', \Maatwebsite\Excel\Excel::CSV);
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

        $defaultMonth = Carbon::now()->subMonth()->format('Y-m'); // Previous month
        $selectedMonth = $request->input('month', $defaultMonth);

        $query = Wallet::select(
            'user_id',
            'mobilenumber',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
            DB::raw('SUM(billing_amount) as total_billing_amount')
        )
            ->whereNotNull(['transaction_date', 'user_id'])
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth])
            ->groupBy(['user_id', 'mobilenumber', DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")')]);

        if ($request->filled('search')) {
            $posId = $request->input('search');
            $query->whereHas('getPos', function ($q) use ($posId) {
                $q->where('id', $posId);
            });
        }
        if ($request->filled('filter')) {
            $mobile = $request->input('filter');
            $query->where('mobilenumber', 'like', "%{$mobile}%");
        }

        $monthlySales = $query->orderBy('id', 'desc')->simplePaginate(15)->through(function ($item) use ($request) {
            $billing_amount = 0;

            $check_sponsor = Sponsor::with('user')->where('sponsor_id', $item->user_id)->get();
            if (!$check_sponsor->isEmpty()) {
                $check_sponsor->each(function ($sponsor) use (&$billing_amount, &$item, $request) {
                    $month = $request->input('month', Carbon::now()->subMonth()->format('Y-m'));
                    $monthlyBilling = Wallet::select(
                        DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
                        DB::raw('SUM(billing_amount) as total_billing_amount')
                    )
                        ->where('user_id', $sponsor->user_id)
                        ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$month])
                        ->groupBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'))
                        ->get();
                    // dd($monthlyBilling );
                    foreach ($monthlyBilling as $monthData) {
                        $billing_amount += $monthData->total_billing_amount;
                    }
                });
                $billing_amount += $item->total_billing_amount;
            }
            // Log::info($monthlySales);
            $item->sponsor_expenditure = $billing_amount;
            return $item;
        });

        $monthlySales->appends($request->only(['search', 'month', 'filter']));

        return view('admin.msr.index', compact('pos', 'monthlySales', 'selectedMonth'));
    }
    public function exportMsr(Request $request)
    {
        $defaultMonth = Carbon::now()->subMonth()->format('Y-m'); // Default to the previous month
        $selectedMonth = $request->input('month', $defaultMonth);

        // Query data for the selected or default month
        $query = Wallet::select(
            'pos_id',
            'user_id',
            'mobilenumber',
            DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
            DB::raw('SUM(billing_amount) as total_billing_amount')
        )
            ->whereNotNull('transaction_date')
            ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth]) // Default or user-provided month
            ->groupBy('user_id', 'pos_id', 'mobilenumber', 'transaction_month');

        if ($request->filled('search')) {
            $userId = $request->search;
            $query->where('pos_id', $userId);
        }

        $filteredData = $query->get();

        // Add sponsor expenditure logic
        $dataWithSponsorExpenditure = $filteredData->map(function ($item) use ($selectedMonth) {
            $billing_amount = 0;

            $check_sponsor = Sponsor::with('user')->where('sponsor_id', $item->user_id)->get();
            if (!$check_sponsor->isEmpty()) {
                $check_sponsor->each(function ($sponsor) use (&$billing_amount, $selectedMonth) {
                    $monthlyBilling = Wallet::select(
                        DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as transaction_month'),
                        DB::raw('SUM(billing_amount) as total_billing_amount')
                    )
                        ->where('user_id', $sponsor->user_id)
                        ->whereRaw('DATE_FORMAT(transaction_date, "%Y-%m") = ?', [$selectedMonth])
                        ->groupBy(DB::raw('DATE_FORMAT(transaction_date, "%Y-%m")'))
                        ->get();

                    foreach ($monthlyBilling as $monthData) {
                        $billing_amount += $monthData->total_billing_amount;
                    }
                });

                $billing_amount += $item->total_billing_amount;
            }

            $item->sponsor_expenditure = $billing_amount;
            return $item;
        });

        // Export data for the selected or default month
        return Excel::download(new MsrExport($dataWithSponsorExpenditure), 'MonthlySalesReport.csv');
    }
}
