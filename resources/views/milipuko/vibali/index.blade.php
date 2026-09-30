@extends('layouts.main')

@section('title', 'Vibali')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali', 'url' => '#', 'icon' => 'bx bx-id-card']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">VIBALI</h6>
            <a href="{{ route('milipuko.vibali.create') }}" class="btn btn-success">
                <i class="bx bx-plus me-1"></i> Sajili Kibali
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Namba</th>
                                <th>Tarehe</th>
                                <th>Duara No.</th>
                                <th>Mlipuaji</th>
                                <th>Hali</th>
                                <th>Matundu</th>
                                <th>Wachorongaji</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vibali as $kibali)
                                <tr>
                                    <td>{{ $kibali->namba }}</td>
                                    <td>{{ $kibali->tarehe->format('d/m/Y') }}</td>
                                    <td>{{ $kibali->duara->namba ?? '—' }}</td>
                                    <td>{{ $kibali->mlipuzi->jina ?? '—' }}</td>
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
                                    <td>{{ $kibali->idadi_ya_matundu }}</td>
                                    <td>{{ $kibali->wachorongaji_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.vibali.show', $kibali) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                        @unless($kibali->imefungwa())
                                            <a href="{{ route('milipuko.vibali.edit', $kibali) }}" class="btn btn-sm btn-outline-warning">Hariri</a>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Hakuna vibali vilivyowekwa bado.</td>
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
