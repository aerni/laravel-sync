<?php

namespace Aerni\Sync;

use Illuminate\Console\Command;
use Aerni\Sync\SyncFacade as Sync;
use Illuminate\Support\Arr;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        sync
        {origin? : The origin you want your data to sync from}
        {target? : The target you want your data to sync to}
        {--options= : Define a set of rsync options}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync your data between locations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->sync($this->origin(), $this->target(), $this->rsyncOptions());
    }

    protected function sync($origin, $target, $options)
    {
        if (is_array($origin) && is_array($target)) {
            foreach ($origin as $key => $value) {
                Sync::origin($value)->target($target[$key])->options($options)->sync();
            }
        } else {
            Sync::origin($origin)->target($target)->options($options)->sync();
        }
    }

    protected function origin()
    {
        $origin = $this->argument('origin')
            ?? $this->choice('Choose the origin', array_keys($this->locations()));

        return Arr::get($this->locations(), $origin);
    }

    protected function target()
    {
        $target = $this->argument('target')
            ?? $this->choice('Choose the target', array_keys($this->locations()));

        return Arr::get($this->locations(), $target);
    }

    protected function rsyncOptions(): string
    {
        $options = $this->option('options')
            ?? $this->ask('Define the rsync options you want to use. Leave empty to use the default options.');

        return $options ?? config('sync.options');
    }

    protected function locations(): array
    {
        return config('sync.locations');
    }
}
