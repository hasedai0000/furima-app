<?php

namespace App\Http\Controllers;

use App\Application\Services\ProfileService;
use App\Application\Services\UserService;
use App\Http\Requests\Profile\ProfileStoreRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
  private $profileService;
  private $userService;

  public function __construct(
    ProfileService $profileService,
    UserService $userService
  ) {
    $this->profileService = $profileService;
    $this->userService = $userService;
  }

  /**
   * プロフィール表示
   *
   * @return \Illuminate\Http\Response
   */
  public function show()
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
  public function store(ProfileStoreRequest $request)
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
      $imgUrl = null;
      if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
        $file = $request->file('imgUrl');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('', $fileName, 'public');
        $imgUrl = 'storage/' . $fileName;
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
  public function update(ProfileUpdateRequest $request)
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
      $imgUrl = null;
      if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
        $file = $request->file('imgUrl');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('', $fileName, 'public');
        $imgUrl = 'storage/' . $fileName;
      }

      // アプリケーションサービスにロジックを委譲
      $this->profileService->updateProfile(
        auth()->id(),
        $imgUrl,
        $validatedData['postcode'],
        $validatedData['address'],
        $validatedData['buildingName']
      );

      return redirect()->route('items.index')->with('success', 'プロフィールが正常に更新されました。');
    } catch (\Exception $e) {
      // エラーが発生した場合はエラーメッセージを表示
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }
}
