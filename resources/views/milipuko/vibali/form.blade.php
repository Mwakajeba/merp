@php
    $isEdit = isset($kibali);
    $hali = old('hali', $kibali->hali ?? 'uzalishaji');
    $duaraId = old('duara_id', $kibali->duara_id ?? '');
    $msimamiziId = old('msimamizi_id', $kibali->msimamizi_id ?? '');
    $mlipuziId = old('mlipuzi_id', $kibali->mlipuzi_id ?? '');
    $duaraLililochaguliwa = $maduara->firstWhere('id', (int) $duaraId);

    $wachorongajiWaliochaguliwa = old('wachorongaji');
    if ($wachorongajiWaliochaguliwa === null) {
        $wachorongajiWaliochaguliwa = [];
        if ($isEdit) {
            foreach ($kibali->wachorongaji as $mtu) {
                $wachorongajiWaliochaguliwa[$mtu->pivot->nafasi] = $mtu->id;
            }
        }
    }

    $mlipuziAliyechaguliwa = $walipuaji->firstWhere('id', (int) $mlipuziId);
    $bcInayoonekana = $mlipuziAliyechaguliwa?->bcInayotumika() ?: '—';

    $maduaraKwaJs = $maduara->map(fn ($duara) => [
        'id' => $duara->id,
        'wasimamizi' => $duara->wasimamizi->map(fn ($msimamizi) => [
            'id' => $msimamizi->id,
            'jina' => $msimamizi->jina,
            'simu' => $msimamizi->simu,
        ])->values(),
    ])->values();

    $walipuajiKwaJs = $walipuaji->map(fn ($mtu) => [
        'id' => $mtu->id,
        'bc' => $mtu->bcInayotumika(),
    ])->values();
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

