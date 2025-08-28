<?php

namespace App\Domain\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateUserService implements CreatesNewUsers
{
    /**
     * ユーザーを作成する
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // メール認証が必要なので、ここではログインしない
        // ユーザーにメール認証通知を送信
        $user->sendEmailVerificationNotification();

        return $user;
    }
}
