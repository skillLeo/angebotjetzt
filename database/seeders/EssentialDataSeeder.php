<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Structural reference data the application cannot function without:
 * the 7 homepage categories, the 7 Kfz-Gutachten service types, the
 * commission setting, and one admin account.
 *
 * This is safe to run on a live production database — it contains no
 * demo/fake customers, inspectors, or bookings. Run it once after the
 * first deploy:
 *
 *   php artisan db:seed --class="Database\Seeders\EssentialDataSeeder" --force
 */
class EssentialDataSeeder extends Seeder
{
    /**
     * Creates the admin account only when none exists yet.
     *
     * Deliberately never touches an existing admin's password: this seeder is
     * documented as safe to re-run on a live database, and an update here would
     * silently reset a password the client had changed in the panel.
     *
     * No password is stored in this file. On a genuinely fresh install the
     * first one comes from ADMIN_INITIAL_PASSWORD, or is generated at random
     * and printed once, so nothing secret ever lives in the repository.
     */
    private function ensureAdminExists(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@angebotjetzt.de');

        if (Admin::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists — password left untouched.");

            return;
        }

        $password = env('ADMIN_INITIAL_PASSWORD') ?: Str::password(20);

        Admin::create([
            'email' => $email,
            'name' => 'Plattform-Administrator',
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->command?->warn("Admin account created: {$email}");
        $this->command?->warn("Initial password: {$password}");
        $this->command?->warn('Change it after the first login. It is not stored anywhere else.');
    }

    public function run(): void
    {
        Setting::set('commission_percent', 10);

        $this->ensureAdminExists();

        $categories = [
            ['Kfz-Gutachten', 'kfz-gutachten', 'car', 'Unabhängige Kfz-Sachverständige für Gutachten und Bewertungen.', true, 1],
            ['Transporte', 'transporte', 'truck', 'Umzüge, Lieferungen und Transporte aller Art.', false, 2],
            ['Handwerk', 'handwerk', 'hammer', 'Qualifizierte Handwerker für jedes Projekt.', false, 3],
            ['Umzug & Entrümpelungen', 'umzug-entruempelungen', 'package', 'Umzüge, Haushaltsauflösungen und Entrümpelungen.', false, 4],
            ['Reinigung', 'reinigung', 'sparkles', 'Professionelle Reinigungsdienste.', false, 5],
            ['Garten & Außenbereich', 'garten-aussenbereich', 'flower-2', 'Gartenpflege und Gestaltung des Außenbereichs.', false, 6],
            ['IT & Digital', 'it-digital', 'monitor', 'IT-Dienstleistungen und digitale Lösungen.', false, 7],
        ];

        foreach ($categories as [$name, $slug, $icon, $desc, $active, $order]) {
            ServiceCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'description' => $desc,
                'is_active' => $active, 'sort_order' => $order,
            ]);
        }

        $auto = ServiceCategory::where('slug', 'kfz-gutachten')->first();

        $types = [
            ['Unfallschadengutachten', 'unfallschadengutachten', 'Umfassende Begutachtung und Dokumentation von Fahrzeugschäden nach einem Unfall für Versicherungsansprüche und rechtliche Zwecke.'],
            ['Fahrzeugbewertung', 'fahrzeugbewertung', 'Ermittlung des aktuellen Marktwerts von PKW, Motorrädern, Nutzfahrzeugen und Oldtimern.'],
            ['Gebrauchtwagencheck', 'gebrauchtwagencheck', 'Unabhängige Prüfung von Gebrauchtwagen vor dem Kauf zur Aufdeckung versteckter Mängel und Bewertung des Gesamtzustands.'],
            ['Reparaturkosten- und Totalschadengutachten', 'reparaturkosten-totalschaden', 'Berechnung der Reparaturkosten und Bewertung, ob ein Fahrzeug reparabel oder ein wirtschaftlicher Totalschaden ist.'],
            ['Wertminderungs- und Restwertgutachten', 'wertminderung-restwert', 'Bewertung des Wertverlusts eines Fahrzeugs nach einem Unfall und Ermittlung des verbleibenden Werts.'],
            ['Versicherungs- und Rechtsgutachten', 'versicherung-rechtsgutachten', 'Unabhängige Sachverständigengutachten für Versicherungen, Gerichte, Anwälte und Streitbeilegung.'],
            ['Spezialgutachten', 'spezialgutachten', 'Zustandsbewertungen für Leasingrückgaben, Elektrofahrzeuge, Flotten, Oldtimer sowie Hagel-, Wasser-, Brand- und Vandalismusschäden.'],
        ];

        // The flow-mode migration assigns these by slug, but on a fresh install
        // it runs before this seeder exists any rows to update — so a brand new
        // database would otherwise come up with both special services on the
        // default offer flow. Set them here as well, and only for rows we just
        // created, so re-seeding never overrides a mode an admin has changed.
        $flowModes = [
            'unfallschadengutachten' => ['direct_accept', null],
            'gebrauchtwagencheck'    => ['external', config('partners.carspector_url')],
        ];

        foreach ($types as $i => [$name, $slug, $desc]) {
            $type = ServiceType::updateOrCreate(['slug' => $slug], [
                'service_category_id' => $auto->id, 'name' => $name,
                'description' => $desc, 'sort_order' => $i + 1, 'is_active' => true,
            ]);

            if ($type->wasRecentlyCreated && isset($flowModes[$slug])) {
                [$mode, $url] = $flowModes[$slug];
                $type->update(['flow_mode' => $mode, 'external_url' => $url]);
            }
        }
    }
}
