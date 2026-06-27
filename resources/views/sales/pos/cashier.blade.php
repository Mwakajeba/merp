@extends('layouts.main')

@section('title', 'POS Cashier')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">
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
                            @unless($canViewAllBills ?? false)
                                <span class="d-block">You only see bills you created.</span>
                            @endunless
                        </p>
                        <div class="table-responsive">
                            <table id="open-bills-table" class="table table-striped table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Customer</th>
                                        @if($canViewAllBills ?? false)
                                        <th>Created By</th>
                                        @endif
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
                        <div class="mb-3">
                            <label for="billSelect" class="form-label">Select Bill(s) <span class="text-danger">*</span></label>
                            <select class="form-select select2-bills" id="billSelect" multiple>
                            </select>
                            <small class="text-muted">Search and select one or more unpaid bills to clear.</small>
                        </div>

                        <div class="alert alert-light border mb-3" id="selectedBillInfo">
                            Select bill(s) from the list to record payment.
                        </div>

                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Paid To (Bank Account) <span class="text-danger">*</span></label>
                            <select class="form-select select2-single" id="paymentMethod" required>
                                <option value="">Select account...</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="paymentAmountWrap">
                            <label for="paymentAmount" class="form-label">Amount (TZS)</label>
                            <input type="number" class="form-control" id="paymentAmount" step="0.01" min="0.01" placeholder="Leave blank to pay full balance">
                            <small class="text-muted">Only used when a single bill is selected.</small>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-success btn-lg" id="payBillBtn" onclick="paySelectedBills()" disabled>
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
<script nonce="{{ $cspNonce ?? '' }}">
const posAutoPrintReceipt = @json($posAutoPrintReceipt);
const openBillsUrl = @json(route('sales.pos.cashier.open-bills'));
const payBillBaseUrl = @json(url('sales/pos/bills'));
let billsTable = null;
let billCatalog = new Map();

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getSelectedBillIds() {
    return $('#billSelect').val() || [];
}

function getSelectedBills() {
    return getSelectedBillIds()
        .map((id) => billCatalog.get(id))
        .filter(Boolean);
}

function updateBillSelectionUi() {
    const selectedBills = getSelectedBills();
    const count = selectedBills.length;
    const totalBalance = selectedBills.reduce((sum, bill) => sum + (parseFloat(bill.balance_due) || 0), 0);
    const infoEl = document.getElementById('selectedBillInfo');
    const amountWrap = document.getElementById('paymentAmountWrap');
    const payBtn = document.getElementById('payBillBtn');

    if (!count) {
        infoEl.innerHTML = 'Select bill(s) from the list to record payment.';
        document.getElementById('paymentAmount').value = '';
        amountWrap.classList.add('d-none');
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="bx bx-check-circle"></i> Complete Payment';
        $('#open-bills-table tbody tr').removeClass('table-active');
        return;
    }

    const lines = selectedBills.map((bill) =>
        `<div><strong>${escapeHtml(bill.invoice_number)}</strong> — ${escapeHtml(bill.customer_name)} — ${formatMoney(bill.balance_due)} ${escapeHtml(bill.currency)}</div>`
    ).join('');

    infoEl.innerHTML =
        `<div class="mb-2"><strong>${count}</strong> bill${count === 1 ? '' : 's'} selected</div>` +
        lines +
        `<div class="mt-2 pt-2 border-top"><strong>Total:</strong> ${formatMoney(totalBalance)} TZS</div>`;

    if (count === 1) {
        amountWrap.classList.remove('d-none');
        document.getElementById('paymentAmount').value = totalBalance.toFixed(2);
    } else {
        amountWrap.classList.add('d-none');
        document.getElementById('paymentAmount').value = '';
    }

    payBtn.disabled = false;
    payBtn.innerHTML = count === 1
        ? '<i class="bx bx-check-circle"></i> Complete Payment'
        : `<i class="bx bx-check-circle"></i> Clear ${count} Bills`;
}

function resetBillSelection() {
    $('#billSelect').val(null).trigger('change');
    updateBillSelectionUi();
}

function addBillToSelection(encodedId) {
    if (!billCatalog.has(encodedId)) {
        return;
    }

    const current = new Set(getSelectedBillIds());
    current.add(encodedId);
    $('#billSelect').val(Array.from(current)).trigger('change');
}

function loadOpenBills() {
    return fetch(openBillsUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Failed to load bills');
        }
        return data.bills || [];
    })
    .then((bills) => {
        const selected = getSelectedBillIds();
        billCatalog = new Map(bills.map((bill) => [bill.id, bill]));

        const $select = $('#billSelect');
        $select.empty();

        bills.forEach((bill) => {
            $select.append(new Option(bill.label, bill.id, false, selected.includes(bill.id)));
        });

        $select.val(selected.filter((id) => billCatalog.has(id))).trigger('change.select2');
        updateBillSelectionUi();
    });
}

