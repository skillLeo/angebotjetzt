<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'AngebotJetzt' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f2ed;font-family:'Segoe UI',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f2ed;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    <tr>
                        <td style="padding:0 8px 24px;">
                            <img src="{{ \App\Models\Setting::logoUrl() ?? (config('app.url').'/images/logo-wordmark.png') }}" alt="AngebotJetzt" height="32" style="height:32px;width:auto;display:block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;border-radius:16px;padding:36px 40px;box-shadow:0 2px 8px rgba(11,37,69,0.06);">
                            {!! $slot !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 8px;color:#6E747E;font-size:13px;line-height:1.6;">
                            AngebotJetzt — Anfragen. Vergleichen. Beauftragen.<br>
                            Diese E-Mail wurde automatisch versendet. Bitte antworten Sie nicht direkt auf diese Nachricht.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
