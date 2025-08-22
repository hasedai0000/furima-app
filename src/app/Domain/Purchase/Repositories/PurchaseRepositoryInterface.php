<?php

namespace App\Domain\Purchase\Repositories;

use App\Domain\Purchase\Entities\Purchase as PurchaseEntity;

interface PurchaseRepositoryInterface
{
    public function settlement(PurchaseEntity $purchase): void;
}
