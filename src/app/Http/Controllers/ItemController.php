<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use App\Application\Services\ItemService;

class ItemController extends Controller
{
  private $itemService;

  public function __construct(
    ItemService $itemService
  ) {
    $this->itemService = $itemService;
  }

  public function index()
  {
    $items = $this->itemService->getItems();
    return view('items.index', compact('items'));
  }

  public function mylist(Request $request)
  {
    $tab = $request->query('tab');
    if ($tab === 'mylist') {
      // 暫定処理
      $items = $this->getMyListItems();
    }

    return view('items.index', compact('tab', 'items'));
  }

  /**
   * おすすめ商品を取得
   */
  private function getRecommendedItems()
  {
    // 基本的な商品一覧を取得
    return Item::with(['category', 'user'])
      ->orderBy('created_at', 'desc')
      ->paginate(12);
  }

  /**
   * マイリスト（お気に入り）の商品を取得
   */
  private function getMyListItems()
  {
    if (!Auth::check()) {
      return collect(); // 未ログインの場合は空のコレクション
    }

    // ログインユーザーがお気に入りした商品を取得
    return Item::whereHas('likes', function ($query) {
      $query->where('user_id', Auth::id());
    })
      ->with(['category', 'user'])
      ->orderBy('created_at', 'desc')
      ->paginate(12);
  }
}
