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
        'assets' => ['storage/app/assets/'],
    ]);
});

it('uses default config options when no CLI options are provided', function () {
    config()->set('sync.options', ['--archive']);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
    ])->expectsOutputToContain('--archive');
});

it('uses CLI options when provided', function () {
    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--option' => ['--compress', '--verbose'],
    ])->expectsOutputToContain('--compress --verbose');
});

it('adds dry-run flags when the dry option is set', function () {
    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--dry' => true,
    ])->expectsOutputToContain('--dry-run');
});

it('deduplicates options', function () {
    $localPath = base_path('storage/app/assets/');

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--option' => ['--archive', '--archive'],
    ])->expectsOutputToContain("--archive {$localPath}");
});
