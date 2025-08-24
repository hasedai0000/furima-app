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
      <h2 class="auth-form__heading-title">配送先住所の変更</h2>
    </div>
    <form class="form" action="{{ route('profile.modifyAddress', ['item_id' => $itemId]) }}" method="post"
      enctype="multipart/form-data">
      @method('PUT')
      @csrf
      <div class="form__group">
        <div class="form__group-title">
          <span class="form__label--item">郵便番号</span>
        </div>
        <div class="form__group-content">
          <div class="form__input--text">
            <input type="text" name="postcode" value="{{ old('postcode', $profile['postcode'] ?? '') }}" />
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
            <input type="text" name="address" value="{{ old('address', $profile['address'] ?? '') }}" />
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
            <input type="text" name="buildingName" value="{{ old('buildingName', $profile['buildingName'] ?? '') }}" />
          </div>
        </div>
      </div>
      <div class="form__button">
        <button class="form__button-submit" type="submit">変更する</button>
      </div>
    </form>
  </div>
@endsection
