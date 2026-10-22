<?php

use App\TableResources\UserTableResource;
use FelipeVa\InertiaTable\Tests\Fixtures\User;
use Illuminate\Support\Facades\File;

it('generates a table resource that builds a query', function () {
    $directory = app_path('TableResources');
    $path = "{$directory}/UserTableResource.php";
    File::deleteDirectory($directory);

    try {
        $this->artisan('make:table-resource', ['name' => 'User'])->assertSuccessful();

        // A developer points the generated builder at a model.
        File::put($path, str_replace('/* TODO: Model::class */', '\\'.User::class.'::class', File::get($path)));
        require $path;

        inertiaRequest('/users');
        $users = UserTableResource::make()->getQuery()->get();

        expect($users)->toHaveCount(4);
    } finally {
        File::deleteDirectory($directory);
    }
});
