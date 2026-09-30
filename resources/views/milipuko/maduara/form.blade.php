@php
    $isEdit = isset($duara);
    $wasimamiziRows = old('wasimamizi');
    $wanachamaRows = old('wanachama');

    if ($wasimamiziRows === null) {
        $wasimamiziRows = $isEdit
            ? $duara->wasimamizi->map(fn ($msimamizi) => ['jina' => $msimamizi->jina, 'simu' => $msimamizi->simu])->all()
            : [];
    }

    if ($wanachamaRows === null) {
        $wanachamaRows = $isEdit
            ? $duara->wanachama->map(fn ($mwanachama) => [
                'jina' => $mwanachama->jina,
                'simu' => $mwanachama->simu,
                'hisa' => $mwanachama->pivot->hisa,
            ])->all()
            : [];
    }

    if (count($wasimamiziRows) === 0) {
        $wasimamiziRows = [['jina' => '', 'simu' => '']];
    }

    if (count($wanachamaRows) === 0) {
        $wanachamaRows = [['jina' => '', 'simu' => '', 'hisa' => '']];
    }
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $isEdit ? route('milipuko.maduara.update', $duara) : route('milipuko.maduara.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="namba" class="form-label">Namba ya duara <span class="text-danger">*</span></label>
            <input type="text" name="namba" id="namba" value="{{ old('namba', $duara->namba ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-8 mb-3">
            <label for="maelezo" class="form-label">Maelezo</label>
            <textarea name="maelezo" id="maelezo" class="form-control" rows="2">{{ old('maelezo', $duara->maelezo ?? '') }}</textarea>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Wasimamizi wa duara</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="ongeza-msimamizi">
            <i class="bx bx-plus"></i> Ongeza msimamizi
        </button>
    </div>
    <p class="text-muted small">Jina na simu. Msimamizi anaunganishwa na duara kupitia jedwali la pivot.</p>
    <div id="wasimamizi-list">
        @foreach($wasimamiziRows as $index => $row)
            <div class="row g-2 align-items-end mb-2 person-row">
                <div class="col-md-5">
                    <label class="form-label">Jina</label>
                    <input type="text" name="wasimamizi[{{ $index }}][jina]" value="{{ $row['jina'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Simu</label>
                    <input type="text" name="wasimamizi[{{ $index }}][simu]" value="{{ $row['simu'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
                </div>
            </div>
        @endforeach
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Wanachama wa duara</h6>
        <button type="button" class="btn btn-sm btn-outline-success" id="ongeza-mwanachama">
            <i class="bx bx-plus"></i> Ongeza mwanachama
        </button>
    </div>
    <p class="text-muted small">Jina, simu, na hisa katika duara. Hisa si lazima. Mwanachama anaunganishwa na duara kupitia jedwali la pivot.</p>
    <div id="wanachama-list">
        @foreach($wanachamaRows as $index => $row)
            <div class="row g-2 align-items-end mb-2 person-row">
                <div class="col-md-4">
                    <label class="form-label">Jina</label>
                    <input type="text" name="wanachama[{{ $index }}][jina]" value="{{ $row['jina'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Simu</label>
                    <input type="text" name="wanachama[{{ $index }}][simu]" value="{{ $row['simu'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hisa <span class="text-muted">(si lazima)</span></label>
                    <input type="number" step="0.01" min="0" name="wanachama[{{ $index }}][hisa]" value="{{ $row['hisa'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('milipuko.maduara.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Rudi
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Hifadhi mabadiliko' : 'Sajili duara' }}
            </button>
        </div>
    </div>
</form>

<template id="msimamizi-template">
    <div class="row g-2 align-items-end mb-2 person-row">
        <div class="col-md-5">
            <label class="form-label">Jina</label>
            <input type="text" data-name="jina" class="form-control">
        </div>
        <div class="col-md-5">
            <label class="form-label">Simu</label>
            <input type="text" data-name="simu" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
        </div>
    </div>
</template>

<template id="mwanachama-template">
    <div class="row g-2 align-items-end mb-2 person-row">
        <div class="col-md-4">
            <label class="form-label">Jina</label>
            <input type="text" data-name="jina" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Simu</label>
            <input type="text" data-name="simu" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hisa <span class="text-muted">(si lazima)</span></label>
            <input type="number" step="0.01" min="0" data-name="hisa" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
        </div>
    </div>
</template>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function reindexRows(list, prefix) {
        list.querySelectorAll('.person-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name], [name]').forEach(function (input) {
                var field = input.getAttribute('data-name') || (input.name.match(/\[(\w+)\]$/) || [])[1];
                if (!field) {
                    return;
                }
                input.name = prefix + '[' + index + '][' + field + ']';
            });
        });
    }

    function addRow(templateId, listId, prefix) {
        var template = document.getElementById(templateId);
        var list = document.getElementById(listId);
        list.appendChild(template.content.cloneNode(true));
        reindexRows(list, prefix);
    }

    document.getElementById('ongeza-msimamizi').addEventListener('click', function () {
        addRow('msimamizi-template', 'wasimamizi-list', 'wasimamizi');
    });

    document.getElementById('ongeza-mwanachama').addEventListener('click', function () {
        addRow('mwanachama-template', 'wanachama-list', 'wanachama');
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.ondoa-row');
        if (!button) {
            return;
        }
        var row = button.closest('.person-row');
        var list = row.parentElement;
        var prefix = list.id === 'wasimamizi-list' ? 'wasimamizi' : 'wanachama';
        row.remove();
        reindexRows(list, prefix);
    });
</script>
@endpush
