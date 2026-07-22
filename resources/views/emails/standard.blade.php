@component('emails.layout', ['subject' => $subject])
    <h1 style="margin:0 0 16px;font-size:22px;color:#14375E;">{{ $title }}</h1>
    @foreach ($lines as $line)
        <p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#3A3F47;">{!! $line !!}</p>
    @endforeach
    @isset($cta)
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 8px;">
            <tr>
                <td style="background-color:#3EAE2B;border-radius:999px;">
                    <a href="{{ $cta['url'] }}" style="display:inline-block;padding:13px 32px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">{{ $cta['label'] }}</a>
                </td>
            </tr>
        </table>
    @endisset
    @isset($footnote)
        <p style="margin:18px 0 0;font-size:13px;line-height:1.6;color:#6E747E;">{!! $footnote !!}</p>
    @endisset
@endcomponent
