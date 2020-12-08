<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remotes
    |--------------------------------------------------------------------------
    |
    | Define on or more remotes. Each remote is a string with its path.
    |
    | Example: 'production' => 'forge@104.26.3.113:/home/forge/statamic.com',
    |
    */

    'remotes' => [

        //

    ],

    /*
    |--------------------------------------------------------------------------
    | Recipes
    |--------------------------------------------------------------------------
    |
    | Define one or more recipes. Each recipe is an array with two values.
    | The first value is the local, and the second value the remote path.
    |
    | Example: 'assets' => [storage_path('app/assets/'), 'storage/app/assets/']
    |
    */

    'recipes' => [

        //

    ],

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | A string of default rsync options.
    | You can override these options when executing the command.
    |
    */

    'options' => '--archive --itemize-changes --verbose --human-readable --progress',

];
