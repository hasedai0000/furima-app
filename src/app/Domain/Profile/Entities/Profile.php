<?php

namespace App\Domain\Profile\Entities;

use App\Domain\Profile\ValueObjects\ProfileImgUrl;
use App\Domain\Profile\ValueObjects\ProfilePostCode;

class Profile
{
 private $id;
 private $userId;
 private $imgUrl;
 private $postcode;
 private $address;
 private $buildingName;

 public function __construct(
  string $id,
  string $userId,
  ?ProfileImgUrl $imgUrl,
  ProfilePostCode $postcode,
  string $address,
  string $buildingName
 ) {
  $this->id = $id;
  $this->userId = $userId;
  $this->imgUrl = $imgUrl;
  $this->postcode = $postcode;
  $this->address = $address;
  $this->buildingName = $buildingName;
 }

 public function getId(): int
 {
  return $this->id;
 }

 public function getUserId(): string
 {
  return $this->userId;
 }

 public function getImgUrl(): ?ProfileImgUrl
 {
  return $this->imgUrl;
 }

 public function getPostcode(): ProfilePostCode
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

 public function setImgUrl(?ProfileImgUrl $imgUrl): void
 {
  $this->imgUrl = $imgUrl;
 }

 public function setPostcode(ProfilePostCode $postcode): void
 {
  $this->postcode = $postcode;
 }

 public function setAddress(string $address): void
 {
  $this->address = $address;
 }

 public function setBuildingName(string $buildingName): void
 {
  $this->buildingName = $buildingName;
 }

 /**
  * エンティティを配列に変換する
  *
  * @return array
  */
 public function toArray(): array
 {
  return [
   'id' => $this->id,
   'userId' => $this->userId,
   'imgUrl' => $this->imgUrl ? $this->imgUrl->value() : null,
   'postcode' => $this->postcode->value(),
   'address' => $this->address,
   'buildingName' => $this->buildingName,
  ];
 }
}
