@extends('layouts.main')

@section('title', 'Walipuaji')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Walipuaji', 'url' => '#', 'icon' => 'bx bx-user-check']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">WALIPUAJI (BLASTERS)</h6>
            <a href="{{ route('milipuko.walipuaji.create') }}" class="btn btn-warning">
                <i class="bx bx-plus me-1"></i> Sajili Mlipuaji
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Picha</th>
                                <th>Jina</th>
                                <th>BC No.</th>
                                <th>Hali</th>
                                <th>Simu</th>
                                <th>Mkoa</th>
                                <th>Wilaya</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($walipuaji as $mlipuzi)
                                <tr>
                                    <td>
                                        @if($mlipuzi->pichaUrl())
                                            <img src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}" width="48" height="48" class="rounded-circle object-fit-cover">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $mlipuzi->jina }}</td>
                                    <td>
                                        {{ $mlipuzi->bcInayotumika() ?: '—' }}
                                        @if($mlipuzi->aina_ya_bc === 'mtu' && $mlipuzi->mwenyeBc)
                                            <div class="small text-muted">Ya {{ $mlipuzi->mwenyeBc->jina }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mlipuzi->hali === 'blocked')
                                            <span class="badge bg-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>{{ $mlipuzi->simu }}</td>
                                    <td>{{ $mlipuzi->mkoa }}</td>
                                    <td>{{ $mlipuzi->wilaya }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.walipuaji.show', $mlipuzi) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                        <a href="{{ route('milipuko.walipuaji.kitambulisho', $mlipuzi) }}" class="btn btn-sm btn-outline-secondary">Kitambulisho</a>
                                        <a href="{{ route('milipuko.walipuaji.edit', $mlipuzi) }}" class="btn btn-sm btn-outline-warning">Hariri</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Hakuna walipuaji waliowekwa bado.</td>
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
