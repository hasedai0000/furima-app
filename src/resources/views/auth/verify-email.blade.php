@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
  <div class="verify-email__content">
    <h2 class="auth-form__heading-title">
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。</h2>
    <div class="form__button">
      <button class="verify-email__button-submit"><a href="http://localhost:8025">認証はこちら</a></button>
    </div>
    <div class="auth__link">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="auth__button-submit" type="submit">認証メールを再送信</button>
      </form>
    </div>
  </div>
@endsection
