<?php

declare(strict_types=1);

use App\Console\Commands\MakeActionCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Tester\CommandTester;

it('can run make:action command without errors', function (): void {
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

it('generates create, update, delete actions by default', function (): void {
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
        app_path(sprintf('Actions/%s/Create%sAction.php', $subFolder, $model)),
        app_path(sprintf('Actions/%s/Update%sAction.php', $subFolder, $model)),
        app_path(sprintf('Actions/%s/Delete%sAction.php', $subFolder, $model)),
    ];

    foreach ($expectedFiles as $file) {
        expect($filesystem->exists($file))->toBeTrue();
        // Cleanup setelah test
        $filesystem->delete($file);
    }
});

it('generates only create action if --create is passed', function (): void {
    $filesystem = new Filesystem();
    $model = 'DummyModel';
    $file = app_path(sprintf('Actions/DummyModels/Create%sAction.php', $model));

    Artisan::call('make:action', [
        '--model' => $model,
        '--create' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('generates only update action if --update is passed', function (): void {
    $filesystem = new Filesystem();
    $model = 'DummyModel';
    $file = app_path(sprintf('Actions/DummyModels/Update%sAction.php', $model));

    Artisan::call('make:action', [
        '--model' => $model,
        '--update' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('generates only delete action if --delete is passed', function (): void {
    $filesystem = new Filesystem();
    $model = 'DummyModel';
    $file = app_path(sprintf('Actions/DummyModels/Delete%sAction.php', $model));

    Artisan::call('make:action', [
        '--model' => $model,
        '--delete' => true,
        '--force' => true,
    ]);

    expect($filesystem->exists($file))->toBeTrue();
    $filesystem->delete($file);
});

it('honors --force to overwrite existing files', function (): void {
    $filesystem = new Filesystem();
    $model = 'DummyModel';
    $file = app_path(sprintf('Actions/DummyModels/Create%sAction.php', $model));

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

it('prompts for name when not provided and creates the file', function (): void {
    $filesystem = new Filesystem();
    $file = app_path('Actions/Posts/PublishPostAction.php');

    try {
        $this->artisan('make:action')
            ->expectsQuestion('What is the class name? (e.g., Posts/PublishPostAction)', 'Posts/PublishPostAction')
            ->assertExitCode(0);

        expect($filesystem->exists($file))->toBeTrue();
    } finally {
        if ($filesystem->exists($file)) {
            $filesystem->delete($file);
        }
    }
});

it('returns early when empty name is provided after prompt', function (): void {
    $this->artisan('make:action')
        ->expectsQuestion('What is the class name? (e.g., Posts/PublishPostAction)', '')
        ->assertExitCode(0);
});

it('asks to overwrite existing file and skips when answered No', function (): void {
    $filesystem = new Filesystem();
    $file = app_path('Actions/SkipAction.php');

    try {
        $filesystem->ensureDirectoryExists(dirname($file));
        $filesystem->put($file, 'old content');

        $command = new MakeActionCommand(resolve(Filesystem::class));
        $command->setLaravel(app());
        $tester = new CommandTester($command);
        $tester->setInputs(['no']);
        $exitCode = $tester->execute(['name' => 'SkipAction']);

        expect($exitCode)->toBe(0)
            ->and($tester->getDisplay())->toContain('Skipped')
            ->and($tester->getDisplay())->toContain('SkipAction.php');
    } finally {
        if ($filesystem->exists($file)) {
            $filesystem->delete($file);
        }
    }
});

it('asks to overwrite existing file and overwrites when answered Yes', function (): void {
    $filesystem = new Filesystem();
    $file = app_path('Actions/OverwriteAction.php');

    try {
        $filesystem->ensureDirectoryExists(dirname($file));
        $filesystem->put($file, 'old content');

        $command = new MakeActionCommand(resolve(Filesystem::class));
        $command->setLaravel(app());
        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $exitCode = $tester->execute(['name' => 'OverwriteAction']);

        expect($exitCode)->toBe(0)
            ->and($filesystem->get($file))->not()->toBe('old content');
    } finally {
        if ($filesystem->exists($file)) {
            $filesystem->delete($file);
        }
    }
});
