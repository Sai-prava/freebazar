<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Carbon\Carbon;
use App\Models\PosModel;
use App\Models\Product;
use App\Models\sponcer;
use App\Models\Sponsor;
use App\Models\User;
use App\Models\UserPayment;
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_profile = auth()->user();
        $userId = $user_profile->id;
        // dd($userId);
        $sponsors = Sponsor::where('sponsor_id', $userId)->get();
        // dd($sponsors);
        $transactionQuery = Wallet::where('user_id', $userId);
        // dd($transactionQuery);
        //     $monthlyPurchases = Wallet::where('user_id', $userId)
        //     ->select(
        //         DB::raw('SUM(billing_amount) as total_billing'),
        //         DB::raw('MONTH(transaction_date) as month')
        //     )
        //     ->groupBy(DB::raw('MONTH(transaction_date)'))
        //     ->get();

        //    dd($monthlyPurchases);

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $monthlyPurchase = Wallet::where('user_id', $userId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('billing_amount');

        if ($request->has('from_date') && $request->has('to_date')) {
            $transactionQuery->whereBetween('transaction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $transactionQuery->where('transaction_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $transactionQuery->where('transaction_date', '<=', $request->to_date);
        }
        $walletList = $transactionQuery->orderBy('id', 'desc')->get();

        $userWallet = UserWallet::where('user_id', $userId)->get();
        $totalUsedAmount = UserWallet::where('user_id', $userId)->sum('used_amount');

        if ($userWallet) {
            $walletBalance = $userWallet->sum('wallet_amount') - $totalUsedAmount;
        } else {
            $walletBalance = 0;
        }

        $walletBalance = max($walletBalance, 0);

        return view('frontend.dashboard.index', compact('sponsors', 'user_profile', 'monthlyPurchase', 'walletBalance', 'walletList'));
    }

    public function payment(Request $request)
    {
        $userId = Auth::user()->id;
        // dd($userId);
        $posId = intval($request->input('pos_id'));
        $pos = PosModel::find($posId);
        // dd($pos);
        $randomNumber = mt_rand(1, 99999);
        $invoiceNumber = $randomNumber + 1;
        $invoice = 'S' . str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT);
        $amount = $request->billing_amount;
        $pay_by = $request->pay_by;
        $mobilenumber = $request->mobilenumber;
        $transaction_date = $request->transaction_date ?? now();

        $transaction_charge = $pos ? $pos->transaction_charge : 0;
        $transaction_amount = $amount * ($transaction_charge / 100);

        $walletUsedAmount = 0;

        if ($pay_by == 'wallet') {
            $userWallet = UserWallet::where('user_id', $userId)->get();
            // dd($userWallet);
            $walletBalance = $userWallet ? $userWallet->sum('wallet_amount') - UserWallet::where('user_id', $userId)->sum('used_amount') : 0;
            // dd($walletBalance);
            if ($walletBalance <= 0) {
                return redirect()->back()->with('error', 'Wallet is empty. Payment cannot be processed.');
            }

            $walletUsedAmount = min($amount, $walletBalance);
            $remainingAmount = $amount - $walletUsedAmount;

            $userWalletEntry = new UserWallet();
            $userWalletEntry->invoice = $invoice;
            $userWalletEntry->used_amount = $walletUsedAmount;
            $userWalletEntry->user_id = $userId;
            $userWalletEntry->mobilenumber = $mobilenumber;
            $userWalletEntry->transaction_date = $transaction_date;
            $userWalletEntry->pay_by = 'wallet';
            $userWalletEntry->trans_type = 'debit';
            $userWalletEntry->pos_id = $pos->id ?? null;
            $userWalletEntry->save();

            $walletEntry = new Wallet();
            $walletEntry->user_id = $userId;
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
                $walletEntry->pay_by = $request->alt_pay_by ?? 'cash';
                $walletEntry->tran_type = 'credit';
                $walletEntry->amount = $remainingAmount;
            }

            $walletEntry->pos_id = $pos->id ?? null;
            $walletEntry->insert_date = now();
            $walletEntry->save();
        } else {
            $remainingAmount = $amount;
        }

        if ($pay_by == 'cash' || $pay_by == 'upi') {
            $payment = new Wallet();
            $payment->user_id = $userId;
            $payment->pos_id = $pos->id ?? null;
            $payment->invoice = $invoice;
            $payment->pay_by = $pay_by;
            $payment->billing_amount = $amount;
            $payment->amount = $remainingAmount;
            $payment->mobilenumber = $mobilenumber;
            $payment->tran_type = 'credit';
            $payment->transaction_date = $transaction_date;
            $payment->transaction_amount = $transaction_amount;
            $payment->insert_date = now();
            $payment->save();
        }

        return redirect()->back()->with('success', 'Payment successfully.');
    }

    public function addUser()
    {
        $user_id = auth()->user()->user_id;
        $states = User::select('state')->where('role', 3)->whereNotNull('state')->where('state', '!=', '')->distinct()->pluck('state');
        $relation_user = User::select('relation_user')->where('role', 3)->whereNotNull('relation_user')->where('relation_user', '!=', '')->distinct()->pluck('relation_user');
        // dd($relation_user);
        return view('frontend.dashboard.adduser', compact('user_id', 'states', 'relation_user'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'mobilenumber' => 'required|unique:users,mobilenumber|regex:/^[0-9]{10}$/',
        ]);
        $user_add = new User;
        $user_add->name = $request->name;
        $user_add->user_id = mt_rand(1000000, 9999999);
        $user_add->email = $request->email;
        $user_add->password = Hash::make('123456');
        $user_add->mobilenumber = $request->mobilenumber;
        $user_add->gender = $request->gender;
        $user_add->sponsor_id = $request->sponsor_id;
        $user_add->address = $request->address;
        $user_add->city = $request->city;
        $user_add->state = $request->state;
        $user_add->zip = $request->zip;
        $user_add->pan_no = $request->pan_no;
        $user_add->ifsc = $request->ifsc;
        $user_add->account_no = $request->account_no;
        $user_add->nominee_name = $request->nominee_name;
        $user_add->bank = $request->bank;
        $user_add->relation_user = $request->relation_user;
        $user_add->role = 3;
        $user_add->assignRole([$user_add->role]);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('images'), $imageName);
            $user_add->image = $imageName;
        }

        if ($user_add->save()) {
            $sponcer = new Sponsor();
            $sponcer->user_id = $user_add->id;
            $sponcer->sponsor_id = $request->hidden_user_id;

            if ($sponcer->save()) {
                flash()->addSuccess('User registered successfully.');
                return redirect()->route('user.index');
            }
        }
        flash()->addError('Whoops! User or Sponsor creation failed!');
        return redirect()->back();
    }
    public function wallet()
    {
        $user_profile = auth()->user();
        $userId = $user_profile->id;
        $userWallet = UserWallet::where('user_id', $userId)->orderBy('id','desc')->simplePaginate(12);
        $totalUsedAmount = UserWallet::where('user_id', $userId)->sum('used_amount');

        if ($userWallet) {
            $walletBalance = $userWallet->sum('wallet_amount') - $totalUsedAmount;
        } else {
            $walletBalance = 0;
        }

        $walletBalance = max($walletBalance, 0);
        return view('frontend.dashboard.wallet', compact('userWallet', 'walletBalance'));
    }
    public function termCondition()
    {
        return view('frontend.dashboard.term_condition');
    }
    public function sponsorList()
    {
        $userId = auth()->user()->id;
        $sponcer = Sponsor::where('sponsor_id', $userId)->get();
        // dd($sponcer);
        return view('frontend.dashboard.sponser_list', compact('sponcer'));
    }
    public function posList(Request $request)
    {
        $query = PosModel::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('city', 'LIKE', "%{$searchTerm}%")
                ->orWhere('zip', 'LIKE', "%{$searchTerm}%");
        }

        $pos = $query->orderBy('id', 'desc')->simplePaginate(15);

        return view('frontend.dashboard.pos_list', compact('pos'));
    }


    public function editprofile()
    {
        $user_profile = auth()->user();
        $states = User::select('state')->where('role', 3)->whereNotNull('state')->distinct()->pluck('state');
        return view('frontend.dashboard.editprofile', compact('user_profile', 'states'));
    }
    public function updateprofile(Request $request)
    {
       
        $update_profile = User::find(auth()->id());

     
        if ($request->mobilenumber !== $update_profile->mobilenumber) {
            $update_profile->requestMobileNumberUpdate($request->mobilenumber);

            flash()->addSuccess('Your mobile number update request has been submitted and is awaiting admin approval.');
            return redirect()->route('user.index');
        }

        $update_profile->email = $request->email;
        $update_profile->address = $request->address;
        $update_profile->city = $request->city;
        $update_profile->state = $request->state;
        $update_profile->zip = $request->zip;
        $update_profile->nominee_name = $request->nominee_name;
        $update_profile->relation_user = $request->relation_user;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('images'), $imageName);
            $update_profile->image = $imageName;
        }
        if ($update_profile->save()) {
            flash()->addSuccess('Profile Update successfully.');
            return redirect()->route('user.index');
        }
    }
    public function changepassword()
    {
        return view('frontend.dashboard.changepassword');
    }

    public function newPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
        $newpass = Auth::user();
        $newpass->password = Hash::make($request->password);
        if ($newpass->save()) {
            flash()->addSuccess('Your password has been successfully updated.');
            return redirect()->route('user.index');
        }
    }
    // public function cart()
    // {
    //     $userId = Auth::id();
    //     $cartItems = Cart::with('product')->where('user_id', $userId)->get();
    //     $totalPrice = $cartItems->sum(fn($item) => $item->product->price);
    //     return view('frontend.dashboard.cart', compact('cartItems', 'totalPrice'));
    // }
}
