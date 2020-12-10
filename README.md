# Laravel Sync
This package provides a git-like artisan command to easily sync files and folders between environments using rsync. This is super useful for assets, documents, and any other files that are untracked in your git repository.

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
    | Define on or more remotes you want to sync with.
    | Each remote is an array with 'user', 'host' and 'root'.
    |
    */

    'remotes' => [

        // 'production' => [
        //     'user' => 'forge',
        //     'host' => '104.26.3.113',
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

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout of the syncing process in seconds.
    |
    */

    'timeout' => 60,

];

```

## Requirements
- `rsync` installed on both your source and destination machine
- A working `SSH` setup between your source and destination machine

## Configuration
To use this package, you have to define at least one remote and recipe.

### Remotes
Each remote consists of a a `user`, `host` and `root`.

`user`: The username to log in to the host
`host`: The IP address of your server
`root`: The absolute path to the project's root folder

**Example:**
```php
'production' => [
    'user' => 'forge',
    'host' => '104.26.3.113',
    'root' => '/home/forge/statamic.com',
],
```

### Recipes
Add any number of recipes with the paths you want to sync. Each recipe is an array of relative paths to your project's root.

**Example:**
```php
'recipes' => [
    'assets' => ['storage/app/assets/', 'storage/app/img/'],
    'env' => ['.env'],
],
```

### Options
Configure the default rsync options to use when performing a sync. You can override these options when executing the command.

**Example:**
```php
'options' => [
    '--archive',
    '--progress'
],
```

### Timeout
The timeout of the syncing process in seconds.

**Example:**
```php
'timeout' => 120,
```

## Usage Example
If you know git, you'll feel right at home with the syntax of this command.

```bash
# The structure of the command
php artisan sync [operation] [remote] [recipe] [--option=*]
```

**Examples:**
```bash
# This will pull the assets recipe from the staging remote
php artisan sync pull staging assets

# This will push the assets recipe to the staging remote
php artisan sync push staging assets

# This will pull the assets recipe from the production remote with custom rsync options
php artisan sync pull production assets --option=-avh --option=--delete
```
