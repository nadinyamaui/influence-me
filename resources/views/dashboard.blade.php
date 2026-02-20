<x-layouts::app :title="__('Dashboard')">
    @php
        $hasLinkedSocialAccount = auth()->user()?->socialAccounts()->exists() ?? false;
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        @if (! $hasLinkedSocialAccount)
            <section class="rounded-2xl border border-sky-200 bg-sky-50 p-6 text-sky-950 dark:border-sky-800 dark:bg-sky-950/20 dark:text-sky-100">
                <h2 class="text-xl font-semibold">Explore your dashboard with demo-ready tools</h2>
                <p class="mt-2 text-sm text-sky-800 dark:text-sky-200">
                    You can start organizing clients, planning content, and preparing proposals or invoices right away.
                    Connecting Instagram is optional and can be done later when you are ready.
                </p>
                <div class="mt-4 grid gap-3 text-sm text-sky-900 dark:text-sky-100 md:grid-cols-2">
                    <div class="rounded-xl border border-sky-200 bg-white/80 p-4 dark:border-sky-800 dark:bg-sky-950/30">
                        Client CRM and portal setup
                    </div>
                    <div class="rounded-xl border border-sky-200 bg-white/80 p-4 dark:border-sky-800 dark:bg-sky-950/30">
                        Proposal and invoice workflows
                    </div>
                    <div class="rounded-xl border border-sky-200 bg-white/80 p-4 dark:border-sky-800 dark:bg-sky-950/30">
                        Content scheduling timeline planning
                    </div>
                    <div class="rounded-xl border border-sky-200 bg-white/80 p-4 dark:border-sky-800 dark:bg-sky-950/30">
                        Analytics dashboards once accounts are connected
                    </div>
                </div>
            </section>
        @endif

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
