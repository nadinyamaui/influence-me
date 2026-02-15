<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Models\Client;
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
        return self::rulesFor((int) $this->user()?->id, $this->integer('client_id'));
    }

    public static function rulesFor(int $userId, ?int $clientId): array
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
            'content' => ['required', 'string', 'max:50000'],
            'campaigns' => ['required', 'array', 'min:1'],
            'campaigns.*.id' => [
                'nullable',
                'integer',
                Rule::exists('campaigns', 'id')->where(function ($query) use ($userId, $clientId): void {
                    $query->whereIn('client_id', Client::query()->where('user_id', $userId)->select('id'));

                    if ($clientId !== null) {
                        $query->where('client_id', $clientId);
                    }
                }),
            ],
            'campaigns.*.name' => ['required_without:campaigns.*.id', 'string', 'max:255'],
            'campaigns.*.description' => ['nullable', 'string', 'max:5000'],
            'campaigns.*.scheduled_items' => ['required', 'array', 'min:1'],
            'campaigns.*.scheduled_items.*.id' => [
                'nullable',
                'integer',
                Rule::exists('scheduled_posts', 'id')->where(
                    fn ($query) => $query->where('user_id', $userId),
                ),
            ],
            'campaigns.*.scheduled_items.*.title' => ['required', 'string', 'max:255'],
            'campaigns.*.scheduled_items.*.description' => ['nullable', 'string', 'max:5000'],
            'campaigns.*.scheduled_items.*.media_type' => [
                'required',
                Rule::in(array_map(
                    static fn (MediaType $mediaType): string => $mediaType->value,
                    MediaType::cases(),
                )),
            ],
            'campaigns.*.scheduled_items.*.instagram_account_id' => [
                'required',
                'integer',
                Rule::exists('instagram_accounts', 'id')->where(
                    fn ($query) => $query->where('user_id', $userId),
                ),
            ],
            'campaigns.*.scheduled_items.*.scheduled_at' => ['required', 'date'],
        ];
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
