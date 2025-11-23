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
      <h2 class="chat-sidebar__title">取引中の商品</h2>
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
      <!-- ヘッダー -->
      <div class="chat-header">
        <div class="chat-header__item">
          <img src="{{ asset($item['imgUrl']) }}" alt="{{ $item['name'] }}" class="chat-header__item-image">
          <div class="chat-header__item-info">
            <div class="chat-header__item-name">{{ $item['name'] }}</div>
            <div class="chat-header__item-price">¥{{ number_format($item['price']) }}</div>
          </div>
        </div>
        @if ($isBuyer && $transaction['status'] === 'active')
          <div class="chat-header__actions">
            <form action="{{ route('transactions.complete', ['transaction_id' => $transaction['id']]) }}" method="POST">
              @csrf
              <button type="submit" class="chat-header__complete-btn">取引を完了する</button>
            </form>
          </div>
        @endif
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
            <div class="chat-message__avatar">
              @if ($userProfile && $userProfile->img_url)
                <img src="{{ asset($userProfile->img_url) }}" alt="{{ $messageUser->name }}">
              @else
                <div class="chat-message__avatar-placeholder">
                  <span>画像なし</span>
                </div>
              @endif
            </div>
            <div class="chat-message__content">
              <div class="chat-message__header">
                <span class="chat-message__author">{{ $messageUser->name }}</span>
                <span
                  class="chat-message__time">{{ \Carbon\Carbon::parse($message['createdAt'])->format('Y/m/d H:i') }}</span>
              </div>
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
              @if ($isOwnMessage)
                <div class="chat-message__actions">
                  <a href="#" class="chat-message__action" onclick="editMessage('{{ $message['id'] }}')">編集</a>
                  <a href="#" class="chat-message__action" onclick="deleteMessage('{{ $message['id'] }}')">削除</a>
                </div>
              @endif
            </div>
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
          <div class="chat-form__input-area">
            <textarea name="content" id="message-content" class="chat-form__textarea" placeholder="メッセージを入力してください" maxlength="400"></textarea>
            <div class="chat-form__char-count">
              <span id="char-count">0</span>/400
            </div>
          </div>
          <div class="chat-form__image-area">
            <label for="message-images" class="chat-form__image-label">
              <i class="fas fa-image"></i> 画像を追加
            </label>
            <input type="file" name="images[]" id="message-images" multiple accept="image/jpeg,image/png"
              class="chat-form__image-input">
            <div class="chat-form__image-preview" id="image-preview"></div>
          </div>
          <button type="submit" class="chat-form__submit">送信</button>
        </form>
      </div>
    </div>
  </div>

  <!-- 評価モーダル -->
  @if ($showRatingModal)
    <div class="rating-modal" id="rating-modal">
      <div class="rating-modal__overlay" onclick="closeRatingModal()"></div>
      <div class="rating-modal__content">
        <h2 class="rating-modal__title">取引を評価する</h2>
        <form action="{{ route('transactions.submitRating', ['transaction_id' => $transaction['id']]) }}"
          method="POST">
          @csrf
          <input type="hidden" name="rated_id" value="{{ $otherUser['id'] }}">
          <div class="rating-modal__rating">
            <label class="rating-modal__label">評価</label>
            <div class="rating-modal__stars">
              @for ($i = 5; $i >= 1; $i--)
                <input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}"
                  {{ $i === 5 ? 'checked' : '' }}>
                <label for="rating-{{ $i }}" class="rating-modal__star">
                  <i class="fas fa-star"></i>
                </label>
              @endfor
            </div>
          </div>
          <div class="rating-modal__comment">
            <label class="rating-modal__label" for="rating-comment">コメント（任意）</label>
            <textarea name="comment" id="rating-comment" class="rating-modal__textarea" placeholder="コメントを入力してください"
              maxlength="500"></textarea>
          </div>
          <div class="rating-modal__actions">
            <button type="submit" class="rating-modal__submit">評価を送信</button>
          </div>
        </form>
      </div>
    </div>
  @endif

  <script>
    // 文字数カウント
    document.getElementById('message-content').addEventListener('input', function() {
      const count = this.value.length;
      document.getElementById('char-count').textContent = count;
    });

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

    // ページ読み込み時に評価モーダルを表示
    @if ($showRatingModal)
      window.addEventListener('load', function() {
        const modal = document.getElementById('rating-modal');
        if (modal) {
          modal.style.display = 'flex';
        }
      });
    @endif
  </script>
@endsection
