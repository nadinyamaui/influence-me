<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public string $name = '';

    public string $email = '';

    public string $company_name = '';

    public string $type = ClientType::Brand->value;

    public string $phone = '';

    public string $notes = '';

    public bool $confirmingDelete = false;

    public function mount(Client $client): void
    {
        $this->authorize('update', $client);

        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->company_name = $client->company_name ?? '';
        $this->type = $client->type->value;
        $this->phone = $client->phone ?? '';
        $this->notes = $client->notes ?? '';
    }

    public function save()
    {
        $this->authorize('update', $this->client);

        $validated = $this->validate((new UpdateClientRequest())->rules());

        $this->client->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?: null,
            'company_name' => $validated['company_name'] ?: null,
            'type' => $validated['type'],
            'phone' => $validated['phone'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ]);

        session()->flash('status', 'Client updated successfully.');

        return $this->redirect('/clients/'.$this->client->id, navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->client);

        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function delete()
    {
        $this->authorize('delete', $this->client);

        $this->client->delete();

        $this->confirmingDelete = false;
        session()->flash('status', 'Client deleted successfully.');

        return $this->redirectRoute('clients.index', navigate: true);
    }

    public function render()
    {
        return view('pages.clients.edit')
            ->layout('layouts.app', [
                'title' => __('Edit Client'),
            ]);
    }
}
