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
          <select class="purchase__select" name="payment_method" id="payment-method-select">
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

        <button type="button" class="purchase__button" id="purchase-button" {{ !$profile ? 'disabled' : '' }}
          style="{{ !$profile ? 'opacity: 0.5;' : '' }}">
          購入する
        </button>
      </div>
    </div>
  </div>

  <script>
    // 支払い方法の選択を注文サマリーに反映
    document.getElementById('payment-method-select').addEventListener('change', function() {
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
    document.getElementById('purchase-button').addEventListener('click', function(e) {
      e.preventDefault();

      const paymentMethod = document.getElementById('payment-method-select').value;
      if (!paymentMethod) {
        alert('支払い方法を選択してください');
        return;
      }

      // プロフィールが設定されているかチェック
      @if (!$profile)
        alert('住所が設定されていません。住所設定ページに移動します。');
        window.location.href = '{{ route('purchase.editAddress', ['item_id' => $item['id']]) }}';
        return;
      @endif

      // 購入確認
      if (!confirm('購入を確定しますか？')) {
        return;
      }

      // Stripe決済の場合はCheckoutページに遷移
      if (paymentMethod === 'credit_card') {
        this.disabled = true;
        this.textContent = 'Stripe決済ページに移動中...';
        window.location.href = '{{ route('purchase.stripe-checkout', ['item_id' => $item['id']]) }}';
      } else {
        // コンビニ支払いの場合はPOSTリクエストで送信
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('purchase.purchase', ['item_id' => $item['id']]) }}';

        // CSRFトークンを追加
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // 支払い方法を追加
        const paymentInput = document.createElement('input');
        paymentInput.type = 'hidden';
        paymentInput.name = 'payment_method';
        paymentInput.value = paymentMethod;
        form.appendChild(paymentInput);

        @if ($profile)
          // プロフィール情報を追加
          const postcodeInput = document.createElement('input');
          postcodeInput.type = 'hidden';
          postcodeInput.name = 'postcode';
          postcodeInput.value = '{{ $profile['postcode'] }}';
          form.appendChild(postcodeInput);

          const addressInput = document.createElement('input');
          addressInput.type = 'hidden';
          addressInput.name = 'address';
          addressInput.value = '{{ $profile['address'] }}';
          form.appendChild(addressInput);

          const buildingNameInput = document.createElement('input');
          buildingNameInput.type = 'hidden';
          buildingNameInput.name = 'buildingName';
          buildingNameInput.value = '{{ $profile['buildingName'] ?? '' }}';
          form.appendChild(buildingNameInput);
        @endif

        document.body.appendChild(form);
        form.submit();
      }
    });
  </script>
@endsection
