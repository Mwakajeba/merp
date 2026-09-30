@extends('layouts.main')

@php
    $isEdit = $rekodi !== null;
    $hali = old('hali', $rekodi->hali ?? 'linaendelea');
@endphp

@section('title', $isEdit ? 'Hariri kosa' : 'Ongeza kosa')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Walipuaji', 'url' => route('milipuko.walipuaji.index'), 'icon' => 'bx bx-user-check'],
            ['label' => $mlipuzi->jina, 'url' => route('milipuko.walipuaji.show', $mlipuzi), 'icon' => 'bx bx-user'],
            ['label' => $isEdit ? 'Hariri kosa' : 'Ongeza kosa', 'url' => '#', 'icon' => 'bx bx-error']
        ]" />

        <h6 class="mb-0 text-uppercase">{{ $isEdit ? 'Hariri kosa' : 'Ongeza kosa' }} — {{ $mlipuzi->jina }}</h6>
        <hr />

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ $isEdit ? route('milipuko.walipuaji.makosa.update', [$mlipuzi, $rekodi]) : route('milipuko.walipuaji.makosa.store', $mlipuzi) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <datalist id="makosa-yaliyopo">
                        @foreach($makosaYaliyopo as $kosaLililopo)
                            <option value="{{ $kosaLililopo }}"></option>
                        @endforeach
                    </datalist>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kosa" class="form-label">Kosa <span class="text-danger">*</span></label>
                            <input type="text" name="kosa" id="kosa" value="{{ old('kosa', $rekodi->kosa->kosa ?? '') }}" class="form-control" list="makosa-yaliyopo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hali" class="form-label">Hali ya kosa <span class="text-danger">*</span></label>
                            <select name="hali" id="hali" class="form-select" required>
                                <option value="linaendelea" @selected($hali === 'linaendelea')>Linaendelea</option>
                                <option value="limekwisha" @selected($hali === 'limekwisha')>Limekwisha</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="maelezo_ya_adhabu" class="form-label">Maelezo ya adhabu</label>
                            <textarea name="maelezo_ya_adhabu" id="maelezo_ya_adhabu" class="form-control" rows="3">{{ old('maelezo_ya_adhabu', $rekodi->maelezo_ya_adhabu ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="barua" class="form-label">Barua</label>
                            <input type="file" name="barua" id="barua" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            @if($isEdit && $rekodi->baruaUrl())
                                <a href="{{ $rekodi->baruaUrl() }}" target="_blank" class="small">Barua iliyopo</a>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <a href="{{ route('milipuko.walipuaji.show', $mlipuzi) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Rudi
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Hifadhi mabadiliko' : 'Hifadhi kosa' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
