<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remotes
    |--------------------------------------------------------------------------
    |
    | Define on or more remotes to sync with.
    | Each remote is an array with 'username', 'host' and 'root'.
    |
    */

    'remotes' => [

        // 'production' => [
        //     'username' => 'forge',
        //     'host' => '104.26.3.113',
        //     'root' => '/home/forge/statamic.com',
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Recipes
    |--------------------------------------------------------------------------
    |
    | Define one or more recipes.
    | Each recipe is a string with a relative path to the location to sync.
    |
    */

    'recipes' => [

        // 'assets' => 'storage/app/assets/',

    ],

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | An array of default rsync options.
    | You can override these options when executing the command.
    |
    */

    'options' => [
        '--archive',
        '--itemize-changes',
        '--verbose',
        '--human-readable',
        '--progress'
    ],

];
