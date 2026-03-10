<div class="min-h-screen flex flex-col" x-data="{ mobileCart: false }">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-stone-200">
        <div class="mx-auto max-w-7xl flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
            <a href="{{ route('athenas') }}" class="flex items-center gap-3">
                <div class="size-9 rounded-full bg-gradient-to-br from-amber-600 to-amber-800 flex items-center justify-center">
                    <span class="font-serif-display text-white text-lg font-bold leading-none">A</span>
                </div>
                <div>
                    <span class="font-serif-display text-xl font-bold text-stone-900 tracking-wide">Athenas</span>
                    <span class="hidden sm:inline text-xs text-stone-400 ml-1.5 tracking-widest uppercase">Boutique</span>
                </div>
            </a>

            {{-- Cart button --}}
            <button
                wire:click="toggleCart"
                x-on:click.sm="mobileCart = !mobileCart"
                class="relative inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span class="hidden sm:inline">Carrito</span>
                @if($this->cartCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white">
                        {{ $this->cartCount }}
                    </span>
                @endif
            </button>
        </div>
    </header>

    {{-- ── Hero ───────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-stone-900 via-stone-800 to-amber-900 py-16 sm:py-24 text-center">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.15&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative mx-auto max-w-3xl px-4">
            <h1 class="font-serif-display text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-wide">
                Athenas Boutique
            </h1>
            <p class="text-amber-200/80 text-lg sm:text-xl max-w-xl mx-auto leading-relaxed">
                Accesorios elegantes al mejor precio. Aleacion de cinc con acabado chapado.
            </p>
            <div class="mt-6 flex items-center justify-center gap-4 text-sm text-stone-400">
                <a href="https://www.instagram.com/{{ config('athenas.instagram') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-amber-300 transition">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    @athenasboutique1
                </a>
                <span class="text-stone-600">|</span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    +58 412-465-2070
                </span>
            </div>
        </div>
    </section>

    {{-- ── Main content ───────────────────────────────────────────── --}}
    <main class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-8">

        {{-- Filters bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-8">
            <div class="relative flex-1 w-full sm:max-w-xs">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-stone-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar producto..."
                    class="w-full rounded-lg border border-stone-300 bg-white py-2 pl-10 pr-4 text-sm text-stone-800 placeholder-stone-400 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition"
                />
            </div>

            <select
                wire:model.live="categoryFilter"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition"
            >
                <option value="">Todas las categorias</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="finishFilter"
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition"
            >
                <option value="">Todos los acabados</option>
                @foreach($this->finishes as $finish)
                    <option value="{{ $finish }}">{{ $finish }}</option>
                @endforeach
            </select>

            @if($search || $categoryFilter || $finishFilter)
                <button wire:click="clearFilters" class="text-sm text-amber-700 hover:text-amber-900 underline underline-offset-2 transition">
                    Limpiar filtros
                </button>
            @endif

            <span class="text-sm text-stone-400 ml-auto">
                {{ count($this->products) }} productos
            </span>
        </div>

        {{-- Product Grid --}}
        @if(count($this->products) === 0)
            <div class="text-center py-20">
                <svg class="mx-auto size-12 text-stone-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="mt-4 text-stone-500">No se encontraron productos con esos filtros.</p>
                <button wire:click="clearFilters" class="mt-2 text-amber-700 hover:text-amber-900 text-sm underline">Limpiar filtros</button>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                @foreach($this->products as $product)
                    <div
                        wire:key="product-{{ $product['code'] }}"
                        class="group relative flex flex-col rounded-xl border border-stone-200 bg-white overflow-hidden hover:shadow-lg hover:border-amber-300 transition-all duration-200"
                    >
                        {{-- Product image --}}
                        <div class="relative aspect-square bg-gradient-to-br from-stone-100 to-stone-50 overflow-hidden">
                            <img
                                src="/images/athenas/{{ mb_strtolower($product['code']) }}.jpg"
                                alt="{{ $product['name'] }}"
                                class="size-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            />

                            @if(!$product['in_stock'])
                                <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Agotado</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex flex-col flex-1 p-3 sm:p-4">
                            <h3 class="font-medium text-sm text-stone-900 truncate">{{ $product['name'] }}</h3>
                            <p class="text-[11px] text-stone-400 mt-0.5">Ref: {{ $product['code'] }}</p>
                            <p class="text-[11px] text-stone-400">{{ $product['category'] }}</p>

                            <div class="mt-auto pt-3 flex items-end justify-between gap-2">
                                <span class="text-lg font-bold text-stone-900">&euro;{{ number_format($product['price'], 2) }}</span>

                                @if($product['in_stock'])
                                    @if(isset($cart[$product['code']]))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                            <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            x{{ $cart[$product['code']] }}
                                        </span>
                                    @endif
                                    <button
                                        wire:click="addToCart('{{ $product['code'] }}')"
                                        class="shrink-0 inline-flex items-center justify-center size-8 rounded-full bg-stone-900 text-white hover:bg-amber-700 transition-colors"
                                        title="Agregar al carrito"
                                    >
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-xs text-stone-400 italic">No disponible</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- ── Cart Sidebar (slide-over) ──────────────────────────────── --}}
    <div
        x-data
        x-show="$wire.showCart"
        x-cloak
        class="fixed inset-0 z-50"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" wire:click="toggleCart"></div>

        {{-- Panel --}}
        <div
            class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl flex flex-col"
            x-show="$wire.showCart"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            {{-- Cart header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-200">
                <h2 class="font-serif-display text-xl font-bold text-stone-900">Tu Carrito</h2>
                <button wire:click="toggleCart" class="rounded-full p-1 hover:bg-stone-100 transition">
                    <svg class="size-5 text-stone-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Cart items --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                @if(count($this->cartItems) === 0)
                    <div class="text-center py-16">
                        <svg class="mx-auto size-16 text-stone-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-4 text-stone-400 text-sm">Tu carrito esta vacio</p>
                        <button wire:click="toggleCart" class="mt-3 text-amber-700 hover:text-amber-900 text-sm underline">Seguir comprando</button>
                    </div>
                @else
                    <ul class="divide-y divide-stone-100">
                        @foreach($this->cartItems as $item)
                            <li wire:key="cart-{{ $item['code'] }}" class="flex items-center gap-4 py-4">
                                {{-- Thumbnail --}}
                                <div class="shrink-0 size-14 rounded-lg overflow-hidden bg-stone-100">
                                    <img src="/images/athenas/{{ mb_strtolower($item['code']) }}.jpg" alt="{{ $item['name'] }}" class="size-full object-cover" />
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-stone-900 truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-stone-400">Ref: {{ $item['code'] }} &middot; &euro;{{ number_format($item['price'], 2) }} c/u</p>

                                    {{-- Quantity controls --}}
                                    <div class="mt-1.5 inline-flex items-center gap-0 rounded-lg border border-stone-200">
                                        <button
                                            wire:click="decrementItem('{{ $item['code'] }}')"
                                            class="px-2 py-0.5 text-stone-500 hover:bg-stone-50 rounded-l-lg transition text-sm"
                                        >&minus;</button>
                                        <span class="px-2.5 py-0.5 text-sm font-medium text-stone-800 border-x border-stone-200 min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                                        <button
                                            wire:click="incrementItem('{{ $item['code'] }}')"
                                            class="px-2 py-0.5 text-stone-500 hover:bg-stone-50 rounded-r-lg transition text-sm"
                                        >&plus;</button>
                                    </div>
                                </div>

                                {{-- Subtotal & remove --}}
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-stone-900">&euro;{{ number_format($item['subtotal'], 2) }}</p>
                                    <button
                                        wire:click="removeFromCart('{{ $item['code'] }}')"
                                        class="mt-1 text-xs text-red-500 hover:text-red-700 transition"
                                    >Eliminar</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Cart footer --}}
            @if(count($this->cartItems) > 0)
                <div class="border-t border-stone-200 px-6 py-5 space-y-4 bg-stone-50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-stone-500">Total ({{ $this->cartCount }} {{ trans_choice('articulo|articulos', $this->cartCount) }})</span>
                        <span class="text-xl font-bold text-stone-900">&euro;{{ number_format($this->cartTotal, 2) }}</span>
                    </div>

                    <a
                        href="{{ $this->whatsappUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700 transition shadow-lg shadow-green-600/20"
                    >
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Comprar por WhatsApp
                    </a>

                    <button
                        wire:click="clearCart"
                        wire:confirm="Seguro que deseas vaciar el carrito?"
                        class="w-full text-center text-sm text-stone-400 hover:text-red-500 transition"
                    >
                        Vaciar carrito
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Footer ─────────────────────────────────────────────────── --}}
    <footer class="bg-stone-900 text-stone-400 text-center py-8 px-4 text-sm space-y-2">
        <p class="font-serif-display text-lg text-white font-bold tracking-wide">Athenas Boutique</p>
        <p>Accesorios de calidad al mejor precio</p>
        <div class="flex items-center justify-center gap-4 pt-2">
            <a href="https://www.instagram.com/{{ config('athenas.instagram') }}" target="_blank" rel="noopener" class="hover:text-amber-400 transition">
                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <a href="https://wa.me/{{ config('athenas.whatsapp') }}" target="_blank" rel="noopener" class="hover:text-green-400 transition">
                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
            <a href="mailto:{{ config('athenas.email') }}" class="hover:text-amber-400 transition">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
            </a>
        </div>
        <p class="text-xs text-stone-600 pt-2">&copy; {{ date('Y') }} Athenas Boutique. Todos los derechos reservados.</p>
    </footer>
</div>
