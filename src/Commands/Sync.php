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
        if (! $this->confirm($this->confirmText(), true)) {
            return;
        };

        $commands = $this->commandGenerator()->run();

        $sync = SyncProcessor::commands($commands)
            ->artisanCommand($this)
            ->run();

        if ($sync->successful()) {
            $this->info('The sync was successful');
        }
    }

    protected function confirmText(): string
    {
        $operation = $this->argument('operation');
        $recipe = $this->argument('recipe');
        $remote = $this->argument('remote');
        $preposition = $operation === 'pull' ? 'from' : 'to';

        return "Please confirm that you want to <comment>$operation</comment> the <comment>$recipe</comment> $preposition <comment>$remote</comment>";
    }
}
