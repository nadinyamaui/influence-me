<x-layouts.athenas-codex :title="$brand['name'].' | Catalogo publico'">
    <div class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 -z-10 h-[36rem] bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.95),_transparent_35%),linear-gradient(135deg,_#f9f2eb_0%,_#ead8c8_48%,_#f5ebe2_100%)]"></div>
        <div class="absolute -left-16 top-28 -z-10 h-64 w-64 rounded-full bg-amber-100/80 blur-3xl"></div>
        <div class="absolute right-0 top-32 -z-10 h-72 w-72 rounded-full bg-stone-200/80 blur-3xl"></div>

        <header class="sticky top-0 z-40 border-b border-stone-300/60 bg-[#f7efe7]/85 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
                <a href="{{ route('athenas-codex.index') }}" class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-stone-300 bg-white text-lg text-stone-700 shadow-sm">
                        <i class="fa-solid fa-gem" aria-hidden="true"></i>
                    </div>
                    <div>
                        <p class="font-display text-2xl leading-none text-stone-900">{{ $brand['name'] }}</p>
                        <p class="text-xs uppercase tracking-[0.28em] text-stone-500">RFC {{ config('athenas_codex.rfc') }} · Catalogo publico</p>
                    </div>
                </a>

                <div class="hidden items-center gap-3 md:flex">
                    <a href="https://www.instagram.com/{{ $contact['instagram'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                        {{ '@'.$contact['instagram'] }}
                    </a>
                    <a href="#carrito" class="inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                        Carrito
                        <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">{{ $cart['item_count'] }}</span>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="mx-auto max-w-7xl px-6 pb-10 pt-10 lg:pb-14 lg:pt-16">
                @if (session('athenas_codex_status'))
                    <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                        {{ session('athenas_codex_status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-6">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-stone-600 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Compra directa por WhatsApp
                        </div>

                        <h1 class="font-display mt-6 max-w-2xl text-5xl leading-[0.94] text-stone-900 sm:text-6xl">
                            {{ $brand['headline'] }}
                        </h1>

                        <p class="mt-6 max-w-2xl text-base leading-relaxed text-stone-600 sm:text-lg">
                            {{ $brand['description'] }}
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#catalogo" class="inline-flex items-center justify-center gap-2 rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-stone-700">
                                <i class="fa-solid fa-sparkles" aria-hidden="true"></i>
                                Ver catalogo
                            </a>
                            <a href="https://wa.me/{{ $contact['whatsapp_number'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-full border border-stone-300 bg-white px-6 py-3 text-sm font-semibold text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
                                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                Escribir ahora
                            </a>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <article class="rounded-[1.75rem] border border-white/70 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Productos</p>
                                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $stats['product_count'] }}</p>
                                <p class="text-sm text-stone-500">items activos en catalogo</p>
                            </article>
                            <article class="rounded-[1.75rem] border border-white/70 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Desde</p>
                                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $stats['formatted_starting_price'] }}</p>
                                <p class="text-sm text-stone-500">precios al detal</p>
                            </article>
                            <article class="rounded-[1.75rem] border border-white/70 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Sesion</p>
                                <p class="mt-3 text-3xl font-semibold text-stone-900">{{ $cart['item_count'] }}</p>
                                <p class="text-sm text-stone-500">items guardados en carrito</p>
                            </article>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-6">
                        @foreach ($heroImages as $index => $image)
                            <article class="{{ $index === 0 ? 'sm:col-span-2' : '' }} overflow-hidden rounded-[2rem] border border-white/60 bg-white/60 p-3 shadow-xl shadow-stone-300/40 backdrop-blur">
                                <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}" class="h-full w-full rounded-[1.4rem] object-cover">
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-y border-stone-300/70 bg-white/55 py-4 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-2 px-6 text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
                    <span>Instagram: {{ '@'.$contact['instagram'] }}</span>
                    <span class="h-1 w-1 rounded-full bg-stone-400"></span>
                    <span>TikTok: {{ '@'.$contact['tiktok'] }}</span>
                    <span class="h-1 w-1 rounded-full bg-stone-400"></span>
                    <span>WhatsApp: {{ $contact['whatsapp_display'] }}</span>
                    <span class="h-1 w-1 rounded-full bg-stone-400"></span>
                    <span>{{ $currencyCode }} · Precios del catalogo</span>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-12">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Destacados</p>
                        <h2 class="font-display mt-3 text-4xl text-stone-900">Piezas que mueven el catalogo</h2>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-stone-600">
                        El carrito y el snapshot del catalogo quedan guardados en la sesion actual para que el cliente pueda revisar, ajustar cantidades y concretar su pedido sin perder progreso.
                    </p>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($featuredProducts as $product)
                        <article data-product-card="{{ $product['sku'] }}" class="rounded-[2rem] border border-stone-300/70 bg-white/75 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">{{ $product['category_label'] }}</p>
                                    <h3 class="mt-2 text-2xl font-semibold text-stone-900">{{ $product['name'] }}</h3>
                                    <p class="mt-2 text-sm text-stone-500">Codigo {{ $product['code'] }}</p>
                                </div>
                                <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-600">
                                    {{ $product['availability_label'] }}
                                </span>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach ($product['colors'] as $color)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">
                                        <span class="h-2.5 w-2.5 rounded-full border border-white/70" style="background-color: {{ $color['hex'] }}"></span>
                                        {{ $color['label'] }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="mt-8 flex items-center justify-between">
                                <p class="text-2xl font-semibold text-stone-900">{{ $product['formatted_price'] }}</p>
                                <form method="POST" action="{{ route('athenas-codex.cart.items.store') }}">
                                    @csrf
                                    <input type="hidden" name="product" value="{{ $product['sku'] }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                    <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
                                    <button data-testid="add-{{ $product['sku'] }}" type="submit" @disabled(! $product['available']) class="inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700 disabled:cursor-not-allowed disabled:bg-stone-300">
                                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                        Agregar
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="catalogo" class="mx-auto max-w-7xl px-6 pb-16">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <div class="rounded-[2rem] border border-stone-300/70 bg-white/70 p-6 shadow-sm">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Catalogo completo</p>
                                    <h2 class="font-display mt-3 text-4xl text-stone-900">Explora y agrega al carrito</h2>
                                </div>
                                <p class="max-w-2xl text-sm leading-relaxed text-stone-600">
                                    Filtra por categoria o disponibilidad. El cliente ve el total en pantalla y al comprar abre WhatsApp con el resumen listo para enviar.
                                </p>
                            </div>

                            <form method="GET" action="{{ route('athenas-codex.index') }}" class="mt-8 grid gap-4 md:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                                <label class="grid gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Buscar</span>
                                    <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Ej. Dalia, collar, anillo..." class="h-12 rounded-2xl border border-stone-300 bg-white px-4 text-sm text-stone-700 outline-none transition focus:border-stone-500">
                                </label>
                                <label class="grid gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Categoria</span>
                                    <select name="category" class="h-12 rounded-2xl border border-stone-300 bg-white px-4 text-sm text-stone-700 outline-none transition focus:border-stone-500">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category['value'] }}" @selected($filters['category'] === $category['value'])>
                                                {{ $category['label'] }} ({{ $category['count'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="grid gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Disponibilidad</span>
                                    <select name="availability" class="h-12 rounded-2xl border border-stone-300 bg-white px-4 text-sm text-stone-700 outline-none transition focus:border-stone-500">
                                        <option value="all" @selected($filters['availability'] === 'all')>Todo</option>
                                        <option value="in-stock" @selected($filters['availability'] === 'in-stock')>Solo disponibles</option>
                                    </select>
                                </label>
                                <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 self-end rounded-2xl bg-stone-900 px-5 text-sm font-semibold text-white transition hover:bg-stone-700">
                                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                    Filtrar
                                </button>
                            </form>
                        </div>

                        @if ($products === [])
                            <div class="mt-6 rounded-[2rem] border border-dashed border-stone-300 bg-white/60 p-10 text-center text-stone-600">
                                <p class="font-display text-3xl text-stone-900">No hay productos para ese filtro.</p>
                                <p class="mt-3 text-sm">Prueba otra categoria o limpia la busqueda para ver todo el catalogo.</p>
                            </div>
                        @else
                            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($products as $product)
                                    <article data-product-card="{{ $product['sku'] }}" class="rounded-[2rem] border border-stone-300/70 bg-white/80 p-5 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <span class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">
                                                    {{ $product['category_label'] }}
                                                </span>
                                                <h3 class="mt-4 text-xl font-semibold text-stone-900">{{ $product['name'] }}</h3>
                                                <p class="mt-1 text-sm text-stone-500">Codigo {{ $product['code'] }}</p>
                                            </div>
                                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] {{ $product['available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $product['availability_label'] }}
                                            </span>
                                        </div>

                                        <div class="mt-5 flex flex-wrap gap-2">
                                            @foreach ($product['colors'] as $color)
                                                <span class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-1 text-xs text-stone-600">
                                                    <span class="h-2.5 w-2.5 rounded-full border border-white/80" style="background-color: {{ $color['hex'] }}"></span>
                                                    {{ $color['label'] }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <div class="mt-5 space-y-2 text-sm text-stone-500">
                                            @foreach ($product['materials'] as $material)
                                                <p class="flex items-center gap-2">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span>
                                                    {{ $material }}
                                                </p>
                                            @endforeach
                                        </div>

                                        <div class="mt-6 flex items-center justify-between gap-4">
                                            <p class="text-2xl font-semibold text-stone-900">{{ $product['formatted_price'] }}</p>
                                            <form method="POST" action="{{ route('athenas-codex.cart.items.store') }}">
                                                @csrf
                                                <input type="hidden" name="product" value="{{ $product['sku'] }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                                <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                                <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
                                                <button data-testid="add-{{ $product['sku'] }}" type="submit" @disabled(! $product['available']) class="inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700 disabled:cursor-not-allowed disabled:bg-stone-300">
                                                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                                    {{ $product['available'] ? 'Agregar' : 'Agotado' }}
                                                </button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <aside id="carrito" class="lg:col-span-4">
                        <div class="lg:sticky lg:top-24">
                            <div class="rounded-[2rem] border border-stone-300/70 bg-stone-900 p-6 text-stone-100 shadow-2xl shadow-stone-300/30">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Carrito en sesion</p>
                                        <h2 class="font-display mt-3 text-4xl text-white">Pedido actual</h2>
                                    </div>
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-stone-100">
                                        {{ $cart['item_count'] }} items
                                    </span>
                                </div>

                                @if ($cart['is_empty'])
                                    <div class="mt-8 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 text-sm leading-relaxed text-stone-300">
                                        Agrega productos desde el catalogo para ver el resumen, totalizar y abrir el mensaje de compra en WhatsApp.
                                    </div>
                                @else
                                    <div class="mt-8 space-y-4">
                                        @foreach ($cart['items'] as $item)
                                            <article class="rounded-[1.6rem] border border-white/10 bg-white/5 p-4">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-white">{{ $item['name'] }}</p>
                                                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-stone-400">{{ $item['code'] }} · {{ $item['category_label'] }}</p>
                                                    </div>
                                                    <p class="text-sm font-semibold text-amber-200">{{ $item['formatted_line_total'] }}</p>
                                                </div>

                                                <div class="mt-4 flex items-center gap-3">
                                                    <form method="POST" action="{{ route('athenas-codex.cart.items.update', $item['sku']) }}" class="flex flex-1 items-center gap-3">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                                        <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
                                                        <input type="number" name="quantity" min="1" max="20" value="{{ $item['quantity'] }}" class="h-11 w-24 rounded-2xl border border-white/10 bg-white/10 px-4 text-sm text-white outline-none transition focus:border-white/25">
                                                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-2xl border border-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/10">
                                                            Actualizar
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('athenas-codex.cart.items.destroy', $item['sku']) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                                        <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
                                                        <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-400/30 bg-rose-400/10 text-rose-200 transition hover:bg-rose-400/20">
                                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>

                                    <div class="mt-8 rounded-[1.7rem] border border-white/10 bg-white/5 p-5">
                                        <div class="flex items-center justify-between text-sm text-stone-300">
                                            <span>Productos distintos</span>
                                            <span>{{ $cart['product_count'] }}</span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between text-sm text-stone-300">
                                            <span>Total de unidades</span>
                                            <span>{{ $cart['item_count'] }}</span>
                                        </div>
                                        <div data-testid="cart-total" class="mt-5 flex items-center justify-between border-t border-white/10 pt-5 text-lg font-semibold text-white">
                                            <span>Total</span>
                                            <span>{{ $cart['formatted_total'] }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-6 grid gap-3">
                                        <a data-testid="whatsapp-checkout" href="{{ $cart['checkout_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-emerald-300">
                                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                            Comprar por WhatsApp
                                        </a>

                                        <form method="POST" action="{{ route('athenas-codex.cart.clear') }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                            <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
                                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                                Vaciar carrito
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <div class="mt-8 rounded-[1.5rem] border border-white/10 bg-white/5 p-5 text-sm text-stone-300">
                                    <p class="font-semibold text-white">Contacto directo</p>
                                    <p class="mt-3 flex items-center gap-3">
                                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                        {{ $contact['whatsapp_display'] }}
                                    </p>
                                    <p class="mt-2 flex items-center gap-3">
                                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                                        {{ '@'.$contact['instagram'] }}
                                    </p>
                                    <p class="mt-2 flex items-center gap-3">
                                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                                        {{ $contact['email'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>

        <footer class="border-t border-stone-300/70 bg-white/55 py-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 text-sm text-stone-500 md:flex-row md:items-center md:justify-between">
                <p>{{ $brand['name'] }} · {{ $brand['subheadline'] }}</p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="https://www.instagram.com/{{ $contact['instagram'] }}" target="_blank" rel="noopener" class="transition hover:text-stone-800">Instagram</a>
                    <a href="https://wa.me/{{ $contact['whatsapp_number'] }}" target="_blank" rel="noopener" class="transition hover:text-stone-800">WhatsApp</a>
                    <a href="mailto:{{ $contact['email'] }}" class="transition hover:text-stone-800">Correo</a>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.athenas-codex>
