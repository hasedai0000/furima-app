<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ProfileStoreRequest extends FormRequest
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
          'name' => 'required|string|max:255',
          'imgUrl' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // 10MB以下
          'postcode' => 'required|string|max:8',
          'address' => 'required|string|max:255',
          'buildingName' => 'nullable|string|max:255',
        ];
    }
}
