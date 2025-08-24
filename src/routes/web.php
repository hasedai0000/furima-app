<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth', 'verified'])->group(function () {
    // プロフィール関連
    Route::get('/mypage', [ProfileController::class, 'index'])->name('mypage.index');
    Route::get('/mypage/profile', [ProfileController::class, 'show'])->name('mypage.profile.show');
    Route::post('/mypage/profile', [ProfileController::class, 'store'])->name('mypage.profile.store');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
    Route::put('/purchase/address/{item_id}', [ProfileController::class, 'modifyAddress'])->name('profile.modifyAddress');

    // 商品関連
    Route::post('/items/{item_id}/comments', [ItemController::class, 'comment'])->name('items.comment');
    Route::post('/items/{item_id}/likes', [ItemController::class, 'like'])->name('items.like');

    // 出品関連
    Route::get('/sell', [ItemController::class, 'sell'])->name('items.sell');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    //　購入関連
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'procedure'])->name('purchase.procedure');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.editAddress');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.purchase');
});

// Item関連ルート
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/{item_id}', [ItemController::class, 'detail'])->name('items.detail');
