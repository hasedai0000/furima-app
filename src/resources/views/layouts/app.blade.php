<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title')</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <a class="header__logo" href="/">
        <img src="{{ asset('images/Free Market App Logo.svg') }}" alt="CorpTech フリマ">
      </a>
      <div class="header__search">
        <input type="text" class="header__search-input" placeholder="なにをお探しですか?">
      </div>
      <nav>
        <ul class="header-nav">
          @if (Auth::check() && Auth::user()->hasVerifiedEmail())
            <li class="header-nav__item">
              <a class="header-nav__link" href="/mypage/profile">マイページ</a>
            </li>
            <li class="header-nav__item">
              <form class="form" action="/logout" method="post">
                @csrf
                <button class="header-nav__button">ログアウト</button>
              </form>
            </li>
            <li class="header-nav__item">
              <a class="header-nav__button--primary" href="/items/create">出品</a>
            </li>
          @else
            <li class="header-nav__item">
              <a class="header-nav__link" href="/login">ログイン</a>
            </li>
            <li class="header-nav__item">
              <a class="header-nav__link" href="/register">新規登録</a>
            </li>
          @endif
        </ul>
      </nav>
    </div>
  </header>

  <main>
    @yield('content')
  </main>
</body>

</html>
