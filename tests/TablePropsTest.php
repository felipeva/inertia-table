<?php

use FelipeVa\InertiaTable\Data\Filter;
use FelipeVa\InertiaTable\Table;
use FelipeVa\InertiaTable\Tests\Fixtures\UserTableResource;

function jsonProps(array $props): array
{
    return json_decode(json_encode($props), true);
}

it('serializes the table props', function () {
    inertiaRequest('/users?sort=-name&filter[name]=bo&filter[status]=active&per_page=2&columns[]=id&columns[]=name');

    $props = UserTableResource::make()->table()->getTableProps();

    expect(jsonProps($props))->toBe([
        'columns' => [
            ['name' => 'id', 'label' => 'Id', 'sorted' => false, 'hidden' => false, 'sortable' => true],
            ['name' => 'name', 'label' => 'Name', 'sorted' => 'desc', 'hidden' => false, 'sortable' => true],
            ['name' => 'status', 'label' => 'Status', 'sorted' => false, 'hidden' => true, 'sortable' => false],
        ],
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    ['value' => '1', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
                'type' => 'select',
                'value' => 'active',
            ],
            ['name' => 'from', 'label' => 'From Date', 'options' => null, 'type' => 'datepicker', 'value' => null],
        ],
        'searches' => [
            [
                'type' => 'search',
                'name' => 'global',
                'label' => 'Global',
                'placeholder' => 'Search...',
                'selectedOption' => '',
                'options' => [],
                'isGlobal' => true,
                'isSelectable' => false,
                'value' => null,
            ],
            [
                'type' => 'search',
                'name' => 'name',
                'label' => 'Name',
                'placeholder' => '',
                'selectedOption' => 'a',
                'options' => ['a', 'b'],
                'isGlobal' => false,
                'isSelectable' => true,
                'value' => 'bo',
            ],
        ],
        'cursor' => null,
        'sort' => '-name',
        'defaultSort' => '',
        'page' => 1,
        'perPage' => '2',
        'pageName' => 'page',
        'perPageOptions' => [2, 15],
    ]);
});

it('serializes filter options with integer values', function () {
    $request = inertiaRequest('/users');

    $props = Table::make($request)
        ->filter(Filter::make('owner')->options([
            ['label' => 'Alice', 'value' => 1],
            ['label' => 'Bob', 'value' => 2],
        ]))
        ->getTableProps();

    expect(jsonProps($props)['filters'][0]['options'])->toBe([
        ['value' => '1', 'label' => 'Alice'],
        ['value' => '2', 'label' => 'Bob'],
    ]);
});

it('exposes the default sort when the request has no sort', function () {
    inertiaRequest('/users');

    $resource = new class extends UserTableResource
    {
        protected string $defaultSort = '-created_at';
    };

    $props = $resource->table()->getTableProps();

    expect($props['defaultSort'])->toBe('-created_at')
        ->and($props['sort'])->toBe('-created_at');
});
