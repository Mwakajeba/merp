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
            justify-content: center;
            padding: 8px 16px 32px;
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
        .id-kicker {
            padding: 1.2mm 3mm 0;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5c6b7a;
        }
        .id-body {
            flex: 1;
            display: flex;
            gap: 2.5mm;
            padding: 1.5mm 3mm 2mm;
            align-items: center;
        }
        .id-photo {
            width: 18mm;
            height: 22mm;
            object-fit: cover;
            border-radius: 1.5mm;
            background: #d7dee6;
            flex: 0 0 auto;
        }
        .id-photo-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5c6b7a;
            font-size: 8px;
        }
        .id-meta { flex: 1; min-width: 0; }
        .id-name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 1mm;
        }
        .id-meta p {
            margin: 0;
            font-size: 8.5px;
            line-height: 1.35;
        }
        .badge {
            display: inline-block;
            margin-top: 1.2mm;
            padding: 0.4mm 1.6mm;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            color: #fff;
        }
        .badge-active { background: #0b6e4f; }
        .badge-blocked { background: #b42318; }
        .id-qr { text-align: center; flex: 0 0 auto; }
        .id-qr img { width: 18mm; height: 18mm; }
        .id-qr small {
            display: block;
            font-size: 6.5px;
            color: #5c6b7a;
            margin-top: 0.4mm;
        }
        @page { size: 85.6mm 54mm; margin: 0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .stage { padding: 0; }
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
        <article class="id-card">
            <header class="id-head">
                <strong>{{ $appName }}</strong>
                <span>{{ $mlipuzi->company->name ?? '' }}</span>
            </header>
            <div class="id-kicker">Kitambulisho cha Mlipuaji</div>
            <div class="id-body">
                @if($mlipuzi->pichaUrl())
                    <img class="id-photo" src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}">
                @else
                    <div class="id-photo id-photo-empty">Hakuna picha</div>
                @endif
                <div class="id-meta">
                    <div class="id-name">{{ $mlipuzi->jina }}</div>
                    <p>BC No. {{ $mlipuzi->bcInayotumika() ?: '—' }}</p>
                    @if($mlipuzi->aina_ya_bc === 'mtu' && $mlipuzi->mwenyeBc)
                        <p>Ya {{ $mlipuzi->mwenyeBc->jina }}</p>
                    @endif
                    <p>{{ $mlipuzi->simu }}</p>
                    <p>{{ $mlipuzi->mkoa }}, {{ $mlipuzi->wilaya }}</p>
                    @if($mlipuzi->hali === 'blocked')
                        <span class="badge badge-blocked">Blocked</span>
                    @else
                        <span class="badge badge-active">Active</span>
                    @endif
                </div>
                <div class="id-qr">
                    <img src="{{ $qr }}" alt="QR ya uthibitisho">
                    <small>Skani kuthibitisha</small>
                </div>
            </div>
        </article>
    </div>
</body>
</html>
