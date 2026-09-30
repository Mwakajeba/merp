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

        <div class="dashibodi-widgets">
            <a href="{{ route('milipuko.maduara.index') }}" class="text-decoration-none">
                <div class="card border-primary dashibodi-card mb-0">
                    <div class="card-body d-flex align-items-center gap-2">
                        <i class="bx bx-grid-alt text-primary"></i>
                        <div>
                            <div class="idadi text-dark">{{ number_format($idadiYaMaduara) }}</div>
                            <div class="jina text-primary">Maduara</div>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('milipuko.walipuaji.index') }}" class="text-decoration-none">
                <div class="card border-warning dashibodi-card mb-0">
                    <div class="card-body d-flex align-items-center gap-2">
                        <i class="bx bx-user-check text-warning"></i>
                        <div>
                            <div class="idadi text-dark">{{ number_format($idadiYaWalipuaji) }}</div>
                            <div class="jina text-warning">Walipuaji</div>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('milipuko.vibali.index') }}" class="text-decoration-none">
                <div class="card border-success dashibodi-card mb-0">
                    <div class="card-body d-flex align-items-center gap-2">
                        <i class="bx bx-id-card text-success"></i>
                        <div>
                            <div class="idadi text-dark">{{ number_format($idadiYaVibali) }}</div>
                            <div class="jina text-success">Vibali</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashibodi-widgets {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .dashibodi-card {
        width: 180px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .dashibodi-card .card-body {
        padding: 0.55rem 0.75rem;
    }

    .dashibodi-card i {
        font-size: 1.45rem;
        line-height: 1;
    }

    .dashibodi-card .idadi {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .dashibodi-card .jina {
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .dashibodi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush
