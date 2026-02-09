<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#f8f4ef] text-[#16130f] antialiased dark:bg-zinc-900 dark:text-zinc-50 font-[Manrope]">
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 opacity-70">
            <div class="absolute -left-40 top-10 h-64 w-64 rounded-full bg-gradient-to-br from-[#f7c37a] via-[#f08973] to-transparent blur-3xl"></div>
            <div class="absolute -right-32 bottom-10 h-72 w-72 rounded-full bg-gradient-to-tl from-[#2c3b2d] via-[#6d8b5e] to-transparent blur-3xl opacity-80"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.35)_0,rgba(255,255,255,0)_35%),radial-gradient(circle_at_80%_10%,rgba(255,255,255,0.3)_0,rgba(255,255,255,0)_30%),radial-gradient(circle_at_70%_80%,rgba(255,255,255,0.25)_0,rgba(255,255,255,0)_35%)] mix-blend-screen"></div>
        </div>

        <div class="relative mx-auto flex max-w-6xl flex-col gap-20 px-6 pb-24 pt-12 sm:px-10 md:px-12 lg:px-16">
            <header class="flex items-center justify-between gap-6 rounded-full border border-[#e5ded4] bg-white/70 px-5 py-3 shadow-[0_18px_60px_-30px_rgba(0,0,0,0.45)] backdrop-blur-md dark:border-zinc-700 dark:bg-zinc-800/70">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-full bg-[#1f2520] text-white shadow-lg">IM</div>
                    <div class="leading-tight">
                        <p class="text-xs uppercase tracking-[0.35em] text-[#7a7367] dark:text-zinc-300">Influence Me</p>
                        <p class="font-semibold text-[#1a1814] dark:text-zinc-50">Influencer OS</p>
                    </div>
                </div>
                <nav class="hidden items-center gap-6 text-sm font-medium text-[#3c362d] dark:text-zinc-200 md:flex">
                    <a class="transition hover:text-[#111] dark:hover:text-white" href="#features">Features</a>
                    <a class="transition hover:text-[#111] dark:hover:text-white" href="#pricing">Pricing</a>
                    <a class="transition hover:text-[#111] dark:hover:text-white" href="#faq">FAQ</a>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#2c2a27] underline-offset-4 transition hover:underline dark:text-zinc-50">Log In</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-[#111] px-4 py-2 text-sm font-semibold text-white shadow-[0_14px_40px_-24px_rgba(0,0,0,0.65)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-20px_rgba(0,0,0,0.55)] dark:bg-white dark:text-zinc-900">Get Started</a>
                </div>
            </header>

            <section class="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#e5ded4] bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-[#5b5243] shadow-[0_10px_40px_-28px_rgba(0,0,0,0.6)] backdrop-blur-md dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-200">
                        <span class="size-2 rounded-full bg-[#f08973]"></span>
                        Built for modern influencers
                    </div>
                    <h1 class="font-['Playfair_Display'] text-4xl leading-[1.05] text-[#120f0c] sm:text-5xl lg:text-6xl dark:text-white">
                        Centralize your influence, sync your content, and get paid faster.
                    </h1>
                    <p class="max-w-2xl text-lg leading-relaxed text-[#4b443a] dark:text-zinc-200">
                        Influence Me connects Instagram, clients, proposals, invoices, and analytics into a single operating system. Spend more time creating — we handle the operations.
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" class="rounded-full bg-[#f08973] px-5 py-3 text-sm font-semibold text-white shadow-[0_22px_45px_-20px_rgba(240,137,115,0.65)] transition hover:-translate-y-0.5 hover:shadow-[0_26px_55px_-20px_rgba(240,137,115,0.75)]">
                            Get Started
                        </a>
                        <a href="{{ route('login') }}" class="rounded-full border border-[#d7cfc4] bg-white/80 px-5 py-3 text-sm font-semibold text-[#2f271f] transition hover:-translate-y-0.5 hover:border-[#2f271f] hover:shadow-[0_16px_35px_-26px_rgba(0,0,0,0.65)] dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-50 dark:hover:border-zinc-200">
                            Log In
                        </a>
                        <div class="flex items-center gap-2 text-sm font-medium text-[#645b4c] dark:text-zinc-300">
                            <span class="size-2.5 rounded-full bg-[#6d8b5e]"></span>
                            Always on, client-ready portal
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold uppercase tracking-[0.25em] text-[#7a7367] dark:text-zinc-400">
                        <span class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-[#6d8b5e]"></span> Sync</span>
                        <span class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-[#f7c37a]"></span> Proposals</span>
                        <span class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-[#f08973]"></span> Payments</span>
                        <span class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-[#2c3b2d]"></span> Analytics</span>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-white/40 blur-3xl"></div>
                    <div class="absolute -right-8 -bottom-10 h-40 w-40 rounded-full bg-[#f7c37a]/40 blur-3xl"></div>
                    <div class="relative overflow-hidden rounded-3xl border border-[#e8e0d8] bg-white/80 shadow-[0_35px_120px_-60px_rgba(0,0,0,0.65)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div class="flex items-center justify-between border-b border-[#e8e0d8] px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-[#7a7367] dark:border-zinc-700 dark:text-zinc-300">
                            Sync Overview
                            <span class="rounded-full bg-[#eae3da] px-3 py-1 text-[11px] text-[#2f271f] dark:bg-zinc-700 dark:text-zinc-100">Live</span>
                        </div>
                        <div class="grid gap-0.5 bg-gradient-to-br from-white via-[#fdf7f1] to-[#f5ece3] p-6 dark:from-zinc-800 dark:via-zinc-800 dark:to-zinc-900">
                            <div class="flex items-center justify-between rounded-2xl border border-[#efe5db] bg-white/90 px-4 py-3 text-sm font-semibold text-[#1f1b16] shadow-[0_22px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-800/90 dark:text-white">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-9 items-center justify-center rounded-full bg-[#f7c37a]/80 text-[#1f1b16]">IG</span>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-[#7a7367] dark:text-zinc-400">Instagram</p>
                                        <p class="font-semibold">@studio.alo</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-[#2f271f] dark:text-zinc-100">
                                    <span class="size-2 rounded-full bg-[#6d8b5e]"></span> Synced 3m ago
                                </div>
                            </div>
                            <div class="grid gap-2 rounded-2xl border border-[#efe5db] bg-white/90 p-5 shadow-[0_22px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-800/90">
                                <div class="flex items-center justify-between text-sm font-semibold text-[#2b241d] dark:text-white">
                                    <p>Campaigns in motion</p>
                                    <span class="rounded-full bg-[#2c3b2d] px-3 py-1 text-[11px] text-white">5 active</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm text-[#2f271f] dark:text-zinc-100">
                                    <div class="rounded-xl border border-[#f0e6da] bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#7a7367] dark:text-zinc-400">Proposals</p>
                                        <p class="mt-2 text-2xl font-semibold">8</p>
                                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Awaiting approval</p>
                                    </div>
                                    <div class="rounded-xl border border-[#f0e6da] bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#7a7367] dark:text-zinc-400">Invoices</p>
                                        <p class="mt-2 text-2xl font-semibold">$14.2k</p>
                                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Pending payment</p>
                                    </div>
                                    <div class="rounded-xl border border-[#f0e6da] bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#7a7367] dark:text-zinc-400">Media</p>
                                        <p class="mt-2 text-2xl font-semibold">326</p>
                                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Synced assets</p>
                                    </div>
                                    <div class="rounded-xl border border-[#f0e6da] bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800/80">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#7a7367] dark:text-zinc-400">Engagement</p>
                                        <p class="mt-2 text-2xl font-semibold">4.8%</p>
                                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Trend past 30d</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-dashed border-[#e9dfd5] bg-[#f8f4ef] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#6a6256] dark:border-zinc-600/70 dark:bg-zinc-900/80">
                                    <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-[#f08973]"></span> Client portal access enabled</span>
                                    <span class="text-[11px] text-[#2f271f] dark:text-zinc-200">Share proposals • invoices • analytics</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="social" class="grid gap-6 rounded-3xl border border-[#e5ded4] bg-white/80 p-6 shadow-[0_30px_90px_-60px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/70">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">Trusted by independent creators</p>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-[#645b4c] dark:text-zinc-300">
                        <span class="size-2 rounded-full bg-[#6d8b5e]"></span>
                        Sync speed · Client clarity · Faster payouts
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/80 px-4 py-3 text-sm font-semibold text-[#2f271f] shadow-[0_16px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-100">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#7a7367] dark:text-zinc-400">Sync uptime</p>
                        <p class="mt-2 text-2xl font-semibold">99.9%</p>
                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Pipeline reliability</p>
                    </div>
                    <div class="rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/80 px-4 py-3 text-sm font-semibold text-[#2f271f] shadow-[0_16px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-100">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#7a7367] dark:text-zinc-400">Clients invited</p>
                        <p class="mt-2 text-2xl font-semibold">12k</p>
                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Portal-ready access</p>
                    </div>
                    <div class="rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/80 px-4 py-3 text-sm font-semibold text-[#2f271f] shadow-[0_16px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-100">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#7a7367] dark:text-zinc-400">Invoice paid</p>
                        <p class="mt-2 text-2xl font-semibold">$8.4M</p>
                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Stripe-connected flows</p>
                    </div>
                    <div class="rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/80 px-4 py-3 text-sm font-semibold text-[#2f271f] shadow-[0_16px_40px_-30px_rgba(0,0,0,0.5)] dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-100">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#7a7367] dark:text-zinc-400">Average reply</p>
                        <p class="mt-2 text-2xl font-semibold">42m</p>
                        <p class="text-xs text-[#6a6256] dark:text-zinc-400">Client feedback loop</p>
                    </div>
                </div>
            </section>

            <section id="benefits" class="grid gap-10 rounded-3xl border border-[#e5ded4] bg-white/90 p-8 shadow-[0_40px_110px_-70px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/80">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">Benefits</p>
                        <h2 class="mt-2 font-['Playfair_Display'] text-3xl text-[#120f0c] dark:text-white">Why creators stay organized here</h2>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-[#4b443a] dark:text-zinc-200">
                        Save time with centralized ops, win revenue with faster proposals and invoices, keep clients delighted with portal clarity, and make smarter decisions with analytics built for influencer businesses.
                    </p>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @php
                        $benefits = [
                            ['title' => 'Centralized ops', 'copy' => 'Calendar, clients, and content in one surface eliminates tab chaos.'],
                            ['title' => 'Faster revenue', 'copy' => 'Send proposals and invoices in minutes with reusable blocks.'],
                            ['title' => 'Client clarity', 'copy' => 'Portal access shows status, deliverables, and payments without back-and-forth.'],
                            ['title' => 'Analytics built-in', 'copy' => 'Media performance, reach, and engagement trends update automatically.'],
                        ];
                    @endphp
                    @foreach($benefits as $benefit)
                        <div class="group relative overflow-hidden rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/70 p-6 shadow-[0_24px_60px_-44px_rgba(0,0,0,0.65)] transition hover:-translate-y-1 hover:border-[#2f271f] hover:shadow-[0_30px_80px_-50px_rgba(0,0,0,0.65)] dark:border-zinc-700 dark:bg-zinc-900/70">
                            <div class="absolute -right-8 -top-6 size-16 rounded-full bg-gradient-to-br from-[#f7c37a] to-[#f08973] opacity-70 blur-2xl transition group-hover:scale-110"></div>
                            <div class="relative flex h-full flex-col gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-[#2c3b2d] text-sm font-semibold text-white shadow-lg shadow-[#2c3b2d]/35">{{ str($benefit['title'])->substr(0,1) }}</div>
                                <h3 class="text-lg font-semibold text-[#1a1814] dark:text-white">{{ $benefit['title'] }}</h3>
                                <p class="text-sm leading-relaxed text-[#4b443a] dark:text-zinc-300">{{ $benefit['copy'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="features" class="grid gap-10 rounded-3xl border border-[#e5ded4] bg-white/90 p-8 shadow-[0_40px_110px_-70px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/80">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">Features</p>
                        <h2 class="mt-2 font-['Playfair_Display'] text-3xl text-[#120f0c] dark:text-white">Everything in one command center</h2>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-[#4b443a] dark:text-zinc-200">
                        Instagram sync, client and campaign management, proposals with approvals, invoices with Stripe links, and a client portal that keeps everyone aligned.
                    </p>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @php
                        $features = [
                            ['title' => 'Instagram sync & insights', 'copy' => 'Graph API-powered profile, media, stories, and demographics with scheduled refresh.'],
                            ['title' => 'Client & campaign workspace', 'copy' => 'Track brands, deliverables, and linked media with clean authorization boundaries.'],
                            ['title' => 'Proposal workflow', 'copy' => 'Draft, preview, send, and capture approvals or revisions without email chaos.'],
                            ['title' => 'Invoices & payments', 'copy' => 'Generate invoices, attach Stripe payment links, and auto-mark paid from webhooks.'],
                            ['title' => 'Client portal', 'copy' => 'Clients see proposals, invoices, and analytics without touching influencer data.'],
                            ['title' => 'Schedule timeline', 'copy' => 'Plan posts, track status, and keep launch dates on one timeline.'],
                        ];
                    @endphp
                    @foreach($features as $feature)
                        <div class="group relative overflow-hidden rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/60 p-6 shadow-[0_24px_60px_-44px_rgba(0,0,0,0.65)] transition hover:-translate-y-1 hover:border-[#2f271f] hover:shadow-[0_30px_80px_-50px_rgba(0,0,0,0.65)] dark:border-zinc-700 dark:bg-zinc-900/70">
                            <div class="absolute -right-10 -bottom-12 h-24 w-24 rounded-full bg-gradient-to-t from-[#2c3b2d]/60 via-transparent to-transparent opacity-70 blur-2xl"></div>
                            <div class="relative flex h-full flex-col gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-[#f08973] text-sm font-semibold text-white shadow-lg shadow-[#f08973]/35">*</div>
                                <h3 class="text-lg font-semibold text-[#1a1814] dark:text-white">{{ $feature['title'] }}</h3>
                                <p class="text-sm leading-relaxed text-[#4b443a] dark:text-zinc-300">{{ $feature['copy'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="how" class="grid gap-8 rounded-3xl border border-[#e5ded4] bg-white/90 p-8 shadow-[0_40px_110px_-70px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/80">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">How it works</p>
                        <h2 class="mt-2 font-['Playfair_Display'] text-3xl text-[#120f0c] dark:text-white">Three clear steps to operate</h2>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @php
                        $steps = [
                            ['title' => 'Connect', 'copy' => 'Link Instagram, sync media, stories, and audience demographics automatically.'],
                            ['title' => 'Build', 'copy' => 'Compose proposals, deliverables, and invoice templates tailored to each campaign.'],
                            ['title' => 'Share & get paid', 'copy' => 'Send via portal, capture approvals, and collect payments with Stripe links.'],
                        ];
                    @endphp
                    @foreach($steps as $index => $step)
                        <div class="relative overflow-hidden rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/70 p-6 shadow-[0_24px_60px_-44px_rgba(0,0,0,0.65)] dark:border-zinc-700 dark:bg-zinc-900/70">
                            <div class="absolute right-4 top-4 text-6xl font-bold text-[#e6d9cc] dark:text-zinc-700">0{{ $index + 1 }}</div>
                            <div class="relative flex h-full flex-col gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-[#6d8b5e] text-sm font-semibold text-white shadow-lg shadow-[#6d8b5e]/35">{{ $step['title'][0] }}</div>
                                <h3 class="text-lg font-semibold text-[#1a1814] dark:text-white">{{ $step['title'] }}</h3>
                                <p class="text-sm leading-relaxed text-[#4b443a] dark:text-zinc-300">{{ $step['copy'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="pricing" class="grid gap-8 rounded-3xl border border-[#e5ded4] bg-white/95 p-8 shadow-[0_40px_120px_-70px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/85">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">Pricing</p>
                        <h2 class="mt-2 font-['Playfair_Display'] text-3xl text-[#120f0c] dark:text-white">Follower-based plans that flex with you</h2>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-[#4b443a] dark:text-zinc-200">
                        Pricing is based on your connected Instagram audience size. Plans include all core features; upgrade automatically as you grow. Enterprise? We tailor it with you.
                    </p>
                </div>
                <p class="rounded-2xl border border-dashed border-[#e8ded3] bg-[#f8f4ef] px-4 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[#6a6256] dark:border-zinc-600/70 dark:bg-zinc-900/80">
                    Follower counts pulled from your linked Instagram account.
                </p>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @php
                        $plans = [
                            ['name' => 'Free', 'price' => '$0', 'range' => '< 1,000 followers', 'cta' => 'Start free', 'highlight' => false],
                            ['name' => 'Growth', 'price' => '$25', 'range' => '1,000 – 10,000 followers', 'cta' => 'Choose Growth', 'highlight' => false],
                            ['name' => 'Creator', 'price' => '$49', 'range' => '10,001 – 99,999 followers', 'cta' => 'Most popular', 'highlight' => true],
                            ['name' => 'Scale', 'price' => '$75', 'range' => '100,000 – 300,000 followers', 'cta' => 'Choose Scale', 'highlight' => false],
                            ['name' => 'Pro', 'price' => '$100', 'range' => '300,000 – 600,000 followers', 'cta' => 'Choose Pro', 'highlight' => false],
                            ['name' => 'Enterprise', 'price' => 'Talk to us', 'range' => '> 600,000 followers', 'cta' => 'Talk to us', 'highlight' => false],
                        ];
                    @endphp
                    @foreach($plans as $plan)
                        <div class="group relative overflow-hidden rounded-2xl border {{ $plan['highlight'] ? 'border-[#2c3b2d] bg-[#1f2520] text-white shadow-[0_30px_100px_-60px_rgba(0,0,0,0.75)]' : 'border-[#e5ded4] bg-[#f8f4ef]/80 text-[#1f1b16] shadow-[0_24px_70px_-50px_rgba(0,0,0,0.6)] dark:border-zinc-700 dark:bg-zinc-900/70 dark:text-zinc-100' }} p-6 transition hover:-translate-y-1">
                            <div class="relative flex h-full flex-col gap-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] {{ $plan['highlight'] ? 'text-[#c7d4c3]' : 'text-[#7a7367] dark:text-zinc-400' }}">{{ $plan['range'] }}</p>
                                        <h3 class="mt-1 text-2xl font-semibold">{{ $plan['name'] }}</h3>
                                    </div>
                                    <span class="rounded-full border {{ $plan['highlight'] ? 'border-white/25 bg-white/10 text-white' : 'border-[#e5ded4] bg-white/60 text-[#2f271f] dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-100' }} px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]">{{ $plan['cta'] }}</span>
                                </div>
                                <p class="text-4xl font-bold">
                                    {{ $plan['price'] }}
                                    <span class="text-base font-semibold">{{ $plan['price'] === 'Talk to us' ? '' : '/month' }}</span>
                                </p>
                                <p class="text-sm leading-relaxed {{ $plan['highlight'] ? 'text-[#d7e2d3]' : 'text-[#4b443a] dark:text-zinc-300' }}">
                                    Full access to sync, proposals, invoices, schedule timeline, client portal, and analytics.
                                </p>
                                <div class="mt-auto flex items-center gap-3">
                                    <a href="{{ route('register') }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $plan['highlight'] ? 'bg-white text-[#1f2520] hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-22px_rgba(0,0,0,0.6)]' : 'bg-[#2c3b2d] text-white hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-22px_rgba(0,0,0,0.5)]' }}">
                                        {{ $plan['cta'] === 'Talk to us' ? 'Talk to us' : 'Get Started' }}
                                    </a>
                                    <a href="{{ route('login') }}" class="text-sm font-semibold underline-offset-4 {{ $plan['highlight'] ? 'text-[#d7e2d3] hover:underline' : 'text-[#2f271f] hover:underline dark:text-zinc-100' }}">Log In</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="faq" class="grid gap-6 rounded-3xl border border-[#e5ded4] bg-white/90 p-8 shadow-[0_40px_110px_-70px_rgba(0,0,0,0.55)] backdrop-blur-xl dark:border-zinc-700 dark:bg-zinc-800/80">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#7a7367] dark:text-zinc-400">FAQ</p>
                        <h2 class="mt-2 font-['Playfair_Display'] text-3xl text-[#120f0c] dark:text-white">Clarity before you start</h2>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-[#4b443a] dark:text-zinc-200">Quick answers for the most common questions.</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    @php
                        $faqs = [
                            ['q' => 'Can I cancel anytime?', 'a' => 'Yes — no lock-in. Downgrade or cancel before the next cycle right inside billing.'],
                            ['q' => 'Do you support annual billing?', 'a' => 'Annual billing is available on request with two months free.'],
                            ['q' => 'Is there a free trial?', 'a' => 'The Free plan covers creators under 1,000 followers with full access to core features.'],
                            ['q' => 'Can clients access a portal without full accounts?', 'a' => 'Yes. Clients sign in via the client portal guard with scoped access to proposals, invoices, and analytics.'],
                        ];
                    @endphp
                    @foreach($faqs as $faq)
                        <div class="rounded-2xl border border-[#e5ded4] bg-[#f8f4ef]/80 p-5 text-left shadow-[0_22px_60px_-44px_rgba(0,0,0,0.6)] dark:border-zinc-700 dark:bg-zinc-900/70">
                            <p class="text-sm font-semibold text-[#1a1814] dark:text-white">{{ $faq['q'] }}</p>
                            <p class="mt-2 text-sm leading-relaxed text-[#4b443a] dark:text-zinc-300">{{ $faq['a'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="cta" class="relative overflow-hidden rounded-3xl border border-[#e5ded4] bg-[#1f2520] p-10 text-white shadow-[0_40px_120px_-70px_rgba(0,0,0,0.75)]">
                <div class="absolute -left-16 top-6 h-48 w-48 rounded-full bg-[#f08973]/40 blur-3xl"></div>
                <div class="absolute -right-10 -bottom-14 h-56 w-56 rounded-full bg-[#6d8b5e]/35 blur-3xl"></div>
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-[0.32em] text-[#d6e2d4]">Ready to run like a studio</p>
                        <h2 class="font-['Playfair_Display'] text-3xl leading-tight">Create, send, and get paid — without leaving one surface.</h2>
                        <p class="max-w-3xl text-sm leading-relaxed text-[#d6e2d4]">Connect Instagram, invite clients, and keep every deliverable on-track. Join now to sync your first account.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-[#1f2520] shadow-[0_22px_45px_-24px_rgba(0,0,0,0.65)] transition hover:-translate-y-0.5 hover:shadow-[0_26px_55px_-22px_rgba(0,0,0,0.75)]">Create Account</a>
                        <a href="{{ route('login') }}" class="rounded-full border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:border-white">Log In</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @fluxScripts
</body>
</html>
