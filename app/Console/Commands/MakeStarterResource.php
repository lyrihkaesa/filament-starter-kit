<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeStarterResource extends Command
{
    protected $signature = 'make:starter-resource {model} {--view} {--soft-deletes} {--force}';

    protected $description = 'Generate a custom Filament resource with action pattern';

    public function handle(): int
    {
        $model = Str::studly($this->argument('model'));
        $modelPlural = Str::plural($model);
        $resourceName = "{$model}Resource";
        $namespace = "App\\Filament\\Resources\\{$modelPlural}";
        $path = app_path("Filament/Resources/{$modelPlural}");

        if (File::exists($path) && ! $this->option('force')) {
            $this->error("Resource {$modelPlural} already exists!");

            return 1;
        }

        $this->generateActions($model);
        $this->generateResourceFiles($model, $modelPlural, $namespace, $path);

        $this->info("Starter Resource for {$model} generated successfully!");

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
            '{{ modelFqn }}' => "App\\Models\\{$model}",
            '{{ resourceFqn }}' => "{$namespace}\\{$model}Resource",
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
        $this->generateFile('resource', "{$path}/{$model}Resource.php", $replacements);
        $this->generateFile('create', "{$path}/Pages/Create{$model}.php", $replacements);
        $this->generateFile('edit', "{$path}/Pages/Edit{$model}.php", $replacements);
        $this->generateFile('list', "{$path}/Pages/List{$modelPlural}.php", $replacements);
        $this->generateFile('view-page', "{$path}/Pages/View{$model}.php", $replacements);
        $this->generateFile('form', "{$path}/Schemas/{$model}Form.php", $replacements);
        $this->generateFile('infolist', "{$path}/Schemas/{$model}Infolist.php", $replacements);
        $this->generateFile('table', "{$path}/Tables/{$modelPlural}Table.php", $replacements);
    }

    private function generateFile(string $stubName, string $targetPath, array $replacements): void
    {
        $stubPath = base_path("stubs/starter-kit/resource/{$stubName}.stub");

        if (! File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");

            return;
        }

        $content = File::get($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        File::put($targetPath, $content);
        $this->line("Generated: {$targetPath}");
    }
}
