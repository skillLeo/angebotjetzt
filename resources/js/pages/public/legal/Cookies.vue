<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';

</script>

<template>
    <LegalPage :title="'Cookie-Richtlinie'" :updated="'Juli 2026'">
        <p>{{ 'Diese Cookie-Richtlinie erklärt, welche Cookies AngebotJetzt verwendet und wie Sie Ihre Einwilligung verwalten können.' }}</p>

        <h2>{{ 'Was sind Cookies?' }}</h2>
        <p>{{ 'Cookies sind kleine Textdateien, die auf Ihrem Gerät gespeichert werden, um die Website funktionsfähig zu halten und die Nutzung zu verbessern.' }}</p>

        <h2>{{ 'Kategorien' }}</h2>
        <h3>{{ 'Notwendige Cookies' }}</h3>
        <p>{{ 'Diese Cookies sind für den Betrieb der Website erforderlich, etwa zur Sitzungsverwaltung und Sicherheit. Sie können nicht deaktiviert werden.' }}</p>
        <h3>{{ 'Statistik-Cookies' }}</h3>
        <p>{{ 'Helfen uns zu verstehen, wie Besucher die Website nutzen. Sie werden nur mit Ihrer Einwilligung gesetzt.' }}</p>
        <h3>{{ 'Marketing-Cookies' }}</h3>
        <p>{{ 'Werden verwendet, um Inhalte und Angebote relevanter zu gestalten. Sie werden nur mit Ihrer Einwilligung gesetzt.' }}</p>

        <h2>{{ 'Einwilligung verwalten' }}</h2>
        <p>{{ 'Beim ersten Besuch können Sie Ihre Auswahl im Cookie-Banner treffen. Sie können Ihre Einwilligung jederzeit widerrufen, indem Sie die gespeicherten Cookies in Ihrem Browser löschen.' }}</p>
    </LegalPage>
</template>
