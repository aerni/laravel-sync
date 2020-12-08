<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    |
    | An array of locations to sync.
    |
    */

    'locations' => [

        'local' => [
            //
        ],

        'staging' => [
            //
        ],

        'production' => [
            //
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | A string of default rsync options.
    | You can override these options with each command.
    |
    */

    'options' => '--archive --itemize-changes --verbose --human-readable --progress',

];
