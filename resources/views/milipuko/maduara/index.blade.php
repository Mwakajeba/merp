@extends('layouts.main')

@section('title', 'Maduara')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Maduara', 'url' => '#', 'icon' => 'bx bx-grid-alt']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">MADUARA</h6>
            <a href="{{ route('milipuko.maduara.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Sajili Duara
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Namba ya duara</th>
                                <th>Maelezo</th>
                                <th>Wasimamizi</th>
                                <th>Wanachama</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maduara as $duara)
                                <tr>
                                    <td>{{ $duara->namba }}</td>
                                    <td>{{ $duara->maelezo ?: '—' }}</td>
                                    <td>{{ $duara->wasimamizi_count }}</td>
                                    <td>{{ $duara->wanachama_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.maduara.show', $duara) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                        <a href="{{ route('milipuko.maduara.edit', $duara) }}" class="btn btn-sm btn-outline-warning">Hariri</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Hakuna maduara yaliyosajiliwa bado.</td>
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
