<?php

namespace Aerni\Sync;

use Illuminate\Support\Facades\Http;

class PathGenerator
{
    public function localPath(string $recipe): string
    {
        return base_path($recipe);
    }

    public function remotePath(array $remote, string $recipe): string
    {
        $fullPath = $this->joinPaths($remote['root'], $recipe);

        if ($this->remoteHostEqualsLocalHost($remote['host'])) {
            return $fullPath;
        }

        return "{$remote['username']}@{$remote['host']}:$fullPath";
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

    protected function remoteHostEqualsLocalHost(string $remoteHost): bool
    {
        $ip = Http::get('https://api.ipify.org/?format=json')->json('ip');

        if ($ip !== $remoteHost) {
            return false;
        }

        return true;
    }
}
