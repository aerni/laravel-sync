<?php

use Aerni\Sync\PathGenerator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Once;

beforeEach(function () {
    Once::flush();
});

it('generates the local path using base_path', function () {
    expect(PathGenerator::localPath('storage/app/assets/'))
        ->toBe(base_path('storage/app/assets/'));
});

it('generates the remote path with user and host prefix', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $remote = [
        'user' => 'forge',
        'host' => '104.26.3.113',
        'root' => '/home/forge/site.com',
    ];

    expect(PathGenerator::remotePath($remote, 'storage/app/assets/'))
        ->toBe('forge@104.26.3.113:/home/forge/site.com/storage/app/assets/');
});

it('generates the remote path without prefix when remote host equals local host', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '104.26.3.113'])]);

    $remote = [
        'user' => 'forge',
        'host' => '104.26.3.113',
        'root' => '/home/forge/site.com',
    ];

    expect(PathGenerator::remotePath($remote, 'storage/app/assets/'))
        ->toBe('/home/forge/site.com/storage/app/assets/');
});

it('normalizes multiple slashes in the remote path', function () {
    Http::fake(['api.ipify.org/*' => Http::response(['ip' => '1.2.3.4'])]);

    $remote = [
        'user' => 'forge',
        'host' => '104.26.3.113',
        'root' => '/home/forge/site.com/',
    ];

    expect(PathGenerator::remotePath($remote, '/storage/app/assets/'))
        ->toBe('forge@104.26.3.113:/home/forge/site.com/storage/app/assets/');
});
