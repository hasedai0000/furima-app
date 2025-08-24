<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'postcode' => 'required|string|max:8',
      'address' => 'required|string|max:255',
      'buildingName' => 'nullable|string|max:255',
    ];
  }
}
