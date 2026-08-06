<?php

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('serves all public pages', function () {
    $paths = ['/', '/kfz-gutachten', '/kfz-gutachten/unfallschadengutachten', '/so-funktionierts',
        '/fuer-gutachter', '/ueber-uns', '/kontakt', '/preise', '/faq', '/bewertungen',
        '/impressum', '/datenschutz', '/agb', '/cookie-richtlinie', '/demnaechst/transport',
        '/anfrage', '/registrieren/gutachter', '/gutachter/login', '/admin/login',
        '/sitemap.xml', '/robots.txt'];

    foreach ($paths as $path) {
        $this->get($path)->assertSuccessful();
    }
});

it('serves all customer pages', function () {
    $user = User::whereNotNull('email_verified_at')->first();
    $request = $user->requests()->first() ?? ServiceRequest::first();
    $user = $request->user;
    $booking = $user->bookings()->first();

    $this->actingAs($user);

    $this->get('/konto')->assertOk();
    $this->get('/konto/anfragen')->assertOk();
    $this->get('/konto/auftraege')->assertOk();
    $this->get('/konto/zahlungen')->assertOk();
    $this->get("/konto/anfragen/{$request->id}")->assertOk();
    $this->get("/konto/anfragen/{$request->id}/angebote")->assertOk();
    if ($booking) {
        $this->get("/konto/auftraege/{$booking->id}")->assertOk();
    }
});

it('serves all inspector pages', function () {
    $inspector = Inspector::has('matches')->first();
    $request = $inspector->matches()->first()->request;
    $booking = $inspector->bookings()->first();

    $this->actingAs($inspector, 'inspector');

    foreach (['/gutachter', '/gutachter/anfragen', '/gutachter/angebote', '/gutachter/auftraege',
        '/gutachter/servicegebiet', '/gutachter/profil'] as $p) {
        $this->get($p)->assertOk();
    }
    $this->get("/gutachter/anfragen/{$request->id}")->assertOk();
    if ($booking) {
        $this->get("/gutachter/auftraege/{$booking->id}")->assertOk();
    }
});

it('serves all admin pages', function () {
    $admin = Admin::first();
    $inspector = Inspector::first();
    $request = ServiceRequest::first();
    $booking = Booking::first();

    $this->actingAs($admin, 'admin');

    foreach (['/admin', '/admin/anfragen', '/admin/angebote', '/admin/auftraege', '/admin/zahlungen',
        '/admin/provisionen', '/admin/gutachter', '/admin/gutachter/import', '/admin/wallets',
        '/admin/auszahlungen', '/admin/kunden', '/admin/dienstleistungen', '/admin/einstellungen',
        '/admin/protokolle'] as $p) {
        $this->get($p)->assertOk();
    }
    $this->get("/admin/anfragen/{$request->id}")->assertOk();
    $this->get("/admin/gutachter/{$inspector->id}")->assertOk();
    if ($booking) {
        $this->get("/admin/auftraege/{$booking->id}")->assertOk();
    }
});
