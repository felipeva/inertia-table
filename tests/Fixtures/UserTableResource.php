<?php

namespace FelipeVa\InertiaTable\Tests\Fixtures;

use FelipeVa\InertiaTable\Data\Column;
use FelipeVa\InertiaTable\Data\Filter;
use FelipeVa\InertiaTable\Data\Search;
use FelipeVa\InertiaTable\Enums\FilterType;
use FelipeVa\InertiaTable\TableResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserTableResource extends TableResource
{
    protected array $perPageOptions = [2, 15];

    protected function query(): QueryBuilder
    {
        return $this->builder(User::class)
            ->defaultSort('-id')
            ->allowedFilters('name', AllowedFilter::exact('status'))
            ->allowedSorts('name', 'id');
    }

    protected function columns(): array
    {
        return [
            Column::make('id')->sortable(),
            Column::make('name')->sortable(),
            Column::make('status')->hidden(),
        ];
    }

    protected function filters(): array
    {
        return [
            Filter::make('status')->type(FilterType::SELECT)->options([
                ['label' => 'Active', 'value' => 1],
                ['label' => 'Inactive', 'value' => 'inactive'],
            ]),
            Filter::make('from')->label('From Date')->type(FilterType::DATEPICKER),
        ];
    }

    protected function searches(): array
    {
        return [
            Search::make('global')->placeholder('Search...'),
            Search::make('name')->selectable()->options(['a', 'b'])->selectedOption('a'),
        ];
    }
}
