<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Structural reference data the application cannot function without:
 * the 10 homepage categories, the 7 Kfz-Gutachten service types, the
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
    public function run(): void
    {
        Setting::set('commission_percent', 10);

        Admin::updateOrCreate(
            ['email' => 'admin@angebotjetzt.de'],
            ['name' => 'Plattform-Administrator', 'password' => Hash::make('AdminSecure2026!'), 'email_verified_at' => now()]
        );

        $categories = [
            ['Transport', 'transport', 'truck', 'Umzüge, Lieferungen und Transporte aller Art.', false, 1],
            ['Kfz-Gutachten', 'kfz-gutachten', 'car', 'Unabhängige Kfz-Sachverständige für Gutachten und Bewertungen.', true, 2],
            ['Handwerk', 'handwerk', 'hammer', 'Qualifizierte Handwerker für jedes Projekt.', false, 3],
            ['Haus & Garten', 'haus-garten', 'flower-2', 'Gartenpflege, Hausmeisterservice und mehr.', false, 4],
            ['Reinigung', 'reinigung', 'sparkles', 'Professionelle Reinigungsdienste.', false, 5],
            ['Bau & Renovierung', 'bau-renovierung', 'paintbrush', 'Bauprojekte und Renovierungen.', false, 6],
            ['Prüfung & Beratung', 'pruefung-beratung', 'clipboard-check', 'Sachverständige und Berater.', false, 7],
            ['IT & Digital', 'it-digital', 'monitor', 'IT-Dienstleistungen und digitale Lösungen.', false, 8],
            ['Events & Kreativ', 'events-kreativ', 'party-popper', 'Eventplanung und kreative Dienstleistungen.', false, 9],
            ['Sicherheit', 'sicherheit', 'shield', 'Sicherheitsdienste und -technik.', false, 10],
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

        foreach ($types as $i => [$name, $slug, $desc]) {
            ServiceType::updateOrCreate(['slug' => $slug], [
                'service_category_id' => $auto->id, 'name' => $name,
                'description' => $desc, 'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }
    }
}
