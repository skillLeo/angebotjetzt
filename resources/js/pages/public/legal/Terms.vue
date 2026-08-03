<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';

</script>

<template>
    <LegalPage :title="'Allgemeine Geschäftsbedingungen'" :updated="'Juli 2026'">
        <h2>{{ '§ 1 Geltungsbereich' }}</h2>
        <p>{{ 'Diese Allgemeinen Geschäftsbedingungen gelten für die Nutzung der Vermittlungsplattform AngebotJetzt durch Kunden und Gutachter (Dienstleister).' }}</p>

        <h2>{{ '§ 2 Vertragsgegenstand' }}</h2>
        <p>{{ 'AngebotJetzt vermittelt zwischen Kunden, die ein Kfz-Gutachten benötigen, und unabhängigen Gutachtern. Der Gutachtervertrag kommt unmittelbar zwischen Kunde und Gutachter zustande.' }}</p>

        <h2>{{ '§ 3 Ablauf der Vermittlung' }}</h2>
        <p>{{ 'Der Kunde stellt eine kostenlose Anfrage. Passende Gutachter geben individuelle Angebote ab. Mit der ausdrücklich bestätigten Annahme eines Angebots kommt ein verbindlicher Auftrag unmittelbar zwischen Kunde und Gutachter zustande.' }}</p>

        <h2>{{ '§ 4 Preise und Zahlung' }}</h2>
        <p>{{ 'Die Preise werden von den Gutachtern individuell festgelegt. Die Zahlung für den Auftrag erfolgt direkt zwischen Kunde und Gutachter und wird außerhalb der Plattform abgewickelt. AngebotJetzt ist an dieser Zahlung nicht beteiligt.' }}</p>

        <h2>{{ '§ 5 Provision' }}</h2>
        <p>{{ 'Für die erfolgreiche Vermittlung stellt AngebotJetzt dem Gutachter eine Provision in Höhe von 10 % des Auftragswerts in Rechnung. Der Auftragswert selbst steht in voller Höhe dem Gutachter zu.' }}</p>

        <h2>{{ '§ 6 Rechnungsstellung' }}</h2>
        <p>{{ 'Mit der Annahme eines Angebots erhält der Gutachter automatisch eine Provisionsrechnung mit Fälligkeitsdatum per E-Mail sowie zum Abruf im Gutachter-Dashboard.' }}</p>

        <h2>{{ '§ 7 Haftung' }}</h2>
        <p>{{ 'AngebotJetzt haftet nicht für die inhaltliche Richtigkeit der erstellten Gutachten. Diese liegt in der alleinigen Verantwortung des jeweiligen Gutachters.' }}</p>

        <h2>{{ '§ 8 Widerrufsrecht' }}</h2>
        <p>{{ 'Verbrauchern steht ein gesetzliches Widerrufsrecht zu. Die Einzelheiten ergeben sich aus der gesonderten Widerrufsbelehrung.' }}</p>

        <h2>{{ '§ 9 Schlussbestimmungen' }}</h2>
        <p>{{ 'Es gilt deutsches Recht. Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.' }}</p>
    </LegalPage>
</template>
