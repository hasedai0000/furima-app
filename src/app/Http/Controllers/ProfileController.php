<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use App\Application\Services\FileUploadService;
use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
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

    public function __construct(
        AuthenticationService $authService,
        ItemService $itemService,
        ProfileService $profileService,
        UserService $userService,
        FileUploadService $fileUploadService
    ) {
        $this->authService = $authService;
        $this->itemService = $itemService;
        $this->profileService = $profileService;
        $this->userService = $userService;
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request): mixed
    {
        // 検索パラメータがある場合は検索を実行
        $searchTerm = $request->filled('search') ? $request->input('search') : '';

        if (!$this->authService->isAuthenticated()) {
            return redirect()->route('login');
        }
        // クエリパラメータでpage=buyの場合はマイリストを表示
        if ($request->query('page') === 'sell') {
            $items = $this->itemService->getMySellItems($searchTerm);
        } else {
            $items = $this->itemService->getMyBuyItems($searchTerm);
        }

        $profileEntity = $this->profileService->getProfile(auth()->id());
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('mypage.index', compact('items', 'searchTerm', 'profile'));
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
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return view('mypage.profile', [
            'name' => $user->getName(),
            'profile' => $profile ? $profile->toArray() : null,
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
