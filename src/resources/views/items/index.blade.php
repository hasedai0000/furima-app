@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
  <div class="items__content">
    @if (Auth::check() && Auth::user()->hasVerifiedEmail())
      <div class="items__tabs">
        @php
          $searchParams = $searchTerm ? ['search' => $searchTerm] : [];
        @endphp

        <a href="{{ route('items.index', $searchParams) }}"
          class="items__tab {{ $currentTab === '' ? 'items__tab--active' : '' }}">
          おすすめ
        </a>
        <a href="{{ route('items.index', array_merge(['tab' => 'mylist'], $searchParams)) }}"
          class="items__tab {{ $currentTab === 'mylist' ? 'items__tab--active' : '' }}">
          マイリスト
        </a>
      </div>
    @endif

    @if ($searchTerm)
      <div class="search-results">
        @php
          $tabLabel = $currentTab === 'mylist' ? 'マイリスト' : 'おすすめ';
        @endphp
        <h2 class="search-results__title">「{{ $searchTerm }}」の{{ $tabLabel }}検索結果</h2>
        <p class="search-results__count">{{ count($items) }}件の商品が見つかりました</p>
        @if (count($items) === 0)
          <div class="search-results__empty">
            @if ($currentTab === 'mylist')
              <p>マイリストに該当する商品が見つかりませんでした。</p>
              <p>別のキーワードで検索するか、おすすめタブで検索してみてください。</p>
            @else
              <p>該当する商品が見つかりませんでした。</p>
              <p>別のキーワードで検索してみてください。</p>
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
