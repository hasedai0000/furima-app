<?php

namespace App\Http\Controllers;

use App\Application\Services\ProfileService;
use App\Application\Services\UserService;
use App\Http\Requests\Profile\ProfileStoreRequest;
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
    try {
      $user = $this->userService->getUser(auth()->id());
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }

    return view('mypage.profile', [
      'name' => $user->getName()
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

      // 画像ファイルの処理
      $imgUrl = null;
      if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
        $file = $request->file('imgUrl');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/profiles', $fileName);
        $imgUrl = 'storage/profiles/' . $fileName;
      }

      // アプリケーションサービスにロジックを委譲
      $this->profileService->createProfile(
        auth()->id(),
        $imgUrl,
        $validatedData['postcode'],
        $validatedData['address'],
        $validatedData['buildingName']
      );

      return redirect()->route('item.index')->with('success', 'プロフィールが正常に作成されました。');
    } catch (\Exception $e) {
      // エラーが発生した場合はエラーメッセージを表示
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }
}
