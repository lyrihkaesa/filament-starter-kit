<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[Description('Generate a custom Filament resource with action pattern')]
#[Signature('make:starter-resource {model} {--view} {--soft-deletes} {--force}')]
final class MakeStarterResourceCommand extends Command
{
    public function handle(): int
    {
        $model = Str::studly($this->argument('model'));
        $modelPlural = Str::plural($model);
        $namespace = 'App\Filament\Resources\\'.$modelPlural;
        $path = app_path('Filament/Resources/'.$modelPlural);

        if (File::exists($path) && ! $this->option('force')) {
            $this->error(sprintf('Resource %s already exists!', $modelPlural));

            return 1;
        }

        $this->generateActions($model);
        $this->generateResourceFiles($model, $modelPlural, $namespace, $path);

        $this->info(sprintf('Starter Resource for %s generated successfully!', $model));

        return 0;
    }

    private function generateActions(string $model): void
    {
        $this->call('make:action', [
            '--model' => $model,
            '--force' => $this->option('force'),
        ]);
    }

    private function generateResourceFiles(string $model, string $modelPlural, string $namespace, string $path): void
    {
        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ model }}' => $model,
            '{{ modelPlural }}' => $modelPlural,
            '{{ modelFqn }}' => 'App\Models\\'.$model,
            '{{ resourceFqn }}' => sprintf('%s\%sResource', $namespace, $model),
            '{{ softDeletesImport }}' => $this->option('soft-deletes')
                ? "use Illuminate\Database\Eloquent\SoftDeletingScope;\nuse Illuminate\Database\Eloquent\Builder;"
                : '',
            '{{ softDeletesMethod }}' => $this->option('soft-deletes')
                ? "\n    public static function getEloquentQuery(): Builder\n    {\n        return parent::getEloquentQuery()\n            ->withoutGlobalScopes([\n                SoftDeletingScope::class,\n            ]);\n    }\n"
                : '',
        ];

        // Ensure directories exist
        File::ensureDirectoryExists($path.'/Pages');
        File::ensureDirectoryExists($path.'/Schemas');
        File::ensureDirectoryExists($path.'/Tables');

        // Generate files from stubs
        $this->generateFile('resource', sprintf('%s/%sResource.php', $path, $model), $replacements);
        $this->generateFile('create', sprintf('%s/Pages/Create%s.php', $path, $model), $replacements);
        $this->generateFile('edit', sprintf('%s/Pages/Edit%s.php', $path, $model), $replacements);
        $this->generateFile('list', sprintf('%s/Pages/List%s.php', $path, $modelPlural), $replacements);
        $this->generateFile('view-page', sprintf('%s/Pages/View%s.php', $path, $model), $replacements);
        $this->generateFile('form', sprintf('%s/Schemas/%sForm.php', $path, $model), $replacements);
        $this->generateFile('infolist', sprintf('%s/Schemas/%sInfolist.php', $path, $model), $replacements);
        $this->generateFile('table', sprintf('%s/Tables/%sTable.php', $path, $modelPlural), $replacements);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function generateFile(string $stubName, string $targetPath, array $replacements): void
    {
        $stubPath = base_path(sprintf('stubs/starter-kit/resource/%s.stub', $stubName));

        if (! File::exists($stubPath)) {
            $this->error('Stub not found: '.$stubPath);

            return;
        }

        $content = File::get($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        File::put($targetPath, $content);
        $this->line('Generated: '.$targetPath);
    }
}
