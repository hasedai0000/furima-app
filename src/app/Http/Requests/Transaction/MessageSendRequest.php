<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class MessageSendRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'content' => 'nullable|string|max:400',
      'images' => 'nullable|array',
      'images.*' => 'nullable|image|mimes:jpeg,png|max:5120', // 5MB以下
    ];
  }

  /**
   * Configure the validator instance.
   *
   * @param  \Illuminate\Validation\Validator  $validator
   * @return void
   */
  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      $content = $this->input('content');
      $images = $this->file('images');

      // contentが空文字列の場合はnullとして扱う
      $hasContent = !empty(trim($content ?? ''));
      $hasImages = !empty($images) && count(array_filter($images)) > 0;

      // contentとimagesの両方がない場合はエラー
      if (!$hasContent && !$hasImages) {
        $validator->errors()->add('content', '本文を入力してください');
      }
    });
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array
   */
  public function messages(): array
  {
    return [
      'content.max' => '本文は 400 文字以内で入力してください',
      'images.array' => '画像は配列形式で送信してください',
      'images.*.image' => '画像ファイルをアップロードしてください',
      'images.*.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
      'images.*.max' => '画像サイズが大きすぎます',
    ];
  }
}
