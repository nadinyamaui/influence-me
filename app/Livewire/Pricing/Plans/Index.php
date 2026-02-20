<?php

namespace App\Livewire\Pricing\Plans;

use App\Enums\CatalogPlanSort;
use App\Enums\CatalogPlanStatusFilter;
use App\Models\CatalogPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $status = 'active';

    public string $sort = 'newest';

    public function mount(): void
    {
        $this->authorize('viewAny', CatalogPlan::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        if (! in_array($this->status, CatalogPlanStatusFilter::values(), true)) {
            $this->status = CatalogPlanStatusFilter::default()->value;
        }

        $this->resetPage();
    }

    public function updatedSort(): void
    {
        if (! in_array($this->sort, CatalogPlanSort::values(), true)) {
            $this->sort = CatalogPlanSort::default()->value;
        }

        $this->resetPage();
    }

    public function render()
    {
        return view('pages.pricing.plans.index', [
            'plans' => $this->plans(),
            'statusOptions' => CatalogPlanStatusFilter::options(),
            'sortOptions' => CatalogPlanSort::options(),
        ])->layout('layouts.app', [
            'title' => __('Pricing Plans'),
        ]);
    }

    private function plans(): LengthAwarePaginator
    {
        $status = in_array($this->status, CatalogPlanStatusFilter::values(), true)
            ? $this->status
            : CatalogPlanStatusFilter::default()->value;

        $sort = in_array($this->sort, CatalogPlanSort::values(), true)
            ? $this->sort
            : CatalogPlanSort::default()->value;

        return CatalogPlan::query()
            ->forUser((int) auth()->id())
            ->search($this->search)
            ->filterByActive(CatalogPlanStatusFilter::from($status)->activeValue())
            ->withItemsCount()
            ->applySort($sort)
            ->paginate(10);
    }
}
