<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
      'email' => 'required|email',
      'password' => 'required',
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
      'email.required' => 'メールアドレスを入力してください。',
      'email.email' => 'メールアドレスはメール形式で入力してください。',
      'password.required' => 'パスワードを入力してください。',
    ];
  }
}
