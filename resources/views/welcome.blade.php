<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Influence Me | The Creator OS</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
/* Dot Pattern Background */
.bg-grid-pattern {
background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
background-size: 24px 24px;
}
/* Hide scrollbar for clean UI */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
details > summary { list-style: none; }
details > summary::-webkit-details-marker { display: none; }
</style>
</head>
<body class="bg-slate-50 text-slate-600 selection:bg-slate-900 selection:text-white">

    <!-- Floating Dock Navigation (Unique Layout) -->
    <nav class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 transform">
        <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-white/90 p-1.5 shadow-xl shadow-slate-200/50 backdrop-blur-md">
            <a href="#" class="group flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white transition-all hover:scale-110">
                <iconify-icon icon="solar:hashtag-square-linear" width="20"></iconify-icon>
            </a>
            <div class="mx-2 h-4 w-px bg-slate-200"></div>
            <a href="#features" class="group relative flex h-10 items-center px-4 rounded-full hover:bg-slate-50 transition-colors">
                <span class="text-xs font-medium text-slate-600 group-hover:text-slate-900">Features</span>
            </a>
            <a href="#pricing" class="group relative flex h-10 items-center px-4 rounded-full hover:bg-slate-50 transition-colors">
                <span class="text-xs font-medium text-slate-600 group-hover:text-slate-900">Pricing</span>
            </a>
            <a href="/client/login" class="group relative flex h-10 items-center px-4 rounded-full hover:bg-slate-50 transition-colors">
                <span class="text-xs font-medium text-slate-600 group-hover:text-slate-900">Portal</span>
            </a>
            <div class="mx-2 h-4 w-px bg-slate-200"></div>
            <a href="/auth/instagram/redirect" class="flex h-10 items-center gap-2 rounded-full bg-indigo-50 px-4 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-100">
                <iconify-icon icon="solar:instagram-linear" width="16"></iconify-icon>
                <span class="hidden sm:inline">Connect</span>
            </a>
        </div>
    </nav>

    <main class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8 mb-24">

        <!-- Header / Hero Grid -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">

            <!-- Branding Block -->
            <div class="col-span-1 lg:col-span-8 relative flex min-h-[500px] flex-col justify-between overflow-hidden rounded-3xl bg-white p-8 sm:p-12 shadow-sm ring-1 ring-slate-200/60">
                <div class="bg-grid-pattern absolute inset-0 opacity-[0.15]"></div>

                <div class="relative z-10 flex items-start justify-between">
                    <div class="flex items-center gap-2 rounded-full bg-slate-50 border border-slate-100 px-3 py-1">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500">v2.0 Live</span>
                    </div>
                </div>

                <div class="relative z-10 max-w-2xl mt-12">
                    <h1 class="text-6xl font-semibold leading-[0.9] tracking-tighter text-slate-900 sm:text-7xl lg:text-8xl">
                        Creator<br>
                        <span class="text-slate-300">Operating System.</span>
                    </h1>
                    <p class="mt-8 text-lg font-medium text-slate-500 max-w-lg leading-relaxed">
                        Stop managing your business in DMs. Sync Instagram, generate proposals, and get paid—all in one unified interface.
                    </p>
                </div>

                <div class="relative z-10 mt-12 flex flex-wrap gap-4">
                    <a href="/auth/instagram/redirect" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-medium text-white transition-transform hover:-translate-y-0.5 hover:shadow-lg">
                        Start for free
                        <iconify-icon icon="solar:arrow-right-linear"></iconify-icon>
                    </a>
                </div>
            </div>

            <!-- Side Widget: Stats -->
            <div class="col-span-1 lg:col-span-4 flex flex-col gap-4 lg:gap-6">
                <!-- Widget 1: Revenue -->
                <div class="flex-1 rounded-3xl bg-slate-900 p-8 text-white shadow-xl ring-1 ring-slate-900 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-8 opacity-10 transition-opacity group-hover:opacity-20">
                        <iconify-icon icon="solar:wallet-money-linear" width="120"></iconify-icon>
                    </div>
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div>
                            <div class="text-sm font-medium text-slate-400">Monthly Revenue</div>
                            <div class="mt-2 text-4xl font-semibold tracking-tight">$12,450.00</div>
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400">
                                <iconify-icon icon="solar:trending-up-linear"></iconify-icon>
                                +18% vs last month
                            </div>
                        </div>
                        <div class="mt-8">
                            <div class="h-1 w-full overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full w-3/4 rounded-full bg-indigo-500"></div>
                            </div>
                            <div class="mt-2 flex justify-between text-xs text-slate-400">
                                <span>Goal: $15k</span>
                                <span>83%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widget 2: Active Deals -->
                <div class="flex-1 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <div class="text-sm font-medium text-slate-500">Active Deals</div>
                        <iconify-icon icon="solar:briefcase-linear" class="text-slate-300"></iconify-icon>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">N</div>
                            <div class="flex-1">
                                <div class="text-xs font-semibold text-slate-900">Nike Spring</div>
                                <div class="text-[10px] text-slate-400">Contract Signed</div>
                            </div>
                            <div class="text-xs font-medium text-slate-900">$4.5k</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-xs font-bold">S</div>
                            <div class="flex-1">
                                <div class="text-xs font-semibold text-slate-900">Sephora Glow</div>
                                <div class="text-[10px] text-slate-400">Proposal Sent</div>
                            </div>
                            <div class="text-xs font-medium text-slate-900">$2.2k</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bento Grid Features -->
        <div id="features" class="mt-4 lg:mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">

            <!-- Feature: Sync (Large) -->
            <div class="col-span-1 md:col-span-2 row-span-2 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60 transition-all hover:ring-slate-300">
                <div class="flex h-full flex-col">
                    <div class="mb-6">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-900">
                            <iconify-icon icon="solar:refresh-circle-linear" width="24"></iconify-icon>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">Instagram Sync</h3>
                        <p class="mt-2 text-sm text-slate-500">Instant OAuth connection. We pull your media, insights, and audience demographics automatically.</p>
                    </div>
                    <div class="mt-auto overflow-hidden rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <!-- Abstract Visual -->
                        <div class="flex gap-4 opacity-75">
                            <div class="h-32 w-24 rounded-lg bg-slate-200"></div>
                            <div class="h-32 w-24 rounded-lg bg-slate-200"></div>
                            <div class="h-32 w-24 rounded-lg bg-slate-200"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature: Invoicing -->
            <div class="col-span-1 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60 transition-all hover:ring-slate-300">
                <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <iconify-icon icon="solar:card-linear" width="24"></iconify-icon>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Smart Invoicing</h3>
                <p class="mt-2 text-xs text-slate-500">Stripe integration for instant payouts. Auto-reminders for late payments.</p>
            </div>

            <!-- Feature: CRM -->
            <div class="col-span-1 rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60 transition-all hover:ring-slate-300">
                <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <iconify-icon icon="solar:users-group-rounded-linear" width="24"></iconify-icon>
                </div>
                <h3 class="text-base font-semibold text-slate-900">Client CRM</h3>
                <p class="mt-2 text-xs text-slate-500">Keep contacts organized. Track communication history and deal status.</p>
            </div>

            <!-- Feature: Portals (Wide) -->
            <div class="col-span-1 md:col-span-2 rounded-3xl bg-slate-900 p-8 text-white shadow-sm ring-1 ring-slate-900 transition-all hover:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                            <iconify-icon icon="solar:laptop-linear" width="24"></iconify-icon>
                        </div>
                        <h3 class="text-xl font-semibold tracking-tight">Client Portals</h3>
                        <p class="mt-2 text-sm text-slate-400 max-w-xs">Give brands a professional login to view stats and approve content.</p>
                    </div>
                    <div class="hidden sm:block">
                        <div class="h-16 w-32 rounded-lg bg-white/10 border border-white/20"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Section: How It Works (Timeline Style) -->
        <div class="mt-4 lg:mt-6 rounded-3xl bg-white p-8 sm:p-12 shadow-sm ring-1 ring-slate-200/60">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 mb-12">Workflow</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                <div class="absolute top-8 left-0 w-full h-px bg-slate-100 hidden md:block"></div>

                <div class="relative group">
                    <div class="h-4 w-4 rounded-full bg-slate-200 border-4 border-white shadow-sm mb-6 relative z-10 group-hover:bg-indigo-500 transition-colors"></div>
                    <h4 class="text-sm font-semibold text-slate-900">1. Connect</h4>
                    <p class="mt-1 text-xs text-slate-500">Auth with Instagram Professional.</p>
                </div>

                <div class="relative group">
                    <div class="h-4 w-4 rounded-full bg-slate-200 border-4 border-white shadow-sm mb-6 relative z-10 group-hover:bg-indigo-500 transition-colors"></div>
                    <h4 class="text-sm font-semibold text-slate-900">2. Proposal</h4>
                    <p class="mt-1 text-xs text-slate-500">Build &amp; send packages to brands.</p>
                </div>

                <div class="relative group">
                    <div class="h-4 w-4 rounded-full bg-slate-200 border-4 border-white shadow-sm mb-6 relative z-10 group-hover:bg-indigo-500 transition-colors"></div>
                    <h4 class="text-sm font-semibold text-slate-900">3. Approval</h4>
                    <p class="mt-1 text-xs text-slate-500">Client approves via portal.</p>
                </div>

                <div class="relative group">
                    <div class="h-4 w-4 rounded-full bg-slate-200 border-4 border-white shadow-sm mb-6 relative z-10 group-hover:bg-indigo-500 transition-colors"></div>
                    <h4 class="text-sm font-semibold text-slate-900">4. Payout</h4>
                    <p class="mt-1 text-xs text-slate-500">Receive funds instantly.</p>
                </div>
            </div>
        </div>

        <!-- Section: Pricing (Horizontal Cards) -->
        <div id="pricing" class="mt-4 lg:mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">

            <!-- Free -->
            <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60">
                <div class="text-sm font-medium text-slate-500">Starter</div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">$0</div>
                <p class="text-xs text-slate-400 mt-1">/month</p>
                <hr class="my-6 border-slate-100">
                <ul class="space-y-3">
                    <li class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <iconify-icon icon="solar:check-circle-linear" class="text-slate-400"></iconify-icon> 1 Account
                    </li>
                    <li class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <iconify-icon icon="solar:check-circle-linear" class="text-slate-400"></iconify-icon> 3 Proposals
                    </li>
                </ul>
                <a href="#" class="mt-8 block w-full rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-semibold text-slate-900 hover:bg-slate-50">Sign Up</a>
            </div>

            <!-- Pro -->
            <div class="rounded-3xl bg-indigo-50 p-8 shadow-sm ring-1 ring-indigo-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 rounded-bl-xl bg-indigo-200 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-700">Popular</div>
                <div class="text-sm font-medium text-indigo-900">Pro</div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-indigo-900">$29</div>
                <p class="text-xs text-indigo-400 mt-1">/month</p>
                <hr class="my-6 border-indigo-200">
                <ul class="space-y-3">
                    <li class="flex items-center gap-2 text-xs font-medium text-indigo-900">
                        <iconify-icon icon="solar:check-circle-bold" class="text-indigo-500"></iconify-icon> 3 Accounts
                    </li>
                    <li class="flex items-center gap-2 text-xs font-medium text-indigo-900">
                        <iconify-icon icon="solar:check-circle-bold" class="text-indigo-500"></iconify-icon> Unlimited Portals
                    </li>
                    <li class="flex items-center gap-2 text-xs font-medium text-indigo-900">
                        <iconify-icon icon="solar:check-circle-bold" class="text-indigo-500"></iconify-icon> Invoicing
                    </li>
                </ul>
                <a href="#" class="mt-8 block w-full rounded-xl bg-indigo-600 py-2 text-center text-xs font-semibold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500">Get Started</a>
            </div>

            <!-- Agency -->
            <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/60">
                <div class="text-sm font-medium text-slate-500">Agency</div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">$99</div>
                <p class="text-xs text-slate-400 mt-1">/month</p>
                <hr class="my-6 border-slate-100">
                <ul class="space-y-3">
                    <li class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <iconify-icon icon="solar:check-circle-linear" class="text-slate-400"></iconify-icon> Unlimited Accounts
                    </li>
                    <li class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <iconify-icon icon="solar:check-circle-linear" class="text-slate-400"></iconify-icon> Team Access
                    </li>
                </ul>
                <a href="#" class="mt-8 block w-full rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-semibold text-slate-900 hover:bg-slate-50">Contact Us</a>
            </div>
        </div>

        <!-- FAQ Area -->
        <div class="mt-4 lg:mt-6 rounded-3xl bg-white p-8 sm:p-12 shadow-sm ring-1 ring-slate-200/60">
            <h2 class="text-xl font-semibold text-slate-900 mb-6">FAQ</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                <details class="group py-2">
                    <summary class="flex cursor-pointer items-center justify-between font-medium text-slate-900 text-sm">
                        Do I need a password?
                        <iconify-icon icon="solar:add-circle-linear" class="text-slate-400 group-open:rotate-45 transition-transform"></iconify-icon>
                    </summary>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">No. We use OAuth verification. Simply log in with your Instagram account.</p>
                </details>
                <details class="group py-2">
                    <summary class="flex cursor-pointer items-center justify-between font-medium text-slate-900 text-sm">
                        Can clients see my other deals?
                        <iconify-icon icon="solar:add-circle-linear" class="text-slate-400 group-open:rotate-45 transition-transform"></iconify-icon>
                    </summary>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">Never. Client portals are sandboxed. They only see the data and proposals you explicitly share with them.</p>
                </details>
                <details class="group py-2">
                    <summary class="flex cursor-pointer items-center justify-between font-medium text-slate-900 text-sm">
                        Is payment secure?
                        <iconify-icon icon="solar:add-circle-linear" class="text-slate-400 group-open:rotate-45 transition-transform"></iconify-icon>
                    </summary>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed">Yes, we process everything via Stripe Connect. Funds go directly to your bank.</p>
                </details>
            </div>
        </div>

        <footer class="mt-12 text-center text-[10px] text-slate-400">
            © 2023 Influence Me. <a href="#" class="hover:text-slate-600">Privacy</a> • <a href="#" class="hover:text-slate-600">Terms</a>
        </footer>

    </main>

</body>
</html>
