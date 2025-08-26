<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AuthController extends Controller
{
    public function showVerificationNotice(): View
    {
        return view('auth.verify-email');
    }
}
