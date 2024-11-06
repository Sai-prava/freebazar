<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Casts\Json;

class CartController extends Controller
{
    public function cart()
    {
        //  dd(session()->get('product_id'));
        $userId = Auth::id();
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();
        $totalPrice = $cartItems->sum(fn($item) => $item->product->price);
        return view('frontend.cart', compact('cartItems','totalPrice'));
    }

    public function addToCart($id)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'You need to be logged in to add items to the cart.');
        }
        $product = Product::find($id);
        if ($product) {
            $cartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $id)
                ->first();

            if ($cartItem) {
                // $cartItem->quantity += 1;
                $cartItem->save();
                return redirect()->back()->with('success','Product Already Added');
            } else {
                $cartItem = new Cart;
                $cartItem->user_id = Auth::id();
                $cartItem->product_id = $id;
                $cartItem->status = 0;
                // $cartItem->quantity = 1;
                $cartItem->save();
            }

            return redirect()->route('cart.index')->with('success', 'Product Added Successfully');
        }
    }
    // public function increaseQuantity($id)
    // {
    //     $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();
    //     if ($cartItem) {
    //         $cartItem->quantity += 1;
    //         $cartItem->save();
    //     }
    //     return redirect()->back();
    // }

    // public function decreaseQuantity($id)
    // {
    //     $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();
    //     if ($cartItem && $cartItem->quantity > 1) {
    //         $cartItem->quantity -= 1;
    //         $cartItem->save();
    //     }
    //     return redirect()->back();
    // }

    public function delete($id)
    {
        $cartItem = Cart::find($id);
        if ($cartItem) {
            $cartItem->delete();
            return redirect()->back()->with('success', 'Product Deleted Successfully');
        }
        return redirect()->back()->with('error', 'Product not found');
    }
}
