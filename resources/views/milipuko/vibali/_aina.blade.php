@php
    $chaguzi = ['cotex' => 'COTEX', 'dull_fuse' => 'DULL FUSE'];
@endphp
@foreach ($chaguzi as $thamani => $jina)
    <span style="display:inline-flex;align-items:center;gap:6px;margin-right:18px;font-weight:700;color:#1a4f8b;">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border:2px solid #1a4f8b;border-radius:50%;background:{{ $kibali->aina_ya_mlipuko === $thamani ? '#1a4f8b' : '#fff' }};flex:0 0 16px;">
            @if ($kibali->aina_ya_mlipuko === $thamani)
                <svg width="10" height="10" viewBox="0 0 10 10" aria-hidden="true">
                    <path d="M1.6 5.1 L4 7.5 L8.4 2.4" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            @endif
        </span>
        {{ $jina }}
    </span>
@endforeach
