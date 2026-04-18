<?php

declare(strict_types=1);

use App\Filament\Resources\Activities\ActivityResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Role::findOrCreate('super_admin');
    $this->user = User::factory()->create();
    $this->user->assignRole('super_admin');
    Gate::before(fn () => true);
});

it('can list activities', function () {
    Activity::create([
        'description' => 'updated',
        'subject_type' => User::class,
        'subject_id' => $this->user->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
        'properties' => ['attributes' => ['name' => 'New Name'], 'old' => ['name' => 'Old Name']],
    ]);

    actingAs($this->user);

    livewire(App\Filament\Resources\Activities\Pages\ManageActivities::class)
        ->assertCanSeeTableRecords(Activity::all());
});

it('can view activity details', function () {
    $activity = Activity::create([
        'description' => 'updated',
        'subject_type' => User::class,
        'subject_id' => $this->user->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
        'properties' => ['attributes' => ['name' => 'New Name'], 'old' => ['name' => 'Old Name']],
    ]);

    actingAs($this->user);

    livewire(App\Filament\Resources\Activities\Pages\ManageActivities::class)
        ->assertTableActionVisible('view', $activity);
});
