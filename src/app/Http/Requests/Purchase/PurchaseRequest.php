<?php

namespace App\Http\Requests\Purchase;

use App\Domain\Purchase\ValueObjects\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
      'payment_method' => 'required|in:' . implode(',', array_keys(PaymentMethod::getOptions())),
      'payment_method_id' => 'nullable|string', // Stripe Payment Method ID
      'postcode' => 'required|string|max:8',
      'address' => 'required|string|max:255',
      'buildingName' => 'nullable|string|max:255',
    ];
  }
}
