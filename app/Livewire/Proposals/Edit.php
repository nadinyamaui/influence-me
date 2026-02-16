<?php

namespace App\Livewire\Proposals;

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Proposal $proposal;

    public string $title = '';

    public string $client_id = '';

    public string $content = '';

    public bool $previewMode = false;

    public int $currentStep = 1;

    public array $campaigns = [];

    public array $scheduledItems = [];

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);

        $this->proposal = $proposal;
        $this->fillFromProposal();
    }

    public function addCampaign(): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        $this->campaigns[] = self::emptyCampaign();
    }

    public function removeCampaign(int $campaignIndex): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        unset($this->campaigns[$campaignIndex]);
        $this->campaigns = array_values($this->campaigns);

        if ($this->campaigns === []) {
            $this->campaigns = [self::emptyCampaign()];
        }

        $this->scheduledItems = collect($this->scheduledItems)
            ->map(function (array $scheduledItem) use ($campaignIndex): ?array {
                $itemCampaignIndex = (int) ($scheduledItem['campaign_index'] ?? 0);

                if ($itemCampaignIndex === $campaignIndex) {
                    return null;
                }

                if ($itemCampaignIndex > $campaignIndex) {
                    $itemCampaignIndex--;
                }

                return [
                    'id' => $scheduledItem['id'] ?? null,
                    'campaign_index' => $itemCampaignIndex,
                    'title' => $scheduledItem['title'] ?? '',
                    'description' => $scheduledItem['description'] ?? '',
                    'media_type' => $scheduledItem['media_type'] ?? MediaType::Post->value,
                    'instagram_account_id' => $scheduledItem['instagram_account_id'] ?? '',
                    'scheduled_at' => $scheduledItem['scheduled_at'] ?? '',
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($this->scheduledItems === []) {
            $this->scheduledItems = [self::emptyScheduledItem()];
        }
    }

    public function addScheduledItem(): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        $this->scheduledItems[] = self::emptyScheduledItem();
    }

    public function removeScheduledItem(int $scheduledItemIndex): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        unset($this->scheduledItems[$scheduledItemIndex]);
        $this->scheduledItems = array_values($this->scheduledItems);

        if ($this->scheduledItems === []) {
            $this->scheduledItems = [self::emptyScheduledItem()];
        }
    }

    public function goToStep(int $step): void
    {
        $this->currentStep = max(1, min(3, $step));
    }

    public function nextStep(ProposalWorkflowService $proposalWorkflowService): void
    {
        $this->persistDraft($proposalWorkflowService);

        $this->goToStep($this->currentStep + 1);
    }

    public function previousStep(ProposalWorkflowService $proposalWorkflowService): void
    {
        $this->persistDraft($proposalWorkflowService);

        $this->goToStep($this->currentStep - 1);
    }

    public function togglePreview(): void
    {
        $this->previewMode = ! $this->previewMode;
    }

    public function update(ProposalWorkflowService $proposalWorkflowService)
    {
        $this->authorize('update', $this->proposal);

        if (! $this->isEditable()) {
            abort(403);
        }

        $proposalWorkflowService->updateDraftWithCampaignSchedule(auth()->user(), $this->proposal, $this->buildPayload());

        session()->flash('status', 'Proposal updated.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function delete()
    {
        $this->authorize('delete', $this->proposal);

        $this->proposal->delete();

        session()->flash('status', 'Proposal deleted.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function isEditable(): bool
    {
        return in_array($this->proposal->status, [ProposalStatus::Draft, ProposalStatus::Revised], true);
    }

    public function render()
    {
        return view('pages.proposals.edit', [
            'clients' => User::availableClients(),
            'instagramAccounts' => User::accounts(),
            'mediaTypes' => MediaType::cases(),
        ])->layout('layouts.app', [
            'title' => __('Edit Proposal'),
        ]);
    }

    private function fillFromProposal(): void
    {
        $this->proposal->loadMissing('campaigns.scheduledPosts');

        $this->title = $this->proposal->title;
        $this->client_id = (string) $this->proposal->client_id;
        $this->content = $this->proposal->content;

        $this->campaigns = $this->proposal->campaigns
            ->values()
            ->map(fn ($campaign): array => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'description' => $campaign->description ?? '',
            ])
            ->all();

        if ($this->campaigns === []) {
            $this->campaigns = [self::emptyCampaign()];
        }

        $this->scheduledItems = [];

        foreach ($this->proposal->campaigns->values() as $campaignIndex => $campaign) {
            foreach ($campaign->scheduledPosts->sortBy('scheduled_at')->values() as $scheduledPost) {
                $this->scheduledItems[] = [
                    'id' => $scheduledPost->id,
                    'campaign_index' => $campaignIndex,
                    'title' => $scheduledPost->title,
                    'description' => $scheduledPost->description ?? '',
                    'media_type' => $scheduledPost->media_type->value,
                    'instagram_account_id' => (string) $scheduledPost->instagram_account_id,
                    'scheduled_at' => $scheduledPost->scheduled_at->format('Y-m-d\TH:i'),
                ];
            }
        }

        if ($this->scheduledItems === []) {
            $this->scheduledItems = [self::emptyScheduledItem()];
        }
    }

    private function buildPayload(): array
    {
        $campaigns = collect($this->campaigns)
            ->values()
            ->map(fn (array $campaign): array => [
                'id' => filled($campaign['id'] ?? null) ? (int) $campaign['id'] : null,
                'name' => $campaign['name'] ?? '',
                'description' => $campaign['description'] ?? '',
                'scheduled_items' => [],
            ])
            ->all();

        foreach ($this->scheduledItems as $scheduledItem) {
            $campaignIndex = (int) ($scheduledItem['campaign_index'] ?? 0);

            if (! array_key_exists($campaignIndex, $campaigns)) {
                $campaignIndex = 0;
            }

            $campaigns[$campaignIndex]['scheduled_items'][] = [
                'id' => filled($scheduledItem['id'] ?? null) ? (int) $scheduledItem['id'] : null,
                'title' => $scheduledItem['title'] ?? '',
                'description' => $scheduledItem['description'] ?? '',
                'media_type' => $scheduledItem['media_type'] ?? MediaType::Post->value,
                'instagram_account_id' => $scheduledItem['instagram_account_id'] ?? '',
                'scheduled_at' => $scheduledItem['scheduled_at'] ?? '',
            ];
        }

        return [
            'title' => $this->title,
            'client_id' => $this->client_id,
            'content' => $this->content,
            'campaigns' => $campaigns,
        ];
    }

    private static function emptyCampaign(): array
    {
        return [
            'id' => null,
            'name' => '',
            'description' => '',
        ];
    }

    private static function emptyScheduledItem(): array
    {
        return [
            'id' => null,
            'campaign_index' => 0,
            'title' => '',
            'description' => '',
            'media_type' => MediaType::Post->value,
            'instagram_account_id' => '',
            'scheduled_at' => '',
        ];
    }

    private function persistDraft(ProposalWorkflowService $proposalWorkflowService): void
    {
        if (! $this->isEditable()) {
            return;
        }

        $proposalWorkflowService->updateDraftWithCampaignSchedule(auth()->user(), $this->proposal, $this->buildPayload());
    }
}
