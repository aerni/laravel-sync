<?php

namespace Aerni\Sync\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Facades\Aerni\Sync\CommandGenerator;

class BaseCommand extends Command
{
    public function __construct()
    {
        $baseSignature = "
            {operation : Choose if you want to 'push' or 'pull'}
            {remote : The remote you want to sync with}
            {recipe : The recipe defining the paths to sync}
            {--option=* : An rsync option to use}
        ";

        $this->signature .= $baseSignature;

        parent::__construct();
    }

    protected function commandGenerator(): \Aerni\Sync\CommandGenerator
    {
        return CommandGenerator::operation($this->operation())
            ->remote($this->remote())
            ->recipe($this->recipe())
            ->options($this->rsyncOptions());
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

    protected function canProcessConsoleCommand(): bool
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
