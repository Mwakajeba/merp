@extends('layouts.main')

@section('title', 'POS Cashier')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    #open-bills-table tbody tr { cursor: pointer; }
    #open-bills-table tbody tr.table-active { background-color: rgba(25, 135, 84, 0.12) !important; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Sales', 'url' => route('sales.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'POS Cashier', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">POS CASHIER — OPEN BILLS</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('sales.pos.index') }}" class="btn btn-outline-info btn-sm">
                    <i class="bx bx-store"></i> POS (Create Bill)
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshBillsBtn">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Unpaid Bills</h5>
                        <p class="text-muted small mb-3">
                            Search by customer name, phone, customer number, or bill number.
                        </p>
                        <div class="table-responsive">
                            <table id="open-bills-table" class="table table-striped table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th class="text-end">Balance</th>
                                        <th class="text-end" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-money me-2"></i>Clear Bill</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="selectedBillId" value="">
                        <div class="alert alert-light border mb-3" id="selectedBillInfo">
                            Select a bill from the list to record payment.
                        </div>

                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Paid To (Bank Account) <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="">Select account...</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="paymentAmount" class="form-label">Amount (TZS)</label>
                            <input type="number" class="form-control" id="paymentAmount" step="0.01" min="0.01" placeholder="Leave blank to pay full balance">
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-success btn-lg" id="payBillBtn" onclick="paySelectedBill()" disabled>
                                <i class="bx bx-check-circle"></i> Complete Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script nonce="{{ $cspNonce ?? '' }}">
const posAutoPrintReceipt = @json($posAutoPrintReceipt);
let selectedBillBalance = 0;
let billsTable = null;

function selectBill(encodedId, invoiceNumber, customerName, balance, highlightRow) {
    document.getElementById('selectedBillId').value = encodedId;
    selectedBillBalance = parseFloat(balance) || 0;
    document.getElementById('paymentAmount').value = selectedBillBalance.toFixed(2);
    document.getElementById('selectedBillInfo').innerHTML =
        `<strong>Bill:</strong> ${invoiceNumber}<br><strong>Customer:</strong> ${customerName}<br><strong>Balance:</strong> ${selectedBillBalance.toFixed(2)} TZS`;
    document.getElementById('payBillBtn').disabled = false;

    if (highlightRow) {
        $('#open-bills-table tbody tr').removeClass('table-active');
        highlightRow.addClass('table-active');
    }
}

function resetBillSelection() {
    document.getElementById('selectedBillId').value = '';
    selectedBillBalance = 0;
    document.getElementById('paymentAmount').value = '';
    document.getElementById('selectedBillInfo').innerHTML = 'Select a bill from the list to record payment.';
    document.getElementById('payBillBtn').disabled = true;
    $('#open-bills-table tbody tr').removeClass('table-active');
}

function paySelectedBill() {
    const encodedId = document.getElementById('selectedBillId').value;
    const bankAccountId = document.getElementById('paymentMethod').value;
    const amount = parseFloat(document.getElementById('paymentAmount').value) || selectedBillBalance;

    if (!encodedId) {
        Swal.fire({ icon: 'warning', title: 'No bill selected', text: 'Please select a bill to pay.' });
        return;
    }

    if (!bankAccountId) {
        Swal.fire({ icon: 'warning', title: 'Bank account required', text: 'Please select where payment was received.' });
        return;
    }

    Swal.fire({
        title: 'Processing payment...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(`{{ url('sales/pos/bills') }}/${encodedId}/pay`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            bank_account_id: bankAccountId,
            amount: amount,
            payment_date: new Date().toISOString().split('T')[0]
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Payment failed');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            if (data.receipt_url && posAutoPrintReceipt) {
                window.open(data.receipt_url, '_blank', 'width=400,height=600');
            }
            Swal.fire({
                icon: 'success',
                title: 'Payment recorded!',
                text: data.message,
                timer: 2000,
                showConfirmButton: true
            }).then(() => {
                resetBillSelection();
                if (billsTable) {
                    billsTable.ajax.reload(null, false);
                }
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Payment failed', text: data.message || 'Could not record payment.' });
        }
    })
    .catch(error => {
        Swal.fire({ icon: 'error', title: 'Payment failed', text: error.message || 'Failed to connect. Please try again.' });
    });
}

$(document).ready(function () {
    billsTable = $('#open-bills-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("sales.pos.cashier") }}',
            type: 'GET'
        },
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'balance_due', name: 'balance_due', className: 'text-end' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[2, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: 'Search customer or bill:',
            lengthMenu: 'Show _MENU_ bills per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ bills',
            infoEmpty: 'Showing 0 to 0 of 0 bills',
            infoFiltered: '(filtered from _MAX_ total bills)',
            emptyTable: 'No open bills. Create orders from POS in bill mode.',
            zeroRecords: 'No matching bills found for this customer'
        }
    });

    $('#refreshBillsBtn').on('click', function () {
        resetBillSelection();
        billsTable.ajax.reload(null, false);
    });

    $('#open-bills-table').on('click', '.pay-bill-btn', function (e) {
        e.stopPropagation();
        const btn = $(this);
        selectBill(
            btn.data('encoded-id'),
            btn.data('invoice-number'),
            btn.data('customer-name'),
            btn.data('balance'),
            btn.closest('tr')
        );
    });

    $('#open-bills-table tbody').on('click', 'tr', function (e) {
        if ($(e.target).closest('a, button').length) {
            return;
        }
        const btn = $(this).find('.pay-bill-btn').first();
        if (!btn.length) {
            return;
        }
        selectBill(
            btn.data('encoded-id'),
            btn.data('invoice-number'),
            btn.data('customer-name'),
            btn.data('balance'),
            $(this)
        );
    });
});
</script>
@endpush
