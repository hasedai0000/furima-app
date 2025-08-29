<?php

namespace App\Application\Services;

use App\Domain\Purchase\Entities\Purchase as PurchaseEntity;
use App\Domain\Purchase\Repositories\PurchaseRepositoryInterface;
use App\Domain\Purchase\ValueObjects\PurchasePostCode;
use App\Domain\Purchase\ValueObjects\PaymentMethod;
use App\Application\Services\ItemService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class PurchaseService
{
    private PurchaseRepositoryInterface $purchaseRepository;
    private ItemService $itemService;

    public function __construct(
        PurchaseRepositoryInterface $purchaseRepository,
        ItemService $itemService
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->itemService = $itemService;
    }

    /**
     * Stripe Checkout セッション作成
     *
     * @param array $item 商品情報
     * @return string Checkout Session URL
     */
    public function createCheckoutSession(array $item): string
    {
        // Stripe秘密鍵を設定
        Stripe::setApiKey(config('services.stripe.st_key'));

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'jpy',
                            'unit_amount' => $item['price'], // 既に円単位
                            'product_data' => [
                                'name' => $item['name'],
                                'images' => [$item['imgUrl']],
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['item_id' => $item['id']]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.procedure', ['item_id' => $item['id']]),
                'customer_email' => Auth::user()->email,
                'metadata' => [
                    'item_id' => $item['id'],
                    'user_id' => Auth::user()->id,
                ],
            ]);

            return $checkoutSession->url;
        } catch (ApiErrorException $e) {
            throw new \Exception('Checkout セッション作成エラー: ' . $e->getMessage());
        }
    }

    /**
     * 購入処理（Checkout成功後用）
     *
     * @param string $itemId
     * @param string $paymentMethod
     * @param string $postcode
     * @param string $address
     * @param string|null $buildingName
     * @param string|null $paymentIntentId Stripe決済の場合のPayment Intent ID
     */
    public function completePurchase(string $itemId, string $paymentMethod, string $postcode, string $address, ?string $buildingName): PurchaseEntity
    {
        $purchasePostcode = new PurchasePostCode($postcode);

        $purchase = new PurchaseEntity(
            Str::uuid()->toString(),
            Auth::user()->id,
            $itemId,
            $paymentMethod,
            $purchasePostcode,
            $address,
            $buildingName ?? '' // nullの場合は空文字列にする
        );

        $this->purchaseRepository->settlement($purchase);

        return $purchase;
    }
}
