<?php

use App\Livewire\Clients\Show;
use App\Mail\ClientPortalInvitation;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('inviting a client to portal creates client user and sends welcome email', function (): void {
    Mail::fake();

    $owner = User::factory()->create(['name' => 'Nadin Creator']);
    $client = Client::factory()->for($owner)->create([
        'name' => 'Avery Client',
        'email' => 'avery@example.test',
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->call('inviteToPortal')
        ->assertHasNoErrors();

    $clientUser = ClientUser::query()
        ->where('client_id', $client->id)
        ->first();

    expect($clientUser)->not->toBeNull()
        ->and($clientUser?->email)->toBe('avery@example.test')
        ->and($clientUser?->name)->toBe('Avery Client');

    Mail::assertSent(ClientPortalInvitation::class, function (ClientPortalInvitation $mail) use ($client): bool {
        $rendered = $mail->render();

        return $mail->hasTo($client->email)
            && filled($mail->temporaryPassword)
            && str_contains($rendered, route('portal.login'))
            && str_contains($rendered, $mail->temporaryPassword)
            && str_contains($rendered, 'Nadin Creator');
    });
});

test('cannot invite a client to portal when email is missing', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create([
        'email' => null,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->call('inviteToPortal')
        ->assertHasErrors(['invite']);

    $this->assertDatabaseMissing('client_users', [
        'client_id' => $client->id,
    ]);

    Mail::assertNothingSent();
});

test('revoke portal access removes existing client user account', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $clientUser = ClientUser::factory()->for($client)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->call('confirmRevokePortalAccess')
        ->assertSet('confirmingRevokePortalAccess', true)
        ->call('revokePortalAccess')
        ->assertHasNoErrors()
        ->assertSet('confirmingRevokePortalAccess', false);

    $this->assertDatabaseMissing('client_users', ['id' => $clientUser->id]);
});

test('client detail page shows portal status and actions based on portal access state', function (): void {
    $owner = User::factory()->create();

    $noPortalClient = Client::factory()->for($owner)->create([
        'email' => 'invite@example.test',
    ]);

    $response = $this->actingAs($owner)->get(route('clients.show', $noPortalClient));

    $response->assertSuccessful()
        ->assertSee('Portal access: No portal access')
        ->assertSee('Invite to Portal')
        ->assertDontSee('Revoke Portal Access');

    $noEmailClient = Client::factory()->for($owner)->create([
        'email' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('clients.show', $noEmailClient))
        ->assertSuccessful()
        ->assertSee('Portal access: No portal access')
        ->assertSee('Add an email to enable portal access.')
        ->assertDontSee('Invite to Portal');

    $activePortalClient = Client::factory()->for($owner)->create([
        'email' => 'active@example.test',
    ]);
    ClientUser::factory()->for($activePortalClient)->create();

    $this->actingAs($owner)
        ->get(route('clients.show', $activePortalClient))
        ->assertSuccessful()
        ->assertSee('Portal access: Active')
        ->assertSee('Revoke Portal Access')
        ->assertDontSee('Invite to Portal');
});
