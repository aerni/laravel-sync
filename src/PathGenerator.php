<?php

namespace Aerni\Sync;

use Illuminate\Support\Facades\Http;

class PathGenerator
{
    public static function localPath(string $path): string
    {
        return base_path($path);
    }

    public static function remotePath(array $remote, string $path): string
    {
        $fullPath = self::joinPaths($remote['root'], $path);

        if (self::remoteHostEqualsLocalHost($remote['host'])) {
            return $fullPath;
        }

        return "{$remote['user']}@{$remote['host']}:$fullPath";
    }

    protected static function joinPaths(): string
    {
        $paths = [];

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', implode('/', $paths));
    }

    protected static function remoteHostEqualsLocalHost(string $host): bool
    {
        return once(fn () => $host === Http::get('https://api.ipify.org/?format=json')->json('ip'));
    }
}
