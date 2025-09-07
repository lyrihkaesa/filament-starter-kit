<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

it('can run make:action command without errors', function () {
    $filesystem = new Filesystem();
    $file = app_path('Actions/TestAction.php');

    try {
        Artisan::call('make:action', [
            'name' => 'TestAction',
        ]);

        expect(Artisan::output())->toContain('Action');

        // Bisa tambahkan check file ada
        expect($filesystem->exists($file))->toBeTrue();
    } finally {
        // Cleanup: hapus file action yang dibuat
        if ($filesystem->exists($file)) {
            $filesystem->delete($file);
        }
    }
});

it('generates create, update, delete actions by default', function () {
    $filesystem = new Filesystem();
    $model = 'Post';
    $subFolder = 'Custom';

    // jalankan command
    Artisan::call('make:action', [
        '--model' => $model,
        '--sub-folder' => $subFolder,
        '--force' => true,
    ]);

    $expectedFiles = [
        app_path("Actions/{$subFolder}/Create{$model}Action.php"),
        app_path("Actions/{$subFolder}/Update{$model}Action.php"),
        app_path("Actions/{$subFolder}/Delete{$model}Action.php"),
    ];

    foreach ($expectedFiles as $file) {
        expect($filesystem->exists($file))->toBeTrue();
        // Cleanup setelah test
        $filesystem->delete($file);
    }
});

it('generates only create action if --create is passed', function () {
    $filesystem = new Filesystem();
    $model = 'Post';
    $file = app_path("Actions/Posts/Create{$model}Action.php");

    Artisan::call('make:action', [
        '--model' => $model,
        '--create' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('generates only update action if --update is passed', function () {
    $filesystem = new Filesystem();
    $model = 'Post';
    $file = app_path("Actions/Posts/Update{$model}Action.php");

    Artisan::call('make:action', [
        '--model' => $model,
        '--update' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('generates only delete action if --delete is passed', function () {
    $filesystem = new Filesystem();
    $model = 'Post';
    $file = app_path("Actions/Posts/Delete{$model}Action.php");

    Artisan::call('make:action', [
        '--model' => $model,
        '--delete' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('honors --force to overwrite existing files', function () {
    $filesystem = new Filesystem();
    $model = 'Post';
    $file = app_path("Actions/Posts/Create{$model}Action.php");

    $filesystem->ensureDirectoryExists(dirname($file));
    $filesystem->put($file, 'old content');

    Artisan::call('make:action', [
        '--model' => $model,
        '--create' => true,
        '--force' => true,
    ]);

    $content = $filesystem->get($file);
    expect($content)->not->toBe('old content');
    $filesystem->delete($file);
});
