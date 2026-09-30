<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kibali {{ $kibali->namba }}</title>
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
        .toolbar button { background: #1a4f8b; color: #fff; }
        .stage { display: flex; justify-content: center; padding: 8px 16px 32px; }
        .karatasi {
            width: 190mm;
            background: #fff;
            border: 2px solid #1a4f8b;
            padding: 10mm 12mm 12mm;
        }
        .kichwa { text-align: center; position: relative; min-height: 34mm; padding: 0 32mm; }
        .ofisi { color: #c0392b; font-weight: 700; font-size: 18px; letter-spacing: 0.04em; }
        .kampuni, .anuani { font-weight: 700; font-size: 14px; }
        .jina-kibali { color: #9b2d6b; font-weight: 700; margin-top: 6px; font-size: 15px; }
        .namba {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 22px;
            font-weight: 700;
            color: #111;
            letter-spacing: 0.08em;
        }
        .mstari { margin: 8px 0; font-weight: 700; font-size: 14px; }
        .mstari span { font-weight: 500; color: #111; }
        .sahihi { float: right; font-weight: 700; }
        .sahihi span { display: inline-block; min-width: 45mm; border-bottom: 1px dotted #1a4f8b; }
        .orodha { margin: 4px 0 10px; padding: 0; list-style: none; }
        .orodha li { margin: 7px 0; font-weight: 700; }
        .jina { font-weight: 500; color: #111; border-bottom: 1px dotted #1a4f8b; display: inline-block; min-width: 90mm; }
        .ukaguzi {
            position: absolute;
            left: 0;
            top: 0;
            width: 28mm;
            margin: 0;
            text-align: center;
            color: #111;
            font-size: 10px;
            line-height: 1.2;
        }
        .ukaguzi img { width: 24mm; height: 24mm; display: block; margin: 0 auto 1mm; }
        .ukaguzi small { display: block; color: #1a4f8b; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .stage { padding: 0; }
            .karatasi { border: 2px solid #1a4f8b; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('milipuko.vibali.show', $kibali) }}">Rudi</a>
        <button type="button" onclick="window.print()">Chapisha</button>
    </div>
    <div class="stage">
        <div class="karatasi">
            <div class="kichwa">
                <div class="ukaguzi">
                    <img src="{{ $qr }}" alt="QR ya ukaguzi">
                    <small>SKANI KWA UKAGUZI</small>
                </div>
                <div class="namba">{{ $kibali->namba }}</div>
                <div class="ofisi">BLASTING OFFICE</div>
                <div class="kampuni">{{ $kibali->company->name ?? 'M-ERP' }}</div>
                @if(filled($kibali->company->address ?? null))
                    <div class="anuani">{{ $kibali->company->address }}</div>
                @endif
                <div class="jina-kibali">KIBALI CHA KUCHORONGA MWAMBA NA KULIPUA</div>
            </div>

            <div class="mstari">HALI: <span>{{ $kibali->haliLabel() }}@if($kibali->imefungwa()) — CLOSED @endif</span></div>
            <div class="mstari">DUARA No. <span>{{ $kibali->duara->namba ?? '' }}</span></div>
            <div class="mstari">
                IDADI YA MATUNDU <span>{{ $kibali->idadi_ya_matundu }}</span>
                &nbsp;&nbsp; BC No. <span>{{ $kibali->bc_no }}</span>
                &nbsp;&nbsp; TAREHE <span>{{ $kibali->tarehe->format('d/m/Y') }}</span>
            </div>
            <div class="mstari">
                JINA LA MSIMAMIZI WA DUARA: <span>{{ $kibali->msimamizi->jina ?? '' }}</span>
                <div class="sahihi">SAHIHI <span></span></div>
            </div>
            <div class="mstari" style="clear: both;">
                JINA LA MLIPUAJI (BLASTA): <span>{{ $kibali->mlipuzi->jina ?? '' }}</span>
                <div class="sahihi">SAHIHI <span></span></div>
            </div>
            <div class="mstari" style="clear: both;">MAJINA YA WACHORONGAJI</div>
            <ol class="orodha">
                @foreach($kibali->wachorongajiKwaNafasi() as $nafasi => $mtu)
                    <li>
                        {{ $nafasi }}. <span class="jina">{{ $mtu->jina ?? '' }}</span>
                        <span class="sahihi">SAHIHI <span></span></span>
                    </li>
                @endforeach
            </ol>
            <div class="mstari" style="clear: both; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                AINA YA MLIPUKO:
                @include('milipuko.vibali._aina')
            </div>
            <div class="mstari">
                MSIMAMIZI WA IDARA (INSPECTOR) <span>{{ $kibali->msimamizi_wa_idara }}</span>
                <div class="sahihi">SAHIHI <span></span></div>
            </div>
            <div class="mstari" style="clear: both;">
                IMETHIBITISHWA NA KATIBU <span>{{ $kibali->katibu }}</span>
                <div class="sahihi">SAHIHI <span></span></div>
            </div>
        </div>
    </div>
</body>
</html>
