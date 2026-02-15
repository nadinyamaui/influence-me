<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Enums\ScheduledPostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduledPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesFor($this->isMethod('post'));
    }

    public static function rulesFor(bool $isCreating): array
    {
        return self::baseRules($isCreating);
    }

    public static function rulesForLivewire(bool $isCreating): array
    {
        $baseRules = self::baseRules($isCreating);

        return [
            'title' => $baseRules['title'],
            'description' => $baseRules['description'],
            'clientId' => $baseRules['client_id'],
            'campaignId' => $baseRules['campaign_id'],
            'mediaType' => $baseRules['media_type'],
            'instagramAccountId' => $baseRules['instagram_account_id'],
            'scheduledAt' => $baseRules['scheduled_at'],
            'status' => $baseRules['status'],
        ];
    }

    private static function baseRules(bool $isCreating): array
    {
        $scheduledAtRules = ['required', 'date'];

        if ($isCreating) {
            $scheduledAtRules[] = 'after:now';
        }

        $clientRules = ['nullable', 'integer'];

        if ($isCreating) {
            $clientRules = ['required', 'integer'];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client_id' => $clientRules,
            'campaign_id' => ['nullable', 'integer'],
            'media_type' => ['required', Rule::enum(MediaType::class)],
            'instagram_account_id' => ['required', 'integer'],
            'scheduled_at' => $scheduledAtRules,
            'status' => ['required', Rule::enum(ScheduledPostStatus::class)],
        ];
    }
}
