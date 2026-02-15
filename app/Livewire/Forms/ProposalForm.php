<?php

namespace App\Livewire\Forms;

use App\Enums\MediaType;
use App\Models\Campaign;
use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProposalForm extends Form
{
    public string $title = '';

    public string $client_id = '';

    public string $content = '';

    /** @var array<int, array{id: string|null, name: string, description: string, scheduled_items: array<int, array{title: string, description: string, media_type: string, instagram_account_id: string, scheduled_at: string}>}> */
    public array $campaigns = [];

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'title' => ['required', 'string', 'max:255'],
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $userId),
            ],
            'content' => ['required', 'string', 'max:50000'],
            'campaigns' => ['required', 'array', 'min:1'],
            'campaigns.*.id' => ['nullable'],
            'campaigns.*.name' => ['required', 'string', 'max:255'],
            'campaigns.*.description' => ['nullable', 'string', 'max:5000'],
            'campaigns.*.scheduled_items' => ['required', 'array', 'min:1'],
            'campaigns.*.scheduled_items.*.title' => ['required', 'string', 'max:255'],
            'campaigns.*.scheduled_items.*.description' => ['nullable', 'string', 'max:5000'],
            'campaigns.*.scheduled_items.*.media_type' => ['required', Rule::enum(MediaType::class)],
            'campaigns.*.scheduled_items.*.instagram_account_id' => [
                'required',
                Rule::exists('instagram_accounts', 'id')->where('user_id', $userId),
            ],
            'campaigns.*.scheduled_items.*.scheduled_at' => ['required', 'date'],
        ];
    }

    protected function messages(): array
    {
        return [
            'campaigns.required' => 'At least one campaign is required.',
            'campaigns.min' => 'At least one campaign is required.',
            'campaigns.*.name.required' => 'Campaign name is required.',
            'campaigns.*.scheduled_items.required' => 'Each campaign requires at least one scheduled content item.',
            'campaigns.*.scheduled_items.min' => 'Each campaign requires at least one scheduled content item.',
            'campaigns.*.scheduled_items.*.title.required' => 'Content title is required.',
            'campaigns.*.scheduled_items.*.media_type.required' => 'Content type is required.',
            'campaigns.*.scheduled_items.*.instagram_account_id.required' => 'Instagram account is required.',
            'campaigns.*.scheduled_items.*.scheduled_at.required' => 'Scheduled date is required.',
        ];
    }

    public function setProposal(Proposal $proposal): void
    {
        $this->title = $proposal->title;
        $this->client_id = (string) $proposal->client_id;
        $this->content = $proposal->content;

        $proposal->load('campaigns.scheduledPosts');

        $this->campaigns = $proposal->campaigns->map(fn (Campaign $campaign): array => [
            'id' => (string) $campaign->id,
            'name' => $campaign->name,
            'description' => $campaign->description ?? '',
            'scheduled_items' => $campaign->scheduledPosts->map(fn ($post): array => [
                'title' => $post->title,
                'description' => $post->description ?? '',
                'media_type' => $post->media_type->value,
                'instagram_account_id' => (string) $post->instagram_account_id,
                'scheduled_at' => $post->scheduled_at->format('Y-m-d\TH:i'),
            ])->values()->all(),
        ])->values()->all();

        if (empty($this->campaigns)) {
            $this->addCampaign();
        }
    }

    public function addCampaign(): void
    {
        $this->campaigns[] = [
            'id' => null,
            'name' => '',
            'description' => '',
            'scheduled_items' => [
                $this->emptyScheduledItem(),
            ],
        ];
    }

    public function removeCampaign(int $index): void
    {
        if (count($this->campaigns) <= 1) {
            return;
        }

        unset($this->campaigns[$index]);
        $this->campaigns = array_values($this->campaigns);
    }

    public function addScheduledItem(int $campaignIndex): void
    {
        $this->campaigns[$campaignIndex]['scheduled_items'][] = $this->emptyScheduledItem();
    }

    public function removeScheduledItem(int $campaignIndex, int $itemIndex): void
    {
        if (count($this->campaigns[$campaignIndex]['scheduled_items']) <= 1) {
            return;
        }

        unset($this->campaigns[$campaignIndex]['scheduled_items'][$itemIndex]);
        $this->campaigns[$campaignIndex]['scheduled_items'] = array_values(
            $this->campaigns[$campaignIndex]['scheduled_items'],
        );
    }

    private function emptyScheduledItem(): array
    {
        return [
            'title' => '',
            'description' => '',
            'media_type' => MediaType::Post->value,
            'instagram_account_id' => '',
            'scheduled_at' => '',
        ];
    }
}
