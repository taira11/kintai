<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Auth;


class ItemController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $tab = $request->query('tab');
        $items = Product::with('transaction')

        ->when(auth()->check(), function ($query) {
            $query->where('seller_id', '!=', auth()->id());
        })

        ->when($tab === 'mylist' && auth()->check(), function ($query) {
            $query->whereHas('favorites', function ($q) {
                $q->where('user_id', auth()->id());
            });
        })

        ->when($tab === 'mylist' && !auth()->check(), function ($query) {
            $query->whereRaw('1 = 0');
        })

        ->when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        })

        ->latest()
        ->get();

        return view('items.index', compact('items', 'keyword', 'tab'));
    }

    public function show($item_id)
    {
        $item = Product::with('categories')
        ->withCount('favorites')
        ->findOrFail($item_id);

        $comments = $item->comments()->with('user')->get();

        return view('items.show', compact('item', 'comments'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('items.create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $item = Product::create([
            'seller_id'   => Auth::id(),
            'name'        => $request->name,
            'brand'       => $request->brand,
            'description' => $request->description,
            'price'       => $request->price,
            'status'      => $request->status,
            'image'       => $imagePath,
        ]);

        $categoryIds = explode(',', $request->category_ids);
        $item->categories()->sync($categoryIds);

        return redirect('/mypage')->with('message', '商品を出品しました！');
    }
}
