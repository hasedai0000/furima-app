<?php

namespace App\Domain\User\Entities;

class Profile
{
 private $id;
 private $user_id;
 private $img_url;
 private $postcode;
 private $address;
 private $building_name;

 public function __construct(
  string $id,
  string $user_id,
  string $img_url,
  string $postcode,
  string $address,
  string $building_name
 ) {
  $this->id = $id;
  $this->user_id = $user_id;
  $this->img_url = $img_url;
  $this->postcode = $postcode;
  $this->address = $address;
  $this->building_name = $building_name;
 }

 public function getId(): int
 {
  return $this->id;
 }

 public function getUserId(): string
 {
  return $this->user_id;
 }

 public function getImgUrl(): string
 {
  return $this->img_url;
 }

 public function getPostcode(): string
 {
  return $this->postcode;
 }

 public function getAddress(): string
 {
  return $this->address;
 }

 public function getBuildingName(): string
 {
  return $this->building_name;
 }

 public function setImgUrl(string $img_url): void
 {
  $this->img_url = $img_url;
 }

 public function setPostcode(string $postcode): void
 {
  $this->postcode = $postcode;
 }

 public function setAddress(string $address): void
 {
  $this->address = $address;
 }

 public function setBuildingName(string $building_name): void
 {
  $this->building_name = $building_name;
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
   'user_id' => $this->user_id,
   'img_url' => $this->img_url,
   'postcode' => $this->postcode,
   'address' => $this->address,
   'building_name' => $this->building_name,
  ];
 }
}
