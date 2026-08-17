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
                                    <div class="form-check form-switch mb-3">
                                        <input type="hidden" name="pos_bill_mode_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="pos_bill_mode_enabled"
                                            name="pos_bill_mode_enabled" value="1"
                                            {{ old('pos_bill_mode_enabled', $currentSettings['pos_bill_mode_enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pos_bill_mode_enabled">
                                            <strong>Bill POS Sale</strong>
                                        </label>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <strong>Off:</strong> Direct POS sale — complete sale and payment at once (supermarket / retail).
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <strong>On:</strong> Bill POS sale — create an unpaid bill first; cashier collects payment later (restaurant / table service).
                                        Walk-in guests are saved as customers. Use the cashier screen to clear open bills.
                                    </p>
                                    @error('pos_bill_mode_enabled')
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

                                    <div class="mt-4">
                                        <label for="pos_receipt_max_prints" class="form-label">
                                            <strong>Maximum POS receipt prints</strong>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('pos_receipt_max_prints') is-invalid @enderror"
                                               id="pos_receipt_max_prints"
                                               name="pos_receipt_max_prints"
                                               min="1"
                                               max="10"
                                               value="{{ old('pos_receipt_max_prints', $currentSettings['pos_receipt_max_prints']) }}"
                                               required>
                                        <small class="text-muted d-block mt-2">
                                            Each POS receipt can only be printed this many times. If a cashier tries to print again after the limit, their account is suspended until an administrator reactivates it.
                                        </small>
                                        @error('pos_receipt_max_prints')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Settings
                                </button>
                                @if(auth()->user()->can('view logs activity') || auth()->user()->can('view sales reports'))
                                <a href="{{ route('sales.reports.pos-reprint-attempts') }}" class="btn btn-outline-danger">
                                    <i class="bx bx-list-ul me-1"></i> Reprint Attempts Report
                                </a>
                                @endif
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
