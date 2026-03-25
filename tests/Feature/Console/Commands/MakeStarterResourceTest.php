<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    // Pastikan folder bersih sebelum test
    File::deleteDirectory(app_path('Filament/Resources/TestModels'));
    File::deleteDirectory(app_path('Actions/TestModels'));
});

afterEach(function (): void {
    // Pastikan folder bersih setelah test
    File::deleteDirectory(app_path('Filament/Resources/TestModels'));
    File::deleteDirectory(app_path('Actions/TestModels'));
});

it('can generate starter resource files', function (): void {
    $model = 'TestModel';
    $path = app_path('Filament/Resources/TestModels');

    $this->artisan('make:starter-resource '.$model)
        ->assertExitCode(0);

    expect(File::exists($path.'/TestModelResource.php'))->toBeTrue();
    expect(File::exists($path.'/Pages/CreateTestModel.php'))->toBeTrue();
    expect(File::exists($path.'/Pages/EditTestModel.php'))->toBeTrue();
    expect(File::exists($path.'/Pages/ListTestModels.php'))->toBeTrue();
    expect(File::exists($path.'/Schemas/TestModelForm.php'))->toBeTrue();
    expect(File::exists($path.'/Tables/TestModelsTable.php'))->toBeTrue();
});

it('fails if resource already exists and not forced', function (): void {
    $model = 'TestModel';
    $path = app_path('Filament/Resources/TestModels');
    File::makeDirectory($path, 0755, true);

    $this->artisan('make:starter-resource '.$model)
        ->expectsOutput('Resource TestModels already exists!')
        ->assertExitCode(1);
});

it('can generate resource with soft deletes', function (): void {
    $model = 'TestModel';
    $path = app_path('Filament/Resources/TestModels');

    $this->artisan('make:starter-resource '.$model.' --soft-deletes')
        ->assertExitCode(0);

    $content = File::get($path.'/TestModelResource.php');
    expect($content)->toContain('SoftDeletingScope')
        ->and($content)->toContain('getEloquentQuery');
});

it('can generate resource with view option', function (): void {
    $model = 'TestModel';
    $path = app_path('Filament/Resources/TestModels');

    $this->artisan('make:starter-resource '.$model.' --view')
        ->assertExitCode(0);

    expect(File::exists($path.'/Pages/ViewTestModel.php'))->toBeTrue();
});

it('shows error if stub is missing', function () {
    // We temporarily move a stub to simulate missing stub
    $stubPath = base_path('stubs/starter-kit/resource/resource.stub');
    $backupPath = base_path('stubs/starter-kit/resource/resource.stub.bak');
    
    File::move($stubPath, $backupPath);
    
    try {
        $this->artisan('make:starter-resource MissingStubModel')
            ->expectsOutputToContain('Stub not found')
            ->assertExitCode(0);
    } finally {
        File::move($backupPath, $stubPath);
        File::deleteDirectory(app_path('Filament/Resources/MissingStubModels'));
        File::deleteDirectory(app_path('Actions/MissingStubModels'));
    }
});
