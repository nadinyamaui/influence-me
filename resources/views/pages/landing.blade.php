<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased bg-surface text-text">
        @php
            $benefits = [
                [
                    'title' => 'Save hours every week',
                    'description' => 'Centralize content planning, client tracking, and delivery updates in one place instead of bouncing between tools.',
                ],
                [
                    'title' => 'Send proposals and invoices faster',
                    'description' => 'Move from draft to client-ready docs quickly so you spend less time on admin and more time on paid work.',
                ],
                [
                    'title' => 'Keep clients confident',
                    'description' => 'Give clients clear visibility through their portal so approvals, revisions, and payment steps feel predictable.',
                ],
                [
                    'title' => 'Make smarter growth decisions',
                    'description' => 'Use analytics snapshots to understand what content performs and where your next campaign should focus.',
                ],
            ];

            $features = [
                [
                    'title' => 'Instagram sync + insights',
                    'description' => 'Connect Instagram accounts, sync media, and review performance data without manual exports.',
                ],
                [
                    'title' => 'Client and campaign management',
                    'description' => 'Track active clients, organize campaign deliverables, and keep every engagement scoped and searchable.',
                ],
                [
                    'title' => 'Proposal workflows',
                    'description' => 'Create, preview, send, and track approval or revision requests in a single proposal lifecycle.',
                ],
                [
                    'title' => 'Invoices with payment links',
                    'description' => 'Generate invoices, send secure payment links, and monitor paid or overdue status in one dashboard.',
                ],
                [
                    'title' => 'Client portal visibility',
                    'description' => 'Clients can review proposals, invoices, and analytics from a dedicated portal view.',
                ],
            ];

            $steps = [
                [
                    'title' => 'Connect accounts and import data',
                    'description' => 'Link your Instagram account and pull media plus audience context into your workspace.',
                ],
                [
                    'title' => 'Build proposals and deliverables',
                    'description' => 'Define campaign scope, assemble content plans, and package offers with clear pricing.',
                ],
                [
                    'title' => 'Share with clients, get approved, get paid',
                    'description' => 'Send client-facing links, collect feedback, finalize invoices, and track payments.',
                ],
            ];

            $plans = [
                [
                    'name' => 'Free',
                    'price' => '$0/month',
                    'followers' => 'Fewer than 1,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Growth',
                    'price' => '$25/month',
                    'followers' => '1,000 to 10,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Creator',
                    'price' => '$49/month',
                    'followers' => '10,001 to 99,999 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => true,
                ],
                [
                    'name' => 'Scale',
                    'price' => '$75/month',
                    'followers' => '100,000 to 300,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Pro',
                    'price' => '$100/month',
                    'followers' => '300,000 to 600,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Enterprise',
                    'price' => 'Talk to us',
                    'followers' => 'More than 600,000 followers',
                    'cta' => 'Talk to us',
                    'href' => 'mailto:sales@influenceme.app',
                    'recommended' => false,
                ],
            ];

            $faqs = [
                [
                    'question' => 'Can I cancel anytime?',
                    'answer' => 'Yes. Monthly plans can be cancelled at any time and access remains active through the current billing period.',
                ],
                [
                    'question' => 'Do you support annual billing?',
                    'answer' => 'Not yet. Annual billing is planned after launch and will be announced in-app when available.',
                ],
                [
                    'question' => 'Is there a free trial?',
                    'answer' => 'Creators under 1,000 followers can start on the Free plan. Paid tiers can be upgraded whenever you are ready.',
                ],
                [
                    'question' => 'Can clients access a portal without full accounts?',
                    'answer' => 'Yes. Client users access a dedicated portal scoped to proposals, invoices, and analytics shared with them.',
                ],
            ];
        @endphp

        <div class="relative overflow-x-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[30rem] bg-linear-to-b from-brand/12 via-brand/4 to-transparent"></div>
            <div class="pointer-events-none absolute -left-24 top-32 size-80 rounded-full bg-brand/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-16 top-96 size-72 rounded-full bg-amber-300/20 blur-3xl"></div>

            <header class="sticky top-0 z-20 border-b border-border/80 bg-surface/95 backdrop-blur">
                <nav class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 lg:px-10" aria-label="Main">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-brand text-brand-foreground">
                            <x-app-logo-icon class="size-5" />
                        </span>
                        <span class="font-display text-lg font-semibold tracking-tight">Influence Me</span>
                    </a>

                    <div class="hidden items-center gap-6 text-sm text-text-muted md:flex">
                        <a href="#benefits" class="transition hover:text-text focus-visible:text-text focus-visible:outline-hidden">Benefits</a>
                        <a href="#features" class="transition hover:text-text focus-visible:text-text focus-visible:outline-hidden">Features</a>
                        <a href="#pricing" class="transition hover:text-text focus-visible:text-text focus-visible:outline-hidden">Pricing</a>
                        <a href="#faq" class="transition hover:text-text focus-visible:text-text focus-visible:outline-hidden">FAQ</a>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button :href="route('login')" variant="ghost" class="focus-visible:outline-hidden" wire:navigate>
                            Log In
                        </flux:button>
                        <flux:button :href="route('register')" variant="primary" color="orange" class="focus-visible:outline-hidden" wire:navigate>
                            Get Started
                        </flux:button>
                    </div>
                </nav>
            </header>

            <main class="relative">
                <section class="mx-auto grid w-full max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-10 lg:py-22" aria-labelledby="hero-heading">
                    <div class="space-y-7">
                        <flux:badge color="orange" rounded class="bg-brand/15 text-text">
                            Creator operations, centralized
                        </flux:badge>

                        <div class="space-y-5">
                            <flux:heading id="hero-heading" level="1" size="xl" class="font-display text-4xl leading-tight text-balance sm:text-5xl lg:text-6xl">
                                Turn creator momentum into a repeatable business system.
                            </flux:heading>
                            <p class="max-w-2xl text-lg leading-relaxed text-text-muted">
                                Influence Me brings content tracking, client management, proposals, invoicing, and analytics into one workflow so you can run partnerships like a real operation.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <flux:button :href="route('register')" variant="primary" color="orange" class="focus-visible:outline-hidden" wire:navigate>
                                Get Started
                            </flux:button>
                            <flux:button :href="route('login')" variant="outline" class="border-border bg-surface-muted text-text hover:bg-surface focus-visible:outline-hidden" wire:navigate>
                                Log In
                            </flux:button>
                        </div>
                    </div>

                    <aside class="rounded-3xl border border-border bg-surface-muted/85 p-6 shadow-xs backdrop-blur sm:p-7">
                        <flux:heading size="lg" class="font-display text-xl text-text">What you can run in one place</flux:heading>
                        <ul class="mt-5 space-y-4 text-sm text-text-muted">
                            <li class="flex items-start gap-3">
                                <flux:icon.check variant="mini" class="mt-0.5 text-brand" />
                                Instagram account sync and media insights
                            </li>
                            <li class="flex items-start gap-3">
                                <flux:icon.check variant="mini" class="mt-0.5 text-brand" />
                                Proposal drafting, sending, and approvals
                            </li>
                            <li class="flex items-start gap-3">
                                <flux:icon.check variant="mini" class="mt-0.5 text-brand" />
                                Invoice generation with payment links
                            </li>
                            <li class="flex items-start gap-3">
                                <flux:icon.check variant="mini" class="mt-0.5 text-brand" />
                                Client portal access for visibility
                            </li>
                        </ul>
                    </aside>
                </section>

                <section class="border-y border-border/70 bg-surface-muted/65" aria-label="Social proof">
                    <div class="mx-auto w-full max-w-7xl px-6 py-12 lg:px-10">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div class="space-y-2">
                                <flux:heading level="2" size="xl" class="font-display text-3xl text-text">Proof in progress, built transparently</flux:heading>
                                <p class="max-w-2xl text-sm text-text-muted">
                                    We are in early rollout. These are platform readiness indicators, not customer performance claims.
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                                <flux:badge color="amber" icon="check" rounded>Pilot onboarding active</flux:badge>
                                <flux:badge color="amber" icon="check" rounded>Stripe payment flow ready</flux:badge>
                                <flux:badge color="amber" icon="check" rounded>Client portal in scope</flux:badge>
                                <flux:badge color="amber" icon="check" rounded>Analytics foundation live</flux:badge>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="benefits" class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10" aria-labelledby="benefits-heading">
                    <div class="max-w-3xl space-y-3">
                        <flux:heading id="benefits-heading" level="2" size="xl" class="font-display text-3xl text-text">Why influencers switch to one operating system</flux:heading>
                        <p class="text-text-muted">
                            Clear operational wins without inflated marketing claims.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2">
                        @foreach ($benefits as $benefit)
                            <article class="rounded-2xl border border-border bg-surface p-6 transition-all hover:-translate-y-0.5 hover:shadow-xs">
                                <div class="mb-4 inline-flex size-10 items-center justify-center rounded-xl bg-brand/15 text-brand">
                                    <flux:icon.layout-grid variant="mini" class="size-5" />
                                </div>
                                <h3 class="font-display text-2xl text-text">{{ $benefit['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-text-muted">{{ $benefit['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <flux:separator class="mx-auto w-full max-w-7xl border-border/80" />

                <section id="features" class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10" aria-labelledby="features-heading">
                    <div class="max-w-3xl space-y-3">
                        <flux:heading id="features-heading" level="2" size="xl" class="font-display text-3xl text-text">Feature coverage from content to cash flow</flux:heading>
                        <p class="text-text-muted">
                            Built for influencer workflows across account sync, client delivery, approvals, and payments.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 lg:grid-cols-5 md:grid-cols-2">
                        @foreach ($features as $feature)
                            <article class="rounded-2xl border border-border bg-surface-muted/70 p-5">
                                <div class="mb-3 inline-flex size-8 items-center justify-center rounded-lg bg-brand/15 text-brand">
                                    <flux:icon.book-open-text variant="mini" class="size-4" />
                                </div>
                                <h3 class="text-base font-semibold text-text">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-text-muted">{{ $feature['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="border-y border-border/70 bg-surface-muted/60" aria-labelledby="how-it-works-heading">
                    <div class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10">
                        <div class="space-y-3">
                            <flux:heading id="how-it-works-heading" level="2" size="xl" class="font-display text-3xl text-text">How it works</flux:heading>
                            <p class="max-w-2xl text-text-muted">
                                Start with account data, package client work, and complete approvals and payments in three steps.
                            </p>
                        </div>

                        <ol class="mt-8 grid gap-4 md:grid-cols-3">
                            @foreach ($steps as $index => $step)
                                <li class="rounded-2xl border border-border bg-surface p-6">
                                    <span class="inline-flex size-9 items-center justify-center rounded-full bg-brand text-sm font-semibold text-brand-foreground">
                                        {{ $index + 1 }}
                                    </span>
                                    <h3 class="mt-4 text-lg font-semibold text-text">{{ $step['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-text-muted">{{ $step['description'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

                <section id="pricing" class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10" aria-labelledby="pricing-heading">
                    <div class="max-w-3xl space-y-3">
                        <flux:heading id="pricing-heading" level="2" size="xl" class="font-display text-3xl text-text">Simple pricing by Instagram audience size</flux:heading>
                        <p class="text-text-muted">
                            Monthly plans scale with follower count so costs stay proportional as your audience grows.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($plans as $plan)
                            <article @class([
                                'rounded-3xl border p-6 transition-all',
                                'border-brand bg-brand/8 shadow-xs md:-translate-y-1' => $plan['recommended'],
                                'border-border bg-surface-muted/55' => ! $plan['recommended'],
                            ])>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-display text-2xl text-text">{{ $plan['name'] }}</h3>
                                    @if ($plan['recommended'])
                                        <flux:badge color="orange" rounded>Recommended</flux:badge>
                                    @endif
                                </div>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-text">{{ $plan['price'] }}</p>
                                <p class="mt-2 text-sm text-text-muted">{{ $plan['followers'] }}</p>

                                @if ($plan['name'] === 'Enterprise')
                                    <flux:button :href="$plan['href']" variant="outline" class="mt-6 w-full border-border bg-surface text-text hover:bg-surface-muted">
                                        {{ $plan['cta'] }}
                                    </flux:button>
                                @else
                                    <flux:button :href="$plan['href']" variant="primary" color="orange" class="mt-6 w-full" wire:navigate>
                                        {{ $plan['cta'] }}
                                    </flux:button>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    <p class="mt-5 text-sm text-text-muted">
                        Follower count is determined by the audience size of your connected Instagram account.
                    </p>
                </section>

                <section id="faq" class="border-y border-border/70 bg-surface-muted/55" aria-labelledby="faq-heading">
                    <div class="mx-auto w-full max-w-5xl px-6 py-16 lg:px-10">
                        <flux:heading id="faq-heading" level="2" size="xl" class="font-display text-3xl text-text">Frequently asked questions</flux:heading>

                        <div class="mt-8 space-y-3">
                            @foreach ($faqs as $faq)
                                <details class="group rounded-2xl border border-border bg-surface p-5">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium text-text">
                                        <span>{{ $faq['question'] }}</span>
                                        <flux:icon.chevron-down class="size-4 transition group-open:rotate-180" />
                                    </summary>
                                    <p class="pt-3 text-sm leading-relaxed text-text-muted">{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                </section>
            </main>

            <footer class="mx-auto w-full max-w-7xl px-6 py-16 lg:px-10">
                <div class="rounded-3xl border border-border bg-brand/12 p-8 sm:p-10">
                    <div class="max-w-3xl space-y-4">
                        <flux:heading level="2" size="xl" class="font-display text-3xl text-text">Ready to run your influence business with less overhead?</flux:heading>
                        <p class="text-text-muted">
                            Set up your workspace, connect Instagram, and move client work from idea to payment with one clear system.
                        </p>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <flux:button :href="route('register')" variant="primary" color="orange" wire:navigate>
                            Create Account
                        </flux:button>
                        <flux:button :href="route('login')" variant="outline" class="border-border bg-surface text-text hover:bg-surface-muted" wire:navigate>
                            Log In
                        </flux:button>
                    </div>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
