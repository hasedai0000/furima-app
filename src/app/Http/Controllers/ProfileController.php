<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use App\Application\Services\FileUploadService;
use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
use App\Application\Services\RatingService;
use App\Application\Services\TransactionService;
use App\Application\Services\UserService;
use App\Http\Requests\Profile\AddressUpdateRequest;
use App\Http\Requests\Profile\ProfileStoreRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private ProfileService $profileService;
    private ItemService $itemService;
    private UserService $userService;
    private FileUploadService $fileUploadService;
    private AuthenticationService $authService;
    private TransactionService $transactionService;
    private RatingService $ratingService;

    public function __construct(
        AuthenticationService $authService,
        ItemService $itemService,
        ProfileService $profileService,
        UserService $userService,
        FileUploadService $fileUploadService,
        TransactionService $transactionService,
        RatingService $ratingService
    ) {
        $this->authService = $authService;
        $this->itemService = $itemService;
        $this->profileService = $profileService;
        $this->userService = $userService;
        $this->fileUploadService = $fileUploadService;
        $this->transactionService = $transactionService;
        $this->ratingService = $ratingService;
    }

    public function index(Request $request): mixed
    {
        // 検索パラメータがある場合は検索を実行
        $searchTerm = $request->filled('search') ? $request->input('search') : '';

        if (!$this->authService->isAuthenticated()) {
            return redirect()->route('login');
        }

        $currentTab = $request->query('page', 'sell');
        $items = [];
        $transactions = [];

        // クエリパラメータでpage=buyの場合は購入した商品（取引完了）を表示
        if ($currentTab === 'sell') {
            $items = $this->itemService->getMySellItems($searchTerm);
        } elseif ($currentTab === 'buy') {
            // 取引が完了した商品を表示
            $items = $this->itemService->getMyCompletedBuyItems($searchTerm);
        } elseif ($currentTab === 'transaction') {
            // 取引中の商品を取得（評価が未完了の商品）
            $userTransactions = $this->transactionService->getUserTransactions();
            $transactions = [];
            $totalUnreadCount = 0;

            // 評価が未完了の取引のみをフィルタリング
            foreach ($userTransactions as $tx) {
                // 評価が完了しているかチェック（購入者と出品者の両方が評価済み）
                $isRatingCompleted = $this->ratingService->isRatingCompleted(
                    $tx->getId(),
                    $tx->getBuyerId(),
                    $tx->getSellerId()
                );

                // 評価が未完了の場合のみ表示
                if (!$isRatingCompleted) {
                    // 商品情報を取得
                    $txItem = $this->itemService->getItem($tx->getItemId());

                    // 検索キーワードでフィルタリング
                    if ($searchTerm && stripos($txItem['name'], $searchTerm) === false) {
                        continue;
                    }

                    // 未読メッセージ数を取得
                    $unreadCount = $this->getUnreadMessageCount($tx->getId(), auth()->id());
                    $totalUnreadCount += $unreadCount;
                    $transactions[] = [
                        'transaction' => $tx->toArray(),
                        'item' => $txItem,
                        'unreadCount' => $unreadCount,
                    ];
                }
            }

            // 新規メッセージが来た順にソート（未読数が多い順、その後更新日時順）
            usort($transactions, function ($a, $b) {
                if ($a['unreadCount'] !== $b['unreadCount']) {
                    return $b['unreadCount'] <=> $a['unreadCount'];
                }
                $aUpdatedAt = $a['transaction']['updatedAt'] ?? '';
                $bUpdatedAt = $b['transaction']['updatedAt'] ?? '';
                return strtotime($bUpdatedAt) <=> strtotime($aUpdatedAt);
            });
        }

        $profileEntity = $this->profileService->getProfile(auth()->id());
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        // ユーザー情報を取得
        $userEntity = $this->userService->getUser(auth()->id());
        $userName = $userEntity ? $userEntity->getName() : 'ユーザー名';

        // 取引中の商品タブの未読メッセージ数の合計を計算
        $transactionUnreadCount = 0;
        if ($currentTab === 'transaction' && isset($transactions)) {
            foreach ($transactions as $txData) {
                $transactionUnreadCount += $txData['unreadCount'];
            }
        } else {
            // 取引中の商品タブがアクティブでない場合でも、未読数を計算
            $userTransactions = $this->transactionService->getUserTransactions();
            foreach ($userTransactions as $tx) {
                // 評価が未完了の取引のみをカウント
                $isRatingCompleted = $this->ratingService->isRatingCompleted(
                    $tx->getId(),
                    $tx->getBuyerId(),
                    $tx->getSellerId()
                );
                if (!$isRatingCompleted) {
                    $transactionUnreadCount += $this->getUnreadMessageCount($tx->getId(), auth()->id());
                }
            }
        }

        return view('mypage.index', compact('items', 'transactions', 'searchTerm', 'profile', 'currentTab', 'transactionUnreadCount', 'userName'));
    }

    /**
     * 未読メッセージ数を取得
     *
     * @param string $transactionId
     * @param string $userId
     * @return int
     */
    private function getUnreadMessageCount(string $transactionId, string $userId): int
    {
        $messages = \App\Models\Message::where('transaction_id', $transactionId)
            ->where('user_id', '!=', $userId)
            ->whereNull('deleted_at')
            ->get();

        $unreadCount = 0;
        foreach ($messages as $message) {
            $read = \App\Models\MessageRead::where('message_id', $message->id)
                ->where('user_id', $userId)
                ->exists();
            if (!$read) {
                $unreadCount++;
            }
        }

        return $unreadCount;
    }

    /**
     * プロフィール表示
     *
     * @return \Illuminate\Http\Response
     */
    public function show(): mixed
    {
        $userId = auth()->id();

        try {
            $user = $this->userService->getUser($userId);
            $profile = $this->profileService->getProfile($userId);
            $averageRating = $this->ratingService->getAverageRating($userId);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return view('mypage.profile', [
            'name' => $user->getName(),
            'profile' => $profile ? $profile->toArray() : null,
            'averageRating' => $averageRating,
        ]);
    }

    /**
     * プロフィール作成
     *
     * @param ProfileStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProfileStoreRequest $request): RedirectResponse
    {
        try {
            // バリデーション
            $validatedData = $request->validated();

            // ユーザー名を更新
            $this->userService->updateUserName(
                auth()->id(),
                $validatedData['name']
            );

            // 画像ファイルの処理
            if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
                $imgUrl = $this->fileUploadService->upload($request->file('imgUrl'));
            } else {
                $imgUrl = null;
            }

            // アプリケーションサービスにロジックを委譲
            $this->profileService->createProfile(
                auth()->id(),
                $imgUrl,
                $validatedData['postcode'],
                $validatedData['address'],
                $validatedData['buildingName']
            );

            return redirect()->route('items.index')->with('success', 'プロフィールが正常に作成されました。');
        } catch (\Exception $e) {
            // エラーが発生した場合はエラーメッセージを表示
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * プロフィール更新
     *
     * @param ProfileUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            // バリデーション
            $validatedData = $request->validated();

            // ユーザー名を更新
            $this->userService->updateUserName(
                auth()->id(),
                $validatedData['name']
            );

            // 画像ファイルの処理
            if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
                $imgUrl = $this->fileUploadService->upload($request->file('imgUrl'));
            } else {
                $imgUrl = null;
            }

            // アプリケーションサービスにロジックを委譲
            $this->profileService->updateProfile(
                auth()->id(),
                $imgUrl,
                $validatedData['postcode'],
                $validatedData['address'],
                $validatedData['buildingName']
            );

            return redirect()->route('mypage.index')->with('success', 'プロフィールが正常に更新されました。');
        } catch (\Exception $e) {
            // エラーが発生した場合はエラーメッセージを表示
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function modifyAddress(AddressUpdateRequest $request, string $itemId): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $this->profileService->updateProfile(
                auth()->id(),
                null,
                $validatedData['postcode'],
                $validatedData['address'],
                $validatedData['buildingName']
            );

            return redirect()->route('purchase.procedure', $itemId)->with('success', '住所を更新しました');
        } catch (\Exception $e) {
            return redirect()->route('purchase.address', $itemId)->with('error', $e->getMessage());
        }
    }
}
