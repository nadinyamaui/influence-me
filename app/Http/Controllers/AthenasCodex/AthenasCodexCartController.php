<?php

namespace App\Http\Controllers\AthenasCodex;

use App\Http\Controllers\Controller;
use App\Services\AthenasCodex\CartService;
use App\Services\AthenasCodex\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AthenasCodexCartController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly CartService $cartService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product' => ['required', 'string', Rule::in($this->catalogService->productSkus())],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'search' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(config('athenas_codex.category_labels', []))])],
            'availability' => ['nullable', 'string', Rule::in(['all', 'in-stock'])],
        ]);

        $filters = $this->catalogService->normalizeFilters($payload);
        $product = $this->catalogService->findOrFail((string) $payload['product']);
        $this->cartService->add((string) $payload['product'], (int) $payload['quantity']);

        return $this->redirectToCatalog($filters, 'catalogo')
            ->with('athenas_codex_status', "{$product['name']} fue agregado al carrito.");
    }

    public function update(Request $request, string $product): RedirectResponse
    {
        $payload = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'search' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(config('athenas_codex.category_labels', []))])],
            'availability' => ['nullable', 'string', Rule::in(['all', 'in-stock'])],
        ]);

        $filters = $this->catalogService->normalizeFilters($payload);
        $productData = $this->catalogService->findOrFail($product);
        $this->cartService->update($product, (int) $payload['quantity']);

        return $this->redirectToCatalog($filters, 'carrito')
            ->with('athenas_codex_status', "Cantidad actualizada para {$productData['name']}.");
    }

    public function destroy(Request $request, string $product): RedirectResponse
    {
        $filters = $this->catalogService->normalizeFilters($request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(config('athenas_codex.category_labels', []))])],
            'availability' => ['nullable', 'string', Rule::in(['all', 'in-stock'])],
        ]));

        $productData = $this->catalogService->findOrFail($product);
        $this->cartService->remove($product);

        return $this->redirectToCatalog($filters, 'carrito')
            ->with('athenas_codex_status', "{$productData['name']} fue eliminado del carrito.");
    }

    public function clear(Request $request): RedirectResponse
    {
        $filters = $this->catalogService->normalizeFilters($request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(config('athenas_codex.category_labels', []))])],
            'availability' => ['nullable', 'string', Rule::in(['all', 'in-stock'])],
        ]));

        $this->cartService->clear();

        return $this->redirectToCatalog($filters, 'carrito')
            ->with('athenas_codex_status', 'El carrito fue vaciado.');
    }

    private function redirectToCatalog(array $filters, string $fragment): RedirectResponse
    {
        return redirect()
            ->route('athenas-codex.index', array_filter($filters, fn (string $value): bool => $value !== '' && $value !== 'all'))
            ->withFragment($fragment);
    }
}
