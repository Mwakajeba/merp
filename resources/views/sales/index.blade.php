@extends('layouts.main')

@section('title', 'Sales Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Sales Management', 'url' => '#', 'icon' => 'bx bx-shopping-bag']
        ]" />
        <h6 class="mb-0 text-uppercase">SALES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Sales Modules</h5>
                        </div>
                        <hr>
                        @php
                            $branchId = session('branch_id') ?? auth()->user()->branch_id;
                            $posSaleMode = \App\Models\SystemSetting::getValue('pos_sale_mode', 'direct');
                            $unpaidBillsQuery = \App\Models\Sales\SalesInvoice::query()
                                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                ->where('reference_no', \App\Services\Sales\PosBillService::REFERENCE_NO)
                                ->where('balance_due', '>', 0)
                                ->whereNotIn('status', ['paid', 'cancelled']);
                            \App\Services\Sales\PosBillService::applyCashierBillVisibility($unpaidBillsQuery);
                            $unpaidPosBillsCount = $posSaleMode === 'bill' ? $unpaidBillsQuery->count() : 0;
                            $posListCountQuery = \App\Models\Sales\PosSale::query()
                                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
                            $posListCount = $posSaleMode === 'bill' ? 0 : $posListCountQuery->visibleToUser()->count();
                        @endphp
                        <div class="row">
                            @can('view customers')
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-primary position-relative h-100">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                            {{ \App\Models\Customer::forBranch(auth()->user()->branch_id)->count() }}
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-group fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Customers</h5>
                                        <p class="card-text">Register and manage customer details.</p>
                                        <a href="{{ route('customers.index') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i> Customers
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @can('view sales invoices')
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-danger position-relative h-100">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ \App\Models\Sales\SalesInvoice::forBranch(auth()->user()->branch_id)->count() }}
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-receipt fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Sales Invoices</h5>
                                        <p class="card-text">Create and manage sales invoices.</p>
                                        <a href="{{ route('sales.invoices.index') }}" class="btn btn-danger">
                                            <i class="bx bx-receipt me-1"></i> Sales Invoices
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-info position-relative h-100">
                                    <div class="card-body text-center">
                                        @if($posSaleMode === 'bill')
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                            {{ $unpaidPosBillsCount }}
                                        </span>
                                        @endif
                                        <div class="mb-3">
                                            <i class="bx bx-credit-card fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">POS</h5>
                                        <p class="card-text">
                                            @if($posSaleMode === 'bill')
                                                Create customer bills.
                                                @if($unpaidPosBillsCount > 0)
                                                    <span class="d-block small text-danger fw-semibold mt-1">{{ $unpaidPosBillsCount }} unpaid bill{{ $unpaidPosBillsCount === 1 ? '' : 's' }}</span>
                                                @endif
                                            @else
                                                Start a new point of sale transaction.
                                            @endif
                                        </p>
                                        <a href="{{ route('sales.pos.index') }}" class="btn btn-info">
                                            <i class="bx bx-store me-1"></i> Open POS
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @if($posSaleMode === 'bill' && auth()->user()->can('access pos cashier'))
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-success position-relative h-100">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                            {{ $unpaidPosBillsCount }}
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">POS Cashier</h5>
                                        <p class="card-text">
                                            Collect payment and clear open POS bills.
                                            @if($unpaidPosBillsCount > 0)
                                                <span class="d-block small text-danger fw-semibold mt-1">{{ $unpaidPosBillsCount }} unpaid bill{{ $unpaidPosBillsCount === 1 ? '' : 's' }}</span>
                                            @endif
                                        </p>
                                        <a href="{{ route('sales.pos.cashier') }}" class="btn btn-success">
                                            <i class="bx bx-money me-1"></i> Open Cashier
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($posSaleMode !== 'bill')
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-dark position-relative h-100">
                                    <div class="card-body text-center">
                                        @can('access pos list')
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                                            {{ $posListCount }}
                                        </span>
                                        @endcan
                                        <div class="mb-3">
                                            <i class="bx bx-list-ul fs-1 text-dark"></i>
                                        </div>
                                        <h5 class="card-title">POS List</h5>
                                        <p class="card-text">View and manage POS sales transactions.</p>
                                        @can('access pos list')
                                        <a href="{{ route('sales.pos.list') }}" class="btn btn-dark">
                                            <i class="bx bx-list-ul me-1"></i> POS List
                                        </a>
                                        @else
                                        <span class="text-muted small">No access</span>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            @endif
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
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .position-relative .badge {
        z-index: 10;
        font-size: 0.7rem;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .fs-1 {
        font-size: 3rem !important;
    }
</style>
@endpush
