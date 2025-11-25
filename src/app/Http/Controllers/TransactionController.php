<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use App\Application\Services\ItemService;
use App\Application\Services\MessageService;
use App\Application\Services\RatingService;
use App\Application\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
  private TransactionService $transactionService;
  private MessageService $messageService;
  private ItemService $itemService;
  private AuthenticationService $authService;
  private RatingService $ratingService;

  public function __construct(
    TransactionService $transactionService,
    MessageService $messageService,
    ItemService $itemService,
    AuthenticationService $authService,
    RatingService $ratingService
  ) {
    $this->transactionService = $transactionService;
    $this->messageService = $messageService;
    $this->itemService = $itemService;
    $this->authService = $authService;
    $this->ratingService = $ratingService;
  }

  /**
   * チャット画面を表示
   *
   * @param string $transactionId
   * @return mixed
   */
  public function show(string $transactionId): mixed
  {
    try {
      $transaction = $this->transactionService->getTransaction($transactionId);
      if (!$transaction) {
        return redirect()->route('mypage.index')->with('error', '取引が見つかりません。');
      }

      $item = $this->itemService->getItem($transaction->getItemId());
      $userId = $this->authService->requireAuthentication();

      // 未読メッセージを既読にする
      $this->messageService->markMessagesAsRead($transactionId, $userId);

      $messages = $this->messageService->getMessagesByTransactionId($transactionId);

      // メッセージを配列に変換
      $messagesArray = array_map(function ($message) {
        return $message->toArray();
      }, $messages);
      $isBuyer = $transaction->getBuyerId() === $userId;
      $otherUserId = $isBuyer ? $transaction->getSellerId() : $transaction->getBuyerId();
      $otherUserModel = \App\Models\User::find($otherUserId);
      $otherUser = [
        'id' => $otherUserId,
        'name' => $otherUserModel ? $otherUserModel->name : 'ユーザー名',
      ];

      // 取引一覧を取得（サイドバー用）
      $userTransactions = $this->transactionService->getUserTransactions();
      $transactionsWithItems = [];
      foreach ($userTransactions as $tx) {
        $txItem = $this->itemService->getItem($tx->getItemId());
        $transactionsWithItems[] = [
          'transaction' => $tx->toArray(),
          'item' => $txItem,
        ];
      }

      // 評価が必要かチェック
      $needsRating = false;
      $showRatingModal = false;
      if ($transaction->getStatus() === 'completed') {
        // 取引が完了している場合、評価が必要かチェック
        $hasRated = $this->ratingService->hasRated($transactionId, $userId);
        if (!$hasRated) {
          $needsRating = true;
          $showRatingModal = true;
        }
      }

      return view('transactions.chat', [
        'transaction' => $transaction->toArray(),
        'item' => $item,
        'messages' => $messagesArray,
        'isBuyer' => $isBuyer,
        'otherUser' => $otherUser,
        'transactions' => $transactionsWithItems,
        'needsRating' => $needsRating,
        'showRatingModal' => $showRatingModal,
      ]);
    } catch (\Exception $e) {
      return redirect()->route('mypage.index')->with('error', $e->getMessage());
    }
  }

  /**
   * メッセージを送信
   *
   * @param Request $request
   * @param string $transactionId
   * @return RedirectResponse
   */
  public function sendMessage(Request $request, string $transactionId): RedirectResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'content' => 'nullable|string|max:400',
        'images.*' => 'nullable|image|mimes:jpeg,png|max:5120', // 5MB以下
      ], [
        'content.max' => '本文は400文字以内で入力してください',
        'images.*.image' => '画像ファイルをアップロードしてください',
        'images.*.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        'images.*.max' => '画像サイズが大きすぎます',
      ]);

      if ($validator->fails()) {
        return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
          ->withErrors($validator)
          ->withInput();
      }

      $validated = $validator->validated();
      $images = $request->hasFile('images') ? $request->file('images') : null;

      $this->messageService->sendMessage(
        $transactionId,
        $validated['content'] ?? null,
        $images
      );

      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('success', 'メッセージを送信しました。');
    } catch (\Exception $e) {
      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('error', $e->getMessage());
    }
  }

  /**
   * メッセージを更新
   *
   * @param Request $request
   * @param string $transactionId
   * @param string $messageId
   * @return RedirectResponse
   */
  public function updateMessage(Request $request, string $transactionId, string $messageId): RedirectResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'content' => 'nullable|string|max:400',
      ], [
        'content.max' => '本文は400文字以内で入力してください',
      ]);

      if ($validator->fails()) {
        return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
          ->withErrors($validator)
          ->withInput();
      }

      $validated = $validator->validated();

      $this->messageService->updateMessage(
        $messageId,
        $validated['content'] ?? null
      );

      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('success', 'メッセージを更新しました。');
    } catch (\Exception $e) {
      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('error', $e->getMessage());
    }
  }

  /**
   * メッセージを削除
   *
   * @param string $transactionId
   * @param string $messageId
   * @return RedirectResponse
   */
  public function deleteMessage(string $transactionId, string $messageId): RedirectResponse
  {
    try {
      $this->messageService->deleteMessage($messageId);

      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('success', 'メッセージを削除しました。');
    } catch (\Exception $e) {
      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('error', $e->getMessage());
    }
  }

  /**
   * 取引を完了にする
   *
   * @param string $transactionId
   * @return RedirectResponse
   */
  public function complete(string $transactionId): RedirectResponse
  {
    try {
      $this->transactionService->completeTransaction($transactionId);

      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('success', '取引を完了しました。評価をお願いします。');
    } catch (\Exception $e) {
      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('error', $e->getMessage());
    }
  }

  /**
   * 評価を送信
   *
   * @param Request $request
   * @param string $transactionId
   * @return RedirectResponse
   */
  public function submitRating(Request $request, string $transactionId): RedirectResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'rated_id' => 'required|string',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
      ], [
        'rated_id.required' => '評価対象のユーザーIDが必要です',
        'rating.required' => '評価を選択してください',
        'rating.min' => '評価は1以上である必要があります',
        'rating.max' => '評価は5以下である必要があります',
      ]);

      if ($validator->fails()) {
        return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
          ->withErrors($validator)
          ->withInput();
      }

      $validated = $validator->validated();

      $this->ratingService->createRating(
        $transactionId,
        $validated['rated_id'],
        $validated['rating'],
        $validated['comment'] ?? null
      );

      return redirect()->route('items.index')
        ->with('success', '評価を送信しました。');
    } catch (\Exception $e) {
      return redirect()->route('transactions.show', ['transaction_id' => $transactionId])
        ->with('error', $e->getMessage());
    }
  }
}
