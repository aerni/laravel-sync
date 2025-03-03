<?php

namespace Aerni\Sync;

use Illuminate\Contracts\Support\Arrayable;
use Stringable;

class SyncCommand implements Arrayable, Stringable
{
    public function __construct(
        protected string $path,
        protected string $operation,
        protected array $remote,
        protected string $options,
    ) {}

    public function toArray(): array
    {
        return [
            'origin' => $this->origin(),
            'target' => $this->target(),
            'options' => $this->options,
            'port' => $this->port(),
        ];
    }

    public function __toString(): string
    {
        return "rsync -e 'ssh -p {$this->port()}' {$this->options} {$this->origin()} {$this->target()}";
    }

    protected function origin(): string
    {
        return $this->operation === 'pull'
            ? PathGenerator::remotePath($this->remote, $this->path)
            : PathGenerator::localPath($this->path);
    }

    protected function target(): string
    {
        return $this->operation === 'pull'
            ? PathGenerator::localPath($this->path)
            : PathGenerator::remotePath($this->remote, $this->path);
    }

    protected function port(): string
    {
        return $this->remote['port'] ?? '22';
    }
}
