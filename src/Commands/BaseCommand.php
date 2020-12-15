<?php

namespace Aerni\Sync\Commands;

use Facades\Aerni\Sync\Config;
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
        return Config::operation($this->argument('operation'));
    }

    protected function remote(): array
    {
        return Config::remote($this->argument('remote'));
    }

    protected function recipe(): array
    {
        return Config::recipe($this->argument('recipe'));
    }

    protected function rsyncOptions(): array
    {
        return Config::rsyncOptions($this->option('option'));
    }
}
