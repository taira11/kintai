<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;
use App\Models\Product;

class FavoriteController extends Controller
{
    public function toggle($product_id)
    {
        $userId = Auth::id();
        $favorite = Favorite::where('user_id', $userId)
                            ->where('product_id', $product_id)
                            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'favorited' => false,
                'count'     => Favorite::where('product_id', $product_id)->count(),
                ]
            );
        }

        Favorite::create([
            'user_id'   => $userId,
            'product_id'=> $product_id,
        ]);

        return response()->json([
            'favorited' => true,
            'count'     => Favorite::where('product_id', $product_id)->count(),
        ]);
    }
}
