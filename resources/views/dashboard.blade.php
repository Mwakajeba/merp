@extends('layouts.main')

@section('title', 'Dashibodi')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home']
        ]" />
        <h6 class="mb-0 text-uppercase">DASHIBODI</h6>
        <hr />

        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <a href="{{ route('milipuko.maduara.index') }}" class="text-decoration-none">
                    <div class="card border-primary dashibodi-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-grid-alt fs-1 text-primary"></i>
                            </div>
                            <h5 class="card-title text-primary">Maduara</h5>
                            <p class="display-6 mb-1 text-dark">{{ number_format($idadiYaMaduara) }}</p>
                            <p class="card-text text-muted mb-0">Idadi ya maduara yaliyosajiliwa</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <a href="{{ route('milipuko.walipuaji.index') }}" class="text-decoration-none">
                    <div class="card border-warning dashibodi-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-user-check fs-1 text-warning"></i>
                            </div>
                            <h5 class="card-title text-warning">Walipuaji</h5>
                            <p class="display-6 mb-1 text-dark">{{ number_format($idadiYaWalipuaji) }}</p>
                            <p class="card-text text-muted mb-0">Walipuaji waliosajiliwa</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <a href="{{ route('milipuko.vibali.index') }}" class="text-decoration-none">
                    <div class="card border-success dashibodi-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-id-card fs-1 text-success"></i>
                            </div>
                            <h5 class="card-title text-success">Vibali</h5>
                            <p class="display-6 mb-1 text-dark">{{ number_format($idadiYaVibali) }}</p>
                            <p class="card-text text-muted mb-0">Jumla ya vibali vilivyotoka</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashibodi-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .dashibodi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .dashibodi-card .fs-1 {
        font-size: 3rem !important;
    }
</style>
@endpush
