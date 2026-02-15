<?php

namespace App\Livewire\Proposals;

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Http\Requests\StoreProposalRequest;
use App\Models\Campaign;
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

    public bool $confirmingDelete = false;

    public array $campaigns = [];

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);

        $this->proposal = $proposal;
        $this->fillFromProposal();
    }

    protected function rules(): array
    {
        return StoreProposalRequest::rulesFor((int) auth()->id(), $this->client_id !== '' ? (int) $this->client_id : null);
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
    }

    public function addScheduledItem(int $campaignIndex): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        $this->campaigns[$campaignIndex]['scheduled_items'][] = self::emptyScheduledItem();
    }

    public function removeScheduledItem(int $campaignIndex, int $scheduledItemIndex): void
    {
        if (! $this->isEditable()) {
            abort(403);
        }

        unset($this->campaigns[$campaignIndex]['scheduled_items'][$scheduledItemIndex]);
        $this->campaigns[$campaignIndex]['scheduled_items'] = array_values($this->campaigns[$campaignIndex]['scheduled_items']);

        if ($this->campaigns[$campaignIndex]['scheduled_items'] === []) {
            $this->campaigns[$campaignIndex]['scheduled_items'] = [self::emptyScheduledItem()];
        }
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

        $validated = $this->validate();

        $proposalWorkflowService->updateDraftWithCampaignSchedule(auth()->user(), $this->proposal, $validated);

        session()->flash('status', 'Proposal updated.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->proposal);

        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function delete()
    {
        $this->authorize('delete', $this->proposal);

        $this->proposal->delete();

        session()->flash('status', 'Proposal deleted.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function duplicate(ProposalWorkflowService $proposalWorkflowService)
    {
        $this->authorize('view', $this->proposal);

        $duplicate = $proposalWorkflowService->duplicate(auth()->user(), $this->proposal);

        session()->flash('status', 'Proposal duplicated as draft.');

        return $this->redirectRoute('proposals.edit', ['proposal' => $duplicate->id], navigate: true);
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
            'availableCampaigns' => $this->availableCampaigns(),
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
            ->map(fn ($campaign): array => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'description' => $campaign->description ?? '',
                'scheduled_items' => $campaign->scheduledPosts
                    ->sortBy('scheduled_at')
                    ->values()
                    ->map(fn ($scheduledPost): array => [
                        'id' => $scheduledPost->id,
                        'title' => $scheduledPost->title,
                        'description' => $scheduledPost->description ?? '',
                        'media_type' => $scheduledPost->media_type->value,
                        'instagram_account_id' => (string) $scheduledPost->instagram_account_id,
                        'scheduled_at' => $scheduledPost->scheduled_at->format('Y-m-d\TH:i'),
                    ])
                    ->all(),
            ])
            ->all();

        if ($this->campaigns === []) {
            $this->campaigns = [self::emptyCampaign()];
        }

        foreach ($this->campaigns as $index => $campaign) {
            if ($campaign['scheduled_items'] === []) {
                $this->campaigns[$index]['scheduled_items'] = [self::emptyScheduledItem()];
            }
        }
    }

    private static function emptyCampaign(): array
    {
        return [
            'id' => null,
            'name' => '',
            'description' => '',
            'scheduled_items' => [self::emptyScheduledItem()],
        ];
    }

    private static function emptyScheduledItem(): array
    {
        return [
            'id' => null,
            'title' => '',
            'description' => '',
            'media_type' => MediaType::Post->value,
            'instagram_account_id' => '',
            'scheduled_at' => '',
        ];
    }

    private function availableCampaigns()
    {
        if ($this->client_id === '' || ! is_numeric($this->client_id)) {
            return collect();
        }

        return Campaign::query()
            ->where('client_id', (int) $this->client_id)
            ->whereHas('client', fn ($query) => $query->where('user_id', auth()->id()))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
