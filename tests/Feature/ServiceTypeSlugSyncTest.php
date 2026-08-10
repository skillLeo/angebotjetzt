<?php

use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function slugTestCategory(): ServiceCategory
{
    return ServiceCategory::firstOrCreate(['slug' => 'kfz-gutachten'], ['name' => 'Kfz', 'is_active' => true]);
}

it('auto-syncs the slug when a service is renamed via the real admin endpoint, and 301-redirects the old URL', function () {
    $admin = makeChecklistAdmin();
    $type = ServiceType::create([
        'service_category_id' => slugTestCategory()->id, 'name' => 'Altes Gutachten',
        'slug' => 'altes-gutachten', 'description' => 'x', 'is_active' => true,
    ]);

    // The old URL works before the rename.
    $this->get('/vehicle-reports/altes-gutachten')->assertOk();

    $this->actingAs($admin, 'admin')
        ->post("/admin/service-types/{$type->id}", ['name' => 'Neues Gutachten', 'description' => 'x'])
        ->assertRedirect();

    $type->refresh();
    expect($type->slug)->toBe('neues-gutachten');
    expect(ServiceTypeRedirect::where('old_slug', 'altes-gutachten')->where('service_type_id', $type->id)->exists())->toBeTrue();

    // New URL works directly.
    $this->get('/vehicle-reports/neues-gutachten')->assertOk();

    // Old URL now 301-redirects to the new one instead of 404ing.
    $old = $this->get('/vehicle-reports/altes-gutachten');
    $old->assertStatus(301)->assertRedirect('/vehicle-reports/neues-gutachten');
});

it('chains redirects correctly through two renames of the same service', function () {
    $admin = makeChecklistAdmin();
    $type = ServiceType::create([
        'service_category_id' => slugTestCategory()->id, 'name' => 'Version Eins',
        'slug' => 'version-eins', 'description' => 'x', 'is_active' => true,
    ]);

    $this->actingAs($admin, 'admin')->post("/admin/service-types/{$type->id}", ['name' => 'Version Zwei', 'description' => 'x']);
    $this->actingAs($admin, 'admin')->post("/admin/service-types/{$type->id}", ['name' => 'Version Drei', 'description' => 'x']);

    $type->refresh();
    expect($type->slug)->toBe('version-drei');

    // Both historical URLs still redirect to the current one.
    $this->get('/vehicle-reports/version-eins')->assertRedirect('/vehicle-reports/version-drei');
    $this->get('/vehicle-reports/version-zwei')->assertRedirect('/vehicle-reports/version-drei');
    $this->get('/vehicle-reports/version-drei')->assertOk();
});

it('appends a distinguishing suffix when a rename would collide with another service\'s slug', function () {
    $admin = makeChecklistAdmin();
    ServiceType::create([
        'service_category_id' => slugTestCategory()->id, 'name' => 'Motorradgutachten',
        'slug' => 'motorradgutachten', 'description' => 'x', 'is_active' => true,
    ]);
    $second = ServiceType::create([
        'service_category_id' => slugTestCategory()->id, 'name' => 'Etwas Anderes',
        'slug' => 'etwas-anderes', 'description' => 'x', 'is_active' => true,
    ]);

    $this->actingAs($admin, 'admin')
        ->post("/admin/service-types/{$second->id}", ['name' => 'Motorradgutachten', 'description' => 'x'])
        ->assertRedirect();

    $second->refresh();
    expect($second->slug)->toBe('motorradgutachten-2');
});

it('404s a slug that never existed at all, unlike a retired one', function () {
    $this->get('/vehicle-reports/this-was-never-a-real-service')->assertNotFound();
});

it('does not touch the slug when a rename only changes the description, not the name', function () {
    $admin = makeChecklistAdmin();
    $type = ServiceType::create([
        'service_category_id' => slugTestCategory()->id, 'name' => 'Unverändert',
        'slug' => 'unveraendert', 'description' => 'alt', 'is_active' => true,
    ]);

    $this->actingAs($admin, 'admin')
        ->post("/admin/service-types/{$type->id}", ['name' => 'Unverändert', 'description' => 'neu']);

    expect($type->refresh()->slug)->toBe('unveraendert');
});
