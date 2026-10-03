@php
    $ukubwa = request('karatasi') === '58' ? '58' : '80';
@endphp
<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kibali cha mawe {{ $kibali->namba }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e8edf2;
            font-family: Arial, Helvetica, sans-serif;
            color: #1a4f8b;
        }
        .toolbar {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
            padding: 16px;
        }
        .toolbar a, .toolbar button {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            background: #fff;
            color: #1b2430;
        }
        .toolbar button.imechaguliwa,
        .toolbar button.chapisha {
            background: #1a4f8b;
            color: #fff;
        }
        .stage { display: flex; justify-content: center; padding: 8px 16px 32px; }
        .karatasi {
            background: #fff;
            border: 1px solid #1a4f8b;
            padding: 3mm 2.5mm 4mm;
        }
        .kichwa, .namba, .ukaguzi { text-align: center; }
        .ukaguzi { margin: 4mm auto 0; color: #111; line-height: 1.2; }
        .ukaguzi img { display: block; margin: 0 auto 1mm; }
        .ukaguzi small { display: block; color: #1a4f8b; font-weight: 700; }
        .namba { font-weight: 700; color: #111; letter-spacing: 0.06em; margin: 2mm 0; }
        .ofisi { color: #c0392b; font-weight: 700; letter-spacing: 0.04em; }
        .kampuni, .anuani, .jina-kibali { font-weight: 700; }
        .jina-kibali { color: #9b2d6b; margin: 1.5mm 0 2mm; line-height: 1.3; }
        .mstari { margin: 2.4mm 0; font-weight: 700; line-height: 1.35; }
        .mstari .thamani { font-weight: 500; color: #111; }
        .sahihi {
            display: block;
            border-bottom: 1px dotted #1a4f8b;
            height: 7mm;
            margin-top: 1mm;
        }
        body.pos-58 .karatasi { width: 58mm; }
        body.pos-80 .karatasi { width: 80mm; }
        body.pos-58 { font-size: 9px; }
        body.pos-80 { font-size: 11px; }
        body.pos-58 .ofisi { font-size: 11px; }
        body.pos-80 .ofisi { font-size: 13px; }
        body.pos-58 .namba { font-size: 16px; }
        body.pos-80 .namba { font-size: 18px; }
        body.pos-58 .jina-kibali { font-size: 10px; }
        body.pos-80 .jina-kibali { font-size: 12px; }
        body.pos-58 .ukaguzi img { width: 22mm; height: 22mm; }
        body.pos-80 .ukaguzi img { width: 26mm; height: 26mm; }
        body.pos-58 .ukaguzi small { font-size: 8px; }
        body.pos-80 .ukaguzi small { font-size: 9px; }
        @page pos58 { size: 58mm auto; margin: 0; }
        @page pos80 { size: 80mm auto; margin: 0; }
        body.pos-58 { page: pos58; }
        body.pos-80 { page: pos80; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .stage { padding: 0; }
            .karatasi { border: 0; }
        }
    </style>
</head>
<body class="pos-{{ $ukubwa }}">
    <div class="toolbar">
        <a href="{{ route('milipuko.mawe.show', $kibali) }}">Rudi</a>
        <button type="button" data-ukubwa="58" onclick="wekaKaratasi('58')">POS 58mm</button>
        <button type="button" data-ukubwa="80" onclick="wekaKaratasi('80')">POS 80mm</button>
        <button type="button" class="chapisha" onclick="window.print()">Chapisha</button>
    </div>
    <div class="stage">
        <div class="karatasi">
            <div class="kichwa">
                <div class="ofisi">BLASTING OFFICE</div>
                <div class="kampuni">{{ $kibali->company->name ?? 'M-ERP' }}</div>
                @if(filled($kibali->company->address ?? null))
                    <div class="anuani">{{ $kibali->company->address }}</div>
                @endif
                <div class="jina-kibali">KIBALI CHA KUSAFIRISHA MZIGO KUTOKA MADUARANI KWENDA OFISINI</div>
            </div>
            <div class="namba">{{ $kibali->namba }}</div>
            <div class="mstari">DUARA No. <span class="thamani">{{ $kibali->duara->namba ?? '' }}</span></div>
            <div class="mstari">IDADI YA MIFUKO <span class="thamani">{{ $kibali->idadi_ya_mifuko }}</span></div>
            <div class="mstari">AINA YA MZIGO <span class="thamani">{{ strtoupper($kibali->ainaLabel()) }}</span></div>
            <div class="mstari">JINA LA MSIMAMIZI WA DUARA <div class="thamani">{{ $kibali->msimamizi->jina ?? '' }}</div></div>
            <div class="mstari">JINA LA KATIBU WA IDARA <div class="thamani">{{ $kibali->katibu }}</div></div>
            <div class="mstari">SAHIHI YA KATIBU WA IDARA <span class="sahihi"></span></div>
            <div class="mstari">TAREHE <span class="thamani">{{ $kibali->tarehe->format('d/m/Y') }}</span></div>
            <div class="ukaguzi">
                <img src="{{ $qr }}" alt="QR ya ukaguzi">
                <small>SKANI KWA UKAGUZI</small>
            </div>
        </div>
    </div>
    <script>
        function wekaKaratasi(ukubwa) {
            document.body.classList.remove('pos-58', 'pos-80');
            document.body.classList.add('pos-' + ukubwa);
            document.querySelectorAll('[data-ukubwa]').forEach(function (kitufe) {
                kitufe.classList.toggle('imechaguliwa', kitufe.getAttribute('data-ukubwa') === ukubwa);
            });
        }
        wekaKaratasi(@json($ukubwa));
    </script>
</body>
</html>
