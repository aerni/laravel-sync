<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Once;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Once::flush();

    config()->set('sync.remotes', [
        'production' => [
            'user' => 'forge',
            'host' => '104.26.3.113',
            'port' => 1431,
            'root' => '/home/forge/site.com',
            'read_only' => false,
        ],
        'staging' => [
            'user' => 'forge',
            'host' => '104.26.3.114',
            'root' => '/home/forge/staging.com',
            'read_only' => true,
        ],
    ]);

    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/', 'storage/app/img/'],
    ]);
});

it('validates that operation must be push or pull', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'invalid',
        'remote' => 'production',
        'recipe' => ['assets'],
    ]);
})->throws(ValidationException::class);

it('validates that remote must exist in config', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'nonexistent',
        'recipe' => ['assets'],
    ]);
})->throws(ValidationException::class);

it('validates that recipe must exist in config', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['nonexistent'],
    ]);
})->throws(ValidationException::class);

it('validates that all recipes must exist in config', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets', 'nonexistent'],
    ]);
})->throws(ValidationException::class);

it('throws when pushing to a read-only remote', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'staging',
        'recipe' => ['assets'],
    ]);
})->throws(RuntimeException::class, "You can't push to the selected target as it is configured to be read-only.");

it('allows pulling from a read-only remote', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->artisan('sync:commands', [
        'operation' => 'pull',
        'remote' => 'staging',
        'recipe' => ['assets'],
    ])->assertSuccessful();
});

it('throws when local path equals remote path', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '104.26.3.113'])]);

    config()->set('sync.remotes.production.root', base_path());

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
    ]);
})->throws(RuntimeException::class, 'The origin and target path are one and the same.');

it('throws when no remotes are configured', function () {
    config()->set('sync.remotes', []);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
    ]);
})->throws(RuntimeException::class, 'You need to define at least one remote in your config file.');

it('throws when no recipes are configured', function () {
    config()->set('sync.recipes', []);

    $this->artisan('sync:commands', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
    ]);
})->throws(RuntimeException::class, 'You need to define at least one recipe in your config file.');
