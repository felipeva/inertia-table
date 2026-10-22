<?php

namespace FelipeVa\InertiaTable;

use FelipeVa\InertiaTable\Commands\MakeTableResourceCommand;
use Inertia\Response;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InertiaTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('inertia-table')
            ->hasCommand(MakeTableResourceCommand::class);
    }

    public function bootingPackage(): void
    {
        Response::macro('getTableProps', function () {
            return $this->props['tableProps'] ?? [];
        });

        Response::macro('table', function (Table $table) {
            return $table->build($this);
        });

        Response::macro('tables', function (array $tables): Response {
            $response = $this;

            /** @var array<int, Table> $tables */
            $tablesCollection = collect($tables);

            $tablesCollection->each(fn (Table $table) => $table->build($response));

            return $response;
        });
    }
}
