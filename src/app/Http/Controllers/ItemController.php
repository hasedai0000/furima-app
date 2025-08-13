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
}
