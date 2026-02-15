<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::initialRulesFor((int) $this->user()?->id);
    }

    public static function initialRulesFor(int $userId): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(
                    fn ($query) => $query->where('user_id', $userId),
                ),
            ],
        ];
    }
}
