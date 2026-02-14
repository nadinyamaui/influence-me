<?php

namespace App\Livewire\Forms;

use App\Enums\ClientType;
use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClientForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $company_name = '';

    public string $type = ClientType::Brand->value;

    public string $phone = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ClientType::class)],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function setClient(Client $client): void
    {
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->company_name = $client->company_name ?? '';
        $this->type = $client->type->value;
        $this->phone = $client->phone ?? '';
        $this->notes = $client->notes ?? '';
    }

    public function payload(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email !== '' ? $this->email : null,
            'company_name' => $this->company_name !== '' ? $this->company_name : null,
            'type' => $this->type,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ];
    }
}
