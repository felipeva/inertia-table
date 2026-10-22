<?php

namespace FelipeVa\InertiaTable;

use FelipeVa\InertiaTable\Contracts\TableComponent;
use FelipeVa\InertiaTable\Data\Column;
use FelipeVa\InertiaTable\Data\Filter;
use FelipeVa\InertiaTable\Data\Search;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Inertia\Response;

/** @phpstan-consistent-constructor */
class Table
{
    private string $name = 'default';

    private string $pageName = 'page';

    /**
     * @var int[]
     */
    private array $perPageOptions = [2, 15, 30, 50, 100];

    private string $perPage = '15';

    /**
     * @var Collection<int, Column>
     */
    private Collection $columns;

    /**
     * @var Collection<int, Filter>
     */
    private Collection $filters;

    /**
     * @var Collection<int, Search>
     */
    private Collection $searches;

    private string $defaultSortColumn = '';

    public function __construct(
        private readonly Request $request
    ) {
        $this->filters = collect();
        $this->columns = collect();
        $this->searches = collect();
    }

    public static function make(Request $request): static
    {
        return new static($request);
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param  array<int|string, mixed>|null|string  $default
     * @return array<int|string, mixed>|string|null
     */
    private function query(string $key, mixed $default = null): array|string|null
    {
        return $this->request->query(
            $this->name === 'default' ? $key : "{$this->name}_{$key}",
            $default
        );
    }

    public static function updateQueryBuilderParameters(string $name): void
    {
        /** @var array<string, string> $config */
        $config = config('query-builder.parameters');

        $newConfig = collect($config)->map(function ($value) use ($name) {
            return "{$name}_{$value}";
        })->all();

        config(['query-builder.parameters' => $newConfig]);
    }

    /**
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function pageName(string $pageName): self
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * @param  int[]  $perPageOptions
     * @return $this
     */
    public function perPageOptions(array $perPageOptions): self
    {
        $this->perPageOptions = $perPageOptions;

        return $this;
    }

    /**
     * @return $this
     */
    public function defaultSort(string $defaultSort): self
    {
        $this->defaultSortColumn = $defaultSort;

        return $this;
    }

    /**
     * @return $this
     */
    public function perPage(string $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @param  array<int, Column>  $columns
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = collect($columns)->each(fn (Column $column) => $this->column($column));

        return $this;
    }

    /**
     * @param  array<int, Search>  $columns
     * @return $this
     */
    public function searches(array $columns): self
    {
        $this->searches = collect($columns)->each(fn (Search $search) => $this->search($search));

        return $this;
    }

    /**
     * @param  array<int, Filter>  $filters
     * @return $this
     */
    public function filters(array $filters): self
    {
        $this->filters = collect($filters)->each(fn (Filter $filter) => $this->filter($filter));

        return $this;
    }

    /**
     * @return $this
     */
    public function column(Column $column): self
    {
        $this->columns = $this->addComponent($this->columns, $column);

        return $this;
    }

    /**
     * @return $this
     */
    public function search(Search $search): self
    {
        $this->searches = $this->addComponent($this->searches, $search);

        return $this;
    }

    /**
     * @return $this
     */
    public function filter(Filter $filter): self
    {
        $this->filters = $this->addComponent($this->filters, $filter);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTableProps(): array
    {
        return [
            'columns' => $this->transformColumns(),
            'filters' => $this->transformFilters(),
            'searches' => $this->transformSearches(),
            'cursor' => $this->query('cursor'),
            'sort' => $this->query('sort', $this->defaultSortColumn),
            'defaultSort' => $this->defaultSortColumn,
            'page' => Paginator::resolveCurrentPage($this->pageName),
            'perPage' => $this->query('per_page', $this->perPage),
            'pageName' => $this->pageName,
            'perPageOptions' => $this->perPageOptions,
        ];
    }

    /**
     * @return Collection<int, Column>
     */
    protected function transformColumns(): Collection
    {
        /** @var null|array<int|string, mixed> $currentColumns */
        $currentColumns = $this->query('columns', []);
        /** @var null|string $currentSort */
        $currentSort = $this->query('sort', $this->defaultSortColumn);

        return $this->columns->map(function (Column $column) use ($currentColumns, $currentSort) {
            if (! is_null($currentColumns) && count($currentColumns) > 0) {
                $column->hidden = ! in_array($column->name, $currentColumns, true);
            }

            if (! is_null($currentSort)) {
                if ($column->name === $currentSort) {
                    $column->sorted = 'asc';
                } elseif ($currentSort === "-$column->name") {
                    $column->sorted = 'desc';
                }
            }

            return $column;
        });
    }

    /**
     * @return Collection<int, Filter>
     */
    protected function transformFilters(): Collection
    {
        /** @var array<int|string, null|string> $currentFilter */
        $currentFilter = $this->query('filter', []);

        return $this->transformComponent($this->filters, $currentFilter);
    }

    /**
     * @return Collection<int, Search>
     */
    protected function transformSearches(): Collection
    {
        /** @var array<int|string, null|string> $currentFilter */
        $currentFilter = $this->query('filter', []);

        return $this->transformComponent($this->searches, $currentFilter);
    }

    /**
     * @template T
     *
     * @param  Collection<int, T>  $collection
     * @param  array<int|string, null|string>  $defaults
     * @return Collection<int, T>
     */
    private function transformComponent(Collection $collection, array $defaults = []): Collection
    {
        return $collection->map(function ($component) use ($defaults) {
            if (array_key_exists($component->name, $defaults)) {
                $component->value = $defaults[$component->name];
            }

            return $component;
        });
    }

    /**
     * @template T of TableComponent
     *
     * @param  Collection<int, T>  $collection
     * @return Collection<int, T>
     */
    private function addComponent(Collection $collection, TableComponent $component): Collection
    {
        return $collection
            ->reject(fn ($c) => $c->name === $component->name)
            ->push($component)
            ->values();
    }

    public function build(Response $response): Response
    {
        // Macros are magic, so PHPStan does not know about them.
        $props = array_merge($response->getTableProps(), [
            $this->name => $this->getTableProps(),
        ]);

        return $response->with('tableProps', $props);
    }
}
