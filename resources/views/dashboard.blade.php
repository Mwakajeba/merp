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

        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <a href="{{ route('milipuko.maduara.index') }}" class="text-decoration-none text-body">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Maduara</p>
                                    <h4 class="font-weight-bold">{{ number_format($idadiYaMaduara) }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-blues text-white">
                                    <i class="bx bx-grid-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('milipuko.walipuaji.index') }}" class="text-decoration-none text-body">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Walipuaji</p>
                                    <h4 class="font-weight-bold">{{ number_format($idadiYaWalipuaji) }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-kyoto text-white">
                                    <i class="bx bx-user-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('milipuko.vibali.index') }}" class="text-decoration-none text-body">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Vibali</p>
                                    <h4 class="font-weight-bold">{{ number_format($idadiYaVibali) }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-lush text-white">
                                    <i class="bx bx-id-card"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
