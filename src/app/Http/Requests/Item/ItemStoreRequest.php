<?php

namespace App\Http\Requests\Item;

use App\Domain\Item\ValueObjects\ItemCondition;
use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|integer|min:0',
            'condition' => 'required|string|in:' . implode(',', array_keys(ItemCondition::getOptions())),
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'string|exists:categories,id',
            'imgUrl' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => '商品名は必須です。',
            'name.max' => '商品名は255文字以内で入力してください。',
            'brand_name.max' => 'ブランド名は255文字以内で入力してください。',
            'description.max' => '商品の説明は1000文字以内で入力してください。',
            'price.required' => '価格は必須です。',
            'price.integer' => '価格は整数で入力してください。',
            'price.min' => '価格は0以上で入力してください。',
            'condition.required' => '商品の状態は必須です。',
            'condition.in' => '商品の状態は選択肢から選んでください。',
            'category_ids.required' => 'カテゴリーは必須です。',
            'category_ids.array' => 'カテゴリーは配列で送信してください。',
            'category_ids.min' => '少なくとも1つのカテゴリーを選択してください。',
            'category_ids.*.exists' => '選択されたカテゴリーは存在しません。',
            'imgUrl.required' => '商品画像は必須です。',
            'imgUrl.image' => '画像ファイルを選択してください。',
            'imgUrl.mimes' => '画像はjpeg、png、jpg、gif形式で選択してください。',
            'imgUrl.max' => '画像サイズは2MB以下で選択してください。',
        ];
    }
}
