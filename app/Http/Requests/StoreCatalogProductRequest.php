<?php

namespace App\Http\Requests;

use App\Enums\BillingUnitType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::initialRules();
    }

    public static function initialRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', Rule::in(PlatformType::values())],
            'media_type' => ['nullable', Rule::in(MediaType::values())],
            'billing_unit' => ['required', Rule::in(BillingUnitType::values())],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
