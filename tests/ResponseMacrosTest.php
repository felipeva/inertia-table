<?php

use FelipeVa\InertiaTable\Tests\Fixtures\NamedUserTableResource;
use FelipeVa\InertiaTable\Tests\Fixtures\UserTableResource;
use Inertia\Inertia;

it('adds a table to the response with the table() macro', function () {
    $request = inertiaRequest('/users?filter[status]=active&per_page=2');
    $resource = UserTableResource::make();

    $props = Inertia::render('Users/Index', ['users' => $resource->resource()])
        ->table($resource->table())
        ->toResponse($request)
        ->getData(true)['props'];

    expect(array_column($props['users']['data'], 'name'))->toBe(['carol', 'bobby'])
        ->and($props['tableProps'])->toHaveKeys(['default'])
        ->and($props['tableProps']['default']['filters'][0]['value'])->toBe('active');
});

it('adds several tables to the response with the tables() macro', function () {
    $request = inertiaRequest('/users');

    $props = Inertia::render('Users/Index')
        ->tables([NamedUserTableResource::make()->table(), UserTableResource::make()->table()])
        ->toResponse($request)
        ->getData(true)['props'];

    expect(array_keys($props['tableProps']))->toBe(['users', 'default'])
        ->and($props['tableProps']['users']['pageName'])->toBe('users_page')
        ->and($props['tableProps']['default']['pageName'])->toBe('page');
});

it('reads prefixed query parameters for a named table', function () {
    $request = inertiaRequest('/users?sort=name&users_sort=-name&users_filter[name]=bo&users_per_page=15');
    $resource = NamedUserTableResource::make();

    $users = $resource->resource()->toResponse($request)->getData(true);
    $props = $resource->table()->getTableProps();

    expect(array_column($users['data'], 'name'))->toBe(['bobby', 'bob'])
        ->and($users['meta']['per_page'])->toBe(15)
        ->and($props['sort'])->toBe('-name')
        ->and($props['perPage'])->toBe('15')
        ->and($props['searches'][1]->value)->toBe('bo');
});
