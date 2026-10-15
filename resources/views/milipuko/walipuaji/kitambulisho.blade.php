<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitambulisho — {{ $mlipuzi->jina }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #e8edf2;
            font-family: Arial, Helvetica, sans-serif;
            color: #1b2430;
        }
        .toolbar {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 16px;
        }
        .toolbar a, .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar a { background: #fff; color: #1b2430; }
        .toolbar a.pdf, .toolbar button { background: #0b6e4f; color: #fff; }
        .stage {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 16px 24px;
        }
        .upande {
            margin: 0 0 6px;
            font-size: 12px;
            color: #5c6b7a;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .id-card {
            width: 85.6mm;
            height: 54mm;
            background: #fff;
            border-radius: 3mm;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            display: flex;
            flex-direction: column;
        }
        .id-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0b6e4f;
            color: #fff;
            padding: 1.6mm 3mm;
            font-size: 9px;
            letter-spacing: 0.04em;
        }
        .id-head-ms { background: #e85d04; }
        .id-head strong { font-size: 11px; }
        .id-main {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5mm 2.4mm 1.5mm;
        }
        .id-kicker {
            margin: 0;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #1b2430;
            text-align: center;
        }
        .id-bc {
            margin: 0.2mm 0 0;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-align: center;
        }
        .id-body {
            display: flex;
            gap: 1.8mm;
            align-items: stretch;
        }
        .id-photo {
            width: 22mm;
            height: 22mm;
            object-fit: cover;
            border-radius: 1.2mm;
            background: transparent;
            flex: 0 0 auto;
        }
        .id-photo-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #d7dee6;
            color: #5c6b7a;
            font-size: 8px;
            text-align: center;
        }
        .id-meta {
            flex: 1;
            min-width: 0;
            height: 22mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .id-meta p {
            margin: 0;
            line-height: 1.05;
        }
        .kichwa {
            font-size: 7px;
            font-weight: 400;
            color: #5c6b7a;
        }
        .thamani {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .id-qr { flex: 0 0 auto; }
        .id-qr img { width: 22mm; height: 22mm; display: block; }
        .id-barcode { text-align: center; }
        .id-barcode img { width: 72mm; height: 8mm; object-fit: fill; }
        .id-back {
            position: relative;
            padding: 0;
            background: #fff;
        }
        .muhuri {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }
        .maneno {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3mm 7mm;
        }
        .sahihi-mstari {
            width: 52mm;
            border-top: 0.6mm solid #1b2430;
            margin: 2.2mm 0 1mm;
        }
        .maneno p {
            margin: 0;
            font-size: 7.5px;
            line-height: 1.25;
        }
        .maneno .ukiokota {
            font-size: 9.5px;
            font-weight: 700;
        }
        .maneno .tofauti {
            font-size: 8px;
            font-weight: 700;
        }
        .katibu {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.06em;
        }
        @page { size: {{ $pdf ? 'A4 portrait' : '85.6mm 54mm' }}; margin: 0; }
        @media print {
            body { background: #fff; margin: 0; }
            .no-print { display: none !important; }
            @if($pdf)
            html, body { width: 210mm; height: 297mm; overflow: hidden; }
            .stage {
                position: absolute;
                left: 0;
                width: 210mm;
                margin: 0;
                padding: 0;
                break-after: avoid;
                page-break-after: avoid;
            }
            .stage.mbele { top: 40mm; }
            .stage.nyuma { top: 160mm; }
            .id-card {
                box-shadow: 0 1.2mm 2.6mm rgba(15, 23, 42, 0.16);
                border: 0.25mm solid #e3e8ee;
            }
            @else
            .stage { padding: 0; margin: 0; break-after: page; page-break-after: page; }
            .stage:last-child { break-after: auto; page-break-after: auto; }
            .id-card { box-shadow: none; border-radius: 0; }
            @endif
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('milipuko.walipuaji.show', $mlipuzi) }}">Rudi</a>
        <a class="pdf" href="{{ route('milipuko.walipuaji.kitambulisho.pdf', $mlipuzi) }}">Pakua PDF</a>
        <button type="button" onclick="window.print()">Chapisha</button>
    </div>
    <div class="stage mbele">
        <p class="upande no-print">Mbele</p>
        <article class="id-card">
            <header class="id-head{{ preg_match('/^MS\.\d{4}$/i', $usajili) ? ' id-head-ms' : '' }}">
                <strong>MILIPUKO</strong>
                <span>{{ $mlipuzi->company->name ?? '' }}</span>
            </header>
            <div class="id-main">
                <div>
                    <p class="id-kicker">KITAMBULISHO CHA MLIPUAJI</p>
                    @if($usajili !== '')
                        <p class="id-bc">{{ $usajili }}</p>
                    @endif
                </div>
                <div class="id-body">
                    @if($pichaInline || $mlipuzi->pichaUrl())
                        <img class="id-photo" src="{{ $pichaInline ?: $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}">
                    @else
                        <div class="id-photo id-photo-empty">Hakuna picha</div>
                    @endif
                    <div class="id-meta">
                        <p><span class="kichwa">Jina:</span> <span class="thamani">{{ $mlipuzi->jina }}</span></p>
                        <p><span class="kichwa">Jinsia:</span> <span class="thamani">{{ $mlipuzi->jinsiaLabel() ?: '—' }}</span></p>
                        <p><span class="kichwa">Simu:</span> <span class="thamani">{{ $mlipuzi->simu }}</span></p>
                        <p><span class="kichwa">Mkoa:</span> <span class="thamani">{{ $mlipuzi->mkoa }}</span></p>
                        <p><span class="kichwa">Wilaya:</span> <span class="thamani">{{ $mlipuzi->wilaya }}</span></p>
                    </div>
                    <div class="id-qr">
                        <img src="{{ $qr }}" alt="QR ya uthibitisho">
                    </div>
                </div>
                @if($barcode)
                    <div class="id-barcode">
                        <img src="{{ $barcode }}" alt="Barcode ya {{ $usajili }}">
                    </div>
                @endif
            </div>
        </article>
    </div>
    <div class="stage nyuma">
        <p class="upande no-print">Nyuma</p>
        <article class="id-card id-back">
            <svg class="muhuri" viewBox="0 0 856 540" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <defs>
                    <radialGradient id="fifia" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#2F6FAD" stop-opacity="0.30"/>
                        <stop offset="55%" stop-color="#5B93C4" stop-opacity="0.14"/>
                        <stop offset="100%" stop-color="#2F6FAD" stop-opacity="0"/>
                    </radialGradient>
                    <path id="mzunguko-juu" d="M 255.3,165.2 A 202,202 0 0 1 600.7,165.2"/>
                    <path id="mzunguko-chini" d="M 245.8,357.1 A 202,202 0 0 0 610.2,357.1"/>
                </defs>
                <rect width="856" height="540" fill="#ffffff"/>
                <circle cx="428" cy="270" r="248" fill="url(#fifia)" stroke="#1A4F8C" stroke-opacity="0.42" stroke-width="6"/>
                <circle cx="428" cy="270" r="231" fill="none" stroke="#1A4F8C" stroke-opacity="0.42" stroke-width="2"/>
                <circle cx="428" cy="270" r="174" fill="none" stroke="#1A4F8C" stroke-opacity="0.42" stroke-width="2.5"/>
                <polygon points="630,260 638,270 630,280 622,270" fill="#1A4F8C" fill-opacity="0.42"/>
                <polygon points="226,260 234,270 226,280 218,270" fill="#1A4F8C" fill-opacity="0.42"/>
                <text fill="#1A4F8C" fill-opacity="0.5" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="700" dominant-baseline="middle">
                    <textPath href="#mzunguko-juu" xlink:href="#mzunguko-juu" startOffset="50%" text-anchor="middle">IDARA YA MILIPUKO</textPath>
                </text>
                <text fill="#1A4F8C" fill-opacity="0.5" font-family="Arial, Helvetica, sans-serif" font-size="32" font-weight="700" dominant-baseline="middle">
                    <textPath href="#mzunguko-chini" xlink:href="#mzunguko-chini" startOffset="50%" text-anchor="middle">KATIBU WA MABLASTA</textPath>
                </text>
            </svg>
            <div class="maneno">
                <p class="tofauti">Kimetolewa na Idara ya Milipuko</p>
                <p class="tofauti">Msasa Gold Mine</p>
                <p>S.L.P 02 BUKOMBE, GEITA</p>
                <p class="ukiokota">Ukikiokota tafadhali wasiliana nasi kupitia {{ filled($mlipuzi->company?->phone) ? $mlipuzi->company->phone : '—' }}</p>
                <div class="sahihi-mstari"></div>
                <p class="katibu">KATIBU WA IDARA</p>
            </div>
        </article>
    </div>
</body>
</html>
