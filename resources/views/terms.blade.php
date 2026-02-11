<x-layouts.marketing title="Terms of Service | Influence Me" body-class="bg-slate-50 text-slate-700">
        <main class="mx-auto max-w-4xl px-6 pb-16 pt-28 sm:pb-20 sm:pt-32">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
                <div class="mb-10 border-b border-slate-100 pb-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Back to home</a>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Terms of Service</h1>
                    <p class="mt-3 text-sm text-slate-500">Last updated: {{ now()->toFormattedDateString() }}</p>
                </div>

                <div class="space-y-8 text-sm leading-7 text-slate-600 sm:text-base">
                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">1. Account Eligibility</h2>
                        <p>Influencer access requires a valid Instagram Professional account authenticated through OAuth. Client portal access requires an invitation from an influencer account owner.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">2. Platform Use</h2>
                        <p>You agree to provide accurate information, maintain security of your session, and use the platform only for lawful content, collaboration, invoicing, and analytics workflows.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">3. Billing and Payments</h2>
                        <p>Paid features and invoice payment flows are processed through Stripe. Influence Me does not store full payment card details and may suspend service for chargeback abuse or fraud.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">4. Data Ownership and Access</h2>
                        <p>Influencers retain ownership of their account and campaign data. Client users only receive access explicitly granted by the associated influencer and cannot access unrelated influencer data.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">5. Service Availability</h2>
                        <p>Service may be temporarily unavailable due to maintenance, dependency outages, or third-party API limits. We may update these terms and will publish revised versions on this page.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">6. Contact</h2>
                        <p>For legal or support inquiries, contact support through your account workspace.</p>
                    </section>
                </div>
            </div>
        </main>
</x-layouts.marketing>
