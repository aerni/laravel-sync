<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Once;

beforeEach(function () {
    Once::flush();
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

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
        'assets' => ['storage/app/assets/', 'storage/app/img/'],
    ]);
});

it('outputs one rsync command per recipe path', function () {
    $localAssets = base_path('storage/app/assets/');
    $localImg = base_path('storage/app/img/');

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
    ])
        ->expectsOutputToContain("rsync -e 'ssh -p 1431' --archive {$localAssets} forge@104.26.3.113:/home/forge/site.com/storage/app/assets/")
        ->expectsOutputToContain("rsync -e 'ssh -p 1431' --archive {$localImg} forge@104.26.3.113:/home/forge/site.com/storage/app/img/")
        ->assertSuccessful();
});

it('outputs the correct rsync command format for pull', function () {
    $localAssets = base_path('storage/app/assets/');

    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/'],
    ]);

    $this->artisan('sync:commands', [
        'operation' => 'pull',
        'remote' => 'production',
        'recipe' => ['assets'],
    ])
        ->expectsOutputToContain("rsync -e 'ssh -p 1431' --archive forge@104.26.3.113:/home/forge/site.com/storage/app/assets/ {$localAssets}")
        ->assertSuccessful();
});
