<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Domain\User\Services\CreateUserService;

class AuthController extends Controller
{
    /**
     * ログイン画面表示
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(config('fortify.home'));
        }

        throw ValidationException::withMessages([
            'email' => ['認証に失敗しました。'],
        ]);
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * 新規登録画面表示
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * 新規登録処理
     */
    public function register(Request $request, CreateUserService $createUserService): RedirectResponse
    {
        $user = $createUserService->create($request->all());

        // ユーザーを一時的にログイン状態にしてメール認証画面にアクセスできるようにする
        Auth::login($user);

        return redirect('/email/verify');
    }

    /**
     * パスワードリセット画面表示（メール送信フォーム）
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * パスワードリセットメール送信
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * メール認証確認画面表示
     */
    public function showVerificationNotice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * メール認証処理
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(config('fortify.home'));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        return redirect(config('fortify.home'))->with('verified', true);
    }

    /**
     * メール認証通知再送信
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect(config('fortify.home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
