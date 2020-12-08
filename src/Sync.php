<?php

namespace Aerni\Sync;

class Sync
{
    protected string $operation;
    protected string $remote;
    protected array $recipe;
    protected string $options;

    public function operation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function remote(string $remote): self
    {
        $this->remote = $remote;

        return $this;
    }

    public function recipe(array $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function options(string $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function run()
    {
        if ($this->operation === 'push') {
            echo shell_exec("rsync {$this->localPath()} {$this->remotePath()} {$this->options}");
        }

        if ($this->operation === 'pull') {
            echo shell_exec("rsync {$this->remotePath()} {$this->localPath()} {$this->options}");
        }
    }

    protected function localPath(): string
    {
        return $this->recipe[0];
    }

    protected function remotePath(): string
    {
        return $this->joinPaths($this->remote, $this->recipe[1]);
    }

    protected function joinPaths(): string
    {
        $paths = [];

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }
}
