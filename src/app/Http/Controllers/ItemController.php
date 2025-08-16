<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Application\Services\ItemService;
use App\Domain\Item\Services\CommentService;
use App\Domain\Item\Services\LikeService;

class ItemController extends Controller
{
  private $itemService;
  private $commentService;
  private $likeService;

  public function __construct(
    ItemService $itemService,
    CommentService $commentService,
    LikeService $likeService
  ) {
    $this->itemService = $itemService;
    $this->commentService = $commentService;
    $this->likeService = $likeService;
  }

  public function index(Request $request)
  {
    // 検索パラメータがある場合は検索を実行
    $searchTerm = $request->filled('search') ? $request->input('search') : '';

    // クエリパラメータでtab=mylistの場合はマイリストを表示
    if ($request->query('tab') === 'mylist') {
      // 認証チェックz
      if (!Auth::check()) {
        return redirect()->route('login');
      }
      $items = $this->itemService->getMyListItems($searchTerm);
    } else {
      $items = $this->itemService->getItems($searchTerm);
    }

    return view('items.index', compact('items', 'searchTerm'));
  }

  public function detail($id)
  {
    $item = $this->itemService->getItem($id);
    return view('items.detail', compact('item'));
  }

  public function comment(Request $request, $item_id)
  {
    try {
      // アプリケーションサービスにロジックを委譲
      $this->commentService->post(
        $request->input('content'),
        $item_id
      );

      return redirect()->route('items.detail', ['item_id' => $item_id])->with('success', 'コメントを投稿しました。');
    } catch (\Exception $e) {
      return redirect()->route('items.detail', ['item_id' => $item_id])->with('error', $e->getMessage());
    }
  }

  public function like($item_id)
  {
    try {
      // 既にいいねしている場合は削除、していない場合は追加
      if ($this->likeService->isLiked($item_id)) {
        $this->likeService->unlike($item_id);
        $message = 'いいねを取り消しました。';
      } else {
        $this->likeService->like($item_id);
        $message = 'いいねしました。';
      }

      return redirect()->route('items.detail', ['item_id' => $item_id])->with('success', $message);
    } catch (\Exception $e) {
      return redirect()->route('items.detail', ['item_id' => $item_id])->with('error', $e->getMessage());
    }
  }
}
