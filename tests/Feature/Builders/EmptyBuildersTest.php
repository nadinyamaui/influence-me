<?php

use App\Builders\ClientUserBuilder;
use App\Builders\InvoiceItemBuilder;
use App\Builders\ScheduledPostBuilder;
use App\Builders\UserBuilder;
use App\Models\ClientUser;
use App\Models\InvoiceItem;
use App\Models\ScheduledPost;
use App\Models\User;

it('uses dedicated builder classes for models without custom query methods yet', function (): void {
    expect(User::query())->toBeInstanceOf(UserBuilder::class)
        ->and(ClientUser::query())->toBeInstanceOf(ClientUserBuilder::class)
        ->and(InvoiceItem::query())->toBeInstanceOf(InvoiceItemBuilder::class)
        ->and(ScheduledPost::query())->toBeInstanceOf(ScheduledPostBuilder::class);
});
