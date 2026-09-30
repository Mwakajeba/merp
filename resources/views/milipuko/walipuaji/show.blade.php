@extends('layouts.main')

@section('title', $mlipuzi->jina)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Walipuaji', 'url' => route('milipuko.walipuaji.index'), 'icon' => 'bx bx-user-check'],
            ['label' => $mlipuzi->jina, 'url' => '#', 'icon' => 'bx bx-user']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">{{ $mlipuzi->jina }}</h6>
            <div>
                <a href="{{ route('milipuko.walipuaji.kitambulisho', $mlipuzi) }}" class="btn btn-primary">Kitambulisho</a>
                <a href="{{ route('milipuko.walipuaji.makosa.create', $mlipuzi) }}" class="btn btn-outline-primary">Makosa</a>
                <a href="{{ route('milipuko.walipuaji.edit', $mlipuzi) }}" class="btn btn-warning">Hariri</a>
                <form action="{{ route('milipuko.walipuaji.destroy', $mlipuzi) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta mlipuaji huyu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Futa</button>
                </form>
            </div>
        </div>
        <hr />

        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Picha</h6>
                @if($mlipuzi->pichaUrl())
                    <img src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}" width="180" height="180" class="rounded object-fit-cover">
                @else
                    <p class="text-muted mb-0">Hakuna picha.</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Taarifa</h6>
                        <p class="mb-1"><strong>Jina:</strong> {{ $mlipuzi->jina }}</p>
                        <p class="mb-1"><strong>Hali:</strong>
                            @if($mlipuzi->hali === 'blocked')
                                <span class="badge bg-danger">Blocked</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </p>
                        <p class="mb-1"><strong>BC No.:</strong>
                            {{ $mlipuzi->bcInayotumika() ?: '—' }}
                            @if($mlipuzi->aina_ya_bc === 'mtu' && $mlipuzi->mwenyeBc)
                                <span class="text-muted">(ya {{ $mlipuzi->mwenyeBc->jina }})</span>
                            @else
                                <span class="text-muted">(yake)</span>
                            @endif
                        </p>
                        <p class="mb-1"><strong>Simu:</strong> {{ $mlipuzi->simu }}</p>
                        <p class="mb-1"><strong>Simu mbadala:</strong> {{ $mlipuzi->simu_mbadala ?: '—' }}</p>
                        <p class="mb-1"><strong>Mkoa:</strong> {{ $mlipuzi->mkoa }}</p>
                        <p class="mb-1"><strong>Wilaya:</strong> {{ $mlipuzi->wilaya }}</p>
                        <p class="mb-0"><strong>Eneo analoishi:</strong> {{ $mlipuzi->eneo ?: '—' }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Watu wa kuwasiliana nao</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Jina</th>
                                <th>Simu</th>
                                <th>Uhusiano</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mlipuzi->wawasiliani as $mtu)
                                <tr>
                                    <td>{{ $mtu->jina }}</td>
                                    <td>{{ $mtu->simu }}</td>
                                    <td>{{ $mtu->uhusiano }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">Hakuna watu wa kuwasiliana nao.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Makosa aliyoyafanya</h6>
                    <a href="{{ route('milipuko.walipuaji.makosa.create', $mlipuzi) }}" class="btn btn-sm btn-outline-primary">Ongeza kosa</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kosa</th>
                                <th>Maelezo ya adhabu</th>
                                <th>Hali</th>
                                <th>Barua</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mlipuzi->makosa as $rekodi)
                                <tr>
                                    <td>{{ $rekodi->kosa->kosa }}</td>
                                    <td>{{ $rekodi->maelezo_ya_adhabu ?: '—' }}</td>
                                    <td>
                                        @if($rekodi->hali === 'limekwisha')
                                            <span class="badge bg-success">Limekwisha</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Linaendelea</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rekodi->baruaUrl())
                                            <a href="{{ $rekodi->baruaUrl() }}" target="_blank">Fungua barua</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.walipuaji.makosa.edit', [$mlipuzi, $rekodi]) }}" class="btn btn-sm btn-outline-warning">Hariri</a>
                                        <form action="{{ route('milipuko.walipuaji.makosa.destroy', [$mlipuzi, $rekodi]) }}" method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta kosa hili?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Futa</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">Hakuna makosa yaliyorekodiwa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($mlipuzi->aina_ya_bc === 'yake')
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Watu wanaotumia BC No. yake</h6>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Jina</th>
                                    <th>Simu</th>
                                    <th>Hali</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mlipuzi->wanaotumiaBcYake as $mtumiaji)
                                    <tr>
                                        <td>{{ $mtumiaji->jina }}</td>
                                        <td>{{ $mtumiaji->simu }}</td>
                                        <td>
                                            @if($mtumiaji->hali === 'blocked')
                                                <span class="badge bg-danger">Blocked</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('milipuko.walipuaji.show', $mtumiaji) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">Hakuna mtu anayetumia BC No. hii.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Vibali alivyohusika</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Namba ya kibali</th>
                                <th>Duara No.</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                                <th>Jukumu</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vibali as $kibali)
                                <tr>
                                    <td>{{ $kibali->namba }}</td>
                                    <td>{{ $kibali->duara->namba ?? '—' }}</td>
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
                                    <td>{{ $kibali->jukumu }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('milipuko.vibali.show', $kibali) }}" class="btn btn-sm btn-outline-primary">Angalia</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted">Hajawahi kuhusika na kibali.</td>
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
