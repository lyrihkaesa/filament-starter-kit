<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class MakeActionCommand extends GeneratorCommand
{
    protected $name = 'make:action';

    protected $description = 'Generate action class(es) for a model or a custom action';

    protected $type = 'Action';

    public function handle(): bool|null|int
    {
        $model = $this->option('model');
        $isSingle = $this->option('single');

        if ($model && ! $isSingle) {
            $actions = $this->resolveActions();

            foreach ($actions as $action) {
                $className = "{$action}{$model}Action";

                $this->call(self::class, [
                    'name' => $className,
                    '--model' => $model,
                    '--sub-folder' => $this->option('sub-folder'),
                    '--'.mb_strtolower($action) => true,
                    '--single' => true,
                    '--force' => $this->option('force'),
                ]);
            }

            $this->info('Generated action(s): '.implode(', ', $actions));

            return 0;
        }

        // Prompt for name if not provided
        $name = $this->argument('name');
        if (! is_string($name) || $name === '') {
            $name = $this->ask('What is the class name? (e.g., Posts/PublishPostAction)');
        }

        $this->input->setArgument('name', $name);

        if (! is_string($name) || $name === '') {
            return 0;
        }

        // Tentukan path file yang akan dibuat
        $path = $this->getPath($this->qualifyClass($name));

        if (file_exists($path) && ! $this->option('force')) {
            $overwrite = $this->confirm(
                "File '{$path}' already exists. Do you want to overwrite it?",
                false // default jawaban NO
            );

            if (! $overwrite) {
                $this->warn('Skipped '.$path);

                return 0;
            }

            // Kalau user pilih YES, set opsi --force supaya parent::handle() otomatis overwrite
            $this->input->setOption('force', true);
        }

        return parent::handle();
    }

    protected function getStub(): string
    {
        if ($this->option('create')) {
            return $this->resolveStub('create-action');
        }
        if ($this->option('update')) {
            return $this->resolveStub('update-action');
        }
        if ($this->option('delete')) {
            return $this->resolveStub('delete-action');
        }

        return $this->resolveStub('custom-action');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->option('model')) {
            return $rootNamespace.'\\Actions\\'.$this->getModelFolder();
        }

        return $rootNamespace.'\\Actions';
    }

    protected function buildClass($name): string
    {
        $replace = [];

        if ($model = $this->option('model')) {
            $replace = [
                '{{ model }}' => class_basename($model),
                '{{ modelNamespace }}' => 'App\\Models\\'.str_replace('/', '\\', $model),
                '{{ modelVariable }}' => lcfirst(class_basename($model)),
            ];
        }

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array<int, array{0: string, 1: int, 2: string}>
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the action class'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array{0: string, 1: ?string, 2: int, 3: string}>
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'The model name (e.g. Post)'],
            ['create', 'c', InputOption::VALUE_NONE, 'Generate only Create action'],
            ['update', 'u', InputOption::VALUE_NONE, 'Generate only Update action'],
            ['delete', 'd', InputOption::VALUE_NONE, 'Generate only Delete action'],
            ['sub-folder', 's', InputOption::VALUE_OPTIONAL, 'Custom subfolder name for generated actions'],
            ['single', null, InputOption::VALUE_NONE, 'Used internally to prevent recursive loop'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }

    /**
     * @return string[]
     */
    private function resolveActions(): array
    {
        $actions = [];

        if ($this->option('create')) {
            $actions[] = 'Create';
        }
        if ($this->option('update')) {
            $actions[] = 'Update';
        }
        if ($this->option('delete')) {
            $actions[] = 'Delete';
        }

        return $actions === [] ? ['Create', 'Update', 'Delete'] : $actions;
    }

    private function getModelFolder(): string
    {
        $folder = $this->option('sub-folder');

        return $folder
            ? str($folder)->studly()->value()
            : str($this->option('model'))->studly()->plural()->value();
    }

    private function resolveStub(string $stub): string
    {
        $customStub = base_path("stubs/{$stub}.stub");

        return file_exists($customStub)
            ? $customStub
            : __DIR__."/../../../stubs/{$stub}.stub";
    }
}
