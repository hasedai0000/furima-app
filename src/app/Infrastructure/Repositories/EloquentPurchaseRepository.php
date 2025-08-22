<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Purchase\Entities\Purchase as PurchaseEntity;
use App\Domain\Purchase\Repositories\PurchaseRepositoryInterface;
use App\Models\Purchase;

class EloquentPurchaseRepository implements PurchaseRepositoryInterface
{
    /**
     * 決済処理（購入処理）
     *
     * @param string $itemId
     * @param string $paymentMethod
     * @param string $postcode
     * @param string $address
     * @param string $buildingName
     */
    public function settlement(PurchaseEntity $purchase): void
    {
        $eloquentPurchase = new Purchase();
        $eloquentPurchase->id = $purchase->getId();
        $eloquentPurchase->user_id = $purchase->getUserId();
        $eloquentPurchase->item_id = $purchase->getItemId();
        $eloquentPurchase->payment_method = $purchase->getPaymentMethod();
        $eloquentPurchase->postcode = $purchase->getPostcode()->formattedValue();
        $eloquentPurchase->address = $purchase->getAddress();
        $eloquentPurchase->building_name = $purchase->getBuildingName();
        $eloquentPurchase->purchased_at = now();
        $eloquentPurchase->save();
    }
}
