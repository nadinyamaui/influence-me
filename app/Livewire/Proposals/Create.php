<?php

namespace App\Livewire\Proposals;

use App\Enums\MediaType;
use App\Http\Requests\StoreProposalRequest;
use App\Models\Campaign;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\ProposalWorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public string $title = '';

    public string $client_id = '';

    public string $content = '';

    public bool $previewMode = false;

    public array $campaigns = [];

    public function mount(): void
    {
        $this->authorize('create', Proposal::class);
        $this->campaigns = [self::emptyCampaign()];
    }

    protected function rules(): array
    {
        return StoreProposalRequest::rulesFor((int) auth()->id(), $this->client_id !== '' ? (int) $this->client_id : null);
    }

    public function addCampaign(): void
    {
        $this->campaigns[] = self::emptyCampaign();
    }

    public function removeCampaign(int $campaignIndex): void
    {
        unset($this->campaigns[$campaignIndex]);
        $this->campaigns = array_values($this->campaigns);

        if ($this->campaigns === []) {
            $this->campaigns = [self::emptyCampaign()];
        }
    }

    public function addScheduledItem(int $campaignIndex): void
    {
        $this->campaigns[$campaignIndex]['scheduled_items'][] = self::emptyScheduledItem();
    }

    public function removeScheduledItem(int $campaignIndex, int $scheduledItemIndex): void
    {
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

    public function save(ProposalWorkflowService $proposalWorkflowService)
    {
        $this->authorize('create', Proposal::class);

        $validated = $this->validate();

        $proposalWorkflowService->createDraftWithCampaignSchedule(auth()->user(), $validated);

        session()->flash('status', 'Proposal created.');

        return $this->redirectRoute('proposals.index', navigate: true);
    }

    public function render()
    {
        return view('pages.proposals.create', [
            'clients' => User::availableClients(),
            'instagramAccounts' => User::accounts(),
            'availableCampaigns' => $this->availableCampaigns(),
            'mediaTypes' => MediaType::cases(),
        ])->layout('layouts.app', [
            'title' => __('Create Proposal'),
        ]);
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
}
