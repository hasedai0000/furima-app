<?php

namespace App\Http\Controllers;

use App\Application\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
  private $profileService;

  public function __construct(
    ProfileService $profileService
  ) {
    $this->profileService = $profileService;
  }

  /**
   * プロフィール表示
   *
   * @return \Illuminate\Http\Response
   */
  public function show()
  {
    return view('mypage.profile');
  }

  /**
   * プロフィール作成
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    try {
      // バリデーション
      $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'postcode' => 'required|string|max:8',
        'address' => 'required|string|max:255',
        'buildingName' => 'nullable|string|max:255',
      ]);

      // アプリケーションサービスにロジックを委譲
      $profile = $this->profileService->createProfile(
        auth()->id(),
        null, // imgUrlは後で実装
        $validatedData['postcode'],
        $validatedData['address'],
        $validatedData['buildingName']
      );

      // 成功時のリダイレクト
      return redirect()->route('mypage.profile.show')->with('success', 'プロフィールが更新されました。');
    } catch (\Exception $e) {
      // エラーが発生した場合はエラーメッセージを表示
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }
}
