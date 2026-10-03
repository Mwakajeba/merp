@extends('layouts.main')

@section('title', 'Milipuko')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => '#', 'icon' => 'bx bx-bomb']
        ]" />
        <h6 class="mb-0 text-uppercase">MILIPUKO</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Milipuko</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-grid-alt fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Maduara</h5>
                                        <p class="card-text">Sajili maduara, wasimamizi na wanachama.</p>
                                        <a href="{{ route('milipuko.maduara.index') }}" class="btn btn-primary">
                                            <i class="bx bx-grid-alt me-1"></i> Fungua Maduara
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-user-check fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Walipuaji (Blasters)</h5>
                                        <p class="card-text">Sajili taarifa za walipuaji na watu wa kuwasiliana nao.</p>
                                        <a href="{{ route('milipuko.walipuaji.index') }}" class="btn btn-warning">
                                            <i class="bx bx-user-check me-1"></i> Fungua Walipuaji
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-id-card fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Vibali vya milipuko</h5>
                                        <p class="card-text">Kibali cha kuchoronga mwamba na kulipua.</p>
                                        <a href="{{ route('milipuko.vibali.index') }}" class="btn btn-success">
                                            <i class="bx bx-id-card me-1"></i> Fungua vibali
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-package fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Vibali vya Mawe</h5>
                                        <p class="card-text">Kibali cha kuchukua mifuko ya mawe au chorongeo kutoka duarani kwenda ofisini.</p>
                                        <a href="{{ route('milipuko.mawe.index') }}" class="btn btn-danger">
                                            <i class="bx bx-package me-1"></i> Fungua vibali
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-check fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Maduara yaliyozalisha</h5>
                                        <p class="card-text">Sajili duara na tarehe iliyozalisha, kisha ona mifuko ya mawe na chorongeo iliyotoka.</p>
                                        <a href="{{ route('milipuko.uzalishaji.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-calendar-check me-1"></i> Fungua uzalishaji
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info milipuko-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-file fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Taarifa</h5>
                                        <p class="card-text">Chagua tarehe na hali ya mlipuko, kisha pata ripoti ya maduara.</p>
                                        <a href="{{ route('milipuko.taarifa.index') }}" class="btn btn-info">
                                            <i class="bx bx-file me-1"></i> Fungua Taarifa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .milipuko-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .milipuko-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .milipuko-card .fs-1 {
        font-size: 3rem !important;
    }
</style>
@endpush
