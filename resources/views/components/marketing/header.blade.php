<nav class="fixed top-0 left-0 right-0 z-50 border-b border-slate-100 bg-white/80 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-6">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white transition group-hover:bg-indigo-600">
                </div>
                <span class="text-sm font-semibold tracking-tight text-slate-900">Okacrm</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}#features" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Features</a>
                <a href="{{ route('home') }}#how-it-works" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">How it Works</a>
                <a href="{{ route('home') }}#pricing" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">Pricing</a>
                <a href="{{ route('home') }}#faq" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">FAQ</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('auth.facebook') }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-medium text-white transition-all hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-200 focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                    Continue with Instagram
                </a>
            </div>
        </div>
    </div>
</nav>
