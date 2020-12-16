<?php

namespace Aerni\Sync\Commands;

class SyncCommands extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sync:commands";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all rsync commands';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $this->commandGenerator()->commandsString()->each(function ($command) {
            $this->info($command);
            $this->newLine();
        });
    }
}
