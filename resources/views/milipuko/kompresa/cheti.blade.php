<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cheti cha usajili — {{ $kompresa->namba }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e8edf2;
            font-family: "Times New Roman", Times, serif;
            color: #1b2430;
        }
        .toolbar {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            padding: 16px;
        }
        .toolbar a, .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            background: #fff;
            color: #1b2430;
        }
        .toolbar a.pdf, .toolbar button { background: #1a4f8b; color: #fff; }
        .ujumbe {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #0b6e4f;
        }
        .stage { display: flex; justify-content: center; padding: 8px 16px 32px; }
        .karatasi {
            width: 186mm;
            min-height: 267mm;
            background: #fff;
            padding: 14mm 16mm 16mm;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            border: 1.2mm solid #1a4f8b;
            outline: 0.4mm solid #c9a227;
            outline-offset: -4mm;
        }
        .kichwa { text-align: center; }
        .logo { width: 22mm; height: 22mm; object-fit: contain; }
        .ofisi {
            margin: 2mm 0 0;
            color: #c0392b;
            font-weight: 700;
            letter-spacing: 0.08em;
            font-size: 13px;
        }
        .kampuni {
            margin: 1mm 0 0;
            color: #1a4f8b;
            font-size: 22px;
            font-weight: 700;
        }
        .anuani { margin: 1mm 0 0; color: #1a4f8b; font-size: 13px; }
        .kichwa h1 {
            margin: 8mm 0 2mm;
            color: #9b2d6b;
            font-size: 22px;
            letter-spacing: 0.04em;
        }
        .namba {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .mstari-mwembamba {
            height: 0;
            margin: 6mm 0;
            border: 0;
            border-top: 0.4mm solid #1a4f8b;
        }
        .mwili { display: flex; gap: 8mm; align-items: flex-start; }
        .picha {
            width: 35mm;
            height: 45mm;
            object-fit: cover;
            border: 0.3mm solid #1a4f8b;
            background: #f4f7fb;
        }
        .picha-tupu {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5c6b7a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            text-align: center;
        }
        table.taarifa { width: 100%; border-collapse: collapse; }
        table.taarifa th, table.taarifa td {
            padding: 2.2mm 0;
            vertical-align: top;
            text-align: left;
            font-size: 15px;
        }
        table.taarifa th {
            width: 42mm;
            color: #1a4f8b;
            font-weight: 700;
        }
        .tamko {
            margin: 8mm 0 0;
            font-size: 15px;
            line-height: 1.55;
            text-align: justify;
        }
        .sahihi {
            margin-top: 18mm;
            width: 70mm;
            text-align: center;
        }
        .sahihi .mstari {
            border-top: 0.35mm solid #1b2430;
            margin-bottom: 2mm;
        }
        .sahihi p { margin: 0; font-size: 13px; font-weight: 700; letter-spacing: 0.06em; }
        .tarehe { margin-top: 8mm; font-size: 14px; }
        .uthibitisho { margin-top: 10mm; text-align: center; }
        .uthibitisho img { width: 32mm; height: 32mm; }
        .uthibitisho p {
            margin: 2mm 0 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1a4f8b;
        }
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .stage { padding: 0; }
            .karatasi { width: auto; min-height: auto; box-shadow: none; border: 0; outline: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('milipuko.kompresa.show', $kompresa) }}">Rudi</a>
        <a class="pdf" href="{{ route('milipuko.kompresa.cheti.pdf', $kompresa) }}">Pakua PDF</a>
        <button type="button" onclick="window.print()">Chapisha</button>
        @if(session('success'))
            <p class="ujumbe">{{ session('success') }}</p>
        @endif
    </div>
    <div class="stage">
        <article class="karatasi">
            <header class="kichwa">
                @if($logoInline || ($kompresa->company?->logo && file_exists(public_path('storage/'.ltrim($kompresa->company->logo, '/')))))
                    <img class="logo" src="{{ $logoInline ?: asset('storage/'.ltrim($kompresa->company->logo, '/')) }}" alt="{{ $kompresa->company->name }}">
                @endif
                <p class="ofisi">BLASTING OFFICE</p>
                <p class="kampuni">{{ $kompresa->company->name ?? 'M-ERP' }}</p>
                @if(filled($kompresa->company->address ?? null))
                    <p class="anuani">{{ $kompresa->company->address }}</p>
                @endif
                @if(filled($kompresa->company->phone ?? null))
                    <p class="anuani">Simu: {{ $kompresa->company->phone }}</p>
                @endif
                <h1>CHETI CHA USAJILI WA KOMPRESA</h1>
                <p class="namba">{{ $kompresa->namba }}</p>
            </header>
            <hr class="mstari-mwembamba">
            <div class="mwili">
                @if($pichaInline || $kompresa->pichaUrl())
                    <img class="picha" src="{{ $pichaInline ?: $kompresa->pichaUrl() }}" alt="{{ $kompresa->jina }}">
                @else
                    <div class="picha picha-tupu">Hakuna picha</div>
                @endif
                <table class="taarifa">
                    <tr>
                        <th>Jina la mmiliki</th>
                        <td>{{ $kompresa->jina }}</td>
                    </tr>
                    <tr>
                        <th>Namba ya kompresa</th>
                        <td>{{ $kompresa->namba }}</td>
                    </tr>
                    <tr>
                        <th>Mkoa</th>
                        <td>{{ $kompresa->mkoa }}</td>
                    </tr>
                    <tr>
                        <th>Wilaya</th>
                        <td>{{ $kompresa->wilaya }}</td>
                    </tr>
                    <tr>
                        <th>Mtaa</th>
                        <td>{{ $kompresa->mtaa ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Simu</th>
                        <td>{{ $kompresa->simu }}</td>
                    </tr>
                    <tr>
                        <th>Simu mbadala</th>
                        <td>{{ $kompresa->simu_mbadala ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Maelezo</th>
                        <td>{{ $kompresa->maelezo ?: '—' }}</td>
                    </tr>
                </table>
            </div>
            <p class="tamko">
                Cheti hiki kinathibitisha kuwa kompresa yenye namba <strong>{{ $kompresa->namba }}</strong>,
                inayomilikiwa na <strong>{{ $kompresa->jina }}</strong> wa {{ $kompresa->wilaya }}, {{ $kompresa->mkoa }},
                imesajiliwa na Idara ya Milipuko.
            </p>
            <p class="tarehe">Tarehe ya usajili: {{ $kompresa->created_at?->format('d/m/Y') }}</p>
            <div class="sahihi">
                <div class="mstari"></div>
                <p>KATIBU WA IDARA</p>
            </div>
            <div class="uthibitisho">
                <img src="{{ $qr }}" alt="QR ya uthibitisho">
                <p>Scan kuthibitisha kuwa cheti hiki ni halali</p>
            </div>
        </article>
    </div>
    @if(request()->boolean('chapisha'))
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>
</html>
