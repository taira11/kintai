<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Transaction;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PurchaseController extends Controller
{
    public function index($item_id)
    {
        $item = Product::findOrFail($item_id);

        if ($item->seller_id === Auth::id()) {
            return redirect('/')
            ->with('error', '自分が出品した商品は購入できません');
        }

        $profile = Auth::user()->profile;
        $defaultAddress = $profile
        ? '〒' . $profile->postal_code . ' ' . $profile->address . ' ' . $profile->building
        : '';
        $shippingAddress = session("shipping_{$item_id}", $defaultAddress);

        return view('purchase.index', compact('item', 'shippingAddress'));
    }

    public function store(Request $request, $item_id)
    {
        $user = Auth::user();
        $item = Product::findOrFail($item_id);

        if ($item->seller_id === $user->id) {
            return redirect('/')
            ->with('error', '自分が出品した商品は購入できません');
        }

        $profile = $user->profile;
        if (!$profile) {
            return redirect('/mypage/edit')
            ->with('error', '購入前に住所を登録してください');
        }

        $shippingAddress = session("shipping_{$item_id}");

        if (!$shippingAddress) {
            $profile = $user->profile;
            if (!$profile) {
                return redirect('/mypage/edit')
                ->with('error', '購入前に住所を登録してください');
            }

            $shippingAddress =
            '〒' . $profile->postal_code . ' ' .
            $profile->address . ' ' .
            ($profile->building ?? '');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethods = $request->payment_method === 'card'
        ? ['card']
        : ['konbini'];

        $session = StripeSession::create([
            'payment_method_types' => $paymentMethods,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
                ]],
                'mode' => 'payment',

                'success_url' => route('purchase.success', $item->id),
                'cancel_url'  => route('purchase.index', $item->id),

                'metadata' => [
                    'product_id' => $item->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $item->seller_id,
                    'shipping_address' => $shippingAddress,
                ],
            ]);

        return redirect($session->url);
    }

    public function address($item_id)
    {
        $item           = Product::findOrFail($item_id);
        $currentAddress = session("shipping_{$item_id}");
        return view('purchase.address', compact('item', 'currentAddress'));
    }

    public function updateAddress(Request $request, $item_id)
    {
        $request->validate([
            'postal_code' => 'required',
            'address'     => 'required',
        ]);

        $shippingAddress =
        '〒' . $request->postal_code . ' ' .
        $request->address . ' ' .
        ($request->building ?? '');
        session(["shipping_{$item_id}" => $shippingAddress]);

        return redirect("/purchase/{$item_id}");
    }

    public function success($item_id)
    {
        $user = Auth::user();
        $item = Product::findOrFail($item_id);

        if ($item->status === Product::STATUS_SOLD) {
            return redirect('/');
        }

    $shippingAddress = session("shipping_{$item_id}");

    if (!$shippingAddress) {
        $profile = $user->profile;

        $shippingAddress =
            '〒' . $profile->postal_code . ' ' .
            $profile->address . ' ' .
            ($profile->building ?? '');
    }

    Transaction::create([
        'product_id'       => $item->id,
        'seller_id'        => $item->seller_id,
        'buyer_id'         => $user->id,
        'price'            => $item->price,
        'payment_method'   => 'stripe',
        'shipping_address' => $shippingAddress,
        'status'           => 1,
        'purchased_at'     => now(),
    ]);

    $item->update([
        'status' => Product::STATUS_SOLD,
    ]);

    return redirect('/')
        ->with('message', '購入が完了しました');
    }
}
