<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('lets admin upload a logo, applies it everywhere via the shared prop, and can reset to default', function () {
    Storage::fake('public');
    $admin = makeChecklistAdmin();

    $this->actingAs($admin, 'admin')->get('/admin/settings')->assertInertia(fn ($page) => $page
        ->where('settings.logoUrl', null));

    // Reject a disallowed format.
    $badFile = UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');
    $this->actingAs($admin, 'admin')->post('/admin/settings/logo', ['logo' => $badFile])
        ->assertSessionHasErrors('logo');

    // Reject an oversized file.
    $tooBig = UploadedFile::fake()->create('logo.png', 3000, 'image/png');
    $this->actingAs($admin, 'admin')->post('/admin/settings/logo', ['logo' => $tooBig])
        ->assertSessionHasErrors('logo');

    // A real, valid upload.
    $goodFile = UploadedFile::fake()->image('logo.png', 400, 100);
    $this->actingAs($admin, 'admin')->post('/admin/settings/logo', ['logo' => $goodFile])->assertRedirect();

    $path = Setting::get('logo_path');
    expect($path)->not->toBeEmpty();
    Storage::disk('public')->assertExists($path);

    $logoUrl = Setting::logoUrl();
    expect($logoUrl)->not->toBeNull()->toContain('/storage/');

    // The shared Inertia prop now carries it — checked on a totally
    // unrelated page to prove it's global, not just the settings page.
    $this->actingAs($admin, 'admin')->get('/admin')->assertInertia(fn ($page) => $page
        ->where('branding.logoUrl', $logoUrl));

    // Uploading a second logo removes the first file from disk.
    $secondFile = UploadedFile::fake()->image('logo2.png', 400, 100);
    $this->actingAs($admin, 'admin')->post('/admin/settings/logo', ['logo' => $secondFile])->assertRedirect();
    Storage::disk('public')->assertMissing($path);

    // Reset to default clears the setting and the shared prop.
    $this->actingAs($admin, 'admin')->delete('/admin/settings/logo')->assertRedirect();
    expect(Setting::logoUrl())->toBeNull();
    $this->actingAs($admin, 'admin')->get('/admin')->assertInertia(fn ($page) => $page
        ->where('branding.logoUrl', null));
});
