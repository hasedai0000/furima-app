<?php

namespace App\Domain\Profile\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateProfileInformationService implements UpdatesUserProfileInformation
{
    /**
     * ユーザーのプロフィール情報を更新する
     *
     * @param array<string, string> $input
     */
    public function update(User $user, array $input): void
    {
        $this->validate($user, $input);

        if (
            $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
             'name' => $input['name'],
             'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * 入力データを検証する
     *
     * @param array<string, string> $input
     */
    private function validate(User $user, array $input): void
    {
        Validator::make($input, [
         'name' => ['required', 'string', 'max:255'],
         'email' => [
          'required',
          'string',
          'email',
          'max:255',
          Rule::unique('users')->ignore($user->id),
         ],
        ])->validate();
    }

    /**
     * 認証済みユーザーのプロフィール情報を更新する
     *
     * @param array<string, string> $input
     */
    private function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
         'name' => $input['name'],
         'email' => $input['email'],
         'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
