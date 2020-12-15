<?php

namespace Aerni\Sync\Exceptions;

use Exception;

class ConfigException extends Exception
{
    public static function invalidOperation(string $operation): ConfigException
    {
        return new static("The operation [$operation] does not exists. The operation has to be [push] or [pull].");
    }

    public static function invalidRemote(string $key): ConfigException
    {
        return new static("The remote [$key] does not exists. Please choose an existing remote.");
    }

    public static function invalidRecipe(string $key): ConfigException
    {
        return new static("The recipe [$key] does not exists. Please choose an existing recipe.");
    }
}
