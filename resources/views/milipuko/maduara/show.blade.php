@extends('layouts.main')

@section('title', 'Duara '.$duara->namba)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Maduara', 'url' => route('milipuko.maduara.index'), 'icon' => 'bx bx-grid-alt'],
            ['label' => $duara->namba, 'url' => '#', 'icon' => 'bx bx-circle']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">DUARA {{ $duara->namba }}</h6>
            <div>
                <a href="{{ route('milipuko.maduara.edit', $duara) }}" class="btn btn-warning">Hariri</a>
                <form action="{{ route('milipuko.maduara.destroy', $duara) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta duara hili?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Futa</button>
                </form>
            </div>
        </div>
        <hr />

        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-1"><strong>Namba ya duara:</strong> {{ $duara->namba }}</p>
                <p class="mb-0"><strong>Maelezo:</strong> {{ $duara->maelezo ?: '—' }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Wasimamizi wa duara</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Jina</th>
                                <th>Simu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($duara->wasimamizi as $msimamizi)
                                <tr>
                                    <td>{{ $msimamizi->jina }}</td>
                                    <td>{{ $msimamizi->simu }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">Hakuna wasimamizi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Wanachama wa duara</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Jina</th>
                                <th>Simu</th>
                                <th>Hisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($duara->wanachama as $mwanachama)
                                <tr>
                                    <td>{{ $mwanachama->jina }}</td>
                                    <td>{{ $mwanachama->simu }}</td>
                                    <td>{{ $mwanachama->pivot->hisa === null ? '—' : rtrim(rtrim(number_format((float) $mwanachama->pivot->hisa, 2, '.', ''), '0'), '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">Hakuna wanachama.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Historia ya vibali</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Namba ya kibali</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                                <th>Blasta</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($duara->vibali as $kibali)
                                <tr>
                                    <td>{{ $kibali->namba }}</td>
                                    <td>
                                        @if($kibali->hali === 'ufukuziaji')
                                            <span class="badge bg-success">{{ $kibali->haliLabel() }}</span>
                                        @elseif($kibali->hali === 'ufreshiaji')
                                            <span class="badge bg-info text-dark">{{ $kibali->haliLabel() }}</span>
                                        @else
                                            <span class="badge bg-primary">{{ $kibali->haliLabel() }}</span>
                                        @endif
                                        @if($kibali->imefungwa())
                                            <span class="badge bg-dark">Closed</span>
                                        @endif
                                    </td>
                                    <td>{{ $kibali->tarehe->format('d/m/Y') }}</td>
                                    <td>{{ $kibali->mlipuzi->jina ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.vibali.show', $kibali) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">Duara hili halijawahi kukata kibali.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
