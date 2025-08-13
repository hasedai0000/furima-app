@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
  <div class="item-detail">
    <div class="item-detail__container">
      <!-- 商品画像エリア -->
      <div class="item-detail__image-area">
        <div class="item-detail__image-placeholder">
          <img src="{{ $item['imgUrl'] }}" alt="{{ $item['name'] }}" class="item-detail__img">
          @if ($item['isSold'])
            <div class="item-detail__sold">
              <span class="sold-label">SOLD</span>
            </div>
          @endif
        </div>
      </div>

      <!-- 商品情報エリア -->
      <div class="item-detail__info-area">
        <!-- 商品タイトル・価格 -->
        <div class="item-detail__header">
          <h1 class="item-detail__title">{{ $item['name'] }}</h1>
          <div class="item-detail__price">¥{{ number_format($item['price']) }} (税込)</div>
        </div>

        <!-- エンゲージメント指標 -->
        <div class="item-detail__metrics">
          <div class="item-detail__metric">
            <div class="item-detail__metric-icon">★</div>
            <span class="item-detail__metric-count">{{ count($item['likes']) }}</span>
          </div>
          <div class="item-detail__metric">
            <div class="item-detail__metric-icon">💬</div>
            <span class="item-detail__metric-count">{{ count($item['comments']) }}</span>
          </div>
        </div>

        <!-- 購入ボタン -->
        <div class="item-detail__purchase">
          <button class="item-detail__purchase-button">購入手続きへ</button>
        </div>

        <!-- 商品説明 -->
        <div class="item-detail__section">
          <h2 class="item-detail__section-title">商品説明</h2>
          <div class="item-detail__description">
            <p>{{ $item['description'] }}</p>
          </div>
        </div>

        <!-- 商品の情報 -->
        <div class="item-detail__section">
          <h2 class="item-detail__section-title">商品の情報</h2>
          <div class="item-detail__info">
            <div class="item-detail__info-item">
              <span class="item-detail__info-label">カテゴリー</span>
              <div class="item-detail__tags">
                @if (isset($item['categories']) && count($item['categories']) > 0)
                  @foreach ($item['categories'] as $category)
                    <span class="item-detail__tag">{{ $category['name'] }}</span>
                  @endforeach
                @else
                  <span class="item-detail__tag">未分類</span>
                @endif
              </div>
            </div>
            <div class="item-detail__info-item">
              <span class="item-detail__info-label">商品の状態</span>
              <span class="item-detail__info-value">{{ $item['condition'] }}</span>
            </div>
          </div>
        </div>

        <!-- コメントセクション -->
        <div class="item-detail__section">
          <h2 class="item-detail__section-title">コメント({{ count($item['comments']) }})</h2>

          @if (isset($item['comments']) && count($item['comments']) > 0)
            @foreach ($item['comments'] as $comment)
              <div class="item-detail__comment">
                <div class="item-detail__comment-header">
                  <div class="item-detail__comment-avatar"></div>
                  <span class="item-detail__comment-author"></span>
                </div>
                <div class="item-detail__comment-content">
                  {{ $comment['content'] }}
                </div>
              </div>
            @endforeach
          @endif

          <!-- コメント投稿フォーム -->
          <div class="item-detail__comment-form">
            <h3 class="item-detail__comment-form-title">商品へのコメント</h3>
            <textarea class="item-detail__comment-input" placeholder="コメントを入力してください"></textarea>
            <button class="item-detail__comment-submit">コメントを送信する</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
