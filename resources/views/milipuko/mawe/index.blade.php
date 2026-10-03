@extends('layouts.main')

@section('title', 'Vibali vya Mawe')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali vya Mawe', 'url' => '#', 'icon' => 'bx bx-package']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">VIBALI VYA MAWE</h6>
            <a href="{{ route('milipuko.mawe.create') }}" class="btn btn-danger">
                <i class="bx bx-plus me-1"></i> Sajili kibali
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mawe-table" class="table table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Namba</th>
                                <th>Tarehe</th>
                                <th>Duara No.</th>
                                <th>Aina</th>
                                <th>Mifuko</th>
                                <th>Msimamizi</th>
                                <th>Katibu</th>
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
        $('#mawe-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('milipuko.mawe.data')),
                type: 'GET'
            },
            order: [[1, 'desc']],
            pageLength: 25,
            columns: [
                { data: 'namba', name: 'namba' },
                { data: 'tarehe', name: 'tarehe' },
                { data: 'duara_namba', name: 'duara_namba' },
                { data: 'aina', name: 'aina_ya_mzigo' },
                { data: 'idadi_ya_mifuko', name: 'idadi_ya_mifuko' },
                { data: 'msimamizi_jina', name: 'msimamizi_jina' },
                { data: 'katibu', name: 'katibu' },
                { data: 'vitendo', name: 'vitendo', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                processing: 'Inapakia...',
                search: 'Tafuta:',
                lengthMenu: 'Onyesha _MENU_',
                info: 'Inaonyesha _START_ hadi _END_ kati ya _TOTAL_',
                infoEmpty: 'Hakuna vibali vya mawe',
                infoFiltered: '(zimechujwa kutoka _MAX_)',
                zeroRecords: 'Hakuna vibali vinavyolingana.',
                emptyTable: 'Hakuna vibali vya mawe vilivyowekwa bado.',
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
