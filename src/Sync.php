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

        if ($this->shouldPerformSync()) {
            $this->run($this->command());
        }
    }

    protected function run(array $command): void
    {
        (new Process($command))
            ->run(function ($type, $buffer) {
                echo $buffer;
            });
    }

    protected function command(): array
    {
        if ($this->sync->operation === 'push') {
            return array_merge(['rsync', $this->localPath(), $this->remotePath()], $this->sync->options);
        }

        if ($this->sync->operation === 'pull') {
            return array_merge(['rsync', $this->remotePath(), $this->localPath()], $this->sync->options);
        }
    }

    protected function localPath(): string
    {
        return PathGenerator::localPath($this->sync->recipe);
    }

    protected function remotePath(): string
    {
        return PathGenerator::remotePath($this->sync->remote, $this->sync->recipe);
    }

    protected function shouldPerformSync(): bool
    {
        if ($this->localPath() === $this->remotePath()) {
            return false;
        }

        return true;
    }
}
