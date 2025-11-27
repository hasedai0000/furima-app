@extends('layouts.app')

@section('css')
  <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('content')
  @if (session('success'))
    <div class="message success-message">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="message error-message">
      {{ session('error') }}
    </div>
  @endif

  <div class="chat-container">
    <!-- サイドバー：取引一覧 -->
    <div class="chat-sidebar">
      <h2 class="chat-sidebar__title">その他の取引</h2>
      <div class="chat-sidebar__list">
        @foreach ($transactions as $txData)
          <a href="{{ route('transactions.show', ['transaction_id' => $txData['transaction']['id']]) }}"
            class="chat-sidebar__item {{ $txData['transaction']['id'] === $transaction['id'] ? 'chat-sidebar__item--active' : '' }}">
            <div class="chat-sidebar__item-image">
              <img src="{{ asset($txData['item']['imgUrl']) }}" alt="{{ $txData['item']['name'] }}">
            </div>
            <div class="chat-sidebar__item-info">
              <div class="chat-sidebar__item-name">{{ $txData['item']['name'] }}</div>
              <div class="chat-sidebar__item-price">¥{{ number_format($txData['item']['price']) }}</div>
            </div>
          </a>
        @endforeach
      </div>
    </div>

    <!-- メインエリア：チャット -->
    <div class="chat-main">
      <!-- タイトル -->
      <div class="chat-title">
        <h1 class="chat-title__text">「{{ $otherUser['name'] }}」さんとの取引画面</h1>
        @if ($isBuyer && $transaction['status'] === 'active')
          <div class="chat-title__actions">
            <form action="{{ route('transactions.complete', ['transaction_id' => $transaction['id']]) }}" method="POST">
              @csrf
              <button type="submit" class="chat-title__complete-btn">取引を完了する</button>
            </form>
          </div>
        @endif
      </div>

      <!-- 商品情報エリア -->
      <div class="chat-item-info">
        <div class="chat-item-info__image">
          <img src="{{ asset($item['imgUrl']) }}" alt="{{ $item['name'] }}" class="chat-item-info__img">
        </div>
        <div class="chat-item-info__details">
          <div class="chat-item-info__name">{{ $item['name'] }}</div>
          <div class="chat-item-info__price">¥{{ number_format($item['price']) }}</div>
        </div>
      </div>

      <!-- メッセージ一覧 -->
      <div class="chat-messages" id="chat-messages">
        @foreach ($messages as $message)
          @php
            $isOwnMessage = $message['userId'] === Auth::id();
            $messageUser = \App\Models\User::find($message['userId']);
            $userProfile = $messageUser->profile;
          @endphp
          <div class="chat-message {{ $isOwnMessage ? 'chat-message--own' : 'chat-message--other' }}">
            <div class="chat-message__header">
              @if (!$isOwnMessage)
                <div class="chat-message__avatar">
                  @if ($userProfile && $userProfile->img_url)
                    <img src="{{ asset($userProfile->img_url) }}" alt="{{ $messageUser->name }}">
                  @else
                    <div class="chat-message__avatar-placeholder">
                      <span>画像なし</span>
                    </div>
                  @endif
                </div>
              @endif
              <span class="chat-message__author">{{ $messageUser->name }}</span>
              @if ($isOwnMessage)
                <div class="chat-message__avatar">
                  @if ($userProfile && $userProfile->img_url)
                    <img src="{{ asset($userProfile->img_url) }}" alt="{{ $messageUser->name }}">
                  @else
                    <div class="chat-message__avatar-placeholder">
                      <span>画像なし</span>
                    </div>
                  @endif
                </div>
              @endif
            </div>
            <div class="chat-message__content">
              @if ($message['content'])
                <div class="chat-message__text" id="message-text-{{ $message['id'] }}">{{ $message['content'] }}</div>
                @if ($isOwnMessage)
                  <div class="chat-message__edit-form" id="edit-form-{{ $message['id'] }}" style="display: none;">
                    <form
                      action="{{ route('transactions.updateMessage', ['transaction_id' => $transaction['id'], 'message_id' => $message['id']]) }}"
                      method="POST" class="chat-message__edit-form-inner">
                      @csrf
                      @method('PUT')
                      <textarea name="content" class="chat-message__edit-textarea" maxlength="400">{{ $message['content'] }}</textarea>
                      <div class="chat-message__edit-actions">
                        <button type="submit" class="chat-message__edit-submit">更新</button>
                        <button type="button" class="chat-message__edit-cancel"
                          onclick="cancelEdit('{{ $message['id'] }}')">キャンセル</button>
                      </div>
                    </form>
                  </div>
                @endif
              @endif
              @php
                $messageModel = \App\Models\Message::find($message['id']);
                $messageImages = $messageModel ? $messageModel->images : [];
              @endphp
              @if (count($messageImages) > 0)
                <div class="chat-message__images">
                  @foreach ($messageImages as $image)
                    <img src="{{ asset($image->image_url) }}" alt="メッセージ画像" class="chat-message__image">
                  @endforeach
                </div>
              @endif
            </div>
            @if ($isOwnMessage)
              <div class="chat-message__actions">
                <a href="#" class="chat-message__action" onclick="editMessage('{{ $message['id'] }}')">編集</a>
                <a href="#" class="chat-message__action" onclick="deleteMessage('{{ $message['id'] }}')">削除</a>
              </div>
            @endif
          </div>
        @endforeach
      </div>

      <!-- メッセージ送信フォーム -->
      <div class="chat-form">
        <form action="{{ route('transactions.sendMessage', ['transaction_id' => $transaction['id']]) }}" method="POST"
          enctype="multipart/form-data" id="message-form">
          @csrf
          <div class="chat-form__errors">
            @error('content')
              <div class="error-message">{{ $message }}</div>
            @enderror
            @error('images.*')
              <div class="error-message">{{ $message }}</div>
            @enderror
          </div>
          <div class="chat-form__input-row">
            <div class="chat-form__input-area">
              <textarea name="content" id="message-content" class="chat-form__textarea" placeholder="取引メッセージを記入してください">{{ old('content') }}</textarea>
            </div>
            <div class="chat-form__image-area">
              <label for="message-images" class="chat-form__image-label">
                画像を追加
              </label>
              <input type="file" name="images[]" id="message-images" multiple accept="image/jpeg,image/png"
                class="chat-form__image-input">
            </div>
            <button type="submit" class="chat-form__submit">
              <img src="{{ asset('images/Input Button.svg') }}" alt="送信" class="chat-form__submit-icon">
            </button>
          </div>
          <div class="chat-form__image-preview" id="image-preview"></div>
        </form>
      </div>
    </div>
  </div>

  <!-- 評価モーダル -->
  @if ($showRatingModal)
    <div class="rating-modal" id="rating-modal">
      <div class="rating-modal__overlay" onclick="closeRatingModal()"></div>
      <div class="rating-modal__content">
        <h2 class="rating-modal__title">取引が完了しました。</h2>
        <div class="rating-modal__divider"></div>
        <p class="rating-modal__question">今回の取引相手はどうでしたか？</p>
        <form action="{{ route('transactions.submitRating', ['transaction_id' => $transaction['id']]) }}"
          method="POST" class="rating-modal__form">
          @csrf
          <input type="hidden" name="rated_id" value="{{ $otherUser['id'] }}">
          <div class="rating-modal__stars-container">
            <div class="rating-modal__stars">
              @for ($i = 1; $i <= 5; $i++)
                <input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}"
                  {{ $i === 3 ? 'checked' : '' }}>
                <label for="rating-{{ $i }}" class="rating-modal__star">
                  <svg width="100" height="100" viewBox="0 0 100 100" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M50 0L61.8 35.2L100 40.5L72.5 66.8L79.4 105L50 85.5L20.6 105L27.5 66.8L0 40.5L38.2 35.2L50 0Z"
                      fill="currentColor" />
                  </svg>
                </label>
              @endfor
            </div>
          </div>
          <div class="rating-modal__divider"></div>
          <div class="rating-modal__actions">
            <button type="submit" class="rating-modal__submit">送信する</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <script>
    // メッセージの自動非表示（3秒後にフェードアウト）
    document.addEventListener('DOMContentLoaded', function() {
      const successMessages = document.querySelectorAll('.success-message');
      const errorMessages = document.querySelectorAll('.error-message');

      function hideMessage(message) {
        message.style.transition = 'opacity 0.5s ease-out';
        message.style.opacity = '0';
        setTimeout(function() {
          message.style.display = 'none';
        }, 500);
      }

      successMessages.forEach(function(message) {
        setTimeout(function() {
          hideMessage(message);
        }, 2000);
      });

      errorMessages.forEach(function(message) {
        setTimeout(function() {
          hideMessage(message);
        }, 2000);
      });
    });

    // FN009: 入力情報保持機能
    const transactionId = '{{ $transaction['id'] }}';
    const storageKey = 'chat_content_' + transactionId;
    const messageContent = document.getElementById('message-content');

    // ページ読み込み時に保存された内容を復元
    // ただし、サーバー側からold('content')で値が返されている場合はそれを優先
    document.addEventListener('DOMContentLoaded', function() {
      const serverContent = messageContent ? messageContent.value : '';
      if (!serverContent || serverContent.trim() === '') {
        // サーバー側の値が空の場合（送信成功時または初回アクセス時）
        // 送信成功時はlocalStorageをクリア済み（フォーム送信時にクリア）
        // 他の画面から戻ってきた場合はlocalStorageから復元
        const savedContent = localStorage.getItem(storageKey);
        if (savedContent && messageContent) {
          messageContent.value = savedContent;
        }
      } else {
        // バリデーションエラーの場合：サーバー側の値をlocalStorageにも保存（次回の遷移時に使用）
        if (messageContent) {
          localStorage.setItem(storageKey, serverContent);
        }
      }
    });

    // テキストエリアの内容が変更されたときにlocalStorageに保存
    if (messageContent) {
      messageContent.addEventListener('input', function() {
        localStorage.setItem(storageKey, this.value);
      });
    }

    // メッセージ送信時にlocalStorageをクリア
    // バリデーションエラーの場合はold('content')で値が返されるため、再度保存される
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
      messageForm.addEventListener('submit', function(e) {
        // フォーム送信時にlocalStorageをクリア
        // 送信成功時はクリアされたまま、バリデーションエラー時はold('content')で値が返され、再度保存される
        localStorage.removeItem(storageKey);
      });
    }

    // 画像プレビュー
    document.getElementById('message-images').addEventListener('change', function(e) {
      const preview = document.getElementById('image-preview');
      preview.innerHTML = '';
      const files = e.target.files;

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.match('image.*')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'chat-form__preview-image';
            preview.appendChild(img);
          };
          reader.readAsDataURL(file);
        }
      }
    });

    // メッセージ編集
    function editMessage(messageId) {
      const textElement = document.getElementById('message-text-' + messageId);
      const editForm = document.getElementById('edit-form-' + messageId);

      if (textElement && editForm) {
        textElement.style.display = 'none';
        editForm.style.display = 'block';

        // テキストエリアにフォーカス
        const textarea = editForm.querySelector('textarea');
        if (textarea) {
          textarea.focus();
          textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }
      }
    }

    // 編集キャンセル
    function cancelEdit(messageId) {
      const textElement = document.getElementById('message-text-' + messageId);
      const editForm = document.getElementById('edit-form-' + messageId);

      if (textElement && editForm) {
        textElement.style.display = 'block';
        editForm.style.display = 'none';
      }
    }

    // メッセージ削除
    function deleteMessage(messageId) {
      if (confirm('このメッセージを削除しますか？')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/transactions/{{ $transaction['id'] }}/messages/' + messageId;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
      }
    }

    // スクロールを最下部に
    window.addEventListener('load', function() {
      const messages = document.getElementById('chat-messages');
      messages.scrollTop = messages.scrollHeight;
    });

    // 評価モーダルを閉じる
    function closeRatingModal() {
      const modal = document.getElementById('rating-modal');
      if (modal) {
        modal.style.display = 'none';
      }
    }

    // 星評価の更新
    function updateStarRating(selectedValue) {
      const stars = document.querySelectorAll('.rating-modal__star');
      stars.forEach((star, index) => {
        const starValue = index + 1;
        if (starValue <= selectedValue) {
          star.style.color = '#FFF048';
        } else {
          star.style.color = '#D9D9D9';
        }
      });
    }

    // 星評価のイベントリスナー
    document.addEventListener('DOMContentLoaded', function() {
      const ratingInputs = document.querySelectorAll('.rating-modal__stars input[type="radio"]');
      ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
          updateStarRating(parseInt(this.value));
        });
      });

      // 初期状態を設定（デフォルトで3が選択されている場合）
      const defaultRating = document.querySelector('.rating-modal__stars input[type="radio"]:checked');
      if (defaultRating) {
        updateStarRating(parseInt(defaultRating.value));
      }
    });

    // ページ読み込み時に評価モーダルを表示
    @if ($showRatingModal)
      window.addEventListener('load', function() {
        const modal = document.getElementById('rating-modal');
        if (modal) {
          modal.style.display = 'flex';
          // モーダル表示時に星評価を初期化
          const defaultRating = document.querySelector('.rating-modal__stars input[type="radio"]:checked');
          if (defaultRating) {
            updateStarRating(parseInt(defaultRating.value));
          }
        }
      });
    @endif
  </script>
@endsection
