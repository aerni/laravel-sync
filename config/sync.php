<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remotes
    |--------------------------------------------------------------------------
    |
    | Define on or more remotes you want to sync with.
    | Each remote is an array with 'user', 'host' and 'root'.
    |
    */

    'remotes' => [

        // 'production' => [
        //     'user' => 'forge',
        //     'host' => '104.26.3.113',
        //     'port' => 1431,
        //     'root' => '/home/forge/statamic.com',
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Recipes
    |--------------------------------------------------------------------------
    |
    | Define one or more recipes with the paths you want to sync.
    | Each recipe is an array of relative paths to your project's root.
    |
    */

    'recipes' => [

        // 'assets' => ['storage/app/assets/', 'storage/app/img/'],

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
