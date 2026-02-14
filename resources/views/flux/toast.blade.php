@props([
    'position' => 'top right',
])

<div
    x-data="{
        toasts: [],
        nextToastId: 1,
        enqueue(detail) {
            const duration = Number(detail.duration ?? 5000);
            const slots = detail.slots ?? {};
            const dataset = detail.dataset ?? {};
            const text = slots.text ?? '';
            const heading = slots.heading ?? '';

            if (! text && ! heading) {
                return;
            }

            const toast = {
                id: this.nextToastId++,
                text,
                heading,
                variant: dataset.variant ?? 'success',
                position: dataset.position ?? @js($position),
            };

            this.toasts.push(toast);

            if (duration > 0) {
                setTimeout(() => this.dismiss(toast.id), duration);
            }
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
        containerClasses(position) {
            const value = (position ?? 'top right').toLowerCase();

            if (value.includes('bottom')) {
                if (value.includes('left')) {
                    return 'bottom-0 left-0 items-start';
                }

                if (value.includes('center')) {
                    return 'bottom-0 left-1/2 -translate-x-1/2 items-center';
                }

                return 'bottom-0 right-0 items-end';
            }

            if (value.includes('left')) {
                return 'top-0 left-0 items-start';
            }

            if (value.includes('center')) {
                return 'top-0 left-1/2 -translate-x-1/2 items-center';
            }

            return 'top-0 right-0 items-end';
        },
        toastClasses(variant) {
            const value = (variant ?? 'success').toLowerCase();

            if (value === 'danger' || value === 'error') {
                return 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/80 dark:text-rose-100';
            }

            if (value === 'warning') {
                return 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/80 dark:text-amber-100';
            }

            if (value === 'info') {
                return 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/80 dark:text-sky-100';
            }

            return 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/80 dark:text-emerald-100';
        },
        headingClasses(variant) {
            const value = (variant ?? 'success').toLowerCase();

            if (value === 'danger' || value === 'error') {
                return 'text-rose-900 dark:text-rose-100';
            }

            if (value === 'warning') {
                return 'text-amber-900 dark:text-amber-100';
            }

            if (value === 'info') {
                return 'text-sky-900 dark:text-sky-100';
            }

            return 'text-emerald-900 dark:text-emerald-100';
        },
    }"
    x-on:toast-show.window="enqueue($event.detail ?? {})"
    class="pointer-events-none fixed z-[120] flex w-full max-w-sm flex-col gap-2 p-4"
    :class="containerClasses(toasts[0]?.position ?? @js($position))"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition.opacity.scale.90
            class="pointer-events-auto w-full rounded-xl border px-4 py-3 shadow-lg backdrop-blur"
            :class="toastClasses(toast.variant)"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p x-show="toast.heading" class="text-sm font-semibold" :class="headingClasses(toast.variant)" x-text="toast.heading"></p>
                    <p class="text-sm" x-text="toast.text"></p>
                </div>

                <button
                    type="button"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-current/20 text-current transition hover:bg-black/5 dark:hover:bg-white/10"
                    @click="dismiss(toast.id)"
                    aria-label="Dismiss notification"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </template>
</div>
