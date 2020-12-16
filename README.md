![Packagist version](https://flat.badgen.net/packagist/v/aerni/sync/latest) ![Packagist Total Downloads](https://flat.badgen.net/packagist/dt/aerni/sync) ![License](https://flat.badgen.net/github/license/aerni/laravel-sync)

# Laravel Sync
This package provides a git-like artisan command to easily sync files and folders between environments. This is super useful for assets, documents, and any other files that are untracked in your git repository.

Laravel Sync is a no-brainer and will soon become best friends with your deploy script. The days are over when you had to manually keep track of files and folders between your environments. Do yourself a favor and give it a try!

## Requirements
- `rsync` on both your source and destination machine
- A working `SSH` setup between your source and destination machine

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
    | Each remote is an array with 'user', 'host', 'root', and optional 'port'.
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
    | Each recipe is an array of paths relative to your project's root.
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
        // '--archive',
        // '--itemize-changes',
        // '--verbose',
        // '--human-readable',
        // '--progress'
    ],

];
```

## Configuration
To use this package, you have to define at least one remote and recipe.

### Remotes
Each remote consists of a a `user`, a `host` and a `root`. Optionally, you may also define the SSH `port`.

| Key    | Description                                    |
| ------ | ---------------------------------------------- |
| `user` | The username to log in to the host             |
| `host` | The IP address of your server.                 |
| `port` | The SSH port to use for this connection        |
| `root` | The absolute path to the project's root folder |

```php
'production' => [
    'user' => 'forge',
    'host' => '104.26.3.113',
    'port' => 1431,
    'root' => '/home/forge/statamic.com',
],
```

### Recipes
Add any number of recipes with the paths you want to sync. Each recipe is an array of paths relative to your project's root.

```php
'recipes' => [
    'assets' => ['storage/app/assets/', 'storage/app/img/'],
    'env' => ['.env'],
],
```

### Options
Configure the default rsync options to use when performing a sync. You can override these options when executing the command.

```php
'options' => [
    '--archive',
    '--progress'
],
```

## The Command
If you know git, you'll feel right at home with the syntax of this command:

```bash
php artisan [command] [operation] [remote] [recipe] [options]
```

### Command Structure
The structure of the command looks like this:

| Structure      | Description                                     |
| -------------- | ----------------------------------------------- |
| `command`      | The command you want to use                     |
| `operation`    | The operation to use. Either `pull` or `push`.  |
| `remote`       | The remote you want to sync with                |
| `recipe`       | The recipe defining the paths to sync           |
| `--O\|option=*` | An rsync option to use                         |
| `--D\|dry`      | Perform a dry run of the sync                  |

### Available Commands
You have three commands at your disposal:

| Command         | Description                                                |
| --------------- | ---------------------------------------------------------- |
| `sync`          | Perform the sync                                           |
| `sync:list`     | List the origin, target, options, and port in a nice table |
| `sync:commands` | List all rsync commands                                    |

### Available Options
You may use the following options:

| Option                | Description                        |
| --------------------- | ---------------------------------- |
| `-O*` or `--option=*` | Override the default rsync options |
| `-D` or `--dry`       | Perform a dry run of the sync      |

## Usage Examples

Pull the assets recipe from the staging remote:
```bash
php artisan sync pull staging assets
```

Push the assets recipe to the production remote with some custom rsync options:
```bash
php artisan sync push production assets --option=-avh --option=--delete
```

Perform a dry sync:
```bash
php artisan sync pull staging assets --dry
```
