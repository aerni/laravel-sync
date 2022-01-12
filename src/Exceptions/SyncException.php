<?php

namespace Aerni\Sync\Exceptions;

use Exception;

class SyncException extends Exception
{
    public static function samePath(): SyncException
    {
        return new static("The origin and target path are one and the same. You can't sync a path with itself.");
    }

    public static function readOnly(): SyncException
    {
        return new static("You can't push to the selected target as it is configured to be read only.");
    }
}
