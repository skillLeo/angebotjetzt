<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Fehler' }} – AngebotJetzt</title>
        <link rel="icon" href="/favicon.ico" sizes="any">

        <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
        <link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
        <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@700,800,900&f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css'])
    </head>
    <body class="relative min-h-screen overflow-hidden bg-sand-50 font-sans antialiased">
        <div class="pointer-events-none absolute -top-32 -left-24 h-96 w-96 rounded-full bg-navy-100/60 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-24 -bottom-32 h-96 w-96 rounded-full bg-green-100/60 blur-3xl" aria-hidden="true"></div>

        <div class="relative flex min-h-screen flex-col items-center justify-center px-6 py-16 text-center">
            <a href="/" class="mb-10 inline-flex">
                <span class="inline-flex items-baseline gap-1.5 font-display text-2xl font-extrabold tracking-tight md:text-[28px]">
                    <span class="text-navy-700">Angebot</span><span class="text-green-500">Jetzt</span>
                </span>
            </a>

            <p class="font-display text-[88px] leading-none font-extrabold text-navy-700 sm:text-[120px]">{{ $code }}</p>

            <h1 class="mt-4 font-display text-2xl font-bold text-navy-700 sm:text-3xl">{{ $heading }}</h1>
            <p class="mx-auto mt-3 max-w-md text-[15px] leading-relaxed text-ink-500">{{ $message }}</p>

            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a href="/" class="inline-flex h-12 items-center justify-center rounded-pill bg-green-500 px-7 text-[15px] font-bold text-white transition hover:bg-green-600">
                    Zur Startseite
                </a>
                <a href="/contact" class="inline-flex h-12 items-center justify-center rounded-pill border border-ink-300 bg-white px-7 text-[15px] font-bold text-navy-700 transition hover:border-navy-500">
                    Support kontaktieren
                </a>
            </div>
        </div>
    </body>
</html>
