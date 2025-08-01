@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
  <div class="login-form__content">
    <div class="login-form__heading">
      <h2 class="login-form__heading-title">メール認証</h2>
    </div>
    <div class="form__group">
      <p>メールアドレスの認証が必要です。</p>
      <p>登録時に送信されたメールのリンクをクリックして認証を完了してください。</p>
    </div>
    <div class="form__button">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="form__button-submit" type="submit">認証メールを再送信</button>
      </form>
    </div>
    <div class="form__button">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="form__button-submit" type="submit">ログアウト</button>
      </form>
    </div>
  </div>
@endsection
