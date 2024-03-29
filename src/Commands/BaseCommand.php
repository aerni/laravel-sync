<?php

namespace Aerni\Sync\Commands;

use Aerni\Sync\SyncCommand;
use Illuminate\Support\Arr;
use Aerni\Sync\PathGenerator;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class BaseCommand extends Command implements PromptsForMissingInput
{
    public function __construct()
    {
        $baseSignature = '
            {operation : Choose if you want to push or pull}
            {remote : The remote you want to sync with}
            {recipe : The recipe defining the paths to sync}
            {--O|option=* : An rsync option to use}
            {--D|dry : Perform a dry run of the sync}
        ';

        $this->signature .= $baseSignature;

        parent::__construct();
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'operation' => fn () => select(
                label: 'Choose if you want to push or pull',
                options: ['push', 'pull'],
            ),
            'remote' => fn () => select(
                label: 'Choose the remote you want to sync with',
                options: array_keys($this->remotes()),
            ),
            'recipe' => fn () => select(
                label: 'Choose the recipe defining the paths to sync',
                options: array_keys($this->recipes()),
            ),
        ];
    }

    protected function validate(): void
    {
        Validator::validate($this->arguments(), [
            'operation' => 'required|in:push,pull',
            'remote' => ['required', Rule::in(array_keys($this->remotes()))],
            'recipe' => ['required', Rule::in(array_keys($this->recipes()))],
        ], [
            'operation.in' => "The :attribute [:input] does not exists. Valid values are [push] or [pull].",
            'remote.in' => "The :attribute [:input] does not exists. Please choose a valid remote.",
            'recipe.in' => "The :attribute [:input] does not exists. Please choose a valid recipe.",
        ]);

        if ($this->localPathEqualsRemotePath()) {
            throw new \RuntimeException("The origin and target path are one and the same. You can't sync a path with itself.");
        }

        if ($this->remoteIsReadOnly() && $this->operation() === 'push') {
            throw new \RuntimeException("You can't push to the selected target as it is configured to be read-only.");
        }
    }

    protected function localPathEqualsRemotePath(): bool
    {
        return PathGenerator::localPath($this->recipe()[0])
            === PathGenerator::remotePath($this->remote(), $this->recipe()[0]);
    }

    protected function remoteIsReadOnly(): bool
    {
        return Arr::get($this->remote(), 'read_only', false);
    }

    protected function commands(): Collection
    {
        return collect($this->recipe())
            ->map(fn ($path) => new SyncCommand(
                path: $path,
                operation: $this->operation(),
                remote: $this->remote(),
                options: $this->rsyncOptions(),
            ));
    }

    protected function operation(): string
    {
        return $this->argument('operation');
    }

    protected function remote(): array
    {
        return Arr::get($this->remotes(), $this->argument('remote'));
    }

    protected function recipe(): array
    {
        return Arr::get($this->recipes(), $this->argument('recipe'));
    }

    protected function remotes(): array
    {
        $remotes = config('sync.remotes');

        if (empty($remotes)) {
            throw new \RuntimeException('You need to define at least one remote in your config file.');
        }

        return $remotes;
    }

    protected function recipes(): array
    {
        $recipes = config('sync.recipes');

        if (empty($recipes)) {
            throw new \RuntimeException('You need to define at least one recipe in your config file.');
        }

        return $recipes;
    }

    protected function rsyncOptions(): string
    {
        $options = $this->option('option');

        if (empty($options)) {
            $options = config('sync.options');
        }

        return collect($options)
            ->when(
                $this->option('dry'),
                fn ($collection) => $collection->merge(['--dry-run', '--human-readable', '--progress', '--stats', '--verbose'])
            )
            ->when(
                $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE,
                fn ($collection) => $collection->merge(['--human-readable', '--progress', '--stats', '--verbose'])
            )
            ->filter()
            ->unique()
            ->implode(' ');
    }
}
