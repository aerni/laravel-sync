<?php

namespace Aerni\Sync;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use TitasGailius\Terminal\Terminal;

class SyncProcessor
{
    protected Collection $commands;

    protected Command $artisanCommand;

    public function commands(Collection $commands): self
    {
        $this->commands = $commands;

        return $this;
    }

    public function artisanCommand(Command $artisanCommand): self
    {
        $this->artisanCommand = $artisanCommand;

        return $this;
    }

    public function run(): self
    {
        $this->commands->each(function ($command) {
            $response = Terminal::timeout(null)
                ->output($this->artisanCommand)
                ->run($command)
                ->throw();

            $this->successful = $response->successful();
        });

        return $this;
    }

    public function successful(): bool
    {
        return $this->successful;
    }
}
