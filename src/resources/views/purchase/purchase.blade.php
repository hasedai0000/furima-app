@extends('layouts.app')

@section('title', '購入手続き')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
  <div class="purchase">
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
            <option value="credit_card">クレジットカード</option>
            <option value="convenience_store">コンビニ払い</option>
            <option value="bank_transfer">銀行振込</option>
          </select>
        </div>

        <!-- 配送先 -->
        <div class="purchase__section">
          <div class="purchase__section-header">
            <h3 class="purchase__section-title">配送先</h3>
            <a href="#" class="purchase__change-link">変更する</a>
          </div>
          <div class="purchase__address">
            <div class="purchase__address-postal">〒 {{ $profile['postCode'] ?? 'XXX-YYYY' }}</div>
            <div class="purchase__address-detail">{{ $profile['address'] ?? 'ここには住所と建物が入ります' }}</div>
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

        <button class="purchase__button" id="purchase-button">購入する</button>
      </div>
    </div>
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

      // ここで購入処理を実行
      if (confirm('購入を確定しますか？')) {
        // 購入処理のAPI呼び出しなど
        console.log('購入処理を実行');
      }
    });
  </script>
@endsection
