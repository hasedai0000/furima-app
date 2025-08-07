@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
  <div class="items__content">
    <div class="items__tabs">
      <a href="/items" class="items__tab">おすすめ</a>
      <a href="/items/mylist" class="items__tab items__tab--active">マイリスト</a>
    </div>

    <div class="items__grid">
      <div class="item-card">
        <div class="item-card__image">
          <span class="item-card__placeholder">商品画像</span>
        </div>
        <div class="item-card__name">商品名</div>
      </div>

      <div class="item-card">
        <div class="item-card__image">
          <span class="item-card__placeholder">商品画像</span>
        </div>
        <div class="item-card__name">商品名</div>
      </div>

      <div class="item-card">
        <div class="item-card__image">
          <span class="item-card__placeholder">商品画像</span>
        </div>
        <div class="item-card__name">商品名</div>
      </div>
    </div>
  </div>
@endsection
