<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use App\Application\Services\ItemService;

class PurchaseController extends Controller
{
  private $itemService;

  public function __construct(
    ItemService $itemService
  ) {
    $this->itemService = $itemService;
  }

  public function purchase($id)
  {
    $item = $this->itemService->getItem($id);
    return view('purchase.purchase', compact('item'));
  }
}
