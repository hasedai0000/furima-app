<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TransactionController;
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

// 認証関連ルート（ゲスト用）
Route::middleware('guest')->group(function () {
    // ログイン
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // 新規登録
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// 認証関連ルート（認証済みユーザー用）
Route::middleware('auth')->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // メール認証
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

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
    // Stripe Checkout関連
    Route::get('/purchase/{item_id}/stripe-checkout', [PurchaseController::class, 'stripeCheckout'])->name('purchase.stripe-checkout');
    Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'checkoutSuccess'])->name('purchase.success');

    // 取引チャット関連
    Route::get('/transactions/{transaction_id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction_id}/messages', [TransactionController::class, 'sendMessage'])->name('transactions.sendMessage');
    Route::put('/transactions/{transaction_id}/messages/{message_id}', [TransactionController::class, 'updateMessage'])->name('transactions.updateMessage');
    Route::delete('/transactions/{transaction_id}/messages/{message_id}', [TransactionController::class, 'deleteMessage'])->name('transactions.deleteMessage');
    Route::post('/transactions/{transaction_id}/complete', [TransactionController::class, 'complete'])->name('transactions.complete');
    Route::post('/transactions/{transaction_id}/ratings', [TransactionController::class, 'submitRating'])->name('transactions.submitRating');
});

// Item関連ルート（認証不要）
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/{item_id}', [ItemController::class, 'detail'])->name('items.detail');
