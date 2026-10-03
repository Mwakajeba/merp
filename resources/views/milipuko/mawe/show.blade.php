@extends('layouts.main')

@section('title', 'Kibali cha mawe '.$kibali->namba)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali vya Mawe', 'url' => route('milipuko.mawe.index'), 'icon' => 'bx bx-package'],
            ['label' => $kibali->namba, 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">KIBALI CHA MAWE {{ $kibali->namba }}</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('milipuko.mawe.chapisha', [$kibali, 'karatasi' => '58']) }}" class="btn btn-outline-secondary">POS 58mm</a>
                <a href="{{ route('milipuko.mawe.chapisha', [$kibali, 'karatasi' => '80']) }}" class="btn btn-outline-secondary">POS 80mm</a>
                <a href="{{ route('milipuko.mawe.edit', $kibali) }}" class="btn btn-warning">Hariri</a>
                <form action="{{ route('milipuko.mawe.destroy', $kibali) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta kibali hiki?');">
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
                        <div class="flex-grow-1 text-center">
                            <div class="kibali-ofisi">BLASTING OFFICE</div>
                            <div class="kibali-kampuni">{{ $kibali->company->name ?? 'M-ERP' }}</div>
                            @if(filled($kibali->company->address ?? null))
                                <div class="kibali-anuani">{{ $kibali->company->address }}</div>
                            @endif
                            <div class="kibali-kichwa">KIBALI CHA KUSAFIRISHA MZIGO KUTOKA MADUARANI KWENDA OFISINI</div>
                        </div>
                        <div class="kibali-namba">{{ $kibali->namba }}</div>
                    </div>

                    <p class="mb-2"><span class="kibali-label">Duara No.:</span> {{ $kibali->duara->namba ?? '—' }}</p>
                    <p class="mb-2"><span class="kibali-label">Idadi ya mifuko:</span> {{ $kibali->idadi_ya_mifuko }}</p>
                    <p class="mb-2"><span class="kibali-label">Aina ya mzigo:</span> {{ $kibali->ainaLabel() }}</p>
                    <p class="mb-2"><span class="kibali-label">Jina la msimamizi wa duara:</span> {{ $kibali->msimamizi->jina ?? '—' }}</p>
                    <p class="mb-2"><span class="kibali-label">Jina la katibu wa idara:</span> {{ $kibali->katibu }}</p>
                    <p class="mb-3"><span class="kibali-label">Tarehe:</span> {{ $kibali->tarehe->format('d/m/Y') }}</p>
                    <div class="text-center">
                        <img src="{{ $qr }}" alt="QR ya ukaguzi" width="120" height="120">
                        <div class="small kibali-label">Skani kwa ukaguzi</div>
                    </div>
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
