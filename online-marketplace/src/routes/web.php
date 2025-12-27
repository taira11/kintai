<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use Laravel\Fortify\Fortify;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [ItemController::class, 'index']);
Route::get('/item/{item_id}', [ItemController::class, 'show']);

Fortify::registerView(fn() => view('auth.register'));
Fortify::loginView(fn() => view('auth.login'));


Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/mypage/edit');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth'])->group(function () {
    Route::get('/mypage', [MyPageController::class, 'index'])
         ->middleware('verified')
         ->name('mypage.index');

    Route::get('/mypage/edit', [MyPageController::class, 'edit'])
         ->middleware('verified')
         ->name('mypage.edit');

    Route::post('/mypage/edit', [MyPageController::class, 'update'])
         ->middleware('auth')
         ->middleware('verified');

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])
        ->middleware('auth')
        ->name('purchase.index');

    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])
        ->middleware('auth');

    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address'])
         ->middleware('verified');

    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])
         ->middleware('verified');

    Route::get('/sell', [ItemController::class, 'create'])->middleware('verified');
    Route::post('/sell', [ItemController::class, 'store'])->middleware('verified');

    Route::post('/item/{product_id}/favorite', [FavoriteController::class, 'toggle'])
         ->middleware('auth')
         ->name('item.favorite');

    Route::get('/purchase/{item_id}/success',
    [PurchaseController::class, 'success'])
        ->name('purchase.success')
        ->middleware('auth');

    Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])
        ->middleware('auth');
});
