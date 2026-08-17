@extends('layouts.main')

@section('title', 'POS Reprint Attempts Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-bar-chart-alt-2'],
            ['label' => 'Sales Reports', 'url' => route('sales.reports.index'), 'icon' => 'bx bx-trending-up'],
            ['label' => 'POS Reprint Attempts', 'url' => '#', 'icon' => 'bx bx-printer']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">POS Reprint Attempts</h4>
                <p class="text-muted mb-0">Users who tried to print a POS receipt after the allowed limit.</p>
            </div>
            <a href="{{ route('settings.sales') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-cog me-1"></i> Print Limit Settings
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 mb-3">
            <div class="col mb-2">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Blocked Attempts</p>
                            <h4 class="mb-0">{{ number_format($totalAttempts ?? 0) }}</h4>
                        </div>
                        <div class="widgets-icons text-white bg-primary">
                            <i class="bx bx-printer"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col mb-2">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Accounts Suspended</p>
                            <h4 class="mb-0">{{ number_format($suspendedCount ?? 0) }}</h4>
                        </div>
                        <div class="widgets-icons text-white bg-danger">
                            <i class="bx bx-user-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-10 mb-3">
            <div class="card-body">
                <form id="reprintFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">User</label>
                        <select name="user_id" id="user_id" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bx bx-filter-alt"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reprintAttemptsTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>User</th>
                                <th>Receipt / Invoice</th>
                                <th>Print Count</th>
                                <th>Outcome</th>
                                <th>IP Address</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
$(function () {
    const table = $('#reprintAttemptsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sales.reports.pos-reprint-attempts.data') }}',
            data: function (d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.user_id = $('#user_id').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'activity_time', name: 'activity_time' },
            { data: 'user_name', name: 'user.name', orderable: false },
            { data: 'reference', name: 'description', orderable: false },
            { data: 'print_count_display', name: 'description', orderable: false, searchable: false },
            { data: 'outcome_badge', name: 'description', orderable: false, searchable: false },
            { data: 'ip_address', name: 'ip_address' },
            { data: 'description', name: 'description' },
        ],
        pageLength: 25,
        language: {
            emptyTable: 'No unauthorized reprint attempts recorded yet.'
        }
    });

    $('#reprintFilterForm').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#resetFilters').on('click', function () {
        $('#date_from, #date_to, #user_id').val('');
        table.ajax.reload();
    });
});
</script>
@endpush
