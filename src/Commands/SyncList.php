<?php

namespace Aerni\Sync\Commands;

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
        $headers = ['Origin', 'Target', 'Options', 'Port'];
        $commands = $this->commandGenerator()->commandsArray();

        $this->table($headers, $commands);
    }
}
