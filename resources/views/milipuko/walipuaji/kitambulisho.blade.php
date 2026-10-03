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
        .toolbar button { background: #0b6e4f; color: #fff; }
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
            background: #d7dee6;
            flex: 0 0 auto;
        }
        .id-photo-empty {
            display: flex;
            align-items: center;
            justify-content: center;
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
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3mm 4mm;
            gap: 1mm;
        }
        .sahihi-mstari {
            width: 46mm;
            border-top: 0.35mm solid #1b2430;
            margin: 3mm 0 1.2mm;
        }
        .id-back p {
            margin: 0;
            font-size: 7.5px;
            line-height: 1.25;
        }
        .id-back .tofauti {
            font-size: 8px;
            font-weight: 700;
        }
        .katibu {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.06em;
        }
        @page { size: 85.6mm 54mm; margin: 0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .stage { padding: 0; break-after: page; }
            .stage:last-child { break-after: auto; }
            .id-card { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('milipuko.walipuaji.show', $mlipuzi) }}">Rudi</a>
        <button type="button" onclick="window.print()">Chapisha</button>
    </div>
    <div class="stage">
        <p class="upande no-print">Mbele</p>
        <article class="id-card">
            <header class="id-head">
                <strong>MILIPUKO</strong>
                <span>{{ $mlipuzi->company->name ?? '' }}</span>
            </header>
            <div class="id-main">
                <div>
                    <p class="id-kicker">KITAMBULISHO CHA MLIPUAJI</p>
                    @if($bc !== '')
                        <p class="id-bc">{{ $bc }}</p>
                    @endif
                </div>
                <div class="id-body">
                    @if($mlipuzi->pichaUrl())
                        <img class="id-photo" src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}">
                    @else
                        <div class="id-photo id-photo-empty">Hakuna picha</div>
                    @endif
                    <div class="id-meta">
                        <p><span class="kichwa">Jina:</span> <span class="thamani">{{ $mlipuzi->jina }}</span></p>
                        <p><span class="kichwa">Jinsia:</span> <span class="thamani">ME</span></p>
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
                        <img src="{{ $barcode }}" alt="Barcode ya {{ $bc }}">
                    </div>
                @endif
            </div>
        </article>
    </div>
    <div class="stage">
        <p class="upande no-print">Nyuma</p>
        <article class="id-card id-back">
            <p class="tofauti">Kimetolewa na Idara ya Milipuko</p>
            <p class="tofauti">Msasa Gold Mine</p>
            <p>S.L.P 02 BUKOMBE, GEITA</p>
            <p>Ukikiokota tafadhali wasiliana nasi kupitia {{ filled($mlipuzi->company?->phone) ? $mlipuzi->company->phone : '—' }}</p>
            <div class="sahihi-mstari"></div>
            <p class="katibu">KATIBU WA IDARA</p>
        </article>
    </div>
</body>
</html>
