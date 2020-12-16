<?php

namespace Aerni\Sync;

use Aerni\Sync\Exceptions\ConfigException;
use Illuminate\Support\Arr;

class Config
{
    public function operation(string $operation): string
    {
        if ($operation !== 'push' && $operation !== 'pull') {
            throw ConfigException::invalidOperation($operation);
        }

        return $operation;
    }

    public function remote(string $key): array
    {
        $remote = Arr::get(config('sync.remotes'), $key);

        if (empty($remote)) {
            throw ConfigException::invalidRemote($key);
        }

        return $remote;
    }

    public function recipe(string $key): array
    {
        $recipe = Arr::get(config('sync.recipes'), $key);

        if (empty($recipe)) {
            throw ConfigException::invalidRecipe($key);
        }

        return $recipe;
    }

    public function options(array $options): array
    {
        if (empty($options)) {
            return config('sync.options');
        }

        return $options;
    }
}
