<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public string $company_name = '';

    public string $type = ClientType::Brand->value;

    public string $phone = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('create', Client::class);
    }

    public function save()
    {
        $this->authorize('create', Client::class);

        $validated = $this->validate((new StoreClientRequest())->rules());

        Auth::user()->clients()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?: null,
            'company_name' => $validated['company_name'] ?: null,
            'type' => $validated['type'],
            'phone' => $validated['phone'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ]);

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
