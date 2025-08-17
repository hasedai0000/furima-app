@extends('layouts.app')

@section('title', '購入手続き')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
  @if (session('success'))
    <div class="success-message">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="error-message">
      {{ session('error') }}
    </div>
  @endif

  <div class="purchase">
    <form action="{{ route('purchase.purchase', ['item_id' => $item['id']]) }}" method="post">
      @csrf
      @if ($profile)
        <input type="hidden" name="postcode" value="{{ $profile['postcode'] }}">
        <input type="hidden" name="address" value="{{ $profile['address'] }}">
        <input type="hidden" name="buildingName" value="{{ $profile['buildingName'] ?? '' }}">
      @endif
      <div class="purchase__container">
        <!-- 左側：商品情報・支払い方法・配送先 -->
        <div class="purchase__left">
          <!-- 商品情報 -->
          <div class="purchase__product">
            <div class="purchase__product-image">
              <img src="{{ $item['imgUrl'] }}" alt="{{ $item['name'] }}" class="purchase__img">
            </div>
            <div class="purchase__product-info">
              <h2 class="purchase__product-name">{{ $item['name'] }}</h2>
              <div class="purchase__product-price">¥{{ number_format($item['price']) }}</div>
            </div>
          </div>

          <!-- 支払い方法 -->
          <div class="purchase__section">
            <h3 class="purchase__section-title">支払い方法</h3>
            <select class="purchase__select" name="payment_method">
              <option value="">選択してください</option>
              @foreach ($paymentMethods as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <!-- 配送先 -->
          <div class="purchase__section">
            <div class="purchase__section-header">
              <h3 class="purchase__section-title">配送先</h3>
              <a href="{{ route('purchase.editAddress', ['item_id' => $item['id']]) }}"
                class="purchase__change-link">変更する</a>
            </div>
            <div class="purchase__address">
              @if ($profile)
                <div class="purchase__address-postal">〒{{ $profile['postcode'] }}</div>
                <div class="purchase__address-detail">
                  {{ $profile['address'] }}
                  @if ($profile['buildingName'])
                    <br>{{ $profile['buildingName'] }}
                  @endif
                </div>
              @else
                <div class="purchase__address-empty">住所が設定されていません<br><a
                    href="{{ route('purchase.editAddress', ['item_id' => $item['id']]) }}"
                    class="purchase__change-link">住所を設定する</a></div>
              @endif
            </div>
          </div>
        </div>

        <!-- 右側：注文サマリー・購入ボタン -->
        <div class="purchase__right">
          <div class="purchase__summary">
            <div class="purchase__summary-item">
              <span class="purchase__summary-label">商品代金</span>
              <span class="purchase__summary-value">¥{{ number_format($item['price']) }}</span>
            </div>
            <div class="purchase__summary-item">
              <span class="purchase__summary-label">支払い方法</span>
              <span class="purchase__summary-value" id="selected-payment">選択してください</span>
            </div>
          </div>

          <button class="purchase__button" id="purchase-button" {{ !$profile ? 'disabled' : '' }}
            style="{{ !$profile ? 'opacity: 0.5;' : '' }}">購入する</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    // 支払い方法の選択を注文サマリーに反映
    document.querySelector('.purchase__select').addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const selectedText = selectedOption.text;
      document.getElementById('selected-payment').textContent = selectedText;

      // 支払い方法が選択されているかチェック
      const purchaseButton = document.getElementById('purchase-button');
      if (this.value) {
        purchaseButton.disabled = false;
        purchaseButton.style.opacity = '1';
      } else {
        purchaseButton.disabled = true;
        purchaseButton.style.opacity = '0.5';
      }
    });

    // 購入ボタンのクリックイベント
    document.getElementById('purchase-button').addEventListener('click', function() {
      const paymentMethod = document.querySelector('.purchase__select').value;
      if (!paymentMethod) {
        alert('支払い方法を選択してください');
        return;
      }

      // プロフィールが設定されているかチェック
      const postcodeField = document.querySelector('input[name="postcode"]');
      if (!postcodeField) {
        alert('住所が設定されていません。住所設定ページに移動します。');
        window.location.href = '{{ route('purchase.editAddress', ['item_id' => $item['id']]) }}';
        return;
      }

      // ここで購入処理を実行
      if (confirm('購入を確定しますか？')) {
        // フォームを送信
        document.querySelector('form').submit();
      }
    });
  </script>
@endsection
