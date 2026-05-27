<?php

namespace Aerni\Sync\Commands;

use Aerni\Sync\PathGenerator;
use Aerni\Sync\SyncCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class BaseCommand extends Command implements PromptsForMissingInput
{
    public const ALL_RECIPES = '__all__';

    private bool $optionsSetInteractively = false;

    public const RSYNC_OPTIONS = [
        '--archive' => 'Archive mode (-a, --archive)',
        '--compress' => 'Compress file data during transfer (-z, --compress)',
        '--verbose' => 'Increase verbosity (-v, --verbose)',
        '--progress' => 'Show progress during transfer (--progress)',
        '--delete' => 'Delete extraneous files from destination (--delete)',
        '--dry-run' => 'Perform a trial run with no changes made (-n, --dry-run)',
        '--stats' => 'Give some file-transfer stats (--stats)',
        '--human-readable' => 'Output numbers in a human-readable format (-h, --human-readable)',
        '--delete-after' => 'Delete after transfer, not during (--delete-after)',
        '--partial' => 'Keep partially transferred files (--partial)',
        '--update' => 'Skip files newer on the receiver (-u, --update)',
        '--checksum' => 'Skip based on checksum, not mod-time & size (-c, --checksum)',
        '--copy-links' => 'Transform symlinks into referent files (-L, --copy-links)',
        '--itemize-changes' => 'Output a change-summary for all updates (-i, --itemize-changes)',
        '--no-perms' => 'Do not preserve permissions (--no-perms)',
        '--no-owner' => 'Do not preserve owner (--no-owner)',
        '--no-group' => 'Do not preserve group (--no-group)',
    ];

    public function __construct()
    {
        $baseSignature = '
            {operation : Choose if you want to push or pull}
            {remote : The remote you want to sync with}
            {recipe?* : The recipes defining the paths to sync}
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
        ];
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $isInteractive = empty($input->getArgument('recipe'));

        if ($isInteractive) {
            $recipeKeys = array_keys($this->recipes());
            $recipeOptions = array_merge(
                [self::ALL_RECIPES => 'All recipes'],
                array_combine($recipeKeys, $recipeKeys),
            );

            $selected = multiselect(
                label: 'Choose the recipes defining the paths to sync',
                options: $recipeOptions,
                required: 'You must select at least one recipe.',
                hint: "Select 'All recipes' to choose every recipe.",
            );

            if (in_array(self::ALL_RECIPES, $selected, true)) {
                $selected = $recipeKeys;
            }

            $input->setArgument('recipe', $selected);

            if (empty($input->getOption('option'))) {
                $configOptions = config('sync.options', []);
                $default = array_values(array_intersect($configOptions, array_keys(self::RSYNC_OPTIONS)));

                $defaultOptions = array_intersect_key(self::RSYNC_OPTIONS, array_flip($default));
                $options = array_merge($defaultOptions, array_diff_key(self::RSYNC_OPTIONS, $defaultOptions));

                $input->setOption('option', multiselect(
                    label: 'Choose rsync options',
                    options: $options,
                    default: $default,
                    hint: 'Config defaults are pre-selected. Use space to toggle.',
                ));

                $this->optionsSetInteractively = true;
            }
        }
    }

    protected function validate(): void
    {
        Validator::validate([
            'operation' => $this->operation(),
            'remote' => $this->argument('remote'),
            'recipe' => $this->selectedRecipes(),
        ], [
            'operation' => 'required|in:push,pull',
            'remote' => ['required', Rule::in(array_keys($this->remotes()))],
            'recipe' => 'required|array|min:1',
            'recipe.*' => [Rule::in(array_keys($this->recipes()))],
        ], [
            'operation.in' => 'The :attribute [:input] does not exists. Valid values are [push] or [pull].',
            'remote.in' => 'The :attribute [:input] does not exists. Please choose a valid remote.',
            'recipe.required' => 'You must provide at least one recipe.',
            'recipe.min' => 'You must provide at least one recipe.',
            'recipe.*.in' => 'The recipe [:input] does not exist. Please choose a valid recipe.',
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
        return collect($this->recipePaths())->contains(
            fn ($path) => PathGenerator::localPath($path) === PathGenerator::remotePath($this->remote(), $path)
        );
    }

    protected function remoteIsReadOnly(): bool
    {
        return Arr::get($this->remote(), 'read_only', false);
    }

    protected function commands(): Collection
    {
        return collect($this->recipePaths())
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

    protected function selectedRecipes(): array
    {
        return (array) $this->argument('recipe');
    }

    protected function recipePaths(): array
    {
        $recipes = $this->recipes();

        return collect($this->selectedRecipes())
            ->flatMap(fn ($name) => Arr::get($recipes, $name, []))
            ->unique()
            ->values()
            ->all();
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

        if (! $this->optionsSetInteractively && empty($options)) {
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
