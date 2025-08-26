<?php

namespace App\Domain\Purchase\Entities;

use App\Domain\Purchase\ValueObjects\PurchasePostCode;

class Purchase
{
    private string $id;
    private string $userId;
    private string $itemId;
    private string $paymentMethod;
    private PurchasePostCode $postcode;
    private string $address;
    private ?string $buildingName;

    public function __construct(
        string $id,
        string $userId,
        string $itemId,
        string $paymentMethod,
        PurchasePostCode $postcode,
        string $address,
        ?string $buildingName
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->itemId = $itemId;
        $this->paymentMethod = $paymentMethod;
        $this->postcode = $postcode;
        $this->address = $address;
        $this->buildingName = $buildingName;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getPostcode(): PurchasePostCode
    {
        return $this->postcode;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getBuildingName(): string
    {
        return $this->buildingName;
    }

    public function setPostcode(PurchasePostCode $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setBuildingName(?string $buildingName): void
    {
        $this->buildingName = $buildingName;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'itemId' => $this->itemId,
            'paymentMethod' => $this->paymentMethod,
            'postcode' => $this->postcode->formattedValue(),
            'address' => $this->address,
            'buildingName' => $this->buildingName ?? null,
        ];
    }
}
