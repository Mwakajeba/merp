@php
    $isEdit = isset($mlipuzi);
    $wawasilianiRows = old('wawasiliani');

    if ($wawasilianiRows === null) {
        $wawasilianiRows = $isEdit
            ? $mlipuzi->wawasiliani->map(fn ($mtu) => [
                'jina' => $mtu->jina,
                'simu' => $mtu->simu,
                'uhusiano' => $mtu->uhusiano,
            ])->all()
            : [];
    }

    if (count($wawasilianiRows) === 0) {
        $wawasilianiRows = [['jina' => '', 'simu' => '', 'uhusiano' => '']];
    }

    $mkoaUliochaguliwa = old('mkoa', $mlipuzi->mkoa ?? '');
    $wilayaIliyochaguliwa = old('wilaya', $mlipuzi->wilaya ?? '');
    $ainaYaBc = old('aina_ya_bc', $mlipuzi->aina_ya_bc ?? 'yake');
    $hali = old('hali', $mlipuzi->hali ?? 'active');
    $mwenyeBcId = old('bc_ya_mlipuzi_id', $mlipuzi->bc_ya_mlipuzi_id ?? '');
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

<form action="{{ $isEdit ? route('milipuko.walipuaji.update', $mlipuzi) : route('milipuko.walipuaji.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="jina" class="form-label">Jina <span class="text-danger">*</span></label>
            <input type="text" name="jina" id="jina" value="{{ old('jina', $mlipuzi->jina ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="hali" class="form-label">Hali <span class="text-danger">*</span></label>
            <select name="hali" id="hali" class="form-select" required>
                <option value="active" @selected($hali === 'active')>Active</option>
                <option value="blocked" @selected($hali === 'blocked')>Blocked</option>
            </select>
        </div>
        <div class="col-12 mb-2">
            <label class="form-label d-block">BC No. <span class="text-danger">*</span></label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="aina_ya_bc" id="aina-yake" value="yake" @checked($ainaYaBc === 'yake')>
                <label class="form-check-label" for="aina-yake">Anatumia BC No. yake</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="aina_ya_bc" id="aina-mtu" value="mtu" @checked($ainaYaBc === 'mtu')>
                <label class="form-check-label" for="aina-mtu">Anatumia BC No. ya mtu</label>
            </div>
        </div>
        <div class="col-md-6 mb-3" id="bc-yake" @if($ainaYaBc !== 'yake') hidden @endif>
            <label for="bc_no" class="form-label">BC No. yake</label>
            <input type="text" name="bc_no" id="bc_no" value="{{ old('bc_no', $mlipuzi->bc_no ?? '') }}" class="form-control" @disabled($ainaYaBc !== 'yake')>
        </div>
        <div class="col-md-6 mb-3" id="bc-mtu" @if($ainaYaBc !== 'mtu') hidden @endif>
            <label for="bc_ya_mlipuzi_id" class="form-label">BC No. ya mtu mwenye BC inayojitegemea</label>
            <select name="bc_ya_mlipuzi_id" id="bc_ya_mlipuzi_id" class="form-select" @disabled($ainaYaBc !== 'mtu')>
                <option value="">Chagua BC No. na jina</option>
                @foreach($wenyeBc as $mwenye)
                    <option value="{{ $mwenye->id }}" @selected((string) $mwenyeBcId === (string) $mwenye->id)>
                        {{ $mwenye->bc_no }} — {{ $mwenye->jina }}
                    </option>
                @endforeach
            </select>
            @if($wenyeBc->isEmpty())
                <div class="form-text">Hakuna walipuaji wenye BC No. zinazojitegemea bado.</div>
            @endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="simu" class="form-label">Simu <span class="text-danger">*</span></label>
            <input type="text" name="simu" id="simu" value="{{ old('simu', $mlipuzi->simu ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="simu_mbadala" class="form-label">Simu mbadala</label>
            <input type="text" name="simu_mbadala" id="simu_mbadala" value="{{ old('simu_mbadala', $mlipuzi->simu_mbadala ?? '') }}" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mkoa" class="form-label">Mkoa <span class="text-danger">*</span></label>
            <select name="mkoa" id="mkoa" class="form-select" required>
                <option value="">Chagua mkoa</option>
                @foreach($mikoa as $mkoa)
                    <option value="{{ $mkoa }}" @selected($mkoaUliochaguliwa === $mkoa)>{{ $mkoa }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="wilaya" class="form-label">Wilaya <span class="text-danger">*</span></label>
            <select name="wilaya" id="wilaya" class="form-select" required>
                <option value="">Chagua mkoa kwanza</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="picha" class="form-label">Picha</label>
            <input type="file" name="picha" id="picha" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="mt-2" id="picha-preview">
                @if($isEdit && $mlipuzi->pichaUrl())
                    <img src="{{ $mlipuzi->pichaUrl() }}" alt="{{ $mlipuzi->jina }}" width="96" height="96" class="rounded object-fit-cover">
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="eneo" class="form-label">Maelezo ya eneo analoishi</label>
            <textarea name="eneo" id="eneo" class="form-control" rows="4">{{ old('eneo', $mlipuzi->eneo ?? '') }}</textarea>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Watu wa kuwasiliana nao</h6>
        <button type="button" class="btn btn-sm btn-outline-warning" id="ongeza-mwasiliano">
            <i class="bx bx-plus"></i> Ongeza mtu
        </button>
    </div>
    <p class="text-muted small">Watu wa kuwasiliana nao endapo lolote likimkuta mlipuaji. Weka jina, simu na uhusiano.</p>
    <div id="wawasiliani-list" data-prefix="wawasiliani">
        @foreach($wawasilianiRows as $index => $row)
            <div class="row g-2 align-items-end mb-2 repeat-row">
                <div class="col-md-4">
                    <label class="form-label">Jina</label>
                    <input type="text" name="wawasiliani[{{ $index }}][jina]" value="{{ $row['jina'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Simu</label>
                    <input type="text" name="wawasiliani[{{ $index }}][simu]" value="{{ $row['simu'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Uhusiano</label>
                    <input type="text" name="wawasiliani[{{ $index }}][uhusiano]" value="{{ $row['uhusiano'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('milipuko.walipuaji.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Rudi
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-warning">
                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Hifadhi mabadiliko' : 'Sajili mlipuaji' }}
            </button>
        </div>
    </div>
</form>

<template id="mwasiliano-template">
    <div class="row g-2 align-items-end mb-2 repeat-row">
        <div class="col-md-4">
            <label class="form-label">Jina</label>
            <input type="text" data-name="jina" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Simu</label>
            <input type="text" data-name="simu" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Uhusiano</label>
            <input type="text" data-name="uhusiano" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 ondoa-row">Ondoa</button>
        </div>
    </div>
</template>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    const wilayaKwaMkoa = @json($wilayaKwaMkoa);
    let wilayaYaKumbuka = @json($wilayaIliyochaguliwa);
    const mkoaSelect = document.getElementById('mkoa');
    const wilayaSelect = document.getElementById('wilaya');

    function jazaWilaya() {
        const mkoa = mkoaSelect.value;
        wilayaSelect.innerHTML = '';
        const kichwa = document.createElement('option');
        kichwa.value = '';
        kichwa.textContent = mkoa ? 'Chagua wilaya' : 'Chagua mkoa kwanza';
        wilayaSelect.appendChild(kichwa);

        (wilayaKwaMkoa[mkoa] || []).forEach(function (wilaya) {
            const option = document.createElement('option');
            option.value = wilaya;
            option.textContent = wilaya;
            if (wilaya === wilayaYaKumbuka) {
                option.selected = true;
            }
            wilayaSelect.appendChild(option);
        });
    }

    mkoaSelect.addEventListener('change', function () {
        wilayaYaKumbuka = '';
        jazaWilaya();
    });
    jazaWilaya();

    function onyeshaBc() {
        const anatumiaYake = document.getElementById('aina-yake').checked;
        document.getElementById('bc-yake').hidden = !anatumiaYake;
        document.getElementById('bc-mtu').hidden = anatumiaYake;
        document.getElementById('bc_no').disabled = !anatumiaYake;
        document.getElementById('bc_ya_mlipuzi_id').disabled = anatumiaYake;
    }

    document.querySelectorAll('input[name="aina_ya_bc"]').forEach(function (radio) {
        radio.addEventListener('change', onyeshaBc);
    });
    onyeshaBc();

    document.getElementById('picha').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('picha-preview');
        if (!file) {
            return;
        }
        const reader = new FileReader();
        reader.onload = function () {
            preview.innerHTML = '<img src="' + reader.result + '" width="96" height="96" class="rounded object-fit-cover" alt="Picha">';
        };
        reader.readAsDataURL(file);
    });

    function reindexRows(list) {
        const prefix = list.getAttribute('data-prefix');
        list.querySelectorAll('.repeat-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name], [name]').forEach(function (input) {
                var field = input.getAttribute('data-name') || (input.name.match(/\[(\w+)\]$/) || [])[1];
                if (!field) {
                    return;
                }
                input.name = prefix + '[' + index + '][' + field + ']';
            });
        });
    }

    document.getElementById('ongeza-mwasiliano').addEventListener('click', function () {
        const list = document.getElementById('wawasiliani-list');
        list.appendChild(document.getElementById('mwasiliano-template').content.cloneNode(true));
        reindexRows(list);
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.ondoa-row');
        const list = button ? button.closest('[data-prefix]') : null;
        if (!list) {
            return;
        }
        button.closest('.repeat-row').remove();
        reindexRows(list);
    });
</script>
@endpush
