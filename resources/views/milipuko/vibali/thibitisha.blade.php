<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kibali ? 'Kibali '.$kibali->namba : 'Kibali hakijathibitishwa' }}</title>
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
        .closed { background: #3f3f46; }
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
        .note { margin: 0; text-align: center; color: #3d4b5c; }
        dl { margin: 0; }
        dl div { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid #e6ebf0; }
        dt { color: #5c6b7a; }
        dd { margin: 0; font-weight: 700; text-align: right; }
        ol { margin: 6px 0 0; padding-left: 18px; }
    </style>
</head>
<body>
    <article class="card">
        @if($kibali)
            <header class="head {{ $imefungwaSasa ? 'halali' : 'closed' }}">
                <strong>{{ $appName }}</strong>
                <span>{{ $kibali->company->name ?? '' }}</span>
            </header>
            <div class="body">
                <h1>Kibali {{ $kibali->namba }}</h1>
                @if($imefungwaSasa)
                    <div class="status halali">Kimethibitishwa</div>
                    <div class="status closed">Closed</div>
                    <p class="note" style="margin-bottom: 16px;">Ukaguzi umekithibitisha na kukifunga. Kibali hiki hakitumiki tena.</p>
                @else
                    <div class="status closed">Closed</div>
                    <p class="note" style="margin-bottom: 16px;">Kibali hiki kimeshafungwa {{ $kibali->imefungwa_at->format('d/m/Y H:i') }}. Hakitumiki tena.</p>
                @endif
                <dl>
                    <div><dt>Hali</dt><dd>{{ $kibali->haliLabel() }}</dd></div>
                    <div><dt>Tarehe</dt><dd>{{ $kibali->tarehe->format('d/m/Y') }}</dd></div>
                    <div><dt>Duara No.</dt><dd>{{ $kibali->duara->namba ?? '—' }}</dd></div>
                    <div><dt>Msimamizi wa duara</dt><dd>{{ $kibali->msimamizi->jina ?? '—' }}</dd></div>
                    <div><dt>Mlipuaji</dt><dd>{{ $kibali->mlipuzi->jina ?? '—' }}</dd></div>
                    <div><dt>BC No.</dt><dd>{{ $kibali->bc_no ?: '—' }}</dd></div>
                    <div><dt>Idadi ya matundu</dt><dd>{{ $kibali->idadi_ya_matundu }}</dd></div>
                    <div><dt>Aina ya mlipuko</dt><dd>@include('milipuko.vibali._aina')</dd></div>
                    <div><dt>Inspector</dt><dd>{{ $kibali->msimamizi_wa_idara }}</dd></div>
                    <div><dt>Katibu</dt><dd>{{ $kibali->katibu }}</dd></div>
                </dl>
                <p style="margin: 14px 0 4px; font-weight: 700;">Wachorongaji</p>
                <ol>
                    @foreach($kibali->wachorongaji as $mtu)
                        <li>{{ $mtu->jina }}</li>
                    @endforeach
                    @if($kibali->wachorongaji->isEmpty())
                        <li>Hakuna</li>
                    @endif
                </ol>
                <p class="note" style="margin-top: 16px;">Linganisha taarifa hizi na kibali ulichonacho. Kama hazilingani, kibali ni bandia.</p>
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