<form action="{{ $isEdit ? route('milipuko.vibali.update', $kibali) : route('milipuko.vibali.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="kibali-karatasi">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1 text-center">
                <div class="kibali-ofisi">BLASTING OFFICE</div>
                <div class="kibali-kampuni">{{ $company->name ?? 'M-ERP' }}</div>
                @if(filled($company->address ?? null))
                    <div class="kibali-anuani">{{ $company->address }}</div>
                @endif
                <div class="kibali-kichwa">KIBALI CHA KUCHORONGA MWAMBA NA KULIPUA</div>
            </div>
            <div class="kibali-namba">{{ $isEdit ? $kibali->namba : '—' }}</div>
        </div>
        @unless($isEdit)
            <p class="text-center text-muted small mb-3">Namba ya kibali itawekwa kiotomatiki baada ya kuhifadhi.</p>
        @endunless

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="hali" class="form-label kibali-label">Hali <span class="text-danger">*</span></label>
                <select name="hali" id="hali" class="form-select select2-single" required>
                    <option value="uzalishaji" @selected($hali === 'uzalishaji')>Uzalishaji</option>
                    <option value="ufreshiaji" @selected($hali === 'ufreshiaji')>Ufreshiaji</option>
                    <option value="ufukuziaji" @selected($hali === 'ufukuziaji')>Ufukuziaji</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="duara_id" class="form-label kibali-label">Duara No. <span class="text-danger">*</span></label>
                <select name="duara_id" id="duara_id" class="form-select select2-single" required>
                    <option value="">Chagua duara</option>
                    @foreach($maduara as $duara)
                        <option value="{{ $duara->id }}" @selected((string) $duaraId === (string) $duara->id)>{{ $duara->namba }}</option>
                    @endforeach
                </select>
                @if($maduara->isEmpty())
                    <div class="form-text">Hakuna maduara bado. Sajili duara kwanza.</div>
                @endif
            </div>
            <div class="col-md-4">
                <label for="tarehe" class="form-label kibali-label">Tarehe <span class="text-danger">*</span></label>
                <input type="date" name="tarehe" id="tarehe" value="{{ old('tarehe', isset($kibali) ? $kibali->tarehe->format('Y-m-d') : now()->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="idadi_ya_matundu" class="form-label kibali-label">Idadi ya matundu <span class="text-danger">*</span></label>
                <input type="number" name="idadi_ya_matundu" id="idadi_ya_matundu" min="0" step="1" value="{{ old('idadi_ya_matundu', $kibali->idadi_ya_matundu ?? '') }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label kibali-label">BC No.</label>
                <div class="form-control bg-light" id="bc-no">{{ $bcInayoonekana }}</div>
                <div class="form-text">Inatoka kwa mlipuaji aliyechaguliwa.</div>
            </div>
        </div>

        <div class="mb-3">
            <label for="msimamizi_id" class="form-label kibali-label">Jina la msimamizi wa duara <span class="text-danger">*</span></label>
            <select name="msimamizi_id" id="msimamizi_id" class="form-select select2-single" required>
                <option value="">Chagua msimamizi</option>
                @foreach($duaraLililochaguliwa?->wasimamizi ?? [] as $msimamizi)
                    <option value="{{ $msimamizi->id }}" @selected((string) $msimamiziId === (string) $msimamizi->id)>
                        {{ $msimamizi->jina }} ({{ $msimamizi->simu }})
                    </option>
                @endforeach
            </select>
            <div class="form-text text-warning" id="msimamizi-onyo" @if(($duaraLililochaguliwa?->wasimamizi->count() ?? 1) > 0) hidden @endif>
                Duara hili halina msimamizi. Ongeza msimamizi kwenye duara kwanza.
            </div>
        </div>

        <div class="mb-3">
            <label for="mlipuzi_id" class="form-label kibali-label">Jina la mlipuaji (blasta) <span class="text-danger">*</span></label>
            <select name="mlipuzi_id" id="mlipuzi_id" class="form-select select2-single" required>
                <option value="">Chagua mlipuaji ambaye hajafungwa</option>
                @foreach($walipuaji as $mtu)
                    @if($mtu->hali === 'blocked' && (string) $mtu->id !== (string) $mlipuziId)
                        @continue
                    @endif
                    <option value="{{ $mtu->id }}" @selected((string) $mlipuziId === (string) $mtu->id)>
                        {{ $mtu->jina }}@if($mtu->bcInayotumika()) — {{ $mtu->bcInayotumika() }}@endif
                        @if($mtu->hali === 'blocked') (Blocked) @endif
                    </option>
                @endforeach
            </select>
            @if($walipuaji->where('hali', 'active')->isEmpty())
                <div class="form-text">Hakuna walipuaji active bado.</div>
            @endif
        </div>

        <div class="mb-3">
            <div class="kibali-label mb-2">Majina ya wachorongaji</div>
            <p class="form-text mt-0 mb-2">Chagua kutoka walipuaji walio active.</p>
            @for($nafasi = 1; $nafasi <= 5; $nafasi++)
                @php $aliyechaguliwa = (string) ($wachorongajiWaliochaguliwa[$nafasi] ?? ''); @endphp
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-auto kibali-label" style="width: 2rem;">{{ $nafasi }}.</div>
                    <div class="col">
                        <select name="wachorongaji[{{ $nafasi }}]" id="mchorongaji-{{ $nafasi }}" class="form-select select2-single mchorongaji-select" data-nafasi="{{ $nafasi }}">
                            <option value="">Chagua mchorongaji</option>
                            @foreach($walipuaji as $mtu)
                                @php $ndioHuyu = $aliyechaguliwa === (string) $mtu->id; @endphp
                                @if($mtu->hali === 'blocked' && ! $ndioHuyu)
                                    @continue
                                @endif
                                <option value="{{ $mtu->id }}" @selected($ndioHuyu)>
                                    {{ $mtu->jina }}@if($mtu->bcInayotumika()) — {{ $mtu->bcInayotumika() }}@endif
                                    @if($mtu->hali === 'blocked') (Blocked) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endfor
        </div>

        <div class="mb-3">
            @php $aina = old('aina_ya_mlipuko', $kibali->aina_ya_mlipuko ?? ''); @endphp
            <div class="kibali-label mb-2">Aina ya mlipuko <span class="text-danger">*</span></div>
            <div class="d-flex flex-wrap gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="aina_ya_mlipuko" id="aina-cotex" value="cotex" @checked($aina === 'cotex') required>
                    <label class="form-check-label" for="aina-cotex">COTEX</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="aina_ya_mlipuko" id="aina-dull-fuse" value="dull_fuse" @checked($aina === 'dull_fuse') required>
                    <label class="form-check-label" for="aina-dull-fuse">DULL FUSE</label>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="msimamizi_wa_idara" class="form-label kibali-label">Msimamizi wa idara (inspector) <span class="text-danger">*</span></label>
                <input type="text" name="msimamizi_wa_idara" id="msimamizi_wa_idara" value="{{ old('msimamizi_wa_idara', $kibali->msimamizi_wa_idara ?? '') }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="katibu" class="form-label kibali-label">Imethibitishwa na katibu <span class="text-danger">*</span></label>
                <input type="text" id="katibu" value="{{ auth()->user()->name }}" class="form-control bg-light" readonly>
                <div class="form-text">Jina la mtu aliyeingia kwenye mfumo.</div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ $isEdit ? route('milipuko.vibali.show', $kibali) : route('milipuko.vibali.index') }}" class="btn btn-secondary">Rudi</a>
        <button type="submit" class="btn btn-success">{{ $isEdit ? 'Hifadhi mabadiliko' : 'Hifadhi kibali' }}</button>
    </div>
