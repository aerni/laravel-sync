<?php

namespace Aerni\Sync;

use Facades\Aerni\Sync\PendingSync;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "
        sync
        {operation : Choose if you want to 'push' or 'pull'}
        {remote : The remote you want to sync with}
        {recipe : The recipe defining the paths to sync}
        {--option=* : An rsync option to use}
    ";

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
    public function handle(): void
    {
        if (! $this->canSync()) {
            return;
        }

        $this->sync();
    }

    protected function sync(): void
    {
        $sync = PendingSync::command($this)
            ->operation($this->operation())
            ->remote($this->remote())
            ->recipe($this->recipe())
            ->options($this->rsyncOptions())
            ->process();

        if ($sync) {
            $this->info('The sync was successful');
        }
    }

    protected function operation(): string
    {
        return $this->argument('operation');
    }

    protected function remote(): ?array
    {
        return Arr::get(config('sync.remotes'), $this->argument('remote'));
    }

    protected function recipe(): ?array
    {
        return Arr::get(config('sync.recipes'), $this->argument('recipe'));
    }

    protected function rsyncOptions(): array
    {
        if (empty($this->option('option'))) {
            return config('sync.options');
        }

        return $this->option('option');
    }

    protected function canSync(): bool
    {
        if ($this->operation() !== 'push' && $this->operation() !== 'pull') {
            $this->error("The provided operation does not exist. The operation has to be either 'push' or 'pull'");
            return false;
        }

        if ($this->remote() === null) {
            $this->error("The provided remote does not exists. Please choose an existing remote.");
            return false;
        };

        if ($this->recipe() === null) {
            $this->error("The provided recipe does not exists. Please choose an existing recipe.");
            return false;
        }

        return true;
    }
}
