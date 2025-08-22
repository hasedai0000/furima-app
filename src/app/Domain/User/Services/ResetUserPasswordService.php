<?php

namespace App\Domain\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPasswordService implements ResetsUserPasswords
{
    /**
     * ユーザーのパスワードをリセットする
     *
     * @param array<string, string> $input
     */
    public function reset(User $user, array $input): void
    {
        $this->validate($input);

        $user->forceFill([
         'password' => Hash::make($input['password']),
        ])->save();
    }

    /**
     * 入力データを検証する
     *
     * @param array<string, string> $input
     */
    private function validate(array $input): void
    {
        Validator::make($input, [
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
