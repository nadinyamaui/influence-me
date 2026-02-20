<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::initialRules();
    }

    public static function initialRules(?int $userId = null): array
    {
        $userId ??= auth()->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'bundle_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.catalog_product_id' => [
                'required',
                'integer',
                Rule::exists('catalog_products', 'id')->where(static function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price_override' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
