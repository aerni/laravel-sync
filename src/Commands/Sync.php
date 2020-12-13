<?php

namespace Aerni\Sync\Commands;

use Facades\Aerni\Sync\SyncProcessor;

class Sync extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sync";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the sync process';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        if (! $this->canProcessConsoleCommand()) {
            return;
        }

        $this->sync();
    }

    protected function sync(): void
    {
        $commands = $this->commandGenerator()->run();

        if ($commands === null) {
            $this->error("The origin and target path are one and the same. You can't sync a path with itself.");

            return;
        }

        $sync = SyncProcessor::commands($commands)
            ->artisanCommand($this)
            ->run();

        if ($sync->successful()) {
            $this->info('The sync was successful');
        }
    }
}
