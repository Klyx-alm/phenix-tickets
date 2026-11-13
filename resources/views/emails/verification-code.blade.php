@php
    $logoCid = $message->embed(public_path('images/phenix-emblem.png'));
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f3efec;font-family:Arial,Helvetica,sans-serif;color:#1f1418;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3efec;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;">

    <tr>
        <td style="background:#2b0a1f;border-radius:20px 20px 0 0;padding:18px 28px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="48"><img src="{{ $logoCid }}" width="44" height="44" alt="" style="display:block;border:0;"></td>
                    <td style="padding-left:12px;color:#ffffff;font-size:20px;font-weight:bold;">{{ config('app.name') }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="background:#ffffff;border-radius:0 0 20px 20px;padding:28px;">
            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.2;">Votre code de vérification</h1>
            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;">Bonjour {{ $user->name }}, saisissez ce code sur le site pour valider votre adresse e-mail :</p>
            <p style="margin:0 0 18px;text-align:center;">
                <span style="display:inline-block;background:#fde8e2;color:#e4361b;font-size:36px;font-weight:bold;letter-spacing:10px;padding:14px 22px 14px 32px;border-radius:14px;">{{ $code }}</span>
            </p>
            <p style="margin:0;font-size:14px;line-height:1.6;color:#57534e;">
                Ce code est valable {{ $minutes }} minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message : personne ne peut utiliser votre compte sans ce code.
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
