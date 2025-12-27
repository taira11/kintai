<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Product;

class MyPageController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $tab     = $request->query('tab', 'selling');
        $keyword = $request->query('keyword');
        $page    = $request->query('page', 'sell');

        if ($tab === 'selling') {
            $items = Product::where('seller_id', $user->id)
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%");
            })
            ->get();
        } else {
            $items = Product::whereIn(
                'id',
                $user->purchases()->pluck('product_id')
                )
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                })
                ->get();
        }
            return view('mypage.index', compact('page', 'items', 'tab', 'keyword'));
    }

    public function edit()
    {
        $user    = Auth::user();
        $profile = $user->profile ?? null;
        return view('mypage.edit', compact('user', 'profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = $user->profile()->create([
                'nickname' => $request->nickname,
                'postal_code' => $request->postal_code,
                'address' => $request->address,
                'building' => $request->building,
                'profile_image' => null,
            ]);
        }

        $profile->nickname = $request->nickname;
        $profile->postal_code = $request->postal_code;
        $profile->address = $request->address;
        $profile->building = $request->building;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $profile->profile_image = $path;
        }

        $profile->save();
        return redirect('/mypage')->with('message', 'プロフィールを更新しました！');
    }
}
