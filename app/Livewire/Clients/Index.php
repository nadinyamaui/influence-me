<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $type = 'all';

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
        if (! in_array($this->type, ClientType::filters(), true)) {
            $this->type = 'all';
        }

        $this->resetPage();
    }

    public function delete(int $clientId): void
    {
        $client = User::resolveClient($clientId);
        $this->authorize('delete', $client);

        $this->resetErrorBag('delete');

        $client->delete();

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

    private function clients(): LengthAwarePaginator
    {
        $query = Auth::user()->clients()
            ->withCount('campaigns')
            ->search($this->search)
            ->filterByType($this->type)
            ->orderBy('name');

        return $query->paginate(10);
    }
}
