<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Sleep;

it('can edit profile name and email via browser', function (): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    // Pest Browser authentication
    $this->actingAs($user);

    $page = $this->visit('/app/profile');

    // Wait for Alpine.js to fully initialize Livewire components before typing
    Sleep::sleep(1);

    // Playwright API in Pest v4 Browser:
    $page->assertSee('Profile Information') // Section title
        ->clear('[id="form.name"]')
        ->typeSlowly('[id="form.name"]', 'New Name Pest', 10)
        ->clear('[id="form.email"]')
        ->typeSlowly('[id="form.email"]', 'new-email-pest@example.com', 10)
        ->click('form[wire\:submit="save"] button[type="submit"]');

    Sleep::sleep(2);

    // Refresh the page in the browser to ensure changes were persisted to the database
    $page = $this->visit('/app/profile');

    // Wait for load
    Sleep::sleep(1);

    // Verify the inputs were populated with the new data
    $page->assertValue('[id="form.name"]', 'New Name Pest');
    $page->assertValue('[id="form.email"]', 'new-email-pest@example.com');
});
