@extends('layouts.main')

@section('title', 'Sales Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Sales Settings', 'url' => '#', 'icon' => 'bx bx-store']
        ]" />

        <h6 class="mb-0 text-uppercase">SALES SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Point of Sale Configuration</h4>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('settings.sales.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bx bx-store me-2"></i>POS Sale Mode</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        Choose how your business uses the POS. Supermarkets typically use direct sale (pay immediately).
                                        Restaurants often create a bill first and collect payment at the cashier later.
                                    </p>

                                    <div class="form-check mb-3 p-3 border rounded {{ old('pos_sale_mode', $currentSettings['pos_sale_mode']) === 'direct' ? 'border-primary bg-light' : '' }}">
                                        <input class="form-check-input" type="radio" name="pos_sale_mode" id="pos_mode_direct" value="direct"
                                            {{ old('pos_sale_mode', $currentSettings['pos_sale_mode']) === 'direct' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="pos_mode_direct">
                                            <strong>Direct POS Sale</strong>
                                            <div class="text-muted small mt-1">
                                                Complete sale and payment at once (supermarket / retail). Creates a POS sale record immediately.
                                            </div>
                                        </label>
                                    </div>

                                    <div class="form-check p-3 border rounded {{ old('pos_sale_mode', $currentSettings['pos_sale_mode']) === 'bill' ? 'border-primary bg-light' : '' }}">
                                        <input class="form-check-input" type="radio" name="pos_sale_mode" id="pos_mode_bill" value="bill"
                                            {{ old('pos_sale_mode', $currentSettings['pos_sale_mode']) === 'bill' ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="pos_mode_bill">
                                            <strong>Bill POS Sale</strong>
                                            <div class="text-muted small mt-1">
                                                Waiter creates an unpaid bill (sales invoice). Walk-in guests are saved as customers.
                                                Cashier clears open bills and records payment separately.
                                            </div>
                                        </label>
                                    </div>
                                    @error('pos_sale_mode')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="card border-secondary mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="bx bx-printer me-2"></i>Receipt Printing</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="pos_auto_print_receipt"
                                            name="pos_auto_print_receipt" value="1"
                                            {{ old('pos_auto_print_receipt', $currentSettings['pos_auto_print_receipt']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pos_auto_print_receipt">
                                            Auto-print thermal receipt after completing a sale or bill payment
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Opens the browser print dialog using the same POS receipt layout as sales invoices.
                                        Ensure a thermal printer is set up on the cashier PC.
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Settings
                                </button>
                                <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
