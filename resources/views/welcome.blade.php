<x-layouts.marketing title="Okacrm | The Operating System for Creators">
    <style>
        .font-display {
            font-family: 'DM Serif Display', serif;
        }

        .font-editorial {
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .grain-overlay {
            background-image: radial-gradient(circle at 25% 20%, rgb(255 255 255 / 0.28) 0, transparent 40%),
                radial-gradient(circle at 78% 8%, rgb(255 255 255 / 0.2) 0, transparent 36%),
                radial-gradient(circle at 10% 84%, rgb(255 255 255 / 0.15) 0, transparent 42%),
                linear-gradient(120deg, rgb(10 14 27) 0%, rgb(20 31 47) 52%, rgb(30 49 61) 100%);
        }

        .line-pattern {
            background-image: linear-gradient(to right, rgb(148 163 184 / 0.16) 1px, transparent 1px),
                linear-gradient(to bottom, rgb(148 163 184 / 0.16) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .glow-dot {
            box-shadow: 0 0 0 6px rgb(45 212 191 / 0.18), 0 0 34px 6px rgb(45 212 191 / 0.3);
        }

        @keyframes floatA {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-7px);
            }
        }

        @keyframes floatB {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(7px);
            }
        }

        .float-a {
            animation: floatA 7s ease-in-out infinite;
        }

        .float-b {
            animation: floatB 8s ease-in-out infinite;
        }

        .stagger {
            animation: floatA 10s ease-in-out infinite;
        }
    </style>

    <div class="font-editorial bg-slate-100 text-slate-900">
        <section class="relative isolate overflow-hidden border-b border-slate-300/70">
            <div class="grain-overlay absolute inset-0 -z-20"></div>
            <div class="line-pattern absolute inset-0 -z-10 opacity-50"></div>
            <div class="absolute -left-20 top-20 h-56 w-56 rounded-full bg-teal-300/20 blur-3xl"></div>
            <div class="absolute -right-24 bottom-10 h-64 w-64 rounded-full bg-amber-300/20 blur-3xl"></div>

            <div class="mx-auto grid max-w-7xl gap-16 px-6 pb-20 pt-24 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-teal-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-teal-300 glow-dot"></span>
                        Influencer CRM platform
                    </div>

                    <h1 class="font-display text-5xl leading-[0.98] text-white sm:text-6xl lg:text-7xl">Run your influencer business like a <span class="text-amber-200">real CRM</span>.</h1>

                    <p class="mt-7 max-w-2xl text-base leading-relaxed text-slate-200 sm:text-lg">
                        Okacrm helps you send polished proposals, manage client deliverables, and share campaign reports automatically. We sync your account stats and linked posts so you spend less time reporting and more time creating.
                    </p>

                    <div class="mt-10 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                        <a href="{{ route('auth.facebook', ['driver' => 'instagram']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-teal-200 bg-teal-300 px-7 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-200 sm:w-auto">
                            <i class="fa-brands fa-instagram text-base" aria-hidden="true"></i>
                            Continue with Instagram
                        </a>
                        <a href="/login" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-white/35 bg-white/10 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/20 sm:w-auto">
                            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                            Login
                        </a>
                        <a href="/pricing" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-white/35 bg-white/10 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/20 sm:w-auto">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            Explore Pricing
                        </a>
                    </div>
                </div>

                <div class="relative lg:col-span-5">
                    <div class="float-a rounded-[2rem] border border-white/20 bg-white/10 p-5 backdrop-blur-xl">
                        <div class="rounded-2xl border border-white/15 bg-slate-950/75 p-5 text-slate-100">
                            <div class="mb-5 flex items-center justify-between text-xs uppercase tracking-[0.18em] text-slate-400">
                                <span>Monday cockpit</span>
                                <span>08:40</span>
                            </div>

                            <div class="grid gap-4">
                                <div class="rounded-xl border border-teal-200/20 bg-teal-200/10 p-4">
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-teal-200">Client Pulse</p>
                                    <p class="mt-2 text-2xl font-bold text-teal-100">6 campaigns</p>
                                    <p class="text-xs text-teal-100/80">3 approvals needed today</p>
                                </div>

                                <div class="grid grid-cols-3 gap-3 text-center">
                                    <div class="rounded-lg border border-slate-700 bg-slate-900 p-3">
                                        <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Reach</p>
                                        <p class="mt-1 text-lg font-semibold text-white">214k</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-700 bg-slate-900 p-3">
                                        <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Deals</p>
                                        <p class="mt-1 text-lg font-semibold text-white">18</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-700 bg-slate-900 p-3">
                                        <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Due</p>
                                        <p class="mt-1 text-lg font-semibold text-amber-200">2 invoices</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-700 bg-slate-900 p-4">
                                    <p class="mb-2 text-[11px] uppercase tracking-[0.18em] text-slate-500">Weekly momentum</p>
                                    <div class="flex h-24 items-end gap-2">
                                        <div class="h-[32%] w-full rounded-t bg-slate-700"></div>
                                        <div class="h-[56%] w-full rounded-t bg-slate-600"></div>
                                        <div class="h-[40%] w-full rounded-t bg-slate-500"></div>
                                        <div class="h-[68%] w-full rounded-t bg-teal-300"></div>
                                        <div class="h-[52%] w-full rounded-t bg-slate-500"></div>
                                        <div class="h-[78%] w-full rounded-t bg-amber-200"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="float-b absolute -bottom-10 -left-8 hidden rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-slate-400/20 md:block">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Payments</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Invoice #3021 marked paid</p>
                        <p class="mt-1 text-xs text-slate-500">via payment confirmation</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-300 bg-white py-5">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-8 gap-y-2 px-6 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500 sm:text-xs">
                <span>Campaign timelines</span>
                <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                <span>Client portal approvals</span>
                <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                <span>Payment status events</span>
                <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                <span>Audience trend snapshots</span>
            </div>
        </section>

        <section id="features" class="relative border-b border-slate-300/80 bg-slate-100 py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">System Modules</p>
                        <h2 class="mt-3 font-display text-4xl leading-tight text-slate-900 sm:text-5xl">Built like a studio OS, not a generic dashboard.</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-relaxed text-slate-600">Every block below reflects the influencer CRM workflow: proposals, deliverables, approvals, reporting, and scheduled content reminders in one place.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-6">
                    <article class="group rounded-3xl border border-slate-300 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-teal-200/30 lg:col-span-2">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                            <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Automated Stats and Post Linking</h3>
                        <p class="mt-2 text-sm text-slate-600">Connect your accounts once and automatically pull audience stats, post performance, and linked campaign content for client-ready reporting.</p>
                    </article>

                    <article class="group rounded-3xl border border-slate-300 bg-slate-900 p-6 text-slate-100 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-500/30 lg:col-span-2">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-700 text-teal-200">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Client Deliverables Hub</h3>
                        <p class="mt-2 text-sm text-slate-300">Track each client deliverable by campaign, keep approvals visible, and give clients a focused portal to review exactly what belongs to them.</p>
                    </article>

                    <article class="group rounded-3xl border border-slate-300 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-200/30 lg:col-span-2">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <i class="fa-solid fa-file-signature" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Proposal Builder</h3>
                        <p class="mt-2 text-sm text-slate-600">Build professional proposals using demographics from your connected accounts, then move each deal from draft to approval with full status clarity.</p>
                    </article>

                    <article class="group rounded-3xl border border-slate-300 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-cyan-200/30 lg:col-span-3">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                            <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Schedule and Reporting Control</h3>
                        <p class="mt-2 text-sm text-slate-600">Plan posting timelines with reminders for what to publish and when, while keeping campaign reporting organized without extra admin work.</p>
                    </article>

                    <article class="rounded-3xl border border-slate-300 bg-gradient-to-br from-amber-50 to-teal-50 p-6 lg:col-span-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Operational Snapshot</p>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-slate-300 bg-white p-3">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Active</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">12</p>
                                <p class="text-xs text-slate-500">campaigns</p>
                            </div>
                            <div class="rounded-xl border border-slate-300 bg-white p-3">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Awaiting</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">4</p>
                                <p class="text-xs text-slate-500">approvals</p>
                            </div>
                            <div class="rounded-xl border border-slate-300 bg-white p-3">
                                <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Paid</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">$8.4k</p>
                                <p class="text-xs text-slate-500">this month</p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="border-b border-slate-300 bg-white py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Workflow</p>
                        <h2 class="mt-3 font-display text-4xl leading-tight text-slate-900">From proposal to published deliverable.</h2>
                        <p class="mt-4 max-w-sm text-sm text-slate-600">One CRM flow for influencer operations. Each stage is connected so execution and reporting stay consistent.</p>
                    </div>

                    <div class="lg:col-span-8">
                        <div class="space-y-4">
                            <div class="grid gap-4 rounded-2xl border border-slate-300 bg-slate-50 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">01</div>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">Connect your social accounts</h3>
                                    <p class="text-sm text-slate-600">Secure OAuth confirms ownership and starts automated sync for profile, content, and demographics.</p>
                                </div>
                                <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-teal-700">Identity</span>
                            </div>

                            <div class="grid gap-4 rounded-2xl border border-slate-300 bg-slate-50 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">02</div>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">Build proposals with real demographics</h3>
                                    <p class="text-sm text-slate-600">Use account insights to create more professional proposals and set clear deliverables before kickoff.</p>
                                </div>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Proposal</span>
                            </div>

                            <div class="grid gap-4 rounded-2xl border border-slate-300 bg-slate-50 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">03</div>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">Manage deliverables and deadlines</h3>
                                    <p class="text-sm text-slate-600">Track what needs to be posted, how it should look, and when it is due with timeline reminders.</p>
                                </div>
                                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Execution</span>
                            </div>

                            <div class="grid gap-4 rounded-2xl border border-slate-300 bg-slate-50 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">04</div>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900">Auto-report performance back to clients</h3>
                                    <p class="text-sm text-slate-600">Linked posts and synced stats keep campaign reports current, reducing manual reporting and admin time.</p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Reporting</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="border-b border-slate-300 bg-slate-100 py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Pricing</p>
                    <h2 class="mt-3 font-display text-4xl text-slate-900 sm:text-5xl">Choose your operating mode.</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm text-slate-600">Start free, scale as your creator business adds more clients, campaigns, and revenue workflows.</p>
                </div>

                <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-3">
                    <article class="stagger flex flex-col rounded-3xl border border-slate-300 bg-white p-7">
                        <h3 class="text-lg font-semibold text-slate-900">Starter</h3>
                        <p class="mt-1 text-sm text-slate-500">For solo creators validating process.</p>
                        <p class="mt-6 text-4xl font-bold text-slate-900">$0<span class="text-sm font-medium text-slate-500"> /mo</span></p>
                        <ul class="mt-6 grow space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>1 Instagram account</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>Core media browsing</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>3 active proposals</li>
                        </ul>
                        <a href="{{ route('auth.facebook', ['driver' => 'instagram']) }}" class="mt-8 inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Get Started</a>
                    </article>

                    <article class="relative flex flex-col rounded-3xl border border-slate-900 bg-slate-900 p-7 text-white shadow-xl shadow-slate-400/30">
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-teal-300 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-950">Most Chosen</span>
                        <h3 class="text-lg font-semibold">Professional</h3>
                        <p class="mt-1 text-sm text-slate-300">For creator businesses running weekly campaigns.</p>
                        <p class="mt-6 text-4xl font-bold">$29<span class="text-sm font-medium text-slate-300"> /mo</span></p>
                        <ul class="mt-6 grow space-y-3 text-sm text-slate-200">
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-300" aria-hidden="true"></i>3 Instagram accounts</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-300" aria-hidden="true"></i>Unlimited clients</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-300" aria-hidden="true"></i>Invoices and payment tracking</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-300" aria-hidden="true"></i>Advanced analytics views</li>
                        </ul>
                        <a href="{{ route('auth.facebook', ['driver' => 'instagram']) }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-teal-300 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-teal-200">Start Free Trial</a>
                    </article>

                    <article class="stagger flex flex-col rounded-3xl border border-slate-300 bg-white p-7 [animation-delay:1.8s]">
                        <h3 class="text-lg font-semibold text-slate-900">Agency</h3>
                        <p class="mt-1 text-sm text-slate-500">For managers with multiple creator seats.</p>
                        <p class="mt-6 text-4xl font-bold text-slate-900">$99<span class="text-sm font-medium text-slate-500"> /mo</span></p>
                        <ul class="mt-6 grow space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>Unlimited accounts</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>Team members + roles</li>
                            <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-teal-500" aria-hidden="true"></i>White-label report exports</li>
                        </ul>
                        <a href="/pricing" class="mt-8 inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Contact Sales</a>
                    </article>
                </div>
            </div>
        </section>

        <section id="faq" class="bg-white py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-6 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">FAQ</p>
                    <h2 class="mt-3 font-display text-4xl leading-tight text-slate-900">Questions creators ask before switching.</h2>
                    <p class="mt-4 text-sm text-slate-600">Clear product boundaries and security practices, without vague marketing answers.</p>
                </div>

                <div class="space-y-4 lg:col-span-8">
                    <details class="group rounded-2xl border border-slate-300 bg-slate-50 p-6 open:bg-white open:shadow-lg open:shadow-slate-300/40 transition">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-slate-900">
                            Do I need a password to sign up?
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition group-open:rotate-180" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">No. Sign in is handled through Instagram OAuth, which confirms account ownership without storing platform passwords.</p>
                    </details>

                    <details class="group rounded-2xl border border-slate-300 bg-slate-50 p-6 open:bg-white open:shadow-lg open:shadow-slate-300/40 transition">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-slate-900">
                            Can clients access only their own campaign data?
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition group-open:rotate-180" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">Yes. Client portal authentication is isolated from influencer auth, and each client user is scoped only to their client records.</p>
                    </details>

                    <details class="group rounded-2xl border border-slate-300 bg-slate-50 p-6 open:bg-white open:shadow-lg open:shadow-slate-300/40 transition">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-slate-900">
                            Does scheduling auto-publish to Instagram?
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition group-open:rotate-180" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">Not in MVP. Scheduled posts are for planning and workflow tracking, while final publishing remains manually controlled.</p>
                    </details>

                    <details class="group rounded-2xl border border-slate-300 bg-slate-50 p-6 open:bg-white open:shadow-lg open:shadow-slate-300/40 transition">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 text-base font-semibold text-slate-900">
                            How do invoice payments update?
                            <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition group-open:rotate-180" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">Invoice payment statuses stay aligned with client and influencer workflow updates.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-300 bg-slate-950 py-20">
            <div class="mx-auto grid max-w-7xl gap-8 px-6 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-200">Start your stack</p>
                    <h2 class="mt-4 font-display text-4xl leading-tight text-white sm:text-5xl">Trade tool sprawl for one creator operating system.</h2>
                    <p class="mt-5 max-w-2xl text-sm leading-relaxed text-slate-300">Connect your Instagram account, sync the fundamentals, and run proposals, clients, and invoices from a single operational backbone.</p>
                    <a href="{{ route('auth.facebook', ['driver' => 'instagram']) }}" class="mt-10 inline-flex items-center justify-center gap-2 rounded-full bg-teal-300 px-8 py-3 text-sm font-semibold text-slate-950 transition hover:bg-teal-200">
                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                        Continue with Instagram
                    </a>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-700 bg-slate-900 p-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Creator Note</p>
                        <p class="mt-4 font-display text-3xl leading-tight text-white">“This finally feels like a business cockpit, not a pile of tabs.”</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-teal-300 to-amber-200"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-100">Independent lifestyle creator</p>
                                <p class="text-xs text-slate-400">3 retained brand contracts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.marketing>
