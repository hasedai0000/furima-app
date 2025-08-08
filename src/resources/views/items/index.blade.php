@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
  <div class="items__content">
    @if (Auth::check() && Auth::user()->hasVerifiedEmail())
      <div class="items__tabs">
        <a href="/items" class="items__tab items__tab--active">おすすめ</a>
        <a href="/items?tab=mylist" class="items__tab">マイリスト</a>
      </div>
    @endif

    <div class="items__grid">
      @foreach ($items as $item)
        <div class="item-card">
          <div class="item-card__image">
            <img src="{{ $item['imgUrl'] }}" alt="{{ $item['name'] }}" class="item-card__img">
          </div>
          <div class="item-card__name">{{ $item['name'] }}</div>
          <div class="item-card__price">¥{{ number_format($item['price']) }}</div>
        </div>
      @endforeach
    </div>
  </div>
@endsection
