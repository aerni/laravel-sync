<?php

namespace Aerni\Sync\Commands;

use Facades\Aerni\Sync\CommandGenerator;

class SyncList extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sync:list";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all sync paths and options';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        if (! $this->canProcessConsoleCommand()) {
            return;
        }

        $this->list();
    }

    protected function list(): void
    {
        $headers = ['Origin', 'Target', 'Options'];
        $commands = $this->commandGenerator()->commandsArray();

        $this->table($headers, $commands);
    }
}
