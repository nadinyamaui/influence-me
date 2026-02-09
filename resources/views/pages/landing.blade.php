<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')

        <style>
            .grain::after {
                content: '';
                position: absolute;
                inset: 0;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
                pointer-events: none;
                z-index: 1;
                mix-blend-mode: overlay;
            }

            .stagger-1 { animation-delay: 0.1s; }
            .stagger-2 { animation-delay: 0.2s; }
            .stagger-3 { animation-delay: 0.3s; }
            .stagger-4 { animation-delay: 0.4s; }

            @keyframes fade-up {
                from { opacity: 0; transform: translateY(16px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .animate-fade-up {
                animation: fade-up 0.6s ease-out both;
            }

            @keyframes shimmer {
                0% { background-position: -200% 0; }
                100% { background-position: 200% 0; }
            }

            .pricing-glow {
                background: linear-gradient(90deg, transparent, var(--color-brand), transparent);
                background-size: 200% 100%;
                animation: shimmer 3s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="antialiased bg-surface text-text selection:bg-brand/20 selection:text-text">
        @php
            $benefits = [
                [
                    'icon' => 'clock',
                    'title' => 'Save hours every week',
                    'description' => 'Centralize content planning, client tracking, and delivery updates in one place instead of bouncing between tools.',
                ],
                [
                    'icon' => 'arrow-trending-up',
                    'title' => 'Send proposals and invoices faster',
                    'description' => 'Move from draft to client-ready docs quickly so you spend less time on admin and more time on paid work.',
                ],
                [
                    'icon' => 'users',
                    'title' => 'Keep clients confident',
                    'description' => 'Give clients clear visibility through their portal so approvals, revisions, and payment steps feel predictable.',
                ],
                [
                    'icon' => 'chart-bar',
                    'title' => 'Make smarter growth decisions',
                    'description' => 'Use analytics snapshots to understand what content performs and where your next campaign should focus.',
                ],
            ];

            $features = [
                [
                    'icon' => 'camera',
                    'title' => 'Instagram sync + insights',
                    'description' => 'Connect Instagram accounts, sync media, and review performance data without manual exports.',
                ],
                [
                    'icon' => 'folder-open',
                    'title' => 'Client and campaign management',
                    'description' => 'Track active clients, organize campaign deliverables, and keep every engagement scoped and searchable.',
                ],
                [
                    'icon' => 'document-text',
                    'title' => 'Proposal workflows',
                    'description' => 'Create, preview, send, and track approval or revision requests in a single proposal lifecycle.',
                ],
                [
                    'icon' => 'credit-card',
                    'title' => 'Invoices with payment links',
                    'description' => 'Generate invoices, send secure payment links, and monitor paid or overdue status in one dashboard.',
                ],
                [
                    'icon' => 'window',
                    'title' => 'Client portal visibility',
                    'description' => 'Clients can review proposals, invoices, and analytics from a dedicated portal view.',
                ],
            ];

            $steps = [
                [
                    'number' => '01',
                    'title' => 'Connect accounts and import data',
                    'description' => 'Link your Instagram account and pull media plus audience context into your workspace.',
                ],
                [
                    'number' => '02',
                    'title' => 'Build proposals and deliverables',
                    'description' => 'Define campaign scope, assemble content plans, and package offers with clear pricing.',
                ],
                [
                    'number' => '03',
                    'title' => 'Share with clients, get approved, get paid',
                    'description' => 'Send client-facing links, collect feedback, finalize invoices, and track payments.',
                ],
            ];

            $plans = [
                [
                    'name' => 'Free',
                    'price' => '$0',
                    'period' => '/month',
                    'followers' => 'Fewer than 1,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Growth',
                    'price' => '$25',
                    'period' => '/month',
                    'followers' => '1,000 to 10,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Creator',
                    'price' => '$49',
                    'period' => '/month',
                    'followers' => '10,001 to 99,999 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => true,
                ],
                [
                    'name' => 'Scale',
                    'price' => '$75',
                    'period' => '/month',
                    'followers' => '100,000 to 300,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Pro',
                    'price' => '$100',
                    'period' => '/month',
                    'followers' => '300,000 to 600,000 followers',
                    'cta' => 'Get Started',
                    'href' => route('register'),
                    'recommended' => false,
                ],
                [
                    'name' => 'Enterprise',
                    'price' => 'Talk to us',
                    'period' => '',
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
            {{-- Ambient background layers --}}
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[40rem] bg-gradient-to-b from-brand/10 via-brand/3 to-transparent dark:from-brand/6 dark:via-brand/2"></div>
            <div class="pointer-events-none absolute -left-32 top-24 size-96 rounded-full bg-brand/8 blur-[100px] dark:bg-brand/5"></div>
            <div class="pointer-events-none absolute -right-20 top-[28rem] size-80 rounded-full bg-amber-400/15 blur-[80px] dark:bg-amber-500/8"></div>

            {{-- ───────────────────── HEADER ───────────────────── --}}
            <header class="sticky top-0 z-30 border-b border-border/60 bg-surface/90 backdrop-blur-xl">
                <nav class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-3.5 lg:px-10" aria-label="Main">
                    <a href="{{ route('home') }}" class="group inline-flex items-center gap-3" wire:navigate>
                        <span class="inline-flex size-9 items-center justify-center rounded-xl bg-brand text-brand-foreground transition-transform duration-200 group-hover:scale-105">
                            <x-app-logo-icon class="size-5" />
                        </span>
                        <span class="font-display text-lg font-semibold tracking-tight text-text">Influence Me</span>
                    </a>

                    <div class="hidden items-center gap-7 text-sm font-medium text-text-muted md:flex">
                        <a href="#benefits" class="transition-colors duration-150 hover:text-text focus-visible:text-text focus-visible:outline-none">Benefits</a>
                        <a href="#features" class="transition-colors duration-150 hover:text-text focus-visible:text-text focus-visible:outline-none">Features</a>
                        <a href="#pricing" class="transition-colors duration-150 hover:text-text focus-visible:text-text focus-visible:outline-none">Pricing</a>
                        <a href="#faq" class="transition-colors duration-150 hover:text-text focus-visible:text-text focus-visible:outline-none">FAQ</a>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button :href="route('login')" variant="ghost" class="text-text-muted hover:text-text focus-visible:outline-none" wire:navigate>
                            Log In
                        </flux:button>
                        <flux:button :href="route('register')" variant="primary" class="!bg-brand !text-brand-foreground hover:!bg-brand/90 focus-visible:outline-none" wire:navigate>
                            Get Started
                        </flux:button>
                    </div>
                </nav>
            </header>

            <main class="relative">
                {{-- ───────────────────── HERO ───────────────────── --}}
                <section class="relative mx-auto w-full max-w-7xl px-6 pb-20 pt-16 lg:px-10 lg:pb-28 lg:pt-24" aria-labelledby="hero-heading">
                    <div class="grid gap-12 lg:grid-cols-[1fr_20rem] lg:gap-16 xl:grid-cols-[1fr_24rem]">
                        <div class="space-y-8 animate-fade-up">
                            <div>
                                <flux:badge color="orange" rounded class="!bg-brand/12 !text-text dark:!bg-brand/15">
                                    Creator operations, centralized
                                </flux:badge>
                            </div>

                            <div class="space-y-5">
                                <h1 id="hero-heading" class="font-display text-4xl font-semibold leading-[1.1] tracking-tight text-text text-balance sm:text-5xl lg:text-[3.5rem] xl:text-6xl">
                                    Turn creator momentum into a repeatable business system.
                                </h1>
                                <p class="max-w-2xl text-lg leading-relaxed text-text-muted lg:text-xl">
                                    Influence Me brings content tracking, client management, proposals, invoicing, and analytics into one workflow so you can run partnerships like a real operation.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <flux:button :href="route('register')" variant="primary" class="!bg-brand !text-brand-foreground hover:!bg-brand/90 !px-6 !py-2.5 !text-base focus-visible:outline-none" wire:navigate>
                                    Get Started
                                </flux:button>
                                <flux:button :href="route('login')" variant="outline" class="!border-border !bg-surface-muted/80 !text-text hover:!bg-surface-muted !px-6 !py-2.5 !text-base focus-visible:outline-none" wire:navigate>
                                    Log In
                                </flux:button>
                            </div>
                        </div>

                        {{-- Side card --}}
                        <aside class="relative animate-fade-up stagger-2 self-start rounded-2xl border border-border/80 bg-surface-muted/60 p-6 shadow-sm backdrop-blur-sm lg:mt-6">
                            <div class="absolute -top-px left-8 right-8 h-px bg-gradient-to-r from-transparent via-brand/40 to-transparent"></div>
                            <h2 class="font-display text-lg font-semibold text-text">What you can run in one place</h2>
                            <ul class="mt-5 space-y-4 text-sm text-text-muted">
                                @foreach ([
                                    'Instagram account sync and media insights',
                                    'Proposal drafting, sending, and approvals',
                                    'Invoice generation with payment links',
                                    'Client portal access for visibility',
                                ] as $item)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand/15 text-brand">
                                            <flux:icon.check variant="micro" class="size-3" />
                                        </span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div>
                </section>

                {{-- ───────────────────── SOCIAL PROOF ───────────────────── --}}
                <section class="relative border-y border-border/50 bg-surface-muted/40 grain" aria-label="Social proof">
                    <div class="relative z-10 mx-auto w-full max-w-7xl px-6 py-12 lg:px-10">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="space-y-2">
                                <h2 class="font-display text-2xl font-semibold text-text sm:text-3xl">Proof in progress, built transparently</h2>
                                <p class="max-w-xl text-sm leading-relaxed text-text-muted">
                                    We are in early rollout. These are platform readiness indicators, not customer performance claims.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ([
                                    'Pilot onboarding active',
                                    'Stripe payment flow ready',
                                    'Client portal in scope',
                                    'Analytics foundation live',
                                ] as $indicator)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-border/80 bg-surface/80 px-3 py-1.5 text-xs font-medium text-text-muted backdrop-blur-sm transition-colors hover:border-brand/30 hover:text-text">
                                        <span class="size-1.5 rounded-full bg-brand"></span>
                                        {{ $indicator }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ───────────────────── BENEFITS ───────────────────── --}}
                <section id="benefits" class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-10 lg:py-28" aria-labelledby="benefits-heading">
                    <div class="max-w-2xl space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-widest text-brand">Why switch</p>
                        <h2 id="benefits-heading" class="font-display text-3xl font-semibold text-text sm:text-4xl">Why influencers switch to one operating system</h2>
                        <p class="text-text-muted leading-relaxed">
                            Clear operational wins without inflated marketing claims.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-5 sm:grid-cols-2">
                        @foreach ($benefits as $i => $benefit)
                            <article class="group relative rounded-2xl border border-border/70 bg-surface p-7 transition-all duration-200 hover:border-border hover:shadow-sm">
                                <div class="mb-5 inline-flex size-11 items-center justify-center rounded-xl bg-brand/10 text-brand transition-colors duration-200 group-hover:bg-brand/15">
                                    <flux:icon :name="$benefit['icon']" variant="mini" class="size-5" />
                                </div>
                                <h3 class="font-display text-xl font-semibold text-text">{{ $benefit['title'] }}</h3>
                                <p class="mt-2.5 text-sm leading-relaxed text-text-muted">{{ $benefit['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- ───────────────────── FEATURES ───────────────────── --}}
                <section id="features" class="relative border-y border-border/50 bg-surface-muted/30 grain" aria-labelledby="features-heading">
                    <div class="relative z-10 mx-auto w-full max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
                        <div class="max-w-2xl space-y-3">
                            <p class="text-sm font-semibold uppercase tracking-widest text-brand">Platform</p>
                            <h2 id="features-heading" class="font-display text-3xl font-semibold text-text sm:text-4xl">Feature coverage from content to cash flow</h2>
                            <p class="text-text-muted leading-relaxed">
                                Built for influencer workflows across account sync, client delivery, approvals, and payments.
                            </p>
                        </div>

                        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                            @foreach ($features as $feature)
                                <article class="group rounded-2xl border border-border/60 bg-surface/80 p-6 backdrop-blur-sm transition-all duration-200 hover:border-border hover:bg-surface hover:shadow-sm">
                                    <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-brand/10 text-brand transition-colors duration-200 group-hover:bg-brand/15">
                                        <flux:icon :name="$feature['icon']" variant="mini" class="size-5" />
                                    </div>
                                    <h3 class="text-base font-semibold text-text">{{ $feature['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-text-muted">{{ $feature['description'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- ───────────────────── HOW IT WORKS ───────────────────── --}}
                <section class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-10 lg:py-28" aria-labelledby="how-it-works-heading">
                    <div class="max-w-2xl space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-widest text-brand">Process</p>
                        <h2 id="how-it-works-heading" class="font-display text-3xl font-semibold text-text sm:text-4xl">How it works</h2>
                        <p class="max-w-xl text-text-muted leading-relaxed">
                            Start with account data, package client work, and complete approvals and payments in three steps.
                        </p>
                    </div>

                    <ol class="mt-12 grid gap-5 md:grid-cols-3">
                        @foreach ($steps as $step)
                            <li class="group relative rounded-2xl border border-border/70 bg-surface p-7 transition-all duration-200 hover:border-border hover:shadow-sm">
                                <span class="font-display text-4xl font-bold text-brand/20 dark:text-brand/15">{{ $step['number'] }}</span>
                                <h3 class="mt-3 text-lg font-semibold text-text">{{ $step['title'] }}</h3>
                                <p class="mt-2.5 text-sm leading-relaxed text-text-muted">{{ $step['description'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </section>

                {{-- ───────────────────── PRICING ───────────────────── --}}
                <section id="pricing" class="relative border-y border-border/50 bg-surface-muted/30 grain" aria-labelledby="pricing-heading">
                    <div class="relative z-10 mx-auto w-full max-w-7xl px-6 py-20 lg:px-10 lg:py-28">
                        <div class="max-w-2xl space-y-3">
                            <p class="text-sm font-semibold uppercase tracking-widest text-brand">Pricing</p>
                            <h2 id="pricing-heading" class="font-display text-3xl font-semibold text-text sm:text-4xl">Simple pricing by Instagram audience size</h2>
                            <p class="text-text-muted leading-relaxed">
                                Monthly plans scale with follower count so costs stay proportional as your audience grows.
                            </p>
                        </div>

                        <div class="mt-12 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($plans as $plan)
                                <article @class([
                                    'group relative rounded-2xl border p-7 transition-all duration-200',
                                    'border-brand/60 bg-surface shadow-md ring-1 ring-brand/20 dark:ring-brand/15 hover:shadow-lg' => $plan['recommended'],
                                    'border-border/60 bg-surface/80 backdrop-blur-sm hover:border-border hover:shadow-sm' => ! $plan['recommended'],
                                ])>
                                    @if ($plan['recommended'])
                                        <div class="absolute -top-px left-6 right-6 h-0.5 rounded-full pricing-glow opacity-60"></div>
                                    @endif

                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-display text-xl font-semibold text-text">{{ $plan['name'] }}</h3>
                                        @if ($plan['recommended'])
                                            <flux:badge color="orange" rounded class="!bg-brand/15 !text-brand shrink-0">Most popular</flux:badge>
                                        @endif
                                    </div>

                                    <div class="mt-4">
                                        <span class="text-4xl font-bold tracking-tight text-text">{{ $plan['price'] }}</span>
                                        @if ($plan['period'])
                                            <span class="text-base text-text-muted">{{ $plan['period'] }}</span>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-sm text-text-muted">{{ $plan['followers'] }}</p>

                                    @if ($plan['name'] === 'Enterprise')
                                        <flux:button :href="$plan['href']" variant="outline" class="mt-6 w-full !border-border !bg-surface-muted/60 !text-text hover:!bg-surface-muted focus-visible:outline-none">
                                            {{ $plan['cta'] }}
                                        </flux:button>
                                    @elseif ($plan['recommended'])
                                        <flux:button :href="$plan['href']" variant="primary" class="mt-6 w-full !bg-brand !text-brand-foreground hover:!bg-brand/90 focus-visible:outline-none" wire:navigate>
                                            {{ $plan['cta'] }}
                                        </flux:button>
                                    @else
                                        <flux:button :href="$plan['href']" variant="outline" class="mt-6 w-full !border-border !bg-surface/80 !text-text hover:!bg-surface-muted focus-visible:outline-none" wire:navigate>
                                            {{ $plan['cta'] }}
                                        </flux:button>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        <p class="mt-6 text-sm text-text-muted">
                            Follower count is determined by the audience size of your connected Instagram account.
                        </p>
                    </div>
                </section>

                {{-- ───────────────────── FAQ ───────────────────── --}}
                <section id="faq" class="mx-auto w-full max-w-5xl px-6 py-20 lg:px-10 lg:py-28" aria-labelledby="faq-heading">
                    <div class="max-w-2xl space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-widest text-brand">Support</p>
                        <h2 id="faq-heading" class="font-display text-3xl font-semibold text-text sm:text-4xl">Frequently asked questions</h2>
                    </div>

                    <div class="mt-10 space-y-3">
                        @foreach ($faqs as $faq)
                            <details class="group rounded-2xl border border-border/60 bg-surface/80 transition-colors duration-150 open:border-border open:bg-surface open:shadow-sm">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 font-medium text-text transition-colors hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2 focus-visible:ring-offset-surface [&::-webkit-details-marker]:hidden">
                                    <span>{{ $faq['question'] }}</span>
                                    <flux:icon.chevron-down class="size-4 shrink-0 text-text-muted transition-transform duration-200 group-open:rotate-180" />
                                </summary>
                                <p class="px-6 pb-5 text-sm leading-relaxed text-text-muted">{{ $faq['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            </main>

            {{-- ───────────────────── FINAL CTA FOOTER ───────────────────── --}}
            <footer class="mx-auto w-full max-w-7xl px-6 pb-16 lg:px-10">
                <div class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-brand/8 via-surface-muted/80 to-surface p-10 sm:p-14 grain">
                    <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-brand/10 blur-[80px]"></div>
                    <div class="pointer-events-none absolute -bottom-12 -left-12 size-48 rounded-full bg-amber-400/10 blur-[60px]"></div>

                    <div class="relative z-10 max-w-2xl space-y-5">
                        <h2 class="font-display text-3xl font-semibold text-text sm:text-4xl">Ready to run your influence business with less overhead?</h2>
                        <p class="text-text-muted leading-relaxed">
                            Set up your workspace, connect Instagram, and move client work from idea to payment with one clear system.
                        </p>
                    </div>
                    <div class="relative z-10 mt-8 flex flex-wrap items-center gap-3">
                        <flux:button :href="route('register')" variant="primary" class="!bg-brand !text-brand-foreground hover:!bg-brand/90 !px-6 !py-2.5 focus-visible:outline-none" wire:navigate>
                            Create Account
                        </flux:button>
                        <flux:button :href="route('login')" variant="outline" class="!border-border !bg-surface/90 !text-text hover:!bg-surface-muted !px-6 !py-2.5 focus-visible:outline-none" wire:navigate>
                            Log In
                        </flux:button>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-border/40 pt-6">
                    <p class="text-xs text-text-muted">&copy; {{ date('Y') }} Influence Me. All rights reserved.</p>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs text-text-muted transition-colors hover:text-text" wire:navigate>
                        <x-app-logo-icon class="size-4" />
                        <span class="font-display font-medium">Influence Me</span>
                    </a>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
