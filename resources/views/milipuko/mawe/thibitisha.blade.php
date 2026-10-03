<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kibali ? 'Kibali cha mawe '.$kibali->namba : 'Kibali hakijathibitishwa' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #e8edf2;
            font-family: Arial, Helvetica, sans-serif;
            color: #1b2430;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .card {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        .head {
            color: #fff;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .halali { background: #0b6e4f; }
        .bandia { background: #b42318; }
        .body { padding: 20px 18px 24px; }
        h1 { font-size: 22px; margin: 0 0 8px; text-align: center; }
        .status {
            display: block;
            text-align: center;
            margin: 0 0 16px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
        }
        .note { margin: 16px 0 0; text-align: center; color: #3d4b5c; }
        dl { margin: 0; }
        dl div { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid #e6ebf0; }
        dt { color: #5c6b7a; }
        dd { margin: 0; font-weight: 700; text-align: right; }
    </style>
</head>
<body>
    <article class="card">
        @if($kibali)
            <header class="head halali">
                <strong>{{ $appName }}</strong>
                <span>{{ $kibali->company->name ?? '' }}</span>
            </header>
            <div class="body">
                <h1>Kibali cha {{ strtolower($kibali->ainaLabel()) }} {{ $kibali->namba }}</h1>
                <div class="status halali">Halali</div>
                <dl>
                    <div><dt>Tarehe</dt><dd>{{ $kibali->tarehe->format('d/m/Y') }}</dd></div>
                    <div><dt>Duara No.</dt><dd>{{ $kibali->duara->namba ?? '—' }}</dd></div>
                    <div><dt>Idadi ya mifuko</dt><dd>{{ $kibali->idadi_ya_mifuko }}</dd></div>
                    <div><dt>Aina ya mzigo</dt><dd>{{ $kibali->ainaLabel() }}</dd></div>
                    <div><dt>Msimamizi wa duara</dt><dd>{{ $kibali->msimamizi->jina ?? '—' }}</dd></div>
                    <div><dt>Katibu wa idara</dt><dd>{{ $kibali->katibu }}</dd></div>
                </dl>
                <p class="note">Linganisha taarifa hizi na kibali ulichonacho. Kama hazilingani, kibali ni bandia.</p>
            </div>
        @else
            <header class="head bandia">
                <strong>{{ $appName }}</strong>
                <span>Ukaguzi</span>
            </header>
            <div class="body">
                <h1>Hakijathibitishwa</h1>
                <div class="status bandia">Bandia</div>
                <p class="note">QR hii hailingani na kibali chochote kilichotolewa. Kibali hiki si halali.</p>
            </div>
        @endif
    </article>
</body>
</html>
