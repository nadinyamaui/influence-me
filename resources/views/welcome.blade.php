<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Influence Me | The Operating System for Creators</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
            details > summary { list-style: none; }
            details > summary::-webkit-details-marker { display: none; }
        </style>
    </head>
    <body class="bg-white text-slate-600 selection:bg-indigo-100 selection:text-indigo-900">
        <nav class="fixed top-0 left-0 right-0 z-50 border-b border-slate-100 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-16 items-center justify-between">
                    <a href="/" class="flex items-center gap-2 group">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white transition group-hover:bg-indigo-600">
                            <iconify-icon icon="solar:hashtag-square-linear" width="20" stroke-width="1.5"></iconify-icon>
                        </div>
                        <span class="text-sm font-semibold tracking-tight text-slate-900">Influence Me</span>
                    </a>

                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Features</a>
                        <a href="#how-it-works" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">How it Works</a>
                        <a href="/pricing" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Pricing</a>
                        <a href="#faq" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">FAQ</a>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="/client/login" class="hidden sm:inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors">
                            Client Portal
                        </a>
                        <a href="/auth/instagram/redirect" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-medium text-white transition-all hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-200 focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            <iconify-icon icon="solar:instagram-linear" width="16" stroke-width="1.5"></iconify-icon>
                            Continue with Instagram
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <section class="relative pt-32 pb-20 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full -z-10 pointer-events-none">
                <div class="absolute top-20 left-20 w-72 h-72 bg-indigo-50 rounded-full blur-3xl opacity-60"></div>
                <div class="absolute top-40 right-20 w-96 h-96 bg-slate-50 rounded-full blur-3xl opacity-60"></div>
            </div>

            <div class="mx-auto max-w-5xl px-6 text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 mb-8 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-500"></span>
                    Built for professional creators &amp; brand partnerships
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-semibold tracking-tight text-slate-900 mb-6 leading-[1.1]">
                    Run your creator business <br class="hidden sm:block"> like a well-oiled machine.
                </h1>

                <p class="mx-auto max-w-2xl text-lg text-slate-500 mb-10 leading-relaxed">
                    Centralize your operations. Sync Instagram insights, manage client portals, create proposals, and automate invoicing-all without the chaos of spreadsheets.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/auth/instagram/redirect" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-8 py-3.5 text-sm font-medium text-white transition-all hover:bg-slate-800 hover:scale-[1.02] shadow-xl shadow-slate-200/50">
                        <iconify-icon icon="solar:instagram-linear" width="18" stroke-width="1.5"></iconify-icon>
                        Continue with Instagram
                    </a>
                    <a href="/pricing" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-8 py-3.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-50 hover:border-slate-300">
                        View Pricing
                    </a>
                </div>

                <p class="mt-6 text-xs text-slate-400">
                    Official Instagram Partner API &bull; No password required
                </p>
            </div>
        </section>

        <section class="pb-24 px-6">
            <div class="mx-auto max-w-6xl rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-200/50">
                <div class="aspect-[16/9] w-full rounded-xl bg-slate-50 overflow-hidden relative border border-slate-100">
                    <div class="absolute inset-0 grid grid-cols-12 gap-4 p-6 sm:p-10">
                        <div class="hidden sm:block col-span-2 h-full rounded-lg bg-white border border-slate-200/60 shadow-sm"></div>
                        <div class="col-span-12 sm:col-span-10 grid grid-rows-3 gap-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg border border-slate-200/60 shadow-sm p-4 flex flex-col justify-between">
                                    <div class="h-8 w-8 rounded-full bg-indigo-50 mb-2"></div>
                                    <div class="h-2 w-16 bg-slate-100 rounded mb-1"></div>
                                    <div class="h-4 w-10 bg-slate-200 rounded"></div>
                                </div>
                                <div class="bg-white rounded-lg border border-slate-200/60 shadow-sm p-4 flex flex-col justify-between">
                                    <div class="h-8 w-8 rounded-full bg-emerald-50 mb-2"></div>
                                    <div class="h-2 w-16 bg-slate-100 rounded mb-1"></div>
                                    <div class="h-4 w-10 bg-slate-200 rounded"></div>
                                </div>
                                <div class="bg-white rounded-lg border border-slate-200/60 shadow-sm p-4 flex flex-col justify-between">
                                    <div class="h-8 w-8 rounded-full bg-orange-50 mb-2"></div>
                                    <div class="h-2 w-16 bg-slate-100 rounded mb-1"></div>
                                    <div class="h-4 w-10 bg-slate-200 rounded"></div>
                                </div>
                            </div>
                            <div class="row-span-2 bg-white rounded-lg border border-slate-200/60 shadow-sm p-6 relative overflow-hidden">
                                <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-indigo-50/50 to-transparent"></div>
                                <div class="h-full w-full flex items-end justify-between px-4 gap-2">
                                    <div class="w-full bg-indigo-100 h-[40%] rounded-t-sm"></div>
                                    <div class="w-full bg-indigo-100 h-[60%] rounded-t-sm"></div>
                                    <div class="w-full bg-indigo-100 h-[50%] rounded-t-sm"></div>
                                    <div class="w-full bg-indigo-500 h-[80%] rounded-t-sm shadow-lg shadow-indigo-200"></div>
                                    <div class="w-full bg-indigo-100 h-[70%] rounded-t-sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="py-24 bg-slate-50/50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-16 max-w-2xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl mb-4">Everything you need to scale.</h2>
                    <p class="text-lg text-slate-500">Move beyond DM negotiations and messy screenshots. Professionalize your workflow with tools built specifically for the creator economy.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <iconify-icon icon="solar:instagram-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Instagram Sync</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Secure OAuth connection instantly imports your media, profile stats, and audience demographics. No manual data entry.</p>
                    </div>

                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                            <iconify-icon icon="solar:calendar-mark-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Content Operations</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Plan your feed with a drag-and-drop calendar. Track upcoming deliverables and keep your campaigns on schedule.</p>
                    </div>

                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <iconify-icon icon="solar:users-group-rounded-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Client Portal &amp; CRM</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Give brands a dedicated login to view live campaign stats and approve content, keeping your DMs clean.</p>
                    </div>

                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <iconify-icon icon="solar:document-add-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Proposals</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Create beautiful, branded proposals in minutes. Send them for digital approval and seamlessly convert them into invoices.</p>
                    </div>

                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                            <iconify-icon icon="solar:card-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Invoicing &amp; Payments</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Powered by Stripe. Send professional invoices and get paid faster with direct checkout links. Track payment status in real-time.</p>
                    </div>

                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-lg hover:shadow-slate-200/50">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                            <iconify-icon icon="solar:chart-2-linear" width="24" stroke-width="1.5"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Advanced Analytics</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">Deep dive into post performance. Generate client-ready reports that prove your ROI without taking screenshots.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="py-24 border-y border-slate-100 bg-white">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Streamlined workflow</h2>
                    <p class="mt-4 text-lg text-slate-500">From connection to cash flow in four steps.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                    <div class="hidden md:block absolute top-6 left-0 w-full h-px bg-slate-100 -z-10"></div>

                    <div class="relative pt-6 md:pt-0">
                        <div class="flex items-center gap-4 md:block">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm text-slate-900 font-semibold mb-4 mx-auto z-10">1</div>
                            <div class="text-left md:text-center">
                                <h3 class="text-base font-medium text-slate-900">Connect</h3>
                                <p class="mt-2 text-sm text-slate-500">Sign in securely with your Instagram Professional account.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative pt-6 md:pt-0">
                        <div class="flex items-center gap-4 md:block">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm text-slate-900 font-semibold mb-4 mx-auto z-10">2</div>
                            <div class="text-left md:text-center">
                                <h3 class="text-base font-medium text-slate-900">Build</h3>
                                <p class="mt-2 text-sm text-slate-500">Sync media, plan content, and generate proposals for brands.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative pt-6 md:pt-0">
                        <div class="flex items-center gap-4 md:block">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm text-slate-900 font-semibold mb-4 mx-auto z-10">3</div>
                            <div class="text-left md:text-center">
                                <h3 class="text-base font-medium text-slate-900">Deliver</h3>
                                <p class="mt-2 text-sm text-slate-500">Clients approve work and view live analytics via their portal.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative pt-6 md:pt-0">
                        <div class="flex items-center gap-4 md:block">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm text-slate-900 font-semibold mb-4 mx-auto z-10">4</div>
                            <div class="text-left md:text-center">
                                <h3 class="text-base font-medium text-slate-900">Earn</h3>
                                <p class="mt-2 text-sm text-slate-500">Send invoices and receive payouts directly to your bank.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="py-24 bg-slate-50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Simple, transparent pricing</h2>
                    <p class="mt-4 text-slate-500">Start for free, upgrade as you grow.</p>
                    <div class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 border border-indigo-100">
                        <iconify-icon icon="solar:shield-check-linear" width="14" stroke-width="1.5"></iconify-icon>
                        Instagram OAuth required for all influencer accounts
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="flex flex-col rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-slate-900">Starter</h3>
                            <p class="text-sm text-slate-500 mt-1">For new creators.</p>
                        </div>
                        <div class="mb-6 flex items-baseline">
                            <span class="text-3xl font-semibold text-slate-900">$0</span>
                            <span class="text-sm text-slate-500 ml-1">/mo</span>
                        </div>
                        <ul class="mb-8 space-y-4 flex-1">
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                Instagram Sync (1 Account)
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                Basic Media Gallery
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                3 Active Proposals
                            </li>
                        </ul>
                        <a href="/auth/instagram/redirect" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                            Get Started
                        </a>
                    </div>

                    <div class="relative flex flex-col rounded-2xl bg-slate-900 p-8 border border-slate-800 shadow-xl shadow-slate-200/50 scale-105 z-10">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                            Most Popular
                        </div>
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-white">Professional</h3>
                            <p class="text-sm text-slate-400 mt-1">For growing businesses.</p>
                        </div>
                        <div class="mb-6 flex items-baseline">
                            <span class="text-3xl font-semibold text-white">$29</span>
                            <span class="text-sm text-slate-400 ml-1">/mo</span>
                        </div>
                        <ul class="mb-8 space-y-4 flex-1">
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <iconify-icon icon="solar:check-circle-bold" class="text-indigo-400"></iconify-icon>
                                Instagram Sync (3 Accounts)
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <iconify-icon icon="solar:check-circle-bold" class="text-indigo-400"></iconify-icon>
                                Unlimited Client Portals
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <iconify-icon icon="solar:check-circle-bold" class="text-indigo-400"></iconify-icon>
                                Invoicing &amp; Payments
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <iconify-icon icon="solar:check-circle-bold" class="text-indigo-400"></iconify-icon>
                                Advanced Analytics
                            </li>
                        </ul>
                        <a href="/auth/instagram/redirect" class="block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-900/30">
                            Start Free Trial
                        </a>
                    </div>

                    <div class="flex flex-col rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-slate-900">Agency</h3>
                            <p class="text-sm text-slate-500 mt-1">For talent managers.</p>
                        </div>
                        <div class="mb-6 flex items-baseline">
                            <span class="text-3xl font-semibold text-slate-900">$99</span>
                            <span class="text-sm text-slate-500 ml-1">/mo</span>
                        </div>
                        <ul class="mb-8 space-y-4 flex-1">
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                Unlimited Accounts
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                Team Members
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <iconify-icon icon="solar:check-circle-linear" class="text-indigo-600"></iconify-icon>
                                White-label Reports
                            </li>
                        </ul>
                        <a href="/pricing" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-24 bg-white">
            <div class="mx-auto max-w-3xl px-6">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 text-center mb-12">Frequently Asked Questions</h2>

                <div class="space-y-4">
                    <details class="group border border-slate-200 rounded-lg bg-slate-50/50 open:bg-white open:shadow-sm transition-all duration-300">
                        <summary class="flex cursor-pointer items-center justify-between p-6 font-medium text-slate-900">
                            Do I need a password to sign up?
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="text-slate-400 transition-transform group-open:rotate-180"></iconify-icon>
                        </summary>
                        <div class="px-6 pb-6 text-sm text-slate-500 leading-relaxed">
                            No. Influence Me uses OAuth verification. You simply log in with your Instagram account. This is more secure and ensures we are syncing data for the correct verified owner.
                        </div>
                    </details>

                    <details class="group border border-slate-200 rounded-lg bg-slate-50/50 open:bg-white open:shadow-sm transition-all duration-300">
                        <summary class="flex cursor-pointer items-center justify-between p-6 font-medium text-slate-900">
                            Can my clients log in separately?
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="text-slate-400 transition-transform group-open:rotate-180"></iconify-icon>
                        </summary>
                        <div class="px-6 pb-6 text-sm text-slate-500 leading-relaxed">
                            Yes. Each client gets access to a secure Client Portal where they can view proposals, pay invoices, and see live analytics for their campaigns. They cannot see your private data or other clients.
                        </div>
                    </details>

                    <details class="group border border-slate-200 rounded-lg bg-slate-50/50 open:bg-white open:shadow-sm transition-all duration-300">
                        <summary class="flex cursor-pointer items-center justify-between p-6 font-medium text-slate-900">
                            Does Influence Me auto-post to Instagram?
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="text-slate-400 transition-transform group-open:rotate-180"></iconify-icon>
                        </summary>
                        <div class="px-6 pb-6 text-sm text-slate-500 leading-relaxed">
                            Currently, our scheduling tools are for planning, approval workflows, and internal tracking. We do not automatically publish content to your feed in this version, prioritizing account safety and manual control.
                        </div>
                    </details>

                    <details class="group border border-slate-200 rounded-lg bg-slate-50/50 open:bg-white open:shadow-sm transition-all duration-300">
                        <summary class="flex cursor-pointer items-center justify-between p-6 font-medium text-slate-900">
                            How are payments handled?
                            <iconify-icon icon="solar:alt-arrow-down-linear" class="text-slate-400 transition-transform group-open:rotate-180"></iconify-icon>
                        </summary>
                        <div class="px-6 pb-6 text-sm text-slate-500 leading-relaxed">
                            We integrate directly with Stripe Connect. You can generate professional invoices or quick checkout links. Funds are transferred directly to your connected bank account.
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white relative overflow-hidden border-t border-slate-100">
            <div class="mx-auto max-w-4xl px-6 text-center relative z-10">
                <h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl mb-6">Ready to professionalize your influence?</h2>
                <div class="flex flex-col items-center gap-4">
                    <a href="/auth/instagram/redirect" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-8 py-3.5 text-sm font-medium text-white transition-all hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-200">
                        <iconify-icon icon="solar:instagram-linear" width="18" stroke-width="1.5"></iconify-icon>
                        Continue with Instagram
                    </a>
                    <a href="/client/login" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                        Client Portal Login
                    </a>
                </div>
            </div>
        </section>

        <footer class="border-t border-slate-100 bg-slate-50 py-12">
            <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-col items-center md:items-start gap-2">
                    <div class="flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded bg-slate-900 text-white">
                            <iconify-icon icon="solar:hashtag-square-linear" width="14" stroke-width="1.5"></iconify-icon>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">Influence Me</span>
                    </div>
                    <p class="text-xs text-slate-500">The operating system for modern creators.</p>
                </div>

                <div class="flex gap-8">
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Privacy Policy</a>
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Terms of Service</a>
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Contact Support</a>
                </div>

                <p class="text-xs text-slate-400">&copy; 2023 Influence Me Inc.</p>
            </div>
        </footer>
    </body>
</html>
