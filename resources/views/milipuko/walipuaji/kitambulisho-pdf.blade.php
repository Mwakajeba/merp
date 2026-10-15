<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <title>Kitambulisho — {{ $mlipuzi->jina }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #1b2430;
        }
        .nafasi-juu { height: 36mm; }
        .nafasi-kati { height: 58mm; }
        .kadi {
            width: 85.6mm;
            height: 54mm;
            margin: 0 auto;
            border: 0.3mm solid #d5dde6;
            border-radius: 3mm;
            overflow: hidden;
            background: #fff;
        }
        .kichwa {
            background: {{ preg_match('/^MS\.\d{4}$/i', $usajili) ? '#e85d04' : '#0b6e4f' }};
            color: #fff;
            padding: 1.4mm 3mm;
        }
        .kichwa table { width: 100%; border-collapse: collapse; }
        .kichwa td { color: #fff; font-size: 8px; }
        .kichwa .brand { font-size: 10px; font-weight: bold; }
        .kichwa .kampuni { text-align: right; }
        .jina-kadi { text-align: center; padding: 1.2mm 2mm 0; }
        .jina-kadi .kicker { margin: 0; font-size: 8px; font-weight: bold; }
        .jina-kadi .bc { margin: 0.4mm 0 0; font-size: 11px; font-weight: bold; }
        .mwili { width: 100%; border-collapse: collapse; margin-top: 1mm; }
        .mwili td { vertical-align: middle; }
        .picha {
            width: 22mm;
            height: 22mm;
            margin-left: 2mm;
            background: #d7dee6;
            text-align: center;
            color: #5c6b7a;
            font-size: 7px;
        }
        .picha img { width: 22mm; height: 22mm; }
        .meta { padding: 0 1.5mm; }
        .meta p { margin: 0; line-height: 1.25; }
        .kichwa-ndogo { font-size: 7px; color: #5c6b7a; }
        .thamani { font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .qr { width: 24mm; text-align: right; padding-right: 2mm; }
        .qr img { width: 20mm; height: 20mm; }
        .barcode { text-align: center; padding: 0.8mm 0 1mm; }
        .barcode img { width: 70mm; height: 8mm; }
        .nyuma { text-align: center; }
        .muhuri {
            width: 42mm;
            height: 42mm;
            margin: 4mm auto 0;
            border: 0.5mm solid #8eafd4;
            border-radius: 21mm;
            text-align: center;
            color: #1a4f8c;
        }
        .muhuri .ndani { padding-top: 8mm; }
        .muhuri p { margin: 0; line-height: 1.25; }
        .muhuri .duara { font-size: 6.5px; font-weight: bold; }
        .muhuri .ukiokota { font-size: 6.5px; font-weight: bold; padding: 0.6mm 3mm 0; }
        .muhuri .ndogo { font-size: 6px; }
        .sahihi {
            width: 28mm;
            margin: 1.4mm auto 0.6mm;
            border-top: 0.35mm solid #1b2430;
        }
        .katibu { font-size: 6.5px; font-weight: bold; letter-spacing: 0.04em; color: #1b2430; }
    </style>
</head>
<body>
    <div class="nafasi-juu"></div>
    <table class="kadi" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kichwa">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="brand">MILIPUKO</td>
                        <td class="kampuni">{{ $mlipuzi->company->name ?? '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="jina-kadi">
                <p class="kicker">KITAMBULISHO CHA MLIPUAJI</p>
                @if($usajili !== '')
                    <p class="bc">{{ $usajili }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <table class="mwili" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="picha">
                            @if($pichaInline)
                                <img src="{{ $pichaInline }}" alt="{{ $mlipuzi->jina }}">
                            @else
                                Hakuna picha
                            @endif
                        </td>
                        <td class="meta">
                            <p><span class="kichwa-ndogo">Jina:</span> <span class="thamani">{{ $mlipuzi->jina }}</span></p>
                            <p><span class="kichwa-ndogo">Jinsia:</span> <span class="thamani">{{ $mlipuzi->jinsiaLabel() ?: '—' }}</span></p>
                            <p><span class="kichwa-ndogo">Simu:</span> <span class="thamani">{{ $mlipuzi->simu }}</span></p>
                            <p><span class="kichwa-ndogo">Mkoa:</span> <span class="thamani">{{ $mlipuzi->mkoa }}</span></p>
                            <p><span class="kichwa-ndogo">Wilaya:</span> <span class="thamani">{{ $mlipuzi->wilaya }}</span></p>
                        </td>
                        <td class="qr">
                            <img src="{{ $qr }}" alt="QR ya uthibitisho">
                        </td>
                    </tr>
                </table>
                @if($barcode)
                    <div class="barcode">
                        <img src="{{ $barcode }}" alt="Barcode ya {{ $usajili }}">
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="nafasi-kati"></div>

    <div class="kadi nyuma">
        <div class="muhuri">
            <div class="ndani">
                <p class="duara">IDARA YA MILIPUKO</p>
                <p class="duara">Kimetolewa na Idara ya Milipuko</p>
                <p class="duara">Msasa Gold Mine</p>
                <p class="ndogo">S.L.P 02 BUKOMBE, GEITA</p>
                <p class="ukiokota">Ukikiokota tafadhali wasiliana nasi kupitia {{ filled($mlipuzi->company?->phone) ? $mlipuzi->company->phone : '—' }}</p>
                <div class="sahihi"></div>
                <p class="katibu">KATIBU WA IDARA</p>
                <p class="duara">KATIBU WA MABLASTA</p>
            </div>
        </div>
    </div>
</body>
</html>
