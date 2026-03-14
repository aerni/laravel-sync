<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Once;

beforeEach(function () {
    Once::flush();
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);
    Process::fake();

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

it('runs rsync for each path in the recipe during dry run', function () {
    $this->withoutMockingConsoleOutput()
        ->artisan('sync push production assets --dry');

    $localAssets = base_path('storage/app/assets/');
    $localImg = base_path('storage/app/img/');

    Process::assertRan(fn ($process) => str_contains($process->command, $localAssets));
    Process::assertRan(fn ($process) => str_contains($process->command, $localImg));
});

it('skips confirmation during dry run', function () {
    $this->withoutMockingConsoleOutput()
        ->artisan('sync push production assets --dry');

    Process::assertRan(fn ($process) => str_contains($process->command, 'rsync'), 2);
});

it('displays dry run messages', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
        '--dry' => true,
    ])->expectsOutputToContain('Starting a dry run');
});
