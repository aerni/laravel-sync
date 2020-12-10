<?php

namespace Aerni\Sync;

use Aerni\Sync\PendingSync;
use Facades\Aerni\Sync\PathGenerator;
use Symfony\Component\Process\Process;

class Sync
{
    protected $sync;

    public function process(PendingSync $sync): void
    {
        $this->sync = $sync;

        $this->run($this->commands());
    }

    protected function run(?array $commands): void
    {
        if ($commands === null) {
            return;
        }

        foreach ($commands as $command) {
            (new Process($command))
                ->setTimeout(7200)
                ->run(function ($type, $buffer) {
                    echo $buffer;
                });
        }
    }

    protected function commands(): ?array
    {
        foreach ($this->sync->recipe as $key => $path) {
            if ($this->shouldPush($key)) {
                $commands[] = $this->pushCommand($key);
            }

            if ($this->shouldPull($key)) {
                $commands[] = $this->pullCommand($key);
            }
        }

        return $commands ?? null;
    }

    protected function pushCommand(string $key): array
    {
        return array_merge(['rsync', $this->localPath($key), $this->remotePath($key)], $this->sync->options);
    }

    protected function pullCommand(string $key): array
    {
        return array_merge(['rsync', $this->remotePath($key), $this->localPath($key)], $this->sync->options);
    }

    protected function localPath(string $key): string
    {
        return PathGenerator::localPath($this->sync->recipe[$key]);
    }

    protected function remotePath(string $key): string
    {
        return PathGenerator::remotePath($this->sync->remote, $this->sync->recipe[$key]);
    }

    protected function shouldPush(string $key): bool
    {
        if ($this->sync->operation !== 'push') {
            return false;
        }

        if ($this->localPath($key) === $this->remotePath($key)) {
            return false;
        }

        return true;
    }

    protected function shouldPull(string $key): bool
    {
        if ($this->sync->operation !== 'pull') {
            return false;
        }

        if ($this->localPath($key) === $this->remotePath($key)) {
            return false;
        }

        return true;
    }
}
