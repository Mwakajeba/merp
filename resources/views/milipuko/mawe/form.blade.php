@php
    $isEdit = isset($kibali);
    $duaraId = old('duara_id', $kibali->duara_id ?? '');
    $msimamiziId = old('msimamizi_id', $kibali->msimamizi_id ?? '');
    $aina = old('aina_ya_mzigo', $kibali->aina_ya_mzigo ?? 'mawe');
    $tarehe = old('tarehe', isset($kibali) ? $kibali->tarehe->format('Y-m-d') : now()->format('Y-m-d'));
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

<form action="{{ $isEdit ? route('milipuko.mawe.update', $kibali) : route('milipuko.mawe.store') }}" method="POST">
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
                <div class="kibali-kichwa">KIBALI CHA KUSAFIRISHA MZIGO KUTOKA MADUARANI KWENDA OFISINI</div>
            </div>
            <div class="kibali-namba">{{ $isEdit ? $kibali->namba : '—' }}</div>
        </div>
        @unless($isEdit)
            <p class="text-center text-muted small mb-3">Namba ya kibali itawekwa kiotomatiki baada ya kuhifadhi.</p>
        @endunless

        <div class="mb-3">
            <div class="kibali-label mb-2">Aina ya mzigo <span class="text-danger">*</span></div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="aina_ya_mzigo" id="aina-mawe" value="mawe" @checked($aina === 'mawe') required>
                <label class="form-check-label" for="aina-mawe">Mawe</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="aina_ya_mzigo" id="aina-chorongeo" value="chorongeo" @checked($aina === 'chorongeo') required>
                <label class="form-check-label" for="aina-chorongeo">Chorongeo</label>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label for="tarehe" class="form-label kibali-label">Tarehe <span class="text-danger">*</span></label>
                <input type="date" name="tarehe" id="tarehe" value="{{ $tarehe }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="duara_id" class="form-label kibali-label">Duara No. <span class="text-danger">*</span></label>
                <select name="duara_id" id="duara_id" class="form-select" required>
                    <option value="">Chagua duara</option>
                </select>
                <div class="form-text" id="duara-onyo">Inaonyesha maduara yaliyosajiliwa kuwa yamezalisha tarehe hii tu.</div>
            </div>
            <div class="col-md-3">
                <label for="idadi_ya_mifuko" class="form-label kibali-label">Idadi ya mifuko <span class="text-danger">*</span></label>
                <input type="number" name="idadi_ya_mifuko" id="idadi_ya_mifuko" min="1" step="1" value="{{ old('idadi_ya_mifuko', $kibali->idadi_ya_mifuko ?? '') }}" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="msimamizi_id" class="form-label kibali-label">Jina la msimamizi wa duara <span class="text-danger">*</span></label>
            <select name="msimamizi_id" id="msimamizi_id" class="form-select" required>
                <option value="">Chagua msimamizi</option>
            </select>
            <div id="msimamizi-mpya" class="border rounded p-3 mt-2 bg-light" hidden>
                <div class="form-text text-warning mb-2">Duara hili halina msimamizi. Andika hapa, atasajiliwa kwenye duara hili.</div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label for="msimamizi_jina" class="form-label kibali-label">Jina</label>
                        <input type="text" name="msimamizi_jina" id="msimamizi_jina" value="{{ old('msimamizi_jina') }}" class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label for="msimamizi_simu" class="form-label kibali-label">Simu</label>
                        <input type="text" name="msimamizi_simu" id="msimamizi_simu" value="{{ old('msimamizi_simu') }}" class="form-control" maxlength="30">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-0">
            <label for="katibu" class="form-label kibali-label">Jina la katibu wa idara <span class="text-danger">*</span></label>
            <input type="text" id="katibu" value="{{ auth()->user()->name }}" class="form-control bg-light" readonly>
            <div class="form-text">Jina la mtu aliyeingia kwenye mfumo. Sahihi inawekwa kwenye karatasi baada ya kuchapisha.</div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ $isEdit ? route('milipuko.mawe.show', $kibali) : route('milipuko.mawe.index') }}" class="btn btn-secondary">Rudi</a>
        <button type="submit" class="btn btn-danger">{{ $isEdit ? 'Hifadhi mabadiliko' : 'Hifadhi kibali' }}</button>
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
        const $tarehe = $('#tarehe');
        const $duara = $('#duara_id');
        const $msimamizi = $('#msimamizi_id');
        const andika = document.getElementById('msimamizi-mpya');
        const duaraOnyo = document.getElementById('duara-onyo');
        let maduara = [];
        let duaraIliyochaguliwa = @json((string) $duaraId);
        let msimamiziAliyechaguliwa = @json((string) $msimamiziId);

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
            const yaSasa = wekaUpya ? '' : String(msimamiziAliyechaguliwa || $msimamizi.val() || '');
            const orodha = duara ? duara.wasimamizi : [];

            $msimamizi.empty();
            $msimamizi.append(new Option('Chagua msimamizi', '', false, yaSasa === ''));
            orodha.forEach(function (msimamizi) {
                const amechaguliwa = String(msimamizi.id) === yaSasa;
                $msimamizi.append(new Option(jinaLaMsimamizi(msimamizi), msimamizi.id, amechaguliwa, amechaguliwa));
            });

            const hakuna = !!duara && orodha.length === 0;
            andika.hidden = !hakuna;
            $msimamizi.prop('required', !hakuna).prop('disabled', hakuna);
            $('#msimamizi_jina, #msimamizi_simu').prop('required', hakuna).prop('disabled', !hakuna);
            chaguo($msimamizi, 'Chagua msimamizi');
            $msimamizi.next('.select2-container').toggle(!hakuna);
            msimamiziAliyechaguliwa = '';
        }

        function jazaMaduara() {
            const tarehe = $tarehe.val();
            if (!tarehe) return;
            $.get(@json(route('milipuko.mawe.maduara')), { tarehe: tarehe }).done(function (jibu) {
                maduara = jibu.maduara || [];
                const yaSasa = String(duaraIliyochaguliwa || '');
                $duara.empty();
                $duara.append(new Option('Chagua duara', '', false, yaSasa === ''));
                maduara.forEach(function (duara) {
                    const amechaguliwa = String(duara.id) === yaSasa;
                    $duara.append(new Option(duara.namba, duara.id, amechaguliwa, amechaguliwa));
                });
                duaraOnyo.textContent = maduara.length
                    ? 'Maduara yaliyosajiliwa kuwa yamezalisha tarehe hii.'
                    : 'Hakuna duara lililosajiliwa kuwa limezalisha tarehe hii. Sajili kwanza kwenye Maduara yaliyozalisha.';
                chaguo($duara, 'Chagua duara');
                duaraIliyochaguliwa = '';
                jazaWasimamizi(false);
            });
        }

        $duara.on('change', function () {
            jazaWasimamizi(true);
        });
        $tarehe.on('change', jazaMaduara);
        jazaMaduara();
    });
</script>
@endpush
