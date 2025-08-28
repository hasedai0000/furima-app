<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
 /**
  * 認可
  *
  * @return bool
  */
 public function authorize(): bool
 {
  return true;
 }

 /**
  * バリデーションルール
  *
  * @return array
  */
 public function rules(): array
 {
  return [
   'name' => ['required', 'string', 'max:255'],
   'email' => [
    'required',
    'string',
    'email',
    'max:255',
    Rule::unique(User::class),
   ],
   'password' => ['required', 'string', Password::min(8), 'confirmed'],
  ];
 }

 /**
  * カスタムバリデーションメッセージ
  *
  * @return array
  */
 public function messages(): array
 {
  return [
   'name.required' => 'お名前を入力してください。',
   'name.string' => 'お名前は文字列で入力してください。',
   'name.max' => 'お名前は255文字以内で入力してください。',
   'email.required' => 'メールアドレスを入力してください。',
   'email.string' => 'メールアドレスは文字列で入力してください。',
   'email.email' => 'メールアドレスはメール形式で入力してください。',
   'email.max' => 'メールアドレスは255文字以内で入力してください。',
   'email.unique' => 'このメールアドレスは既に使用されています。',
   'password.required' => 'パスワードを入力してください。',
   'password.min' => 'パスワードは8文字以上で入力してください。',
   'password.string' => 'パスワードは文字列で入力してください。',
   'password.confirmed' => 'パスワードと一致しません。',
  ];
 }
}
