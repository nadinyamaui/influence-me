<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
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
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(static function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }),
            ],
            'due_date' => ['required', 'date', 'after:today'],
            'tax_id' => [
                'nullable',
                'integer',
                Rule::exists('tax_rates', 'id')->where(static function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }),
            ],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.catalog_product_id' => [
                'nullable',
                'integer',
                Rule::exists('catalog_products', 'id')->where(static function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }),
            ],
            'items.*.catalog_plan_id' => [
                'nullable',
                'integer',
                Rule::exists('catalog_plans', 'id')->where(static function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                }),
            ],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
