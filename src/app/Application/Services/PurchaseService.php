<?php

namespace App\Application\Services;

use App\Domain\Purchase\Entities\Purchase as PurchaseEntity;
use App\Domain\Purchase\Repositories\PurchaseRepositoryInterface;
use App\Domain\Purchase\ValueObjects\PurchasePostCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PurchaseService
{
  private $purchaseRepository;

  public function __construct(
    PurchaseRepositoryInterface $purchaseRepository
  ) {
    $this->purchaseRepository = $purchaseRepository;
  }

  /**
   * 購入処理
   *
   * @param string $itemId
   * @param string $paymentMethod
   * @param string $postcode
   * @param string $address
   * @param string $buildingName
   */
  public function purchase(string $itemId, string $paymentMethod, string $postcode, string $address, string $buildingName): PurchaseEntity
  {
    $purchasePostcode = new PurchasePostCode($postcode);

    $purchase = new PurchaseEntity(
      Str::uuid()->toString(),
      Auth::user()->id,
      $itemId,
      $paymentMethod,
      $purchasePostcode,
      $address,
      $buildingName
    );

    $this->purchaseRepository->settlement($purchase);

    return $purchase;
  }
}
