<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $type = 'all';

    public ?int $deletingClientId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Client::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        if (! in_array($this->type, array_merge(['all'], $this->filterTypes()), true)) {
            $this->type = 'all';
        }

        $this->resetPage();
    }

    public function confirmDelete(int $clientId): void
    {
        $client = $this->resolveClient($clientId);
        $this->authorize('delete', $client);

        $this->resetErrorBag('delete');
        $this->deletingClientId = $client->id;
    }

    public function cancelDelete(): void
    {
        $this->deletingClientId = null;
    }

    public function delete(): void
    {
        if ($this->deletingClientId === null) {
            return;
        }

        $client = $this->resolveClient($this->deletingClientId);
        $this->authorize('delete', $client);

        $client->delete();

        $this->deletingClientId = null;
        session()->flash('status', 'Client deleted.');
    }

    public function render()
    {
        return view('pages.clients.index', [
            'clients' => $this->clients(),
        ])->layout('layouts.app', [
            'title' => __('Clients'),
        ]);
    }

    public function deletingClient(): ?Client
    {
        if ($this->deletingClientId === null) {
            return null;
        }

        return Auth::user()?->clients()
            ->whereKey($this->deletingClientId)
            ->first();
    }

    private function clients(): LengthAwarePaginator
    {
        $user = Auth::user();

        if ($user === null) {
            abort(403);
        }

        $query = $user->clients()
            ->withCount('instagramMedia')
            ->orderBy('name');

        $search = trim($this->search);
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $searchTerm = '%'.$search.'%';

                $builder->where('name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('company_name', 'like', $searchTerm);
            });
        }

        if (in_array($this->type, $this->filterTypes(), true)) {
            $query->where('type', $this->type);
        }

        return $query->paginate(10);
    }

    private function filterTypes(): array
    {
        return [
            ClientType::Brand->value,
            ClientType::Individual->value,
        ];
    }

    private function resolveClient(int $clientId): Client
    {
        $client = Auth::user()?->clients()
            ->whereKey($clientId)
            ->first();

        if ($client === null) {
            abort(404);
        }

        return $client;
    }
}
