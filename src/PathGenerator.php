<?php

namespace Aerni\Sync;

use Illuminate\Support\Facades\Http;

class PathGenerator
{
    public function localPath(string $path): string
    {
        return base_path($path);
    }

    public function remotePath(array $remote, string $path): string
    {
        $fullPath = $this->joinPaths($remote['root'], $path);

        if ($this->remoteHostEqualsLocalHost($remote['host'])) {
            return $fullPath;
        }

        return "{$remote['user']}@{$remote['host']}:$fullPath";
    }

    protected function joinPaths(): string
    {
        $paths = [];

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', implode('/', $paths));
    }

    protected function remoteHostEqualsLocalHost(string $remoteHost): bool
    {
        $publicIp = Http::get('https://api.ipify.org/?format=json')->json('ip');

        return $publicIp === $remoteHost;
    }
}
