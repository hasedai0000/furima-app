<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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
    Route::get('/mypage/profile', [ProfileController::class, 'show'])->name('mypage.profile.show');
    Route::post('/mypage/profile', [ProfileController::class, 'store'])->name('mypage.profile.store');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    // 商品詳細にコメントを投稿する
    Route::post('/items/{item_id}/comments', [ItemController::class, 'comment'])->name('items.comment');
    // 商品詳細にいいねをする(マイリストに追加する)
    Route::post('/items/{item_id}/likes', [ItemController::class, 'like'])->name('items.like');

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'purchase'])->name('purchase.purchase');
});

// Item関連ルート
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/{item_id}', [ItemController::class, 'detail'])->name('items.detail');
