@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
  @if (session('success'))
    <div class="success-message">
      {{ session('success') }}
    </div>
  @endif

  <div class="item-detail">
    <div class="item-detail__container">
      <!-- 商品画像エリア -->
      <div class="item-detail__image-area">
        <div class="item-detail__image-placeholder">
          <img src="{{ asset($item['imgUrl']) }}" alt="{{ $item['name'] }}" class="item-detail__img">
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
          <p class="item-detail__brand-name">{{ $item['brandName'] }}</p>
          <div class="item-detail__price">¥{{ number_format($item['price']) }} (税込)</div>
        </div>

        <!-- エンゲージメント指標 -->
        <div class="item-detail__metrics">
          <div class="item-detail__metric">
            @auth
              <form action="{{ route('items.like', ['item_id' => $item['id']]) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="item-detail__like-button">
                  @if (isset($item['isLiked']) && $item['isLiked'])
                    <div class="item-detail__metric-icon liked"><i class="fas fa-star"></i></div>
                  @else
                    <div class="item-detail__metric-icon"><i class="far fa-star"></i></div>
                  @endif
                  <span class="item-detail__metric-count">{{ count($item['likes']) }}</span>
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" class="item-detail__like-button">
                <div class="item-detail__metric-icon"><i class="far fa-heart"></i></div>
                <span class="item-detail__metric-count">{{ count($item['likes']) }}</span>
              </a>
            @endauth
          </div>
          <div class="item-detail__metric">
            <div class="item-detail__metric-icon"><i class="fas fa-comment"></i></div>
            <span class="item-detail__metric-count">{{ count($item['comments']) }}</span>
          </div>
        </div>

        <!-- 購入ボタン -->
        <div class="item-detail__purchase">
          @if ($item['isSold'])
            <button class="item-detail__purchase-button" disabled>購入手続きへ</button>
          @else
            <a href="{{ route('purchase.procedure', ['item_id' => $item['id']]) }}">
              <button class="item-detail__purchase-button">購入手続きへ</button>
            </a>
          @endif
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
                  <div class="item-detail__comment-avatar">
                    <img src="{{ asset($comment['user']['profile_img_url']) }}" alt="{{ $comment['user']['name'] }}">
                  </div>
                  <span class="item-detail__comment-author">{{ $comment['user']['name'] ?? '匿名ユーザー' }}</span>
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
            <form action="{{ route('items.comment', ['item_id' => $item['id']]) }}" method="POST">
              @csrf
              <textarea name="content" class="item-detail__comment-input" placeholder="コメントを入力してください"></textarea>
              @error('content')
                <div class="error-message">{{ $message }}</div>
              @enderror
              <button type="submit" class="item-detail__comment-submit">コメントを送信する</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
