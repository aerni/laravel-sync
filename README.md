# Laravel Sync
This package provides a git-like artisan command to easily sync locations.

## Installation
Install the package using Composer.

```bash
composer require aerni/sync
```

Publish the config of the package.

```bash
php artisan vendor:publish --provider="Aerni\Sync\SyncServiceProvider"
```

The following config will be published to `config/sync.php`.

```php
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
```

## Configuration

### Remotes
Think of remotes like a remote git repository. Each remote consists of a `username`, `host` and `root`.

### Recipes
Add any number of recipes with the paths you want to sync. A very common recipe is `assets`.

### Options
Configure the default rsync options to use when performing a sync.

## Usage Example
The syntax of this command is inspired by git.

```bash
# This will pull the assets from the staging remote
php artisan sync pull staging assets

# This will push the assets to the staging remote
php artisan sync push staging assets
```
