<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uthibitisho wa kompresa{{ $kompresa ? ' — '.$kompresa->namba : '' }}</title>
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
            width: min(440px, 100%);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        .head {
            background: #1a4f8b;
            color: #fff;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }
        .body { padding: 20px 18px 24px; text-align: center; }
        img.photo {
            width: 140px;
            height: 170px;
            object-fit: cover;
            border-radius: 12px;
            background: #d7dee6;
        }
        h1 { font-size: 22px; margin: 14px 0 4px; }
        p { margin: 4px 0; color: #3d4b5c; }
        .status {
            display: inline-block;
            margin-top: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
        }
        .halali { background: #0b6e4f; }
        .si-halali { background: #b42318; }
        .note { margin-top: 14px; font-size: 13px; }
    </style>
</head>
<body>
    <article class="card">
        <header class="head">
            <strong>{{ $appName }}</strong>
            <span>{{ $kompresa?->company->name ?? 'Idara ya Milipuko' }}</span>
        </header>
        <div class="body">
            @if($kompresa)
                @if($kompresa->pichaUrl())
                    <img class="photo" src="{{ $kompresa->pichaUrl() }}" alt="{{ $kompresa->jina }}">
                @endif
                <h1>{{ $kompresa->jina }}</h1>
                <p>Namba ya kompresa {{ $kompresa->namba }}</p>
                <p>{{ $kompresa->wilaya }}, {{ $kompresa->mkoa }}</p>
                @if($kompresa->mtaa)
                    <p>Mtaa: {{ $kompresa->mtaa }}</p>
                @endif
                <p>Simu: {{ $kompresa->simu }}</p>
                <div class="status halali">Halali</div>
                <p class="note">Cheti cha usajili wa kompresa kimethibitishwa.</p>
            @else
                <h1>Haijapatikana</h1>
                <div class="status si-halali">Si halali</div>
                <p class="note">Cheti hiki hakipo kwenye rejesta ya Idara ya Milipuko.</p>
            @endif
        </div>
    </article>
</body>
</html>
