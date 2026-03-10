<?php

namespace App\Http\Controllers\AthenasCodex;

use App\Http\Controllers\Controller;
use App\Services\AthenasCodex\CartService;
use App\Services\AthenasCodex\CatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AthenasCodexPageController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly CartService $cartService,
    ) {}

    public function __invoke(Request $request): View
    {
        $filters = $this->catalogService->normalizeFilters($request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(config('athenas_codex.category_labels', []))])],
            'availability' => ['nullable', 'string', Rule::in(['all', 'in-stock'])],
        ]));

        return view('pages.AthenasCodex.index', [
            'brand' => config('athenas_codex.brand'),
            'contact' => config('athenas_codex.contact'),
            'heroImages' => config('athenas_codex.hero_images', []),
            'products' => $this->catalogService->products($filters),
            'featuredProducts' => $this->catalogService->featured(),
            'categories' => $this->catalogService->categories(),
            'stats' => $this->catalogService->stats(),
            'filters' => $filters,
            'cart' => $this->cartService->summary(),
            'currencyCode' => config('athenas_codex.currency.code'),
        ]);
    }
}
