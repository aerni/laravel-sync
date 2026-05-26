<?php

use Aerni\Sync\Commands\BaseCommand;
use Illuminate\Process\FakeProcessResult;
use Illuminate\Process\PendingProcess;
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
        'recipe' => ['assets'],
        '--dry' => true,
    ])->expectsOutputToContain('Starting a dry run');
});

it('displays dry run success message', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--dry' => true,
    ])->expectsOutputToContain('The dry run of the');
});

it('aborts when user declines confirmation', function () {
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
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
        'recipe' => ['assets'],
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
        'recipe' => ['assets'],
    ])->expectsConfirmation(
        'You are about to pull the assets from production. Are you sure?',
        'yes',
    )->assertSuccessful();
});

it('shows process output in verbose mode without dry run', function () {
    $fakeResult = new FakeProcessResult(exitCode: 0, output: 'rsync output');
    $fakePending = Mockery::mock(PendingProcess::class);
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
        'recipe' => ['assets'],
        '-v' => true,
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'yes',
    )->expectsOutputToContain('Syncing files')
        ->assertSuccessful();
});

it('streams process output when output-producing rsync option is selected', function (string $option) {
    $fakeResult = new FakeProcessResult(exitCode: 0, output: 'rsync output');
    $fakePending = Mockery::mock(PendingProcess::class);
    $fakePending->shouldReceive('forever')->andReturnSelf();
    $fakePending->shouldReceive('run')->andReturnUsing(function ($command, $callback) use ($fakeResult) {
        if ($callback) {
            $callback('out', 'rsync output');
        }

        return $fakeResult;
    });

    Process::swap($fakePending);

    ob_start();
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--option' => [$option],
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'yes',
    )->assertSuccessful();
    $captured = ob_get_clean();

    expect($captured)->toContain('rsync output');
})->with(['--verbose', '--progress', '--stats', '--itemize-changes', '--human-readable']);

it('does not stream process output when no output-producing rsync options are selected', function () {
    $fakeResult = new FakeProcessResult(exitCode: 0, output: 'rsync output');
    $fakePending = Mockery::mock(PendingProcess::class);
    $fakePending->shouldReceive('forever')->andReturnSelf();
    $fakePending->shouldReceive('run')->andReturnUsing(function ($command, $callback) use ($fakeResult) {
        if ($callback) {
            $callback('out', 'rsync output');
        }

        return $fakeResult;
    });

    Process::swap($fakePending);

    ob_start();
    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets'],
        '--option' => ['--archive'],
    ])->expectsConfirmation(
        'You are about to push the assets to production. Are you sure?',
        'yes',
    )->assertSuccessful();
    $captured = ob_get_clean();

    expect($captured)->not->toContain('rsync output');
});

it('prompts for missing arguments interactively', function () {
    $this->artisan('sync', ['--dry' => true])
        ->expectsChoice('Choose if you want to push or pull', 'push', ['push', 'pull'])
        ->expectsChoice('Choose the remote you want to sync with', 'production', ['production'])
        ->expectsChoice('Choose the recipes defining the paths to sync', ['assets'], ['All recipes', '__all__', 'assets', 'assets'])
        ->expectsChoice('Choose rsync options', ['--archive'], array_merge(array_keys(BaseCommand::RSYNC_OPTIONS), array_values(BaseCommand::RSYNC_OPTIONS)))
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, 'rsync'));
});

it('expands all recipes when the all sentinel is selected interactively', function () {
    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/'],
        'uploads' => ['storage/app/uploads/'],
    ]);

    $this->artisan('sync', ['--dry' => true])
        ->expectsChoice('Choose if you want to push or pull', 'push', ['push', 'pull'])
        ->expectsChoice('Choose the remote you want to sync with', 'production', ['production'])
        ->expectsChoice('Choose the recipes defining the paths to sync', ['__all__'], ['All recipes', '__all__', 'assets', 'assets', 'uploads', 'uploads'])
        ->expectsChoice('Choose rsync options', ['--archive'], array_merge(array_keys(BaseCommand::RSYNC_OPTIONS), array_values(BaseCommand::RSYNC_OPTIONS)))
        ->assertSuccessful();

    Process::assertRan(fn ($process) => str_contains($process->command, base_path('storage/app/assets/')));
    Process::assertRan(fn ($process) => str_contains($process->command, base_path('storage/app/uploads/')));
});

it('runs rsync for paths from multiple recipes', function () {
    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/'],
        'uploads' => ['storage/app/uploads/'],
    ]);

    $this->withoutMockingConsoleOutput()
        ->artisan('sync push production assets uploads --dry');

    $localAssets = base_path('storage/app/assets/');
    $localUploads = base_path('storage/app/uploads/');

    Process::assertRan(fn ($process) => str_contains($process->command, $localAssets));
    Process::assertRan(fn ($process) => str_contains($process->command, $localUploads));
});

it('uses the correct confirmation text for multiple recipes', function () {
    config()->set('sync.recipes', [
        'assets' => ['storage/app/assets/'],
        'uploads' => ['storage/app/uploads/'],
    ]);

    $this->artisan('sync', [
        'operation' => 'push',
        'remote' => 'production',
        'recipe' => ['assets', 'uploads'],
    ])->expectsConfirmation(
        'You are about to push the assets and uploads to production. Are you sure?',
        'yes',
    )->assertSuccessful();
});
