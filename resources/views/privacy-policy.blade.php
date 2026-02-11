<x-layouts.marketing title="Privacy Policy | Influence Me" body-class="bg-slate-50 text-slate-700">
        <main class="mx-auto max-w-4xl px-6 pb-16 pt-28 sm:pb-20 sm:pt-32">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
                <div class="mb-10 border-b border-slate-100 pb-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Back to home</a>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Privacy Policy</h1>
                    <p class="mt-3 text-sm text-slate-500">Last updated: {{ now()->toFormattedDateString() }}</p>
                </div>

                <div class="space-y-8 text-sm leading-7 text-slate-600 sm:text-base">
                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">1. Information We Collect</h2>
                        <p>Influence Me collects account profile details, connected platform account identifiers, published media metadata, proposal and invoice records, and basic usage analytics required to provide the product features.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">2. How We Use Information</h2>
                        <p>We use collected information to authenticate users, sync connected account data, power content and analytics dashboards, facilitate client collaboration, and process invoice workflows including Stripe-powered payments.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">3. Data Sharing</h2>
                        <p>We do not sell personal data. Data is shared only with service providers needed to operate the platform, such as infrastructure hosting, Instagram and TikTok platform integrations, and Stripe for payment processing.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">4. Data Ownership and Access</h2>
                        <p>Influencer users can access only their own workspace data. Client users can access only records scoped to their client organization, including proposals, invoices, and explicitly shared analytics.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">5. Security and Retention</h2>
                        <p>We apply technical and organizational safeguards designed to protect data in transit and at rest. We retain data only as long as needed for product operation, legal compliance, and legitimate business requirements.</p>
                    </section>

                    <section class="space-y-3">
                        <h2 class="text-xl font-semibold text-slate-900">6. Contact</h2>
                        <p>For privacy questions or data requests, contact our support team at <a href="mailto:privacy@influenceme.app" class="font-medium text-indigo-600 hover:text-indigo-500">privacy@influenceme.app</a>.</p>
                    </section>
                </div>
            </div>
        </main>
</x-layouts.marketing>
