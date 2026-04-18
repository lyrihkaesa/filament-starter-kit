<?php

declare(strict_types=1);

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Role::findOrCreate('super_admin');
    $this->user = User::factory()->create();
    $this->user->assignRole('super_admin');
    Gate::before(fn (): true => true);
});

it('can list activities', function (): void {
    Activity::query()->create([
        'description' => 'updated',
        'subject_type' => User::class,
        'subject_id' => $this->user->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
        'properties' => ['attributes' => ['name' => 'New Name'], 'old' => ['name' => 'Old Name']],
    ]);

    actingAs($this->user);

    livewire(ManageActivities::class)
        ->assertCanSeeTableRecords(Activity::all());
});

it('can view activity details', function (): void {
    $activity = Activity::query()->create([
        'description' => 'updated',
        'subject_type' => User::class,
        'subject_id' => $this->user->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
        'properties' => ['attributes' => ['name' => 'New Name'], 'old' => ['name' => 'Old Name']],
    ]);

    actingAs($this->user);

    livewire(ManageActivities::class)
        ->assertTableActionVisible('view', $activity);
});
