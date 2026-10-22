<?php

use FelipeVa\InertiaTable\Tests\TestCase;
use Illuminate\Http\Request;

uses(TestCase::class)->in(__DIR__);

function inertiaRequest(string $uri): Request
{
    $request = Request::create($uri, 'GET');
    $request->headers->set('X-Inertia', 'true');
    app()->instance('request', $request);

    return $request;
}
