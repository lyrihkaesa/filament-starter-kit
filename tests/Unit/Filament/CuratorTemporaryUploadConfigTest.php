<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class);

it('uses a dedicated livewire temporary upload disk', function (): void {
    expect(Config::string('livewire.temporary_file_upload.disk'))->toBe('uploads_tmp');
});

it('keeps livewire temporary uploads on a different disk than curator by default', function (): void {
    expect(Config::string('livewire.temporary_file_upload.disk'))
        ->not->toBe(Config::string('curator.default_disk'));
});

it('stores livewire temporary uploads outside the final curator root directory', function (): void {
    $temporaryRoot = Config::string('filesystems.disks.'.Config::string('livewire.temporary_file_upload.disk').'.root');
    $curatorRoot = Config::string('filesystems.disks.'.Config::string('curator.default_disk').'.root');

    expect($temporaryRoot)->not->toBe($curatorRoot);
});
