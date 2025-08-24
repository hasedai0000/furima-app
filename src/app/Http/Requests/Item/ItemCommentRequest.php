<?php

namespace App\Http\Requests\Item;

use App\Domain\Item\ValueObjects\ItemCondition;
use Illuminate\Foundation\Http\FormRequest;

class ItemCommentRequest extends FormRequest
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
            'content' => 'required|string|max:255',
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
            'content.required' => 'コメントが入力されていません。',
            'content.max' => 'コメントは255文字以内で入力してください。',
        ];
    }
}
