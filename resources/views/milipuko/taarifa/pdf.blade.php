<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <title>Taarifa ya milipuko</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1b2430;
            font-size: 12px;
            margin: 0;
        }
        .kichwa {
            text-align: center;
            border-bottom: 2px solid #1a4f8b;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .ofisi {
            color: #c0392b;
            font-weight: 700;
            letter-spacing: 0.04em;
            font-size: 14px;
        }
        .kampuni, .anuani {
            color: #1a4f8b;
            font-weight: 700;
        }
        h1 {
            color: #9b2d6b;
            font-size: 16px;
            margin: 8px 0 0;
        }
        .chujio {
            margin: 0 0 14px;
        }
        .chujio td {
            padding: 2px 16px 2px 0;
        }
        table.orodha {
            width: 100%;
            border-collapse: collapse;
        }
        table.orodha th, table.orodha td {
            border: 1px solid #c5d0dc;
            padding: 7px 8px;
            text-align: left;
        }
        table.orodha th {
            background: #1a4f8b;
            color: #fff;
        }
        table.orodha tr:nth-child(even) td {
            background: #f4f7fb;
        }
        .tupu {
            margin-top: 12px;
            color: #667085;
        }
    </style>
</head>
<body>
    <div class="kichwa">
        <div class="ofisi">BLASTING OFFICE</div>
        <div class="kampuni">{{ $company->name ?? 'M-ERP' }}</div>
        @if(filled($company->address ?? null))
            <div class="anuani">{{ $company->address }}</div>
        @endif
        <h1>TAARIFA YA MILIPUKO</h1>
    </div>

    <table class="chujio">
        <tr>
            <td><strong>Tarehe:</strong> {{ $tarehe->format('d/m/Y') }}</td>
            <td><strong>Hali:</strong> {{ $haliLebo }}</td>
        </tr>
    </table>

    <table class="orodha">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Duara No.</th>
                <th>Hali</th>
                <th>Jina la blasta</th>
                <th>Jina la msimamizi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vibali as $kibali)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kibali->duara->namba ?? '—' }}</td>
                    <td>{{ $kibali->haliLabel() }}</td>
                    <td>{{ $kibali->mlipuzi->jina ?? '—' }}</td>
                    <td>{{ $kibali->msimamizi->jina ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="tupu">Hakuna maduara kwa tarehe na hali hii.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
