<?php

namespace Aerni\Sync;

use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            Commands\Sync::class,
            Commands\SyncCommands::class,
            Commands\SyncList::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/sync.php' => config_path('sync.php'),
        ], 'sync-config');

        $this->mergeConfigFrom(__DIR__.'/../config/sync.php', 'sync');
    }

    public function register()
    {
        //
    }
}
