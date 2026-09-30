@extends('layouts.main')

@section('title', 'Taarifa ya milipuko')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Taarifa', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <h6 class="mb-0 text-uppercase">TAARIFA YA MILIPUKO</h6>
        <hr />

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Chagua tarehe na hali ya mlipuko. Ripoti itaonyesha maduara, blasta na msimamizi.</p>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('milipuko.taarifa.pdf') }}" method="GET" class="no-global-submit-guard">
                            <div class="mb-3">
                                <label for="tarehe" class="form-label">Tarehe <span class="text-danger">*</span></label>
                                <input type="date" name="tarehe" id="tarehe" value="{{ old('tarehe', now()->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="hali" class="form-label">Hali ya mlipuko</label>
                                <select name="hali" id="hali" class="form-select">
                                    <option value="zote" @selected(old('hali', 'zote') === 'zote')>Hali zote</option>
                                    <option value="uzalishaji" @selected(old('hali') === 'uzalishaji')>Uzalishaji</option>
                                    <option value="ufreshiaji" @selected(old('hali') === 'ufreshiaji')>Ufreshiaji</option>
                                    <option value="ufukuziaji" @selected(old('hali') === 'ufukuziaji')>Ufukuziaji</option>
                                </select>
                            </div>
                            <a href="{{ route('milipuko.index') }}" class="btn btn-secondary">Rudi</a>
                            <button type="submit" class="btn btn-info">
                                <i class="bx bx-file me-1"></i> Toa ripoti
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
