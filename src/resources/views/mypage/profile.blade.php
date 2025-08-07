@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/profile/profile.css') }}">
@endsection

@section('content')
  <div class="auth-form__content">
    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif
    <div class="profile-form__heading">
      <h2 class="auth-form__heading-title">プロフィール設定</h2>
    </div>
    @if ($profile)
      <form class="form" action="{{ route('mypage.profile.update') }}" method="post" enctype="multipart/form-data">
        @method('PUT')
        @csrf
      @else
        <form class="form" action="{{ route('mypage.profile.store') }}" method="post" enctype="multipart/form-data">
    @endif
    @csrf
    <div class="form__group">
      <div class="form__group-content">
        <div class="profile-image__container">
          <div class="profile-image__preview">
            <img src="{{ asset($profile ? $profile['imgUrl'] : '') }}" class="profile-image__current" id="preview">
          </div>
          <input type="file" name="imgUrl" id="imageInput" accept="image/*" class="profile-image__input">
          <label for="imageInput" class="profile-image__button">画像を選択する</label>
        </div>
        <div class="form__error">
          @error('imgUrl')
            {{ $message }}
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">ユーザー名</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="name" value="{{ $name }}" />
        </div>
        <div class="form__error">
          @error('name')
            {{ $message }}
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">郵便番号</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="postcode" value="{{ $profile ? $profile['postcode'] ?? '' : '' }}" />
        </div>
        <div class="form__error">
          @error('postcode')
            {{ $message }}
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">住所</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="address" value="{{ $profile ? $profile['address'] ?? '' : '' }}" />
        </div>
        <div class="form__error">
          @error('address')
            {{ $message }}
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">建物名</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="buildingName" value="{{ $profile ? $profile['buildingName'] ?? '' : '' }}" />
        </div>
      </div>
    </div>
    <div class="form__button">
      <button class="form__button-submit" type="submit">更新する</button>
    </div>
    </form>
  </div>
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imageInput');
    const preview = document.getElementById('preview');

    fileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  });
</script>
