# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer install          # Install dependencies
composer test             # Run tests (alias for vendor/bin/pest)
vendor/bin/pest           # Run tests directly
vendor/bin/pint           # Fix code style (Laravel Pint)
```

Run a single test:
```bash
vendor/bin/pest --filter "test description"
```

## Architecture

This is a **Laravel package** (`aerni/sync`) that provides artisan commands to sync files/folders between environments using `rsync` over SSH.

### Core Concepts

- **Remotes**: Named SSH targets (user, host, root, port, read_only) defined in `config/sync.php`
- **Recipes**: Named groups of file paths to sync, also defined in `config/sync.php`
- **Operations**: `push` (local → remote) or `pull` (remote → local)

### Key Files

- `src/SyncServiceProvider.php` — Registers the three artisan commands and publishes `config/sync.php`
- `src/SyncCommand.php` — Value object representing a single rsync invocation; implements `Arrayable` and `Stringable` to produce the shell command string
- `src/PathGenerator.php` — Builds local and remote paths; detects if remote host equals local host by calling `api.ipify.org`
- `src/Commands/BaseCommand.php` — Shared base for all three commands; handles argument parsing, interactive prompts (via Laravel Prompts), validation, and building the collection of `SyncCommand` objects
- `src/Commands/Sync.php` — Executes rsync via `Illuminate\Support\Facades\Process`
- `src/Commands/SyncList.php` — Displays a table of origin/target/options/port
- `src/Commands/SyncCommands.php` — Prints the raw rsync shell commands

### Command Signature Pattern

`BaseCommand` appends shared arguments/options to the child command's `$signature`:
```
{operation} {remote} {recipe} {--O|option=*} {--D|dry}
```
All three commands (`sync`, `sync:list`, `sync:commands`) share this signature via inheritance.

### Testing

Tests use [Pest 3](https://pestphp.com) with [Orchestra Testbench](https://github.com/orchestral/testbench). `tests/Pest.php` configures all tests to extend `Orchestra\Testbench\TestCase` automatically, so test files just use `it()` / `expect()` directly without any class boilerplate.

### CI

- **run-tests.yaml**: Runs PHPUnit across PHP 8.2/8.3/8.4 × Laravel 11/12 × prefer-lowest/prefer-stable
- **pint.yaml**: Auto-commits style fixes via Laravel Pint on every push
