# 033 - Client Edit Form

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #006, #012, #032

## Description

Create a Livewire page at `/clients/{client}/edit` with a pre-filled form to update an existing client.

## Implementation

### Create Route
```php
Route::livewire('clients/{client}/edit', 'clients.edit')
    ->middleware(['auth'])
    ->name('clients.edit');
```

### Create Form Request
`App\Http\Requests\UpdateClientRequest` (similar to StoreClientRequest but for updates):
- Same validation rules as create
- Authorize: user owns the client (use policy)

### Create Livewire Page
`resources/views/pages/clients/edit.blade.php`

### Livewire Logic
- Mount: load client, authorize via policy `$this->authorize('update', $client)`
- Pre-fill form with existing data
- On save: validate, update client
- Redirect to client detail page with success message
- Include "Delete" button with confirmation modal

### Delete Flow
- Confirmation modal: "Are you sure? This will also delete all proposals and invoices for this client."
- On confirm: `$client->delete()` (cascade handles related records)
- Redirect to clients list with success message

## Files to Create
- `resources/views/pages/clients/edit.blade.php`
- `app/Http/Requests/UpdateClientRequest.php`

## Files to Modify
- `routes/web.php` — add route

## Acceptance Criteria
- [ ] Form renders at `/clients/{client}/edit` with pre-filled data
- [ ] Only the owning user can access (403 for others)
- [ ] Validation works correctly
- [ ] Update saves changes
- [ ] Delete with confirmation works
- [ ] Cascade deletes related records
- [ ] Feature tests cover update, unauthorized access, and delete
