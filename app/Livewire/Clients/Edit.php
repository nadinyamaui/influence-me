<?php

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public ClientForm $form;

    public bool $confirmingDelete = false;

    public function mount(Client $client): void
    {
        $this->authorize('update', $client);

        $this->client = $client;
        $this->form->setClient($client);
    }

    public function save()
    {
        $this->authorize('update', $this->client);

        $this->form->validate();

        $this->client->update($this->form->payload());

        Flux::toast('Client updated successfully.', variant: 'success');

        return $this->redirectRoute('clients.show', ['client' => $this->client->id], navigate: true);
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
        Flux::toast('Client deleted successfully.', variant: 'success');

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
