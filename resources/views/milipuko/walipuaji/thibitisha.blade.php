<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uthibitisho — {{ $mlipuzi->jina }}</title>
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
            width: min(420px, 100%);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        .head {
            background: #0b6e4f;
            color: #fff;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .body { padding: 20px 18px 24px; text-align: center; }
        img.photo {
            width: 140px;
            height: 160px;
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
        .active { background: #0b6e4f; }
        .blocked { background: #b42318; }
        .note { margin-top: 14px; font-size: 13px; }
    </style>
</head>
<body>
    <article class="card">
        <header class="head">
            <strong>{{ $appName }}</strong>
            <span>{{ $mlipuzi->company->name ?? '' }}</span>
        </header>
        <div class="body">
            @if($mlipuzi->pichaUrl())
                <img class="photo" src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}">
            @endif
            <h1>{{ $mlipuzi->jina }}</h1>
            <p>BC No. {{ $mlipuzi->bcInayotumika() ?: '—' }}</p>
            @if($mlipuzi->aina_ya_bc === 'mtu' && $mlipuzi->mwenyeBc)
                <p>Ya {{ $mlipuzi->mwenyeBc->jina }}</p>
            @endif
            <p>{{ $mlipuzi->mkoa }}, {{ $mlipuzi->wilaya }}</p>
            @if($mlipuzi->hali === 'blocked')
                <div class="status blocked">Blocked</div>
                <p class="note">Mlipuaji huyu amefungwa. Kitambulisho hiki hakitumiki.</p>
            @else
                <div class="status active">Active</div>
                <p class="note">Kitambulisho kimethibitishwa.</p>
            @endif
        </div>
    </article>
</body>
</html>
