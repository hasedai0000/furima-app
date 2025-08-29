<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthenticationService;
use App\Application\Services\ItemService;
use App\Application\Services\ProfileService;
use App\Application\Services\PurchaseService;
use App\Http\Requests\Purchase\PurchaseRequest;
use App\Domain\Purchase\ValueObjects\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    private ItemService $itemService;
    private ProfileService $profileService;
    private PurchaseService $purchaseService;
    private AuthenticationService $authService;

    public function __construct(
        ItemService $itemService,
        ProfileService $profileService,
        PurchaseService $purchaseService,
        AuthenticationService $authService
    ) {
        $this->itemService = $itemService;
        $this->profileService = $profileService;
        $this->purchaseService = $purchaseService;
        $this->authService = $authService;
    }

    public function procedure(string $id): View
    {
        $item = $this->itemService->getItem($id);
        $paymentMethods = PaymentMethod::getOptions();
        $userId = $this->authService->requireAuthentication();
        $profileEntity = $this->profileService->getProfile($userId);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.procedure', compact('item', 'paymentMethods', 'profile'));
    }

    public function editAddress(string $itemId): View
    {
        $userId = $this->authService->requireAuthentication();
        $profileEntity = $this->profileService->getProfile($userId);
        $profile = $profileEntity ? $profileEntity->toArray() : null;

        return view('purchase.address', compact('itemId', 'profile'));
    }

    /**
     * Stripe Checkoutページへリダイレクト
     */
    public function stripeCheckout(string $itemId): RedirectResponse
    {
        try {
            $item = $this->itemService->getItem($itemId);
            $checkoutUrl = $this->purchaseService->createCheckoutSession($item);

            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            return redirect()->route('purchase.procedure', $itemId)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Stripe Checkout成功後の処理
     */
    public function checkoutSuccess(Request $request, string $itemId): RedirectResponse
    {
        try {
            $sessionId = $request->query('session_id');

            if (!$sessionId) {
                throw new \Exception('セッションIDが見つかりません');
            }

            // Stripe セッション情報を取得
            Stripe::setApiKey(config('services.stripe.st_key'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                throw new \Exception('決済が完了していません');
            }

            // 購入処理を実行（住所情報はプロフィールから取得）
            $userId = $this->authService->requireAuthentication();
            $profileEntity = $this->profileService->getProfile($userId);

            if (!$profileEntity) {
                throw new \Exception('住所情報が設定されていません');
            }

            $profile = $profileEntity->toArray();

            $this->purchaseService->completePurchase(
                $itemId,
                PaymentMethod::CREDIT_CARD,
                $profile['postcode'],
                $profile['address'],
                $profile['buildingName']
            );

            return redirect()->route('items.detail', $itemId)
                ->with('success', '購入が完了しました');
        } catch (\Exception $e) {
            return redirect()->route('items.detail', $itemId)
                ->with('error', $e->getMessage());
        }
    }

    public function purchase(PurchaseRequest $request, string $itemId): RedirectResponse
    {
        try {
            // バリデーション
            $validatedData = $request->validated();

            // リクエストから住所情報を取得してPurchaseテーブルに保存
            $this->purchaseService->completePurchase(
                $itemId,
                $validatedData['payment_method'],
                $validatedData['postcode'],
                $validatedData['address'],
                $validatedData['buildingName']
            );

            return redirect()->route('items.detail', $itemId)->with('success', '購入が完了しました');
        } catch (\Exception $e) {
            return redirect()->route('items.detail', $itemId)->with('error', $e->getMessage());
        }
    }
}
