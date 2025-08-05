<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

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
    Route::get('/', [AuthController::class, 'index'])->name('index');

    Route::get('/mypage/profile', [ProfileController::class, 'show'])->name('mypage.profile.show');
    Route::post('/mypage/profile', [ProfileController::class, 'store'])->name('mypage.profile.store');
});
