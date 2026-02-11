<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Terms of Service | Influence Me</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-slate-50 text-slate-600 selection:bg-indigo-100 selection:text-indigo-900">
        <main class="mx-auto max-w-4xl px-6 py-16 sm:py-24">
            <div class="mb-10 flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-100">
                    Back to home
                </a>
                <p class="text-xs text-slate-400">Last updated: {{ now()->toFormattedDateString() }}</p>
            </div>

            <header class="mb-10">
                <h1 class="text-4xl font-semibold tracking-tight text-slate-900">Terms of Service</h1>
                <p class="mt-4 text-sm text-slate-500">These terms govern use of Influence Me by influencer and client portal users.</p>
            </header>

            <div class="space-y-8 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">1. Account Eligibility</h2>
                    <p class="text-sm leading-relaxed">Influencer access requires a valid Instagram Professional account authenticated through OAuth. Client portal access requires an invitation from an influencer account owner.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">2. Platform Use</h2>
                    <p class="text-sm leading-relaxed">You agree to provide accurate information, maintain security of your session, and use the platform only for lawful content, collaboration, invoicing, and analytics workflows.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">3. Billing and Payments</h2>
                    <p class="text-sm leading-relaxed">Paid features and invoice payment flows are processed through Stripe. Influence Me does not store full payment card details and may suspend service for chargeback abuse or fraud.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">4. Data Ownership and Access</h2>
                    <p class="text-sm leading-relaxed">Influencers retain ownership of their account and campaign data. Client users only receive access explicitly granted by the associated influencer and cannot access unrelated influencer data.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">5. Service Availability</h2>
                    <p class="text-sm leading-relaxed">Service may be temporarily unavailable due to maintenance, dependency outages, or third-party API limits. We may update these terms and will publish revised versions on this page.</p>
                </section>

                <section class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">6. Contact</h2>
                    <p class="text-sm leading-relaxed">For legal or support inquiries, contact support through your account workspace.</p>
                </section>
            </div>
        </main>
    </body>
</html>
