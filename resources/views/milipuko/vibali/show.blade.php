@extends('layouts.main')

@section('title', 'Kibali '.$kibali->namba)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali', 'url' => route('milipuko.vibali.index'), 'icon' => 'bx bx-id-card'],
            ['label' => $kibali->namba, 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">KIBALI {{ $kibali->namba }}</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('milipuko.vibali.chapisha', $kibali) }}" class="btn btn-outline-secondary">Chapisha</a>
                @unless($kibali->imefungwa())
                    <a href="{{ route('milipuko.vibali.edit', $kibali) }}" class="btn btn-warning">Hariri</a>
                @endunless
                <form action="{{ route('milipuko.vibali.destroy', $kibali) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta kibali hiki?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Futa</button>
                </form>
            </div>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="kibali-karatasi">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="text-center" style="width: 110px;">
                            <img src="{{ $qr }}" alt="QR ya ukaguzi" width="96" height="96">
                            <div class="small kibali-label">Skani kwa ukaguzi</div>
                        </div>
                        <div class="flex-grow-1 text-center">
                            <div class="kibali-ofisi">BLASTING OFFICE</div>
                            <div class="kibali-kampuni">{{ $kibali->company->name ?? 'M-ERP' }}</div>
                            @if(filled($kibali->company->address ?? null))
                                <div class="kibali-anuani">{{ $kibali->company->address }}</div>
                            @endif
                            <div class="kibali-kichwa">KIBALI CHA KUCHORONGA MWAMBA NA KULIPUA</div>
                        </div>
                        <div class="kibali-namba">{{ $kibali->namba }}</div>
                    </div>

                    <p class="mb-2"><span class="kibali-label">Hali:</span> {{ $kibali->haliLabel() }}
                        @if($kibali->imefungwa())
                            <span class="badge bg-dark">Closed</span>
                            <span class="text-muted">{{ $kibali->imefungwa_at->format('d/m/Y H:i') }}. Hakitumiki tena.</span>
                        @endif
                    </p>
                    <p class="mb-2"><span class="kibali-label">Duara No.:</span> {{ $kibali->duara->namba ?? '—' }}</p>
                    <p class="mb-2"><span class="kibali-label">Idadi ya matundu:</span> {{ $kibali->idadi_ya_matundu }}</p>
                    <p class="mb-2"><span class="kibali-label">BC No.:</span> {{ $kibali->bc_no ?: '—' }}</p>
                    <p class="mb-2"><span class="kibali-label">Tarehe:</span> {{ $kibali->tarehe->format('d/m/Y') }}</p>
                    <p class="mb-2"><span class="kibali-label">Jina la msimamizi wa duara:</span> {{ $kibali->msimamizi->jina ?? '—' }}</p>
                    <p class="mb-2"><span class="kibali-label">Jina la mlipuaji (blasta):</span> {{ $kibali->mlipuzi->jina ?? '—' }}</p>

                    <div class="kibali-label mb-1">Majina ya wachorongaji</div>
                    <ol class="mb-3">
                        @foreach($kibali->wachorongajiKwaNafasi() as $mtu)
                            <li>{{ $mtu->jina ?? '—' }}</li>
                        @endforeach
                    </ol>

                    <p class="mb-2 d-flex flex-wrap align-items-center gap-2">
                        <span class="kibali-label">Aina ya mlipuko:</span>
                        @include('milipuko.vibali._aina')
                    </p>
                    <p class="mb-2"><span class="kibali-label">Msimamizi wa idara (inspector):</span> {{ $kibali->msimamizi_wa_idara }}</p>
                    <p class="mb-0"><span class="kibali-label">Imethibitishwa na katibu:</span> {{ $kibali->katibu }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .kibali-karatasi {
        border: 2px solid #1a4f8b;
        border-radius: 4px;
        padding: 1.25rem 1.5rem 1.5rem;
        background: #fff;
    }
    .kibali-ofisi { color: #c0392b; font-weight: 700; letter-spacing: 0.04em; }
    .kibali-kampuni, .kibali-anuani { color: #1a4f8b; font-weight: 700; }
    .kibali-kichwa { color: #9b2d6b; font-weight: 700; margin: 0.35rem 0 1rem; }
    .kibali-namba { font-size: 1.6rem; font-weight: 700; letter-spacing: 0.08em; }
    .kibali-label { color: #1a4f8b; font-weight: 700; }
</style>
@endpush
