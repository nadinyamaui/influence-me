<?php

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public ClientForm $form;

    public function mount(): void
    {
        $this->authorize('create', Client::class);
    }

    public function save()
    {
        $this->authorize('create', Client::class);

        $this->form->validate();

        Auth::user()->clients()->create($this->form->payload());

        session()->flash('status', 'Client created successfully.');

        return $this->redirectRoute('clients.index', navigate: true);
    }

    public function render()
    {
        return view('pages.clients.create')
            ->layout('layouts.app', [
                'title' => __('Add Client'),
            ]);
    }
}
