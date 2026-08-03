<?php

use Aerni\Sync\SyncCommand;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Once;

beforeEach(function () {
    Once::flush();
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $this->remote = [
        'user' => 'forge',
        'host' => '104.26.3.113',
        'port' => 1431,
        'root' => '/home/forge/site.com',
    ];
});

it('generates the correct array for a push operation', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'push',
        remote: $this->remote,
        options: '--archive',
    );

    expect($command->toArray())->toBe([
        'origin' => base_path('storage/app/assets/'),
        'target' => 'forge@104.26.3.113:/home/forge/site.com/storage/app/assets/',
        'options' => '--archive',
        'port' => '1431',
    ]);
});

it('generates the correct array for a pull operation', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'pull',
        remote: $this->remote,
        options: '--archive',
    );

    expect($command->toArray())->toBe([
        'origin' => 'forge@104.26.3.113:/home/forge/site.com/storage/app/assets/',
        'target' => base_path('storage/app/assets/'),
        'options' => '--archive',
        'port' => '1431',
    ]);
});

it('generates the correct rsync string for push', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'push',
        remote: $this->remote,
        options: '--archive',
    );

    $localPath = base_path('storage/app/assets/');

    expect((string) $command)
        ->toBe("rsync -e 'ssh -p 1431' --archive {$localPath} forge@104.26.3.113:/home/forge/site.com/storage/app/assets/");
});

it('generates the correct rsync string for pull', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'pull',
        remote: $this->remote,
        options: '--archive',
    );

    $localPath = base_path('storage/app/assets/');

    expect((string) $command)
        ->toBe("rsync -e 'ssh -p 1431' --archive forge@104.26.3.113:/home/forge/site.com/storage/app/assets/ {$localPath}");
});

it('uses the default port 22 when port is not set', function () {
    $remote = collect($this->remote)->except('port')->all();

    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'push',
        remote: $remote,
        options: '--archive',
    );

    expect($command->toArray()['port'])->toBe('22');
});

it('uses the custom port from the remote', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'push',
        remote: $this->remote,
        options: '--archive',
    );

    expect($command->toArray()['port'])->toBe('1431');
});

it('implements Arrayable and Stringable', function () {
    $command = new SyncCommand(
        path: 'storage/app/assets/',
        operation: 'push',
        remote: $this->remote,
        options: '--archive',
    );

    expect($command)
        ->toBeInstanceOf(Arrayable::class)
        ->toBeInstanceOf(Stringable::class);
});
