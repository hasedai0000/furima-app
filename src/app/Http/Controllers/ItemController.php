<?php

namespace App\Http\Controllers;

use App\Application\Contracts\FileUploadServiceInterface;
use App\Application\Services\AuthenticationService;
use App\Application\Services\ItemService;
use App\Domain\Item\Services\CategoryService;
use App\Domain\Item\Services\CommentService;
use App\Domain\Item\Services\LikeService;
use App\Domain\Item\ValueObjects\ItemCondition;
use App\Http\Requests\Item\ItemCommentRequest;
use App\Http\Requests\Item\ItemStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;


class ItemController extends Controller
{
    private ItemService $itemService;
    private CommentService $commentService;
    private LikeService $likeService;
    private CategoryService $categoryService;
    private FileUploadServiceInterface $fileUploadService;
    private AuthenticationService $authService;

    public function __construct(
        ItemService $itemService,
        CommentService $commentService,
        LikeService $likeService,
        CategoryService $categoryService,
        FileUploadServiceInterface $fileUploadService,
        AuthenticationService $authService
    ) {
        $this->itemService = $itemService;
        $this->commentService = $commentService;
        $this->likeService = $likeService;
        $this->categoryService = $categoryService;
        $this->fileUploadService = $fileUploadService;
        $this->authService = $authService;
    }

    public function index(Request $request): mixed
    {
        // 検索パラメータがある場合は検索を実行
        $searchTerm = $request->filled('search') ? $request->input('search') : '';

        // 現在のタブを取得
        $currentTab = $request->query('tab', '');

        // クエリパラメータでtab=mylistの場合はマイリストを表示
        if ($currentTab === 'mylist') {
            // 認証チェック
            if (! $this->authService->isAuthenticated()) {
                return redirect()->route('login');
            }
            $items = $this->itemService->getMyListItems($searchTerm);
        } else {
            $items = $this->itemService->getItems($searchTerm);
        }

        return view('items.index', compact('items', 'searchTerm', 'currentTab'));
    }

    public function detail(string $id): View
    {
        $item = $this->itemService->getItemWithLikeStatus($id);

        return view('items.detail', compact('item'));
    }

    public function comment(ItemCommentRequest $request, string $item_id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            // アプリケーションサービスにロジックを委譲
            $this->commentService->post(
                $validatedData['content'],
                $item_id
            );

            return redirect()->route('items.detail', ['item_id' => $item_id])->with('success', 'コメントを投稿しました。');
        } catch (\Exception $e) {
            return redirect()->route('items.detail', ['item_id' => $item_id])->with('error', $e->getMessage());
        }
    }

    public function like(string $item_id): RedirectResponse
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

    public function sell(Request $request): View
    {
        $categories = $this->categoryService->getCategories();
        $itemConditions = ItemCondition::getOptions();

        return view('items.sell', compact('categories', 'itemConditions'));
    }

    public function store(ItemStoreRequest $request): RedirectResponse
    {
        try {
            // バリデーション済みデータの取得
            $validatedData = $request->validated();

            if ($request->hasFile('imgUrl') && $request->file('imgUrl')->isValid()) {
                $imgUrl = $this->fileUploadService->upload($request->file('imgUrl'));
            } else {
                $imgUrl = '';
            }

            // アプリケーションサービスにロジックを委譲
            $this->itemService->createItem(
                auth()->id(),
                $validatedData['name'],
                $validatedData['brand_name'],
                $validatedData['description'],
                $validatedData['price'],
                $validatedData['condition'],
                $imgUrl,
                $validatedData['category_ids'],
            );

            return redirect()->route('items.index')->with('success', '商品を出品しました。');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
