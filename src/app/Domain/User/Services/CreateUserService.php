<?php

namespace App\Domain\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CreateUserService implements CreatesNewUsers
{
 /**
  * ユーザーを作成する
  *
  * @param array<string, string> $input
  */
 public function create(array $input): User
 {
  $this->validate($input);

  $user = User::create([
   'name' => $input['name'],
   'email' => $input['email'],
   'password' => Hash::make($input['password']),
  ]);

  // ユーザーをログイン状態にする
  Auth::login($user);

  return $user;
 }

 /**
  * 入力データを検証する
  *
  * @param array<string, string> $input
  */
 private function validate(array $input): void
 {
  Validator::make($input, [
   'name' => ['required', 'string', 'max:255'],
   'email' => [
    'required',
    'string',
    'email',
    'max:255',
    Rule::unique(User::class),
   ],
   'password' => $this->passwordRules(),
  ])->validate();
 }

 /**
  * パスワードの検証ルールを取得する
  *
  * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
  */
 private function passwordRules(): array
 {
  return ['required', 'string', \Illuminate\Validation\Rules\Password::default(), 'confirmed'];
 }
}
