<?php

namespace App\Support;

/**
 * The single source of truth for every editable piece of marketing copy.
 *
 * Each field carries the string that used to be hardcoded in the Vue
 * component as its default, so the site renders exactly as before until the
 * client actually edits something. The admin screens are generated from this
 * structure, and the seeder writes these defaults into site_contents so the
 * admin forms open with real content rather than blank fields.
 *
 * Keys are stable identifiers — renaming one orphans the client's edit, so
 * treat them as permanent once shipped.
 */
class SiteContentRegistry
{
    /**
     * Homepage sections, numbered to match the order they appear on the page.
     *
     * @return array<int, array{key: string, label: string, hint?: string, fields: array<int, array{key: string, label: string, type: string, hint?: string, default: string}>}>
     */
    public static function homeSections(): array
    {
        return [
            [
                'key' => 'hero',
                'label' => '1. Hero',
                'hint' => 'Der erste Bildschirm mit Überschrift und Anfrage-Leiste.',
                'fields' => [
                    ['key' => 'home.hero.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Anfragen. Vergleichen. Buchen.'],
                    ['key' => 'home.hero.headline_line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Der richtige Anbieter'],
                    ['key' => 'home.hero.headline_line2_prefix', 'label' => 'Überschrift, Zeile 2 (vor dem Kringel)', 'type' => 'text', 'default' => 'jetzt'],
                    ['key' => 'home.hero.headline_line2_highlight', 'label' => 'Überschrift, Zeile 2 (im Kringel)', 'type' => 'text', 'default' => 'vergleichen'],
                    ['key' => 'home.hero.subheadline', 'label' => 'Einleitungstext', 'type' => 'textarea', 'default' => 'Beschreiben Sie, was Sie brauchen. Geprüfte Anbieter aus Ihrer Region senden Ihnen individuelle Angebote – Sie vergleichen und beauftragen online. Kostenlos und unverbindlich.'],
                    ['key' => 'home.hero.category_placeholder', 'label' => 'Auswahlfeld: Kategorie', 'type' => 'text', 'default' => 'Welche Kategorie?'],
                    ['key' => 'home.hero.service_placeholder', 'label' => 'Auswahlfeld: Leistung', 'type' => 'text', 'default' => 'Welche Leistung?'],
                    ['key' => 'home.hero.location_placeholder', 'label' => 'Eingabefeld: PLZ oder Ort', 'type' => 'text', 'default' => 'PLZ oder Ort'],
                    ['key' => 'home.hero.cta', 'label' => 'Button-Beschriftung', 'type' => 'text', 'default' => 'Angebote erhalten'],
                    ['key' => 'home.hero.note', 'label' => 'Hinweis unter der Anfrage-Leiste', 'type' => 'text', 'default' => 'Bereits über 8.000 Aufträge in ganz Deutschland vermittelt.'],
                    ['key' => 'home.hero.chip_location', 'label' => 'Bild-Chip: Ort', 'type' => 'text', 'default' => 'Ort: Köln, 50667'],
                    ['key' => 'home.hero.chip_service', 'label' => 'Bild-Chip: Leistung', 'type' => 'text', 'default' => 'Leistung: Unfallschaden'],
                    ['key' => 'home.hero.chip_offers', 'label' => 'Bild-Chip: Angebote', 'type' => 'text', 'default' => '3 Angebote erhalten'],
                    ['key' => 'home.hero.chip_price_prefix', 'label' => 'Bild-Chip: Preis-Vorsatz', 'type' => 'text', 'default' => 'ab'],
                    ['key' => 'home.hero.chip_price', 'label' => 'Bild-Chip: Preis', 'type' => 'text', 'default' => "249\u{00A0}€"],
                ],
            ],
            [
                'key' => 'trust',
                'label' => '2. Vertrauens-Leiste',
                'hint' => 'Die drei Kacheln direkt unter dem Hero.',
                'fields' => [
                    ['key' => 'home.trust.rating', 'label' => 'Bewertungsnote', 'type' => 'text', 'default' => '4,9'],
                    ['key' => 'home.trust.reviews_suffix', 'label' => 'Text nach der Bewertungsanzahl', 'type' => 'text', 'hint' => 'Die Anzahl selbst wird automatisch aus den echten Bewertungen berechnet.', 'default' => '+ Bewertungen'],
                    ['key' => 'home.trust.badge2_title', 'label' => 'Kachel 2: Überschrift', 'type' => 'text', 'default' => '0 € für Ihre Anfrage'],
                    ['key' => 'home.trust.badge2_text', 'label' => 'Kachel 2: Unterzeile', 'type' => 'text', 'default' => 'Kostenlos & unverbindlich'],
                    ['key' => 'home.trust.badge3_title', 'label' => 'Kachel 3: Überschrift', 'type' => 'text', 'default' => 'Geprüfte Anbieter'],
                    ['key' => 'home.trust.badge3_text', 'label' => 'Kachel 3: Unterzeile', 'type' => 'text', 'default' => 'In ganz Deutschland'],
                ],
            ],
            [
                'key' => 'stats',
                'label' => '3. Zahlen-Band',
                'hint' => 'Der dunkle Streifen mit den vier Zählern. Die Zahlen stammen aus den echten Daten; der hinterlegte Wert wird angezeigt, solange die echten Daten darunter liegen.',
                'fields' => [
                    ['key' => 'home.stats.label1', 'label' => 'Zähler 1: Beschriftung', 'type' => 'text', 'default' => 'Aufträge vermittelt'],
                    ['key' => 'home.stats.min1', 'label' => 'Zähler 1: Mindestwert', 'type' => 'text', 'default' => '8000'],
                    ['key' => 'home.stats.label2', 'label' => 'Zähler 2: Beschriftung', 'type' => 'text', 'default' => 'Geprüfte Anbieter'],
                    ['key' => 'home.stats.min2', 'label' => 'Zähler 2: Mindestwert', 'type' => 'text', 'default' => '25'],
                    ['key' => 'home.stats.label3', 'label' => 'Zähler 3: Beschriftung', 'type' => 'text', 'default' => 'Ø Angebote pro Anfrage'],
                    ['key' => 'home.stats.min3', 'label' => 'Zähler 3: Ersatzwert', 'type' => 'text', 'default' => '3.2'],
                    ['key' => 'home.stats.label4', 'label' => 'Zähler 4: Beschriftung', 'type' => 'text', 'default' => 'Ø Antwortzeit'],
                    ['key' => 'home.stats.min4', 'label' => 'Zähler 4: Ersatzwert', 'type' => 'text', 'default' => '3'],
                    ['key' => 'home.stats.suffix4', 'label' => 'Zähler 4: Einheit', 'type' => 'text', 'hint' => 'Wird automatisch mit einem Abstand hinter die Zahl gesetzt.', 'default' => 'Std.'],
                ],
            ],
            [
                'key' => 'press',
                'label' => '4. Bekannt aus',
                'hint' => 'Die Presse-Leiste unter dem Anbieter-Aufruf.',
                'fields' => [
                    ['key' => 'home.press.label', 'label' => 'Beschriftung', 'type' => 'text', 'default' => 'Bekannt aus'],
                    ['key' => 'home.press.names', 'label' => 'Medien', 'type' => 'textarea', 'hint' => 'Mit Komma getrennt.', 'default' => 'Handelsblatt, Gründerszene, t3n, FOCUS, WirtschaftsWoche, Capital'],
                ],
            ],
            [
                'key' => 'how',
                'label' => '5. So funktioniert es',
                'hint' => 'Die drei Schritte neben dem Handy-Mockup.',
                'fields' => [
                    ['key' => 'home.how.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'In drei Schritten'],
                    ['key' => 'home.how.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'So funktioniert'],
                    ['key' => 'home.how.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'AngebotJetzt'],
                    ['key' => 'home.how.step1_title', 'label' => 'Schritt 1: Überschrift', 'type' => 'text', 'default' => 'Anfrage stellen'],
                    ['key' => 'home.how.step1_text', 'label' => 'Schritt 1: Text', 'type' => 'textarea', 'default' => 'Beschreiben Sie, welche Leistung Sie benötigen, und Ihre Region. In zwei Minuten erledigt – kostenlos und unverbindlich.'],
                    ['key' => 'home.how.step2_title', 'label' => 'Schritt 2: Überschrift', 'type' => 'text', 'default' => 'Angebote vergleichen'],
                    ['key' => 'home.how.step2_text', 'label' => 'Schritt 2: Text', 'type' => 'textarea', 'default' => 'Geprüfte Anbieter aus Ihrer Nähe senden Ihnen individuelle Preisangebote. Sie sehen alle Angebote übersichtlich nebeneinander.'],
                    ['key' => 'home.how.step3_title', 'label' => 'Schritt 3: Überschrift', 'type' => 'text', 'default' => 'Anbieter beauftragen'],
                    ['key' => 'home.how.step3_text', 'label' => 'Schritt 3: Text', 'type' => 'textarea', 'default' => 'Wählen Sie das beste Angebot und beauftragen Sie Ihren Anbieter verbindlich. Die Zahlung vereinbaren Sie anschließend direkt miteinander.'],
                ],
            ],
            [
                'key' => 'live',
                'label' => '6. Aktuelle Anfragen',
                'hint' => 'Überschrift über dem Karussell. Die Anfragen selbst kommen aus den echten Daten.',
                'fields' => [
                    ['key' => 'home.live.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Aktuelle Anfragen'],
                    ['key' => 'home.live.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Diese Anfragen suchen'],
                    ['key' => 'home.live.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'gerade den passenden Anbieter'],
                    ['key' => 'home.live.price_label', 'label' => 'Preis-Beschriftung auf der Karte', 'type' => 'text', 'default' => 'Angebote ab'],
                ],
            ],
            [
                'key' => 'map',
                'label' => '7. Deutschlandkarte',
                'hint' => 'Überschrift und Live-Aktivität. Die Kartenpunkte pflegen Sie unter „Kartenstandorte".',
                'fields' => [
                    ['key' => 'home.map.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Bundesweit'],
                    ['key' => 'home.map.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Geprüfte Anbieter'],
                    ['key' => 'home.map.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'in ganz Deutschland'],
                    ['key' => 'home.map.activity_title', 'label' => 'Überschrift der Aktivitätsliste', 'type' => 'text', 'default' => 'Live-Aktivität'],
                    ['key' => 'home.map.activity_prefix', 'label' => 'Text vor dem Ortsnamen', 'type' => 'text', 'default' => 'Neue Anfrage in'],
                ],
            ],
            [
                'key' => 'providers',
                'label' => '8. Unsere Anbieter',
                'hint' => 'Überschrift über dem Anbieter-Karussell. Die Partner pflegen Sie unter „Startseiten-Partner".',
                'fields' => [
                    ['key' => 'home.providers.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Unser Netzwerk'],
                    ['key' => 'home.providers.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Unsere geprüften'],
                    ['key' => 'home.providers.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'Anbieter'],
                ],
            ],
            [
                'key' => 'testimonials',
                'label' => '9. Kundenstimmen',
                'hint' => 'Überschrift über dem grünen Bewertungs-Band. Die Bewertungen pflegen Sie unter „Startseiten-Bewertungen".',
                'fields' => [
                    ['key' => 'home.testimonials.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Kundenstimmen'],
                    ['key' => 'home.testimonials.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Tausende zufriedene Kunden'],
                    ['key' => 'home.testimonials.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'vertrauen AngebotJetzt'],
                    ['key' => 'home.testimonials.badge', 'label' => 'Kennzeichnung auf der Karte', 'type' => 'text', 'default' => 'Positiv'],
                    ['key' => 'home.testimonials.photo_badge', 'label' => 'Kennzeichnung auf dem Foto', 'type' => 'text', 'default' => 'Bewertung abgegeben'],
                ],
            ],
            [
                'key' => 'categories',
                'label' => '10. Unsere Services',
                'hint' => 'Überschrift und Beschriftungen der Kategorie-Kacheln. Die Kategorien selbst pflegen Sie unter „Dienstleistungen".',
                'fields' => [
                    ['key' => 'home.categories.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Unsere Services'],
                    ['key' => 'home.categories.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Für jeden Bedarf'],
                    ['key' => 'home.categories.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'der passende Anbieter'],
                    ['key' => 'home.categories.cta', 'label' => 'Beschriftung auf aktiven Kacheln', 'type' => 'text', 'default' => 'Angebote erhalten'],
                    ['key' => 'home.categories.soon_badge', 'label' => 'Beschriftung auf geplanten Kacheln', 'type' => 'text', 'default' => 'Demnächst'],
                ],
            ],
            [
                'key' => 'recruitment',
                'label' => '11. Anbieter-Aufruf',
                'hint' => 'Der dunkle Block mit dem Aufruf an neue Anbieter.',
                'fields' => [
                    ['key' => 'home.recruitment.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Für Anbieter'],
                    ['key' => 'home.recruitment.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Mehr Aufträge.'],
                    ['key' => 'home.recruitment.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'Kein Papierkram.'],
                    ['key' => 'home.recruitment.body', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Erhalten Sie passende Anfragen aus Ihrem Servicegebiet automatisch per E-Mail. Sie bestimmen Ihre Preise selbst, verwalten Ihr Guthaben im Wallet und lassen sich auszahlen, wann immer Sie möchten.'],
                    ['key' => 'home.recruitment.cta_primary', 'label' => 'Button 1', 'type' => 'text', 'default' => 'Als Anbieter registrieren'],
                    ['key' => 'home.recruitment.cta_secondary', 'label' => 'Button 2', 'type' => 'text', 'default' => 'Mehr erfahren'],
                ],
            ],
            [
                'key' => 'faq',
                'label' => '12. Häufige Fragen',
                'hint' => 'Leere Frage oder Antwort blendet den Eintrag auf der Startseite aus.',
                'fields' => [
                    ['key' => 'home.faq.eyebrow', 'label' => 'Kleiner Text über der Überschrift', 'type' => 'text', 'default' => 'Häufige Fragen'],
                    ['key' => 'home.faq.line1', 'label' => 'Überschrift, Zeile 1', 'type' => 'text', 'default' => 'Alles, was Sie'],
                    ['key' => 'home.faq.line2', 'label' => 'Überschrift, Zeile 2 (grün)', 'type' => 'text', 'default' => 'wissen müssen'],
                    ['key' => 'home.faq.q1', 'label' => 'Frage 1', 'type' => 'text', 'default' => 'Was kostet mich eine Anfrage?'],
                    ['key' => 'home.faq.a1', 'label' => 'Antwort 1', 'type' => 'textarea', 'default' => 'Ihre Anfrage und der Angebotsvergleich sind für Sie als Kunde vollständig kostenlos und unverbindlich. Sie zahlen ausschließlich den Preis des Anbieters, den Sie beauftragen.'],
                    ['key' => 'home.faq.q2', 'label' => 'Frage 2', 'type' => 'text', 'default' => 'Wie schnell erhalte ich Angebote?'],
                    ['key' => 'home.faq.a2', 'label' => 'Antwort 2', 'type' => 'textarea', 'default' => 'In der Regel treffen die ersten Angebote innerhalb weniger Stunden ein. Da wir alle passenden Anbieter aus Ihrer Region automatisch benachrichtigen, erhalten Sie meist mehrere Angebote zum Vergleich.'],
                    ['key' => 'home.faq.q3', 'label' => 'Frage 3', 'type' => 'text', 'default' => 'Sind die Anbieter geprüft?'],
                    ['key' => 'home.faq.a3', 'label' => 'Antwort 3', 'type' => 'textarea', 'default' => 'Ja. Alle Anbieter auf AngebotJetzt durchlaufen eine Prüfung, bevor sie Anfragen erhalten. Viele stammen aus unserem etablierten Netzwerk und sind seit Jahren aktiv.'],
                    ['key' => 'home.faq.q4', 'label' => 'Frage 4', 'type' => 'text', 'default' => 'Wie funktioniert die Bezahlung?'],
                    ['key' => 'home.faq.a4', 'label' => 'Antwort 4', 'type' => 'textarea', 'default' => 'Sie bezahlen sicher online im Moment der Beauftragung – direkt über unsere verschlüsselte Zahlungsabwicklung. Kein Papierkram, keine Vorkasse per Überweisung.'],
                    ['key' => 'home.faq.q5', 'label' => 'Frage 5', 'type' => 'text', 'default' => 'Kann ich zwischen mehreren Angeboten wählen?'],
                    ['key' => 'home.faq.a5', 'label' => 'Antwort 5', 'type' => 'textarea', 'default' => 'Selbstverständlich. Sie sehen alle eingegangenen Angebote übersichtlich nebeneinander – mit Preis, Anbieterprofil und Bewertung – und entscheiden in Ruhe, wen Sie beauftragen.'],
                ],
            ],
        ];
    }

    /**
     * The four legal pages. Each has a plain-text title, an optional "Stand:"
     * line and a rich-text body stored as HTML.
     *
     * @return array<int, array{key: string, label: string, url: string, fields: array<int, array{key: string, label: string, type: string, default: string}>}>
     */
    public static function legalPages(): array
    {
        return [
            [
                'key' => 'imprint',
                'label' => 'Impressum',
                'url' => '/imprint',
                'fields' => [
                    ['key' => 'legal.imprint.title', 'label' => 'Seitentitel', 'type' => 'text', 'default' => 'Impressum'],
                    ['key' => 'legal.imprint.updated', 'label' => 'Stand (leer lassen, um die Zeile auszublenden)', 'type' => 'text', 'default' => ''],
                    ['key' => 'legal.imprint.body', 'label' => 'Inhalt', 'type' => 'html', 'default' => self::imprintBody()],
                ],
            ],
            [
                'key' => 'privacy',
                'label' => 'Datenschutzerklärung',
                'url' => '/privacy',
                'fields' => [
                    ['key' => 'legal.privacy.title', 'label' => 'Seitentitel', 'type' => 'text', 'default' => 'Datenschutzerklärung'],
                    ['key' => 'legal.privacy.updated', 'label' => 'Stand (leer lassen, um die Zeile auszublenden)', 'type' => 'text', 'default' => 'Juli 2026'],
                    ['key' => 'legal.privacy.body', 'label' => 'Inhalt', 'type' => 'html', 'default' => self::privacyBody()],
                ],
            ],
            [
                'key' => 'terms',
                'label' => 'Allgemeine Geschäftsbedingungen',
                'url' => '/terms',
                'fields' => [
                    ['key' => 'legal.terms.title', 'label' => 'Seitentitel', 'type' => 'text', 'default' => 'Allgemeine Geschäftsbedingungen'],
                    ['key' => 'legal.terms.updated', 'label' => 'Stand (leer lassen, um die Zeile auszublenden)', 'type' => 'text', 'default' => 'Juli 2026'],
                    ['key' => 'legal.terms.body', 'label' => 'Inhalt', 'type' => 'html', 'default' => self::termsBody()],
                ],
            ],
            [
                'key' => 'cookies',
                'label' => 'Cookie-Richtlinie',
                'url' => '/cookie-policy',
                'fields' => [
                    ['key' => 'legal.cookies.title', 'label' => 'Seitentitel', 'type' => 'text', 'default' => 'Cookie-Richtlinie'],
                    ['key' => 'legal.cookies.updated', 'label' => 'Stand (leer lassen, um die Zeile auszublenden)', 'type' => 'text', 'default' => 'Juli 2026'],
                    ['key' => 'legal.cookies.body', 'label' => 'Inhalt', 'type' => 'html', 'default' => self::cookiesBody()],
                ],
            ],
        ];
    }

    /** @var array<string, string>|null */
    private static ?array $defaults = null;

    /** @var array<string, string>|null */
    private static ?array $groups = null;

    /**
     * Every key mapped to its default value, across both groups.
     *
     * Memoised: the homepage resolves ~80 keys per request, and rebuilding
     * this whole structure for each one is pure waste.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        if (self::$defaults !== null) {
            return self::$defaults;
        }

        $defaults = [];

        foreach (self::allFields() as $field) {
            $defaults[$field['key']] = $field['default'];
        }

        return self::$defaults = $defaults;
    }

    /**
     * Every key mapped to the group it is stored under.
     *
     * @return array<string, string>
     */
    public static function groups(): array
    {
        if (self::$groups !== null) {
            return self::$groups;
        }

        $groups = [];

        foreach (self::homeSections() as $section) {
            foreach ($section['fields'] as $field) {
                $groups[$field['key']] = 'home';
            }
        }

        foreach (self::legalPages() as $page) {
            foreach ($page['fields'] as $field) {
                $groups[$field['key']] = 'legal';
            }
        }

        return self::$groups = $groups;
    }

    /**
     * Flat list of every field definition.
     *
     * @return array<int, array{key: string, label: string, type: string, hint?: string, default: string}>
     */
    public static function allFields(): array
    {
        $fields = [];

        foreach (self::homeSections() as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = $field;
            }
        }

        foreach (self::legalPages() as $page) {
            foreach ($page['fields'] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private static function imprintBody(): string
    {
        return <<<'HTML'
            <h2>Angaben gemäß § 5 TMG</h2>
            <p>AngebotJetzt GmbH<br>Musterstraße 1<br>10115 Berlin<br>Deutschland</p>
            <h2>Vertreten durch</h2>
            <p>Geschäftsführer: Max Beispielmann</p>
            <h2>Kontakt</h2>
            <p>Telefon: +49 30 1234567<br>E-Mail: kontakt@angebotjetzt.de</p>
            <h2>Registereintrag</h2>
            <p>Eintragung im Handelsregister.<br>Registergericht: Amtsgericht Berlin-Charlottenburg<br>Registernummer: HRB 000000</p>
            <h2>Umsatzsteuer-ID</h2>
            <p>Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>DE000000000</p>
            <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
            <p>Max Beispielmann, Anschrift wie oben.</p>
            <h2>Streitschlichtung</h2>
            <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr</a>. Wir sind nicht verpflichtet und nicht bereit, an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
            HTML;
    }

    private static function privacyBody(): string
    {
        return <<<'HTML'
            <p>Der Schutz Ihrer personenbezogenen Daten ist uns ein wichtiges Anliegen. Nachfolgend informieren wir Sie gemäß der Datenschutz-Grundverordnung (DSGVO) über die Verarbeitung Ihrer Daten auf AngebotJetzt.</p>
            <h2>1. Verantwortlicher</h2>
            <p>AngebotJetzt GmbH, Musterstraße 1, 10115 Berlin, E-Mail: datenschutz@angebotjetzt.de.</p>
            <h2>2. Welche Daten wir verarbeiten</h2>
            <p>Im Rahmen einer Anfrage erheben wir die von Ihnen angegebenen Fahrzeug-, Kontakt- und Standortdaten (Name, E-Mail, Telefon, PLZ, Ort). Diese Daten werden ausschließlich zur Vermittlung passender Dienstleister und zur Abwicklung Ihres Auftrags verwendet.</p>
            <h2>3. Rechtsgrundlage</h2>
            <p>Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO (Vertragserfüllung) sowie Art. 6 Abs. 1 lit. a DSGVO (Einwilligung), soweit Sie eingewilligt haben.</p>
            <h2>4. Weitergabe an Dienstleister</h2>
            <p>Zur Erbringung der Dienstleistung geben wir Ihre Anfrage an geprüfte Dienstleister in Ihrer Region weiter. Vollständige Kontaktdaten werden erst nach verbindlicher Beauftragung offengelegt.</p>
            <h2>5. Zahlungsabwicklung</h2>
            <p>Die Zahlung für den beauftragten Gutachten-Auftrag erfolgt direkt zwischen Ihnen und dem beauftragten Dienstleister, außerhalb der Plattform. AngebotJetzt ist an dieser Zahlung nicht beteiligt und verarbeitet hierzu keine Zahlungsdaten. Für ihre Vermittlungsleistung stellt AngebotJetzt dem Dienstleister eine gesonderte Provisionsrechnung.</p>
            <h2>6. Speicherdauer</h2>
            <p>Wir speichern Ihre Daten nur so lange, wie es für die genannten Zwecke erforderlich ist oder gesetzliche Aufbewahrungsfristen dies vorschreiben.</p>
            <h2>7. Ihre Rechte</h2>
            <p>Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit sowie Widerspruch. Zudem können Sie sich bei einer Aufsichtsbehörde beschweren.</p>
            <h2>8. Cookies</h2>
            <p>Details zur Verwendung von Cookies finden Sie in unserer <a href="/cookie-policy">Cookie-Richtlinie</a>. Nicht notwendige Cookies werden nur mit Ihrer Einwilligung gesetzt.</p>
            HTML;
    }

    private static function termsBody(): string
    {
        return <<<'HTML'
            <h2>§ 1 Geltungsbereich</h2>
            <p>Diese Allgemeinen Geschäftsbedingungen gelten für die Nutzung der Vermittlungsplattform AngebotJetzt durch Kunden und Dienstleister (Dienstleister).</p>
            <h2>§ 2 Vertragsgegenstand</h2>
            <p>AngebotJetzt vermittelt zwischen Kunden, die ein Kfz-Gutachten benötigen, und unabhängigen Dienstleistern. Der Dienstleistervertrag kommt unmittelbar zwischen Kunde und Dienstleister zustande.</p>
            <h2>§ 3 Ablauf der Vermittlung</h2>
            <p>Der Kunde stellt eine kostenlose Anfrage. Passende Dienstleister geben individuelle Angebote ab. Mit der ausdrücklich bestätigten Annahme eines Angebots kommt ein verbindlicher Auftrag unmittelbar zwischen Kunde und Dienstleister zustande.</p>
            <h2>§ 4 Preise und Zahlung</h2>
            <p>Die Preise werden von den Dienstleistern individuell festgelegt. Die Zahlung für den Auftrag erfolgt direkt zwischen Kunde und Dienstleister und wird außerhalb der Plattform abgewickelt. AngebotJetzt ist an dieser Zahlung nicht beteiligt.</p>
            <h2>§ 5 Provision</h2>
            <p>Für die erfolgreiche Vermittlung stellt AngebotJetzt dem Dienstleister eine Provision in Höhe von 10 % des Auftragswerts in Rechnung. Der Auftragswert selbst steht in voller Höhe dem Dienstleister zu.</p>
            <h2>§ 6 Rechnungsstellung</h2>
            <p>Mit der Annahme eines Angebots erhält der Dienstleister automatisch eine Provisionsrechnung mit Fälligkeitsdatum per E-Mail sowie zum Abruf im Dienstleister-Dashboard.</p>
            <h2>§ 7 Haftung</h2>
            <p>AngebotJetzt haftet nicht für die inhaltliche Richtigkeit der erstellten Gutachten. Diese liegt in der alleinigen Verantwortung des jeweiligen Dienstleisters.</p>
            <h2>§ 8 Widerrufsrecht</h2>
            <p>Verbrauchern steht ein gesetzliches Widerrufsrecht zu. Die Einzelheiten ergeben sich aus der gesonderten Widerrufsbelehrung.</p>
            <h2>§ 9 Schlussbestimmungen</h2>
            <p>Es gilt deutsches Recht. Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.</p>
            HTML;
    }

    private static function cookiesBody(): string
    {
        return <<<'HTML'
            <p>Diese Cookie-Richtlinie erklärt, welche Cookies AngebotJetzt verwendet und wie Sie Ihre Einwilligung verwalten können.</p>
            <h2>Was sind Cookies?</h2>
            <p>Cookies sind kleine Textdateien, die auf Ihrem Gerät gespeichert werden, um die Website funktionsfähig zu halten und die Nutzung zu verbessern.</p>
            <h2>Kategorien</h2>
            <h3>Notwendige Cookies</h3>
            <p>Diese Cookies sind für den Betrieb der Website erforderlich, etwa zur Sitzungsverwaltung und Sicherheit. Sie können nicht deaktiviert werden.</p>
            <h3>Statistik-Cookies</h3>
            <p>Helfen uns zu verstehen, wie Besucher die Website nutzen. Sie werden nur mit Ihrer Einwilligung gesetzt.</p>
            <h3>Marketing-Cookies</h3>
            <p>Werden verwendet, um Inhalte und Angebote relevanter zu gestalten. Sie werden nur mit Ihrer Einwilligung gesetzt.</p>
            <h2>Einwilligung verwalten</h2>
            <p>Beim ersten Besuch können Sie Ihre Auswahl im Cookie-Banner treffen. Sie können Ihre Einwilligung jederzeit widerrufen, indem Sie die gespeicherten Cookies in Ihrem Browser löschen.</p>
            HTML;
    }
}
