@extends('layouts.main')

@section('title', 'Vibali')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali', 'url' => '#', 'icon' => 'bx bx-id-card']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">VIBALI</h6>
            <a href="{{ route('milipuko.vibali.create') }}" class="btn btn-success">
                <i class="bx bx-plus me-1"></i> Sajili Kibali
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="vibali-table" class="table table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Namba</th>
                                <th>Tarehe</th>
                                <th>Duara No.</th>
                                <th>Mlipuaji</th>
                                <th>Hali</th>
                                <th>Matundu</th>
                                <th>Wachorongaji</th>
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
        $('#vibali-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('milipuko.vibali.data')),
                type: 'GET'
            },
            order: [[1, 'desc']],
            pageLength: 25,
            columns: [
                { data: 'namba', name: 'namba' },
                { data: 'tarehe', name: 'tarehe' },
                { data: 'duara_namba', name: 'duara_namba' },
                { data: 'mlipuzi_jina', name: 'mlipuzi_jina' },
                { data: 'hali_onyesho', name: 'hali', orderable: false },
                { data: 'idadi_ya_matundu', name: 'idadi_ya_matundu' },
                { data: 'wachorongaji_count', name: 'wachorongaji_count', searchable: false, defaultContent: '0' },
                { data: 'vitendo', name: 'vitendo', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                processing: 'Inapakia...',
                search: 'Tafuta:',
                lengthMenu: 'Onyesha _MENU_',
                info: 'Inaonyesha _START_ hadi _END_ kati ya _TOTAL_',
                infoEmpty: 'Hakuna vibali',
                infoFiltered: '(zimechujwa kutoka _MAX_)',
                zeroRecords: 'Hakuna vibali vinavyolingana.',
                emptyTable: 'Hakuna vibali vilivyowekwa bado.',
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
