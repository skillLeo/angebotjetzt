<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';

</script>

<template>
    <LegalPage :title="'Datenschutzerklärung'" :updated="'Juli 2026'">
        <p>{{ 'Der Schutz Ihrer personenbezogenen Daten ist uns ein wichtiges Anliegen. Nachfolgend informieren wir Sie gemäß der Datenschutz-Grundverordnung (DSGVO) über die Verarbeitung Ihrer Daten auf AngebotJetzt.' }}</p>

        <h2>{{ '1. Verantwortlicher' }}</h2>
        <p>{{ 'AngebotJetzt GmbH, Musterstraße 1, 10115 Berlin, E-Mail: datenschutz@angebotjetzt.de.' }}</p>

        <h2>{{ '2. Welche Daten wir verarbeiten' }}</h2>
        <p>{{ 'Im Rahmen einer Anfrage erheben wir die von Ihnen angegebenen Fahrzeug-, Kontakt- und Standortdaten (Name, E-Mail, Telefon, PLZ, Ort). Diese Daten werden ausschließlich zur Vermittlung passender Dienstleister und zur Abwicklung Ihres Auftrags verwendet.' }}</p>

        <h2>{{ '3. Rechtsgrundlage' }}</h2>
        <p>{{ 'Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO (Vertragserfüllung) sowie Art. 6 Abs. 1 lit. a DSGVO (Einwilligung), soweit Sie eingewilligt haben.' }}</p>

        <h2>{{ '4. Weitergabe an Dienstleister' }}</h2>
        <p>{{ 'Zur Erbringung der Dienstleistung geben wir Ihre Anfrage an geprüfte Dienstleister in Ihrer Region weiter. Vollständige Kontaktdaten werden erst nach verbindlicher Beauftragung offengelegt.' }}</p>

        <h2>{{ '5. Zahlungsabwicklung' }}</h2>
        <p>{{ 'Die Zahlung für den beauftragten Gutachten-Auftrag erfolgt direkt zwischen Ihnen und dem beauftragten Dienstleister, außerhalb der Plattform. AngebotJetzt ist an dieser Zahlung nicht beteiligt und verarbeitet hierzu keine Zahlungsdaten. Für ihre Vermittlungsleistung stellt AngebotJetzt dem Dienstleister eine gesonderte Provisionsrechnung.' }}</p>

        <h2>{{ '6. Speicherdauer' }}</h2>
        <p>{{ 'Wir speichern Ihre Daten nur so lange, wie es für die genannten Zwecke erforderlich ist oder gesetzliche Aufbewahrungsfristen dies vorschreiben.' }}</p>

        <h2>{{ '7. Ihre Rechte' }}</h2>
        <p>{{ 'Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit sowie Widerspruch. Zudem können Sie sich bei einer Aufsichtsbehörde beschweren.' }}</p>

        <h2>{{ '8. Cookies' }}</h2>
        <p>
            {{ 'Details zur Verwendung von Cookies finden Sie in unserer' }}
            <a href="/cookie-policy">{{ 'Cookie-Richtlinie' }}</a>{{ '. Nicht notwendige Cookies werden nur mit Ihrer Einwilligung gesetzt.' }}
        </p>
    </LegalPage>
</template>
