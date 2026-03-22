<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('can generate starter resource files', function () {
    $model = 'Category';
    $path = app_path('Filament/Resources/Categories');

    // Clean up if exists
    if (File::exists($path)) {
        File::deleteDirectory($path);
    }
    if (File::exists(app_path('Actions/Categories'))) {
        File::deleteDirectory(app_path('Actions/Categories'));
    }

    $this->artisan("make:starter-resource {$model}")
        ->assertExitCode(0);

    expect(File::exists("{$path}/CategoryResource.php"))->toBeTrue();
    expect(File::exists("{$path}/Pages/CreateCategory.php"))->toBeTrue();
    expect(File::exists("{$path}/Pages/EditCategory.php"))->toBeTrue();
    expect(File::exists("{$path}/Pages/ListCategories.php"))->toBeTrue();
    expect(File::exists("{$path}/Schemas/CategoryForm.php"))->toBeTrue();
    expect(File::exists("{$path}/Tables/CategoriesTable.php"))->toBeTrue();
    expect(File::exists(app_path('Actions/Categories/CreateCategoryAction.php')))->toBeTrue();

    // Clean up
    File::deleteDirectory($path);
    File::deleteDirectory(app_path('Actions/Categories'));
});
