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

it('displays dry run success message', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
        '--dry' => true,
    ])->expectsOutputToContain('The dry run of the');
});

it('aborts when user declines confirmation', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'no',
    )->assertSuccessful();

    Process::assertNotRan(fn ($process) => str_contains($process->command, 'rsync'));
});

it('runs sync when user confirms', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'yes',
    )->expectsOutputToContain('Syncing files')
     ->expectsOutputToContain('The sync of the');

    Process::assertRan(fn ($process) => str_contains($process->command, 'rsync'));
});

it('uses the correct confirmation text for pull', function () {
    $this->artisan('sync', [
        'operation' => 'pull',
        'remote' => 'production',
        'recipe' => 'assets',
    ])->expectsConfirmation(
        'You are about to pull the assets from production. Are you sure?',
        'yes',
    )->assertSuccessful();
});

it('shows process output in verbose mode without dry run', function () {
    $fakeResult = new \Illuminate\Process\FakeProcessResult(exitCode: 0, output: 'rsync output');
    $fakePending = Mockery::mock(\Illuminate\Process\PendingProcess::class);
    $fakePending->shouldReceive('forever')->andReturnSelf();
    $fakePending->shouldReceive('run')->andReturnUsing(function ($command, $callback) use ($fakeResult) {
        if ($callback) {
            $callback('out', 'rsync output');
        }

        return $fakeResult;
    });

    Process::swap($fakePending);

    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => 'assets',
        '-v' => true,
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'yes',
    )->expectsOutputToContain('Syncing files')
     ->assertSuccessful();
});

it('prompts for missing arguments interactively', function () {
    $this->artisan('sync', ['--dry' => true])
        ->expectsChoice('Choose if you want to push or pull', 'push', ['push', 'pull'])
        ->expectsChoice('Choose the remote you want to sync with', 'production', ['production'])
        ->expectsChoice('Choose the recipe defining the paths to sync', 'assets', ['assets'])
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, 'rsync'));
});
