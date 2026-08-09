<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #14161a; }
        .header { display: table; width: 100%; margin-bottom: 40px; }
        .header .issuer { display: table-cell; width: 50%; vertical-align: top; }
        .header .meta { display: table-cell; width: 50%; vertical-align: top; text-align: right; }
        h1 { font-size: 22px; color: #14375e; margin: 0 0 4px; }
        .muted { color: #6e747e; }
        .recipient { margin-bottom: 30px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th { text-align: left; border-bottom: 2px solid #14375e; padding: 8px 4px; font-size: 11px; text-transform: uppercase; color: #6e747e; }
        table.items td { padding: 10px 4px; border-bottom: 1px solid #edeff2; }
        table.items td.amount, table.items th.amount { text-align: right; }
        .total-row td { border-top: 2px solid #14375e; border-bottom: none; font-weight: bold; font-size: 14px; padding-top: 14px; }
        .footer { margin-top: 50px; font-size: 10px; color: #6e747e; }
    </style>
</head>
<body>
    <div class="header">
        <div class="issuer">
            <strong>AngebotJetzt GmbH</strong><br>
            Musterstraße 1<br>
            10115 Berlin<br>
            Deutschland
        </div>
        <div class="meta">
            <h1>Rechnung</h1>
            <div class="muted">Rechnungsnummer: {{ $invoice->invoice_number }}</div>
            <div class="muted">Rechnungsdatum: {{ $invoice->created_at->format('d.m.Y') }}</div>
            <div class="muted">Fällig am: {{ $invoice->due_date->format('d.m.Y') }}</div>
            <div class="muted">Auftrag: {{ $booking->booking_number }}</div>
        </div>
    </div>

    <div class="recipient">
        <div class="muted" style="margin-bottom: 4px;">Rechnungsempfänger</div>
        <strong>{{ $inspector->company_name ?: $inspector->name }}</strong><br>
        @if($inspector->company_name)
            {{ $inspector->name }}<br>
        @endif
        @if($inspector->street)
            {{ $inspector->street }}<br>
        @endif
        {{ trim("{$inspector->plz} {$inspector->city}") }}<br>
        Deutschland
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Beschreibung</th>
                <th class="amount">Betrag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Vermittlungsprovision für Auftrag {{ $booking->booking_number }}<br>
                    <span class="muted">Angebotssumme: {{ number_format($invoice->offer_amount_cents / 100, 2, ',', '.') }} €</span>
                </td>
                <td class="amount">{{ number_format($invoice->offer_amount_cents / 100, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>Provision ({{ rtrim(rtrim(number_format($invoice->commission_percent, 2, ',', '.'), '0'), ',') }} %)</td>
                <td class="amount">{{ number_format($invoice->commission_cents / 100, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total-row">
                <td>Gesamtbetrag fällig</td>
                <td class="amount">{{ number_format($invoice->commission_cents / 100, 2, ',', '.') }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        AngebotJetzt GmbH · Musterstraße 1 · 10115 Berlin<br>
        Bitte überweisen Sie den Gesamtbetrag bis zum {{ $invoice->due_date->format('d.m.Y') }} unter Angabe der Rechnungsnummer {{ $invoice->invoice_number }}.
    </div>
</body>
</html>
