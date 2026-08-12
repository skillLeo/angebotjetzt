<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <meta name="theme-color" content="#ffffff">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <meta name="description" content="AngebotJetzt: Geprüfte Anbieter aus Ihrer Region senden Ihnen individuelle Angebote. Vergleichen und online beauftragen – kostenlos und unverbindlich.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="AngebotJetzt">
        <meta property="og:title" content="AngebotJetzt – Angebote vergleichen und Anbieter beauftragen">
        <meta property="og:description" content="Geprüfte Anbieter aus Ihrer Region. Angebote vergleichen und online beauftragen.">
        <meta property="og:locale" content="de_DE">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="AngebotJetzt – Angebote vergleichen">
        <meta name="twitter:description" content="Geprüfte Anbieter aus Ihrer Region. Angebote vergleichen und online beauftragen.">

        @php
            $jsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => 'AngebotJetzt',
                'description' => 'Marktplatz für Dienstleistungen: geprüfte Anbieter aus ganz Deutschland.',
                'url' => rtrim(config('app.url'), '/'),
                'areaServed' => 'DE',
                'priceRange' => '€€',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'Musterstraße 1',
                    'postalCode' => '10115',
                    'addressLocality' => 'Berlin',
                    'addressCountry' => 'DE',
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

        {{-- Satoshi and Cabinet Grotesk are bundled with our own assets now
             (resources/css/fonts.css), so there is no third-party request to
             wait on and no flash of system text on a first visit. --}}
        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'AngebotJetzt') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
