@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
  <div class="items__content">
    @if (Auth::check() && Auth::user()->hasVerifiedEmail())
      <div class="items__tabs">
        @php
          $currentTab = request('page', '');
          $searchParams = $searchTerm ? ['search' => $searchTerm] : [];
        @endphp

        <a href="{{ route('mypage.index', array_merge(['page' => 'sell'], $searchParams)) }}"
          class="items__tab {{ $currentTab === 'sell' ? 'items__tab--active' : '' }}">
          出品した商品
        </a>
        <a href="{{ route('mypage.index', array_merge(['page' => 'buy'], $searchParams)) }}"
          class="items__tab {{ $currentTab === 'buy' ? 'items__tab--active' : '' }}">
          購入した商品
        </a>
      </div>
    @endif

    @if ($searchTerm)
      <div class="search-results">
        @php
          $tabLabel = $currentTab === 'sell' ? '出品した商品' : '購入した商品';
        @endphp
        <h2 class="search-results__title">「{{ $searchTerm }}」の{{ $tabLabel }}検索結果</h2>
        <p class="search-results__count">{{ count($items) }}件の商品が見つかりました</p>
        @if (count($items) === 0)
          <div class="search-results__empty">
            @if ($currentTab === 'sell')
              <p>出品した商品はありません。</p>
              <p>別のキーワードで検索するか、出品した商品タブで検索してみてください。</p>
            @else
              <p>購入した商品はありません。</p>
              <p>別のキーワードで検索するか、購入した商品タブで検索してみてください。</p>
            @endif
          </div>
        @endif
      </div>
    @endif

    <div class="items__grid">
      @foreach ($items as $item)
        <a href="{{ route('items.detail', $item['id']) }}">
          <div class="item-card">
            <div class="item-card__image">
              <img src="{{ $item['imgUrl'] }}" alt="{{ $item['name'] }}" class="item-card__img">
              @if ($item['isSold'])
                <div class="item-card__sold">
                  <span class="sold-label">SOLD</span>
                </div>
              @endif
            </div>
            <div class="item-card__name">{{ $item['name'] }}</div>
            <div class="item-card__price">¥{{ number_format($item['price']) }}</div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endsection
