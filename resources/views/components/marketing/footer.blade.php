<footer class="border-t border-slate-100 bg-slate-50 py-12">
    <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex flex-col items-center md:items-start gap-2">
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded bg-slate-900 text-white">
                </div>
                <span class="text-sm font-semibold text-slate-900">Influence Me</span>
            </div>
            <p class="text-xs text-slate-500">The operating system for modern creators.</p>
        </div>

        <div class="flex gap-8">
            <a href="{{ route('privacy-policy') }}" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Terms of Service</a>
            <a href="{{ route('portal.login') }}" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Client Portal</a>
            <a href="#" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">Contact Support</a>
        </div>

        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Influence Me Inc.</p>
    </div>
</footer>
