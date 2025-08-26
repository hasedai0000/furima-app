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

    /**
     * 購入処理（従来のStripe Elements用 - 後方互換性のため残す）
     *
     * @param string $itemId
     * @param string $paymentMethod
     * @param string $postcode
     * @param string $address
     * @param string|null $buildingName
     * @param string|null $paymentMethodId Stripe決済の場合のPayment Method ID
     */
    public function purchase(string $itemId, string $paymentMethod, string $postcode, string $address, ?string $buildingName, ?string $paymentMethodId = null): PurchaseEntity
    {
        $purchasePostcode = new PurchasePostCode($postcode);

        // 商品情報を取得
        $item = $this->itemService->getItem($itemId);

        // Stripe決済の場合は決済処理を実行
        if ($paymentMethod === PaymentMethod::CREDIT_CARD) {
            $this->processStripePayment($item['price'], $paymentMethodId);
        }

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

    /**
     * Stripe決済処理（Elements用）
     *
     * @param int $amount 支払い金額
     * @param string $paymentMethodId Payment Method ID
     * @throws \Exception 決済失敗時
     */
    private function processStripePayment(int $amount, string $paymentMethodId): void
    {
        // Stripe秘密鍵を設定
        Stripe::setApiKey(config('services.stripe.st_key'));

        try {
            // PaymentIntentを作成して即座に確認
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount, // 金額（円）
                'currency' => 'jpy',
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('items.index'), // 決済後のリダイレクト先
            ]);

            // 決済が成功していない場合はエラーを投げる
            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('決済処理に失敗しました: ' . $paymentIntent->status);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new \Exception('Stripe決済エラー: ' . $e->getMessage());
        }
    }
}
