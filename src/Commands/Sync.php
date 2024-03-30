<?php

namespace Aerni\Sync\Commands;

use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;

class Sync extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the sync process';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->validate();

        /* Only show the confirmation if we're not performing a dry run */
        if (! $this->option('dry') && ! confirm($this->confirmText())) {
            return;
        }

        $this->option('dry')
            ? $this->info('Starting a dry run ...')
            : $this->info('Syncing files ...');

        $this->commands()->each(function ($command) {
            Process::forever()->run($command, function (string $type, string $output) {
                /* Only show the output if we're performing a dry run or the verbosity is set to verbose */
                if ($this->option('dry') || $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    echo $output;
                }
            });
        });

        $this->option('dry')
            ? $this->info("The dry run of the <comment>{$this->argument('recipe')}</comment> recipe was successfull.")
            : $this->info("The sync of the <comment>{$this->argument('recipe')}</comment> recipe was successfull.");
    }

    protected function confirmText(): string
    {
        $operation = $this->argument('operation');
        $recipe = $this->argument('recipe');
        $remote = $this->argument('remote');
        $preposition = $operation === 'pull' ? 'from' : 'to';

        return "You are about to $operation the $recipe $preposition $remote. Are you sure?";
    }
}
