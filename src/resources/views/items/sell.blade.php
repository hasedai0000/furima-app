@extends('layouts.app')

@section('title', '商品の出品')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('content')
  <div class="auth-form__content">
    <div class="auth-form__heading">
      <h2 class="auth-form__heading-title">商品の出品</h2>
    </div>

    <form class="form" action="{{ route('items.store') }}" method="post" enctype="multipart/form-data">
      @csrf
      <!-- 商品画像 -->
      <div class="form__group">
        <div class="form__group-title">
          <h3 class="form__label--item">商品画像</h3>
        </div>
        <div class="form__group-content">
          <div class="item-image__container">
            <div class="item-image__preview">
              <input type="file" name="imgUrl" id="imageInput" accept="image/*" class="item-image__input">
              <label for="imageInput" class="item-image__button">画像を選択する</label>
              <img src="" class="item-image__current" id="preview" style="display: none;">
            </div>
          </div>
          <div class="form__error">
            @error('imgUrl')
              {{ $message }}
            @enderror
          </div>
        </div>
      </div>

      <!-- 商品の詳細 -->
      <div class="form__group">
        <div class="form__group-title">
          <h2 class="form__sub-title">商品の詳細</h2>
        </div>
        <div class="form__group-content">

          <!-- カテゴリー -->
          <div class="category-section">
            <div class="form__group-title">
              <h3 class="form__label--item">カテゴリー</h3>
            </div>
            <div class="category-buttons">
              @foreach ($categories as $category)
                <label class="category-button">
                  <input type="checkbox" name="category_ids[]" value="{{ $category['id'] }}"
                    class="category-button__input" {{ $category['name'] === 'インテリア' ? 'checked' : '' }}>
                  <span class="category-button__text">{{ $category['name'] }}</span>
                </label>
              @endforeach
            </div>
            <div class="form__error">
              @error('category_ids')
                {{ $message }}
              @enderror
            </div>
          </div>

          <!-- 商品の状態 -->
          <div class="condition-section">
            <div class="form__group-title">
              <h3 class="form__label--item">商品の状態</h3>
            </div>
            <select name="condition" class="condition-options">
              <option value="">選択してください</option>
              @foreach ($itemConditions as $value => $label)
                <option value="{{ $value }}" {{ $label === '良好' ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
            <div class="form__error">
              @error('condition')
                {{ $message }}
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- 商品名と説明 -->
      <div class="form__group">
        <div class="form__group-title">
          <h2 class="form__sub-title">商品名と説明</h2>
        </div>
        <div class="form__group-content">

          <!-- 商品名 -->
          <div class="form__input-group">
            <div class="form__group-title">
              <h3 class="form__label--item">商品名</h3>
            </div>
            <div class="form__input--text">
              <input type="text" name="name" value="{{ old('name') }}" />
            </div>
            <div class="form__error">
              @error('name')
                {{ $message }}
              @enderror
            </div>
          </div>

          <!-- ブランド名 -->
          <div class="form__input-group">
            <div class="form__group-title">
              <h3 class="form__label--item">ブランド名</h3>
            </div>
            <div class="form__input--text">
              <input type="text" name="brand_name" value="{{ old('brand_name') }}" />
            </div>
            <div class="form__error">
              @error('brand_name')
                {{ $message }}
              @enderror
            </div>
          </div>

          <!-- 商品の説明 -->
          <div class="form__input-group">
            <div class="form__group-title">
              <h3 class="form__label--item">商品の説明</h3>
            </div>
            <div class="form__input--textarea">
              <textarea name="description" rows="5">{{ old('description') }}</textarea>
            </div>
            <div class="form__error">
              @error('description')
                {{ $message }}
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- 販売価格 -->
      <div class="form__group">
        <div class="form__group-title">
          <h3 class="form__label--item">販売価格</h3>
        </div>
        <div class="form__group-content">
          <div class="price-input">
            <span class="price-input__symbol">¥</span>
            <div class="form__input--text">
              <input type="number" name="price" value="{{ old('price') }}" min="0" />
            </div>
          </div>
          <div class="form__error">
            @error('price')
              {{ $message }}
            @enderror
          </div>
        </div>
      </div>

      <!-- 出品ボタン -->
      <div class="form__button">
        <button class="form__button-submit" type="submit">出品する</button>
      </div>
    </form>
  </div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imageInput');
    const preview = document.getElementById('preview');
    const button = document.querySelector('.item-image__button');

    fileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          button.style.display = 'none';
        };
        reader.readAsDataURL(file);
      }
    });
  });
</script>
