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
        <p>{{ 'Der Kunde stellt eine kostenlose Anfrage. Passende Gutachter geben individuelle Angebote ab. Mit Annahme eines Angebots und erfolgreicher Online-Zahlung kommt ein verbindlicher Auftrag zustande.' }}</p>

        <h2>{{ '§ 4 Preise und Zahlung' }}</h2>
        <p>{{ 'Die Preise werden von den Gutachtern individuell festgelegt. Die Zahlung erfolgt online im Moment der Beauftragung über unseren Zahlungsdienstleister.' }}</p>

        <h2>{{ '§ 5 Provision' }}</h2>
        <p>{{ 'AngebotJetzt behält je abgeschlossener Buchung eine Provision in Höhe von 10 % des Auftragswerts ein. Der verbleibende Betrag steht dem Gutachter zu.' }}</p>

        <h2>{{ '§ 6 Auszahlung an Gutachter' }}</h2>
        <p>{{ 'Das Guthaben der Gutachter wird als Wallet-Guthaben auf der Plattform geführt. Auszahlungen erfolgen auf Anforderung des Gutachters manuell durch die Plattform.' }}</p>

        <h2>{{ '§ 7 Haftung' }}</h2>
        <p>{{ 'AngebotJetzt haftet nicht für die inhaltliche Richtigkeit der erstellten Gutachten. Diese liegt in der alleinigen Verantwortung des jeweiligen Gutachters.' }}</p>

        <h2>{{ '§ 8 Widerrufsrecht' }}</h2>
        <p>{{ 'Verbrauchern steht ein gesetzliches Widerrufsrecht zu. Die Einzelheiten ergeben sich aus der gesonderten Widerrufsbelehrung.' }}</p>

        <h2>{{ '§ 9 Schlussbestimmungen' }}</h2>
        <p>{{ 'Es gilt deutsches Recht. Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.' }}</p>
    </LegalPage>
</template>
