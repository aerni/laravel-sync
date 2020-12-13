<?php

namespace Aerni\Sync;

use Facades\Aerni\Sync\PathGenerator;
use Illuminate\Support\Collection;

class CommandGenerator
{
    protected string $operation;
    protected array $remote;
    protected array $recipe;
    protected string $options;

    public function operation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function remote(array $remote): self
    {
        $this->remote = $remote;

        return $this;
    }

    public function recipe(array $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = implode(' ', $options);

        return $this;
    }

    public function run(): ?Collection
    {
        if ($this->localPathEqualsRemotePath()) {
            return null;
        }

        return $this->commandsString();
    }

    public function commandsArray(): Collection
    {
        return collect($this->recipe)->map(function ($path, $key) {
            return [
                'origin' => ($this->operation === 'pull') ? $this->remotePath($key) : $this->localPath($key),
                'target' => ($this->operation === 'pull') ? $this->localPath($key) : $this->remotePath($key),
                'options' => $this->options,
            ];
        });
    }

    protected function commandsString(): Collection
    {
        return $this->commandsArray()->map(function ($command) {
            return "rsync {$command['origin']} {$command['target']} {$command['options']}";
        });
    }

    protected function localPath(string $key): string
    {
        return PathGenerator::localPath($this->recipe[$key]);
    }

    protected function remotePath(string $key): string
    {
        return PathGenerator::remotePath($this->remote, $this->recipe[$key]);
    }

    protected function localPathEqualsRemotePath(): bool
    {
        if ($this->localPath(0) !== $this->remotePath(0)) {
            return false;
        }

        return true;
    }
}
