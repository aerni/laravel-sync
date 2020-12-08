<?php

namespace Aerni\Sync;

class Sync
{
    protected string $origin;
    protected string $target;
    protected string $options;

    public function origin(string $path): self
    {
        $this->origin = $path;

        return $this;
    }

    public function target(string $path): self
    {
        $this->target = $path;

        return $this;
    }

    public function options(string $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function sync()
    {
        echo shell_exec("rsync {$this->origin} {$this->target} {$this->options}");
    }
}
