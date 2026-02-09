# 032 - Client Create Form

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #006, #012

## Description

Create a Livewire page at `/clients/create` with a form to add a new client.

## Implementation

### Create Route
```php
Route::livewire('clients/create', 'clients.create')
    ->middleware(['auth'])
    ->name('clients.create');
```

### Create Form Request
`App\Http\Requests\StoreClientRequest`:
- `name`: required, string, max:255
- `email`: nullable, string, email, max:255
- `company_name`: nullable, string, max:255
- `type`: required, string, in:brand,individual (or use enum validation)
- `phone`: nullable, string, max:50
- `notes`: nullable, string, max:5000

### Create Livewire Page
`resources/views/pages/clients/create.blade.php`

### Form Fields (Flux UI)
- `<flux:input>` for name (required)
- `<flux:input>` for email
- `<flux:input>` for company_name
- `<flux:select>` for type (Brand, Individual)
- `<flux:input>` for phone
- `<flux:textarea>` for notes
- Cancel button (back to clients list)
- Save button

### Livewire Logic
- Validate using the Form Request rules
- Create `Client` with `auth()->user()->clients()->create($data)`
- Redirect to client list with success flash message

## Files to Create
- `resources/views/pages/clients/create.blade.php`
- `app/Http/Requests/StoreClientRequest.php`

## Files to Modify
- `routes/web.php` — add route

## Acceptance Criteria
- [ ] Form renders at `/clients/create`
- [ ] All fields validate correctly
- [ ] Client created and belongs to authenticated user
- [ ] Redirects to clients list with success message
- [ ] Cancel returns to clients list
- [ ] Feature test verifies creation with valid and invalid data