</form>

@push('styles')
<style>
    .kibali-karatasi {
        border: 2px solid #1a4f8b;
        border-radius: 4px;
        padding: 1.25rem 1.25rem 1.5rem;
        background: #fff;
    }
    .kibali-ofisi {
        color: #c0392b;
        font-weight: 700;
        letter-spacing: 0.04em;
    }
    .kibali-kampuni,
    .kibali-anuani {
        color: #1a4f8b;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .kibali-kichwa {
        color: #9b2d6b;
        font-weight: 700;
        margin-top: 0.35rem;
    }
    .kibali-namba {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        min-width: 4.5rem;
        text-align: right;
    }
    .kibali-label {
        color: #1a4f8b;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    $(function () {
        const maduara = @json($maduaraKwaJs);
        const walipuaji = @json($walipuajiKwaJs);
        const $duara = $('#duara_id');
        const $msimamizi = $('#msimamizi_id');
        const $mlipuzi = $('#mlipuzi_id');
        const bcBox = document.getElementById('bc-no');
        const onyo = document.getElementById('msimamizi-onyo');

        function jinaLaMsimamizi(msimamizi) {
            return msimamizi.simu ? msimamizi.jina + ' (' + msimamizi.simu + ')' : msimamizi.jina;
        }

        function chaguo(select, placeholder) {
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            select.select2({
                placeholder: placeholder,
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        function jazaWasimamizi(wekaUpya) {
            const duara = maduara.find(function (item) {
                return String(item.id) === String($duara.val());
            });
            const yaSasa = wekaUpya ? '' : String($msimamizi.val() || '');
            const orodha = duara ? duara.wasimamizi : [];

            $msimamizi.empty();
            $msimamizi.append(new Option('Chagua msimamizi', '', false, yaSasa === ''));
            orodha.forEach(function (msimamizi) {
                const amechaguliwa = String(msimamizi.id) === yaSasa;
                $msimamizi.append(new Option(jinaLaMsimamizi(msimamizi), msimamizi.id, amechaguliwa, amechaguliwa));
            });

            onyo.hidden = !duara || orodha.length > 0;
            chaguo($msimamizi, 'Chagua msimamizi');
        }

        function onyeshaBc() {
            const mtu = walipuaji.find(function (item) {
                return String(item.id) === String($mlipuzi.val());
            });
            bcBox.textContent = mtu && mtu.bc ? mtu.bc : '—';
        }

        $duara.on('change', function () {
            jazaWasimamizi(true);
        });
        $mlipuzi.on('change', onyeshaBc);
        jazaWasimamizi(false);
        onyeshaBc();
    });
</script>
@endpush
