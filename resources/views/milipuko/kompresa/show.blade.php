@extends('layouts.main')

@section('title', $kompresa->namba)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Usajili wa Kompresa', 'url' => route('milipuko.kompresa.index'), 'icon' => 'bx bx-cog'],
            ['label' => $kompresa->namba, 'url' => '#', 'icon' => 'bx bx-cog']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">{{ $kompresa->namba }}</h6>
            <div>
                <a href="{{ route('milipuko.kompresa.cheti', $kompresa) }}" class="btn btn-dark">Chapisha cheti</a>
                <a href="{{ route('milipuko.kompresa.cheti.pdf', $kompresa) }}" class="btn btn-outline-dark">Pakua PDF</a>
                <a href="{{ route('milipuko.kompresa.edit', $kompresa) }}" class="btn btn-warning">Hariri</a>
                <form action="{{ route('milipuko.kompresa.destroy', $kompresa) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta usajili huu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Futa</button>
                </form>
            </div>
        </div>
        <hr />

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        @if($kompresa->pichaUrl())
                            <img src="{{ $kompresa->pichaUrl() }}" alt="{{ $kompresa->jina }}" width="180" height="220" class="rounded object-fit-cover">
                        @else
                            <p class="text-muted mb-0">Hakuna picha.</p>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Jina la mmiliki</dt>
                            <dd class="col-sm-8">{{ $kompresa->jina }}</dd>
                            <dt class="col-sm-4">Namba ya kompresa</dt>
                            <dd class="col-sm-8">{{ $kompresa->namba }}</dd>
                            <dt class="col-sm-4">Mkoa</dt>
                            <dd class="col-sm-8">{{ $kompresa->mkoa }}</dd>
                            <dt class="col-sm-4">Wilaya</dt>
                            <dd class="col-sm-8">{{ $kompresa->wilaya }}</dd>
                            <dt class="col-sm-4">Mtaa</dt>
                            <dd class="col-sm-8">{{ $kompresa->mtaa ?: '—' }}</dd>
                            <dt class="col-sm-4">Simu</dt>
                            <dd class="col-sm-8">{{ $kompresa->simu }}</dd>
                            <dt class="col-sm-4">Simu mbadala</dt>
                            <dd class="col-sm-8">{{ $kompresa->simu_mbadala ?: '—' }}</dd>
                            <dt class="col-sm-4">Maelezo</dt>
                            <dd class="col-sm-8">{{ $kompresa->maelezo ?: '—' }}</dd>
                            <dt class="col-sm-4">Tarehe ya usajili</dt>
                            <dd class="col-sm-8">{{ $kompresa->created_at?->format('d/m/Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
