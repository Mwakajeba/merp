@extends('layouts.main')

@section('title', 'Walipuaji')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Walipuaji', 'url' => '#', 'icon' => 'bx bx-user-check']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">{{ $hali === 'blocked' ? 'BLASTA WALIOFUNGIWA' : 'WALIPUAJI (BLASTERS)' }}</h6>
            <a href="{{ route('milipuko.walipuaji.create') }}" class="btn btn-warning">
                <i class="bx bx-plus me-1"></i> Sajili Mlipuaji
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="walipuaji-table" class="table table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Picha</th>
                                <th>Jina</th>
                                <th>Namba ya usajili</th>
                                <th>BC No.</th>
                                <th>Hali</th>
                                <th>Simu</th>
                                <th>Mkoa</th>
                                <th>Wilaya</th>
                                <th class="text-end">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
        $('#walipuaji-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('milipuko.walipuaji.data')),
                type: 'GET',
                data: function (d) {
                    d.hali = @json($hali);
                }
            },
            order: [[1, 'asc']],
            pageLength: 25,
            columns: [
                { data: 'picha', name: 'picha', orderable: false, searchable: false },
                { data: 'jina', name: 'jina' },
                { data: 'namba_ya_usajili', name: 'namba_ya_usajili' },
                { data: 'bc', name: 'bc' },
                { data: 'hali', name: 'hali' },
                { data: 'simu', name: 'simu' },
                { data: 'mkoa', name: 'mkoa' },
                { data: 'wilaya', name: 'wilaya' },
                { data: 'vitendo', name: 'vitendo', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                processing: 'Inapakia...',
                search: 'Tafuta:',
                lengthMenu: 'Onyesha _MENU_',
                info: 'Inaonyesha _START_ hadi _END_ kati ya _TOTAL_',
                infoEmpty: @json($hali === 'blocked' ? 'Hakuna blasta waliofungiwa' : 'Hakuna walipuaji'),
                infoFiltered: '(zimechujwa kutoka _MAX_)',
                zeroRecords: 'Hakuna walipuaji wanaolingana.',
                emptyTable: @json($hali === 'blocked' ? 'Hakuna blasta waliofungiwa.' : 'Hakuna walipuaji waliowekwa bado.'),
                paginate: {
                    first: 'Kwanza',
                    last: 'Mwisho',
                    next: 'Mbele',
                    previous: 'Nyuma'
                }
            }
        });
    });
</script>
@endpush
