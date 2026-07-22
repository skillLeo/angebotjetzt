<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CommissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private array $cities = [
        'Berlin' => [10115, 14199], 'Hamburg' => [20095, 22769], 'München' => [80331, 81929],
        'Köln' => [50667, 51149], 'Frankfurt am Main' => [60306, 65936], 'Stuttgart' => [70173, 70629],
        'Düsseldorf' => [40210, 40629], 'Dortmund' => [44135, 44388], 'Leipzig' => [4103, 4357],
        'Bremen' => [28195, 28779], 'Hannover' => [30159, 30659], 'Nürnberg' => [90402, 90491],
        'Essen' => [45127, 45359], 'Dresden' => [1067, 1326], 'Bonn' => [53111, 53229],
    ];

    private array $inspectorNames = [
        ['Thomas Bergmann', 'KFZ-Sachverständigenbüro Bergmann'], ['Sabine Krüger', 'Gutachten Krüger GmbH'],
        ['Andreas Wolff', 'Wolff Fahrzeugbewertung'], ['Nadine Hoffmann', 'Hoffmann & Partner Kfz-Gutachter'],
        ['Kai Lehmann', 'Sachverständigenbüro Lehmann'], ['Miriam Stein', 'Stein Automotive Expertise'],
        ['Jörg Petersen', 'Petersen Kfz-Prüfdienst'], ['Christina Vogel', 'Vogel Gutachten'],
        ['Stefan Brandt', 'Brandt Fahrzeugexperten'], ['Julia Neumann', 'Neumann Schadensgutachten'],
        ['Martin Schulte', 'Schulte & Söhne Kfz-Bewertung'], ['Anja Richter', 'Richter Expertise'],
        ['Frank Zimmermann', 'Zimmermann Gutachterbüro'], ['Katrin Albrecht', 'Albrecht Kfz-Sachverstand'],
        ['Oliver Franke', 'Franke Fahrzeuganalyse'], ['Melanie Busch', 'Busch Gutachten Service'],
        ['Ralf Dietrich', 'Dietrich Kfz-Prüfstelle'], ['Simone Krause', 'Krause Sachverständige'],
        ['Peter Engel', 'Engel Automotive Gutachten'], ['Daniela Horn', 'Horn Kfz-Expertise'],
        ['Matthias Seidel', 'Seidel Schadenbewertung'], ['Verena Roth', 'Roth Fahrzeuggutachten'],
        ['Christian Baumann', 'Baumann Kfz-Sachverständige'], ['Heike Winter', 'Winter Gutachterdienst'],
        ['Tobias Arnold', 'Arnold Fahrzeugbewertung'],
    ];

    private array $vehicles = [
        ['Volkswagen', 'Golf VII'], ['BMW', '320d Touring'], ['Mercedes-Benz', 'C 220 d'],
        ['Audi', 'A4 Avant'], ['Opel', 'Astra K'], ['Ford', 'Focus Turnier'], ['Škoda', 'Octavia Combi'],
        ['Volkswagen', 'Passat B8'], ['BMW', 'X3 xDrive20d'], ['Mercedes-Benz', 'E 200'],
        ['Audi', 'Q5 40 TDI'], ['Seat', 'Leon FR'], ['Volkswagen', 'Tiguan'], ['Porsche', '911 Carrera'],
        ['Volvo', 'V60 T4'], ['Toyota', 'Corolla Hybrid'], ['Hyundai', 'i30 Kombi'], ['Renault', 'Mégane'],
        ['Fiat', '500X'], ['Mazda', 'CX-5'],
    ];

    public function run(): void
    {
        // Categories, service types, commission setting, admin account —
        // safe on production, run on its own via EssentialDataSeeder.
        $this->call(EssentialDataSeeder::class);

        // Everything below is fake demo data for local development only —
        // never run this on a live client database.
        $inspectors = $this->seedInspectors();
        $customers = $this->seedCustomers();
        $this->seedMarketplaceActivity($inspectors, $customers);
    }

    /** @return array<int, Inspector> */
    private function seedInspectors(): array
    {
        $cityNames = array_keys($this->cities);
        $inspectors = [];

        foreach ($this->inspectorNames as $i => [$name, $company]) {
            $city = $cityNames[$i % count($cityNames)];
            [$plzFrom, $plzTo] = $this->cities[$city];
            $email = 'gutachter'.($i + 1).'@angebotjetzt.de';

            $inspector = Inspector::updateOrCreate(['email' => $email], [
                'name' => $name,
                'company_name' => $company,
                'phone' => '+491'.str_pad((string) (520000000 + $i * 731), 9, '0'),
                'password' => Hash::make('Gutachter2026!'),
                'email_verified_at' => now(),
                'city' => $city,
                'bio' => "Öffentlich bestellter und vereidigter Kfz-Sachverständiger mit Schwerpunkt Unfallschäden und Fahrzeugbewertung im Raum {$city}.",
                'qualifications' => 'TÜV-zertifizierter Kfz-Sachverständiger, Mitglied im BVSK',
                'years_experience' => 5 + ($i % 20),
                'is_active' => true,
                'is_verified' => true,
                'member_since' => now()->subMonths(6 + $i * 2)->startOfMonth(),
                'imported_from' => 'carspector.de',
            ]);

            $inspector->serviceAreas()->delete();
            $inspector->serviceAreas()->create(['type' => 'city', 'city_name' => $city]);
            $inspector->serviceAreas()->create([
                'type' => 'postal_range',
                'postal_from' => max(1, $plzFrom - 500),
                'postal_to' => $plzTo + 500,
            ]);

            Wallet::firstOrCreate(['inspector_id' => $inspector->id]);
            $inspectors[] = $inspector;
        }

        return $inspectors;
    }

    /** @return array<int, User> */
    private function seedCustomers(): array
    {
        $names = [
            'Lena Fischer', 'Maximilian Weber', 'Sophie Wagner', 'Jan Becker', 'Laura Schäfer',
            'Felix Koch', 'Marie Bauer', 'Lukas Richter', 'Emma Klein', 'Niklas Wolf',
            'Hannah Schröder', 'Tim Neumann', 'Lea Schwarz', 'Jonas Zimmermann', 'Mia Braun',
            'David Hofmann', 'Anna Hartmann', 'Paul Lange', 'Clara Schmitt', 'Erik Werner',
        ];

        $customers = [];
        foreach ($names as $i => $name) {
            $customers[] = User::updateOrCreate(
                ['email' => 'kunde'.($i + 1).'@example.de'],
                [
                    'name' => $name,
                    'phone' => '+4915'.str_pad((string) (200000000 + $i * 317), 9, '0'),
                    'password' => Hash::make('Kunde2026!'),
                    'email_verified_at' => now(),
                    'agb_accepted' => true,
                    'privacy_accepted_at' => now(),
                ]
            );
        }

        return $customers;
    }

    private function seedMarketplaceActivity(array $inspectors, array $customers): void
    {
        if (ServiceRequest::count() > 0) {
            return;
        }

        $commission = new CommissionService();
        $types = ServiceType::orderBy('sort_order')->get();
        $cityNames = array_keys($this->cities);
        $fuels = ['Benzin', 'Diesel', 'Elektro', 'Hybrid'];
        $transmissions = ['Schaltgetriebe', 'Automatik'];
        $reviewTexts = [
            'Sehr professionelle Abwicklung. Das Gutachten war innerhalb von zwei Tagen fertig und die Versicherung hat alles anstandslos übernommen.',
            'Schnelle Terminvergabe, faire Preise und ein sehr ausführliches Gutachten. Gerne wieder.',
            'Kompetente Beratung am Telefon und vor Ort. Der Gutachter hat sich viel Zeit genommen.',
            'Dank des Gebrauchtwagenchecks habe ich vom Kauf abgesehen — ein versteckter Unfallschaden wurde entdeckt. Jeden Cent wert.',
            'Zuverlässig, pünktlich und gründlich. Die Kommunikation über die Plattform war unkompliziert.',
            'Das Wertgutachten für meinen Oldtimer war hervorragend dokumentiert. Absolute Empfehlung.',
            'Sehr zufrieden. Der Preisvergleich hat mir über 150 Euro gespart.',
            'Unkomplizierte Terminfindung, transparentes Angebot, sauberes Gutachten. Top.',
        ];

        DB::transaction(function () use ($commission, $types, $cityNames, $fuels, $transmissions, $reviewTexts, $inspectors, $customers) {
            $bookingIndex = 0;

            for ($r = 0; $r < 60; $r++) {
                $customer = $customers[$r % count($customers)];
                $type = $types[$r % $types->count()];
                $city = $cityNames[$r % count($cityNames)];
                [$plzFrom, $plzTo] = $this->cities[$city];
                [$make, $model] = $this->vehicles[$r % count($this->vehicles)];
                $createdAt = now()->subDays(90 - $r)->setTime(8 + ($r % 10), ($r * 13) % 60);

                $request = ServiceRequest::create([
                    'request_number' => sprintf('AJ-%d-%06d', $createdAt->year, $r + 1),
                    'user_id' => $customer->id,
                    'service_type_id' => $type->id,
                    'vehicle_make' => $make,
                    'vehicle_model' => $model,
                    'first_registration' => '0'.(1 + $r % 9).'/'.(2012 + ($r % 12)),
                    'mileage' => 25000 + $r * 3350,
                    'fuel_type' => $fuels[$r % 4],
                    'transmission' => $transmissions[$r % 2],
                    'plz' => str_pad((string) rand($plzFrom, $plzTo), 5, '0', STR_PAD_LEFT),
                    'ort' => $city,
                    'preferred_date' => $createdAt->copy()->addDays(5)->toDateString(),
                    'contact_name' => $customer->name,
                    'contact_email' => $customer->email,
                    'contact_phone' => $customer->phone,
                    'status' => 'open',
                    'expires_at' => $createdAt->copy()->addDays(14),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $cityInspectors = array_values(array_filter($inspectors, fn ($i) => $i->city === $city));
                $extra = array_slice($inspectors, ($r * 3) % 20, 2);
                $matched = collect(array_merge($cityInspectors, $extra))->unique('id')->values();

                foreach ($matched as $inspector) {
                    $request->matches()->create([
                        'inspector_id' => $inspector->id,
                        'notified_at' => $createdAt,
                        'viewed_at' => $r % 3 === 0 ? null : $createdAt->copy()->addHours(2),
                    ]);
                }
                $request->update(['matched_count' => $matched->count()]);

                $offerCount = min($matched->count(), $r < 12 ? 1 : ($r % 4 === 0 ? 4 : 2));
                $offers = [];
                foreach ($matched->take($offerCount)->values() as $o => $inspector) {
                    $price = 15000 + (($r * 37 + $o * 91) % 60) * 1000;
                    $split = $commission->split($price);
                    $offers[] = Offer::create([
                        'request_id' => $request->id,
                        'inspector_id' => $inspector->id,
                        'price_cents' => $price,
                        'commission_cents' => $split['commission'],
                        'inspector_cents' => $split['inspector'],
                        'message' => $o % 2 === 0 ? 'Gerne übernehme ich die Begutachtung. Ein Termin ist kurzfristig möglich, das Gutachten liegt in der Regel innerhalb von 48 Stunden vor.' : null,
                        'estimated_date' => $createdAt->copy()->addDays(4 + $o),
                        'status' => 'open',
                        'expires_at' => $createdAt->copy()->addDays(14),
                        'created_at' => $createdAt->copy()->addHours(3 + $o * 5),
                        'updated_at' => $createdAt->copy()->addHours(3 + $o * 5),
                    ]);
                }

                if (! empty($offers)) {
                    $request->update(['status' => 'offers_received']);
                }

                if ($r >= 12 && count($offers) > 0 && $bookingIndex < 40) {
                    $bookingIndex++;
                    $accepted = $offers[0];
                    $accepted->update(['status' => 'accepted']);
                    foreach (array_slice($offers, 1) as $rejected) {
                        $rejected->update(['status' => 'rejected']);
                    }

                    $paidAt = $createdAt->copy()->addDays(2);
                    $booking = Booking::create([
                        'booking_number' => sprintf('AJB-%d-%06d', $paidAt->year, $bookingIndex),
                        'request_id' => $request->id,
                        'offer_id' => $accepted->id,
                        'user_id' => $customer->id,
                        'inspector_id' => $accepted->inspector_id,
                        'status' => 'confirmed',
                        'completed_at' => $paidAt->copy()->addDays(4),
                        'confirmed_at' => $paidAt->copy()->addDays(5),
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt->copy()->addDays(5),
                    ]);

                    Payment::create([
                        'booking_id' => $booking->id,
                        'stripe_payment_intent_id' => 'pi_seed_'.str_pad((string) $r, 10, '0', STR_PAD_LEFT),
                        'total_cents' => $accepted->price_cents,
                        'commission_cents' => $accepted->commission_cents,
                        'inspector_cents' => $accepted->inspector_cents,
                        'status' => 'paid',
                        'paid_at' => $paidAt,
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);

                    $request->update(['status' => 'completed']);

                    $wallet = Wallet::where('inspector_id', $accepted->inspector_id)->first();
                    $balanceBefore = $wallet->available_cents;
                    $wallet->available_cents += $accepted->inspector_cents;
                    $wallet->lifetime_cents += $accepted->inspector_cents;
                    $wallet->save();
                    $wallet->transactions()->create([
                        'type' => 'credit_pending',
                        'amount_cents' => $accepted->inspector_cents,
                        'balance_after_cents' => $balanceBefore,
                        'source_type' => Booking::class,
                        'source_id' => $booking->id,
                        'description' => "Gutschrift (ausstehend) für Auftrag {$booking->booking_number}",
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ]);
                    $wallet->transactions()->create([
                        'type' => 'release',
                        'amount_cents' => $accepted->inspector_cents,
                        'balance_after_cents' => $wallet->available_cents,
                        'source_type' => Booking::class,
                        'source_id' => $booking->id,
                        'description' => "Freigabe für Auftrag {$booking->booking_number}",
                        'created_at' => $paidAt->copy()->addDays(5),
                        'updated_at' => $paidAt->copy()->addDays(5),
                    ]);

                    Review::create([
                        'booking_id' => $booking->id,
                        'user_id' => $customer->id,
                        'inspector_id' => $accepted->inspector_id,
                        'rating' => $r % 5 === 0 ? 4 : 5,
                        'comment' => $reviewTexts[$r % count($reviewTexts)],
                        'is_published' => true,
                        'created_at' => $paidAt->copy()->addDays(6),
                        'updated_at' => $paidAt->copy()->addDays(6),
                    ]);
                }
            }
        });
    }
}
