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
    <form class="form" action="{{ route('mypage.profile.store') }}" method="post" enctype="multipart/form-data">
      @csrf
      <div class="form__group">
        <div class="form__group-content">
          <div class="profile-image__container">
            <div class="profile-image__preview">
              @if (auth()->user()->profile && auth()->user()->profile->img_url)
                <img src="{{ asset('storage/' . auth()->user()->profile->img_url) }}" alt="プロフィール画像"
                  class="profile-image__current">
              @else
                <div class="profile-image__placeholder">
                  <span></span>
                </div>
              @endif
            </div>
            <div class="profile-image__upload">
              <input type="file" name="profile_image" id="profile_image" accept="image/*" class="profile-image__input">
              <label for="profile_image" class="profile-image__button">画像を選択する</label>
            </div>
          </div>
          <div class="form__error">
            @error('profile_image')
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
            <input type="text" name="name" value="{{ old('name') }}" />
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
            <input type="text" name="postcode" value="{{ old('postcode') }}" />
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
            <input type="text" name="address" value="{{ old('address') }}" />
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
            <input type="text" name="buildingName" value="{{ old('buildingName') }}" />
          </div>
        </div>
      </div>
      <div class="form__button">
        <button class="form__button-submit" type="submit">更新する</button>
      </div>
    </form>
  </div>
@endsection

@section('js')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const fileInput = document.getElementById('profile_image');
      const preview = document.querySelector('.profile-image__preview');

      fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="プレビュー画像" class="profile-image__current">`;
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endsection
