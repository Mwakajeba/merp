@extends('layouts.main')

@push('styles')
<link href="{{ asset('assets/vendor/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/vendor/select2/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Inventory', 'url' => route('inventory.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Movements', 'url' => route('inventory.movements.index'), 'icon' => 'bx bx-transfer'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Edit Movement - {{ $movement->reference ?? 'REF-' . $movement->id }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('inventory.movements.form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
@endpush
