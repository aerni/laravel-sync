<?php

namespace Aerni\Sync\Commands;

use function Laravel\Prompts\table;

class SyncList extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all sync paths and options';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->validate();

        table(
            ['Origin', 'Target', 'Options', 'Port'],
            $this->commands()->map->toArray()
        );
    }
}
