<?php

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Carbon\CarbonImmutable;

use function Pest\Laravel\actingAs;

it('scopes invoices to the authenticated user when no user id is passed', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    $ownerInvoice = Invoice::factory()->for($owner)->for($ownerClient)->create();
    Invoice::factory()->for($outsider)->for($outsiderClient)->create();

    actingAs($owner);

    $ids = Invoice::query()
        ->forUser()
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$ownerInvoice->id]);
});

it('scopes invoices by client and status', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $otherClient = Client::factory()->for($owner)->create();

    $matchingInvoice = Invoice::factory()->for($owner)->for($client)->create([
        'status' => InvoiceStatus::Sent,
    ]);

    Invoice::factory()->for($owner)->for($client)->create([
        'status' => InvoiceStatus::Draft,
    ]);

    Invoice::factory()->for($owner)->for($otherClient)->create([
        'status' => InvoiceStatus::Sent,
    ]);

    $ids = Invoice::query()
        ->forUser($owner->id)
        ->forClient($client->id)
        ->filterByStatus(InvoiceStatus::Sent->value)
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$matchingInvoice->id]);
});

it('returns pending and overdue invoice scopes', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $sentInvoice = Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Sent,
    ]);
    $overdueInvoice = Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Overdue,
    ]);

    Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Draft,
    ]);
    Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Paid,
    ]);

    $pendingIds = Invoice::query()
        ->forUser($user->id)
        ->pending()
        ->pluck('id')
        ->all();

    $overdueIds = Invoice::query()
        ->forUser($user->id)
        ->overdue()
        ->pluck('id')
        ->all();

    expect($pendingIds)->toEqualCanonicalizing([$sentInvoice->id, $overdueInvoice->id])
        ->and($overdueIds)->toBe([$overdueInvoice->id]);
});

it('filters paid invoices for a month and orders newest first', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $olderPaidInvoice = Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Paid,
        'paid_at' => '2026-02-02 10:00:00',
        'created_at' => '2026-02-01 10:00:00',
    ]);

    $newerPaidInvoice = Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Paid,
        'paid_at' => '2026-02-18 10:00:00',
        'created_at' => '2026-02-17 10:00:00',
    ]);

    Invoice::factory()->for($user)->for($client)->create([
        'status' => InvoiceStatus::Paid,
        'paid_at' => '2026-01-20 10:00:00',
        'created_at' => '2026-01-18 10:00:00',
    ]);

    $ids = Invoice::query()
        ->forUser($user->id)
        ->paidInMonth(CarbonImmutable::parse('2026-02-20 10:00:00'))
        ->latestFirst()
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$newerPaidInvoice->id, $olderPaidInvoice->id]);
});
