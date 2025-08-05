<?php

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserPassword;

class User
{
  private $id;
  private $name;
  private $email;
  private $password;

  public function __construct(
    string $id,
    int $name,
    UserEmail $email,
    UserPassword $password
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->email = $email;
    $this->password = $password;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getEmail(): UserEmail
  {
    return $this->email;
  }

  public function getPassword(): UserPassword
  {
    return $this->password;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function setEmail(UserEmail $email): void
  {
    $this->email = $email;
  }

  public function setPassword(UserPassword $password): void
  {
    $this->password = $password;
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
      'name' => $this->name,
      'email' => $this->email->value(),
      'password' => $this->password->value(),
    ];
  }
}
