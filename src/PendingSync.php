<?php

namespace Aerni\Sync;

use Facades\Aerni\Sync\Sync;

class PendingSync
{
    public string $operation;
    public array $remote;
    public array $recipe;
    public array $options;

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
        $this->options = $options;

        return $this;
    }

    public function process(): void
    {
        Sync::process($this);
    }
}
