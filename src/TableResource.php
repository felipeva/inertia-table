<?php

namespace FelipeVa\InertiaTable;

use FelipeVa\InertiaTable\Data\Column;
use FelipeVa\InertiaTable\Data\Filter;
use FelipeVa\InertiaTable\Data\Search;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

/** @phpstan-consistent-constructor */
abstract class TableResource
{
    protected string $tableName = 'default';

    protected int $linksOnEachSide = 0;

    /**
     * This is used to set the name of the key in the request
     * Just in case you're using the Inertia Attribute
     */
    protected string $resourceName;

    /**
     * @var class-string<JsonResource>
     */
    protected string $resourceClass;

    /**
     * @var int[]
     */
    protected array $perPageOptions = [15, 30, 50];

    protected string $defaultSort = '';

    protected string $perPage = '15';

    /**
     * @var array<int, Column>
     */
    protected array $columns = [];

    /**
     * @var array<int, Filter>
     */
    protected array $filters = [];

    protected Request $request;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function make(array $parameters = []): static
    {
        return app(static::class, $parameters);
    }

    abstract protected function query(): QueryBuilder;

    /**
     * @return array<int, Column>
     */
    abstract protected function columns(): array;

    /**
     * @return array<int, Filter>
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * @return array<int, Search>
     */
    protected function searches(): array
    {
        return [];
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function resource(bool $paginate = true): ResourceCollection
    {
        return $paginate ? $this->paginateResource() : $this->resourceWithoutPagination();
    }

    public function resourceWithoutPagination(): ResourceCollection
    {
        if (isset($this->resourceClass)) {
            return $this->resourceClass::collection(
                $this->query()->get()
            );
        }

        return new ResourceCollection(
            $this->query()->get()
        );
    }

    public function paginateResource(): ResourceCollection
    {
        if (isset($this->resourceClass)) {
            return $this->resourceClass::collection(
                $this->paginate($this->query())->withQueryString()->onEachSide($this->linksOnEachSide)
            );
        }

        return new ResourceCollection(
            $this->paginate($this->query())->withQueryString()->onEachSide($this->linksOnEachSide)
        );
    }

    public function table(?callable $callback = null): Table
    {
        $table = Table::make($this->request)
            ->name($this->tableName)
            ->filters($this->filters())
            ->columns($this->columns())
            ->searches($this->searches())
            ->pageName($this->tableName === 'default' ? 'page' : "{$this->tableName}_page")
            ->perPage($this->perPage)
            ->perPageOptions($this->perPageOptions)
            ->defaultSort($this->defaultSort);

        if ($callback !== null) {
            $callback($table);
        }

        return $table;
    }

    /**
     * @template T of Model
     *
     * @param  string|EloquentBuilder<T>|Relation<T, Model, mixed>|QueryBuilder  $subject
     */
    protected function builder(QueryBuilder|Relation|EloquentBuilder|string $subject, ?Request $request = null): QueryBuilder
    {
        $this->updateQueryBuilderConfig();

        if ($subject instanceof QueryBuilder) {
            return $subject;
        }

        return QueryBuilder::for($subject, $request);
    }

    /**
     * @return LengthAwarePaginator<Model>
     */
    protected function paginate(QueryBuilder $builder): LengthAwarePaginator
    {
        $queryKey = $this->getQueryKey();
        $perPage = $this->request->integer("{$queryKey}per_page", 15);

        if (! in_array($perPage, $this->perPageOptions, true)) {
            $perPage = min($this->perPageOptions);
        }

        return $builder->paginate(
            perPage: $perPage,
            pageName: "{$queryKey}page",
        );
    }

    private function updateQueryBuilderConfig(): void
    {
        if ($this->tableName !== 'default') {
            Table::updateQueryBuilderParameters($this->tableName);
        }
    }

    private function getQueryKey(): string
    {
        return $this->tableName === 'default' ? '' : "{$this->tableName}_";
    }

    public function getQuery(): QueryBuilder
    {
        return $this->query();
    }
}
