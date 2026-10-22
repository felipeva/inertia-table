<?php

namespace FelipeVa\InertiaTable\Tests;

use FelipeVa\InertiaTable\InertiaTableServiceProvider;
use FelipeVa\InertiaTable\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'FelipeVa\InertiaTable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
            $table->timestamps();
        });

        foreach (['alice' => 'active', 'bob' => 'inactive', 'bobby' => 'active', 'carol' => 'active'] as $name => $status) {
            User::create(['name' => $name, 'status' => $status]);
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            QueryBuilderServiceProvider::class,
            InertiaServiceProvider::class,
            InertiaTableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_skeleton_table.php.stub';
        $migration->up();
        */
    }
}
