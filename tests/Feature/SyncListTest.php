<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Once;
use Laravel\Prompts\Prompt;

beforeEach(function () {
    Once::flush();
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);
    Prompt::fallbackWhen(true);

    config()->set('sync.remotes', [
        'production' => [
            'user' => 'forge',
            'host' => '104.26.3.113',
            'port' => 1431,
            'root' => '/home/forge/site.com',
            'read_only' => false,
        ],
    ]);

    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/'],
    ]);
});

it('executes successfully for a push operation', function () {
    $this->artisan('sync:list', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
    ])->assertSuccessful();
});

it('executes successfully for a pull operation', function () {
    $this->artisan('sync:list', [
        'operation' => 'pull',
        'remote' => 'production',
        'recipe' => 'assets',
    ])->assertSuccessful();
});
