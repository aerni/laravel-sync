<?php

use Aerni\Sync\SyncServiceProvider;
use Illuminate\Support\Once;
use Orchestra\Testbench\TestCase;

pest()->extend(TestCase::class)
    ->beforeEach(function () {
        $this->app->register(SyncServiceProvider::class);
        Once::flush();
    })
    ->in(__DIR__);
