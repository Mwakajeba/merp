@extends('layouts.main')

@section('title', 'Maduara yaliyozalisha')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Maduara yaliyozalisha', 'url' => '#', 'icon' => 'bx bx-calendar-check']
        ]" />

        <h6 class="mb-0 text-uppercase">MADUARA YALIYOZALISHA</h6>
        <hr />

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="text-primary">Sajili duara</h6>
                <p class="text-muted small">Weka tarehe na duara lililozalisha. Vibali vya kusafirisha vya tarehe hiyo vitavuta duara hizi tu.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('milipuko.uzalishaji.store') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label for="tarehe" class="form-label">Tarehe</label>
                        <input type="date" name="tarehe" id="tarehe" value="{{ old('tarehe', now()->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="duara_id" class="form-label">Duara</label>
                        <select name="duara_id" id="duara_id" class="form-select select2-single" required>
                            <option value="">Chagua duara</option>
                            @foreach($maduara as $duara)
                                <option value="{{ $duara->id }}" @selected((string) old('duara_id') === (string) $duara->id)>{{ $duara->namba }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Sajili</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tarehe</th>
                                <th>Duara No.</th>
                                <th>Mifuko ya mawe</th>
                                <th>Mifuko ya chorongeo</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekodi as $rekodiMoja)
                                <tr>
                                    <td>{{ $rekodiMoja->tarehe->format('d/m/Y') }}</td>
                                    <td>{{ $rekodiMoja->duara->namba ?? '—' }}</td>
                                    <td>{{ (int) $rekodiMoja->mifuko_ya_mawe }}</td>
                                    <td>{{ (int) $rekodiMoja->mifuko_ya_chorongeo }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('milipuko.uzalishaji.destroy', $rekodiMoja) }}" method="POST" class="d-inline" onsubmit="return confirm('Ondoa duara hili kwenye uzalishaji wa tarehe hii?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Ondoa</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Hakuna maduara yaliyosajiliwa kuwa yamezalisha.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $rekodi->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
