@php
    $isEdit = isset($kompresa);
    $mkoaUliochaguliwa = old('mkoa', $kompresa->mkoa ?? '');
    $wilayaIliyochaguliwa = old('wilaya', $kompresa->wilaya ?? '');
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

<form action="{{ $isEdit ? route('milipuko.kompresa.update', $kompresa) : route('milipuko.kompresa.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="jina" class="form-label">Jina la mmiliki <span class="text-danger">*</span></label>
            <input type="text" name="jina" id="jina" value="{{ old('jina', $kompresa->jina ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="namba" class="form-label">Namba ya kompresa <span class="text-danger">*</span></label>
            <input type="text" name="namba" id="namba" value="{{ old('namba', $kompresa->namba ?? '') }}" class="form-control" required>
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
            <label for="mtaa" class="form-label">Mtaa</label>
            <input type="text" name="mtaa" id="mtaa" value="{{ old('mtaa', $kompresa->mtaa ?? '') }}" class="form-control">
            <div class="form-text">Si lazima.</div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="simu" class="form-label">Simu <span class="text-danger">*</span></label>
            <input type="text" name="simu" id="simu" value="{{ old('simu', $kompresa->simu ?? '') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="simu_mbadala" class="form-label">Simu mbadala</label>
            <input type="text" name="simu_mbadala" id="simu_mbadala" value="{{ old('simu_mbadala', $kompresa->simu_mbadala ?? '') }}" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="picha" class="form-label">Picha ya mmiliki @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
            <input type="file" name="picha" id="picha" class="form-control" accept="image/jpeg,image/png,image/webp" @unless($isEdit) required @endunless>
            <div class="mt-2" id="picha-preview">
                @if($isEdit && $kompresa->pichaUrl())
                    <img src="{{ $kompresa->pichaUrl() }}" alt="{{ $kompresa->jina }}" width="96" height="96" class="rounded object-fit-cover">
                @endif
            </div>
        </div>
        <div class="col-12 mb-3">
            <label for="maelezo" class="form-label">Maelezo</label>
            <textarea name="maelezo" id="maelezo" class="form-control" rows="4">{{ old('maelezo', $kompresa->maelezo ?? '') }}</textarea>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('milipuko.kompresa.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Rudi
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-dark">
                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Hifadhi mabadiliko' : 'Sajili kompresa' }}
            </button>
        </div>
    </div>
</form>

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

    document.getElementById('picha').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('picha-preview');
        preview.innerHTML = '';
        if (!file) {
            return;
        }
        const img = document.createElement('img');
        img.width = 96;
        img.height = 96;
        img.className = 'rounded object-fit-cover';
        img.alt = 'Picha';
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
    });
</script>
@endpush