async function paySingleBill(encodedId, bankAccountId, amount) {
    const response = await fetch(`${payBillBaseUrl}/${encodedId}/pay`, {
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
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) {
        throw new Error(data.message || 'Payment failed');
    }

    return data;
}

async function paySelectedBills() {
    const selectedBills = getSelectedBills();
    const bankAccountId = document.getElementById('paymentMethod').value;

    if (!selectedBills.length) {
        Swal.fire({ icon: 'warning', title: 'No bill selected', text: 'Please select at least one bill to pay.' });
        return;
    }

    if (!bankAccountId) {
        Swal.fire({ icon: 'warning', title: 'Bank account required', text: 'Please select where payment was received.' });
        return;
    }

    const isSingleBill = selectedBills.length === 1;
    const customAmount = parseFloat(document.getElementById('paymentAmount').value);
    const payments = selectedBills.map((bill) => ({
        bill,
        amount: isSingleBill && !Number.isNaN(customAmount) && customAmount > 0
            ? customAmount
            : parseFloat(bill.balance_due) || 0,
    }));

    Swal.fire({
        title: 'Processing payment...',
        html: `Clearing <strong>0</strong> of <strong>${payments.length}</strong> bill(s)...`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const paid = [];
    const failed = [];
    let lastReceiptUrl = null;

    for (let index = 0; index < payments.length; index++) {
        const payment = payments[index];
        Swal.update({
            html: `Clearing <strong>${index + 1}</strong> of <strong>${payments.length}</strong> bill(s)...`
        });

        try {
            const data = await paySingleBill(payment.bill.id, bankAccountId, payment.amount);
            paid.push(payment.bill.invoice_number);
            if (data.receipt_url) {
                lastReceiptUrl = data.receipt_url;
            }
        } catch (error) {
            failed.push({
                invoice_number: payment.bill.invoice_number,
                message: error.message || 'Payment failed',
            });
        }
    }

    if (paid.length && posAutoPrintReceipt && lastReceiptUrl && paid.length === 1) {
        window.open(lastReceiptUrl, '_blank', 'width=400,height=600');
    }

    if (!failed.length) {
        Swal.fire({
            icon: 'success',
            title: paid.length === 1 ? 'Payment recorded!' : 'Bills cleared!',
            text: paid.length === 1
                ? `Bill ${paid[0]} paid successfully.`
                : `${paid.length} bills paid successfully.`,
            timer: 2500,
            showConfirmButton: true
        }).then(async () => {
            resetBillSelection();
            await loadOpenBills();
            if (billsTable) {
                billsTable.ajax.reload(null, false);
            }
        });
        return;
    }

    const failureText = failed
        .map((item) => `${item.invoice_number}: ${item.message}`)
        .join('\n');

    Swal.fire({
        icon: paid.length ? 'warning' : 'error',
        title: paid.length ? 'Some payments failed' : 'Payment failed',
        text: paid.length
            ? `${paid.length} bill(s) paid.\n\nFailed:\n${failureText}`
            : failureText,
        showConfirmButton: true
    }).then(async () => {
        resetBillSelection();
        await loadOpenBills();
        if (billsTable) {
            billsTable.ajax.reload(null, false);
        }
    });
}

$(document).ready(function () {
    $('#billSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search and select unpaid bills...',
        allowClear: true,
        closeOnSelect: false
    });

    if (!$('#paymentMethod').hasClass('select2-hidden-accessible')) {
        $('#paymentMethod').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select account...',
            allowClear: true
        });
    }

    $('#billSelect').on('change', function () {
        updateBillSelectionUi();

        const selectedIds = new Set(getSelectedBillIds());
        $('#open-bills-table tbody tr').each(function () {
            const encodedId = $(this).find('.pay-bill-btn').first().data('encoded-id');
            $(this).toggleClass('table-active', selectedIds.has(encodedId));
        });
    });

    loadOpenBills().catch((error) => {
        Swal.fire({
            icon: 'error',
            title: 'Could not load bills',
            text: error.message || 'Please refresh and try again.'
        });
    });

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
            @if($canViewAllBills ?? false)
            { data: 'created_by_name', name: 'created_by_name', orderable: false, searchable: false },
            @endif
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'balance_due', name: 'balance_due', className: 'text-end' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[{{ ($canViewAllBills ?? false) ? 3 : 2 }}, 'desc']],
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
        loadOpenBills().finally(() => {
            if (billsTable) {
                billsTable.ajax.reload(null, false);
            }
        });
    });

    $('#open-bills-table').on('click', '.pay-bill-btn', function (e) {
        e.stopPropagation();
        addBillToSelection($(this).data('encoded-id'));
    });

    $('#open-bills-table tbody').on('click', 'tr', function (e) {
        if ($(e.target).closest('a, button').length) {
            return;
        }
        const btn = $(this).find('.pay-bill-btn').first();
        if (!btn.length) {
            return;
        }
        addBillToSelection(btn.data('encoded-id'));
    });
});
</script>
@endpush
