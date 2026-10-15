@extends('layouts.main')

@section('title', 'Maduara')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Maduara', 'url' => '#', 'icon' => 'bx bx-grid-alt']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">{{ $hali === 'imefungwa' ? 'MADUARA YALIYOFUNGWA' : 'MADUARA' }}</h6>
            <a href="{{ route('milipuko.maduara.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Sajili Duara
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="maduara-table" class="table table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Namba ya duara</th>
                                <th>Hali</th>
                                <th>Maelezo</th>
                                <th>Wasimamizi</th>
                                <th>Wanachama</th>
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
        $('#maduara-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('milipuko.maduara.data')),
                type: 'GET',
                data: function (d) {
                    d.hali = @json($hali);
                }
            },
            order: [[0, 'asc']],
            pageLength: 25,
            columns: [
                { data: 'namba', name: 'namba' },
                { data: 'hali', name: 'hali' },
                { data: 'maelezo', name: 'maelezo' },
                { data: 'wasimamizi_count', name: 'wasimamizi_count', searchable: false },
                { data: 'wanachama_count', name: 'wanachama_count', searchable: false },
                { data: 'vitendo', name: 'vitendo', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                processing: 'Inapakia...',
                search: 'Tafuta:',
                lengthMenu: 'Onyesha _MENU_',
                info: 'Inaonyesha _START_ hadi _END_ kati ya _TOTAL_',
                infoEmpty: @json($hali === 'imefungwa' ? 'Hakuna maduara yaliyofungwa' : 'Hakuna maduara'),
                infoFiltered: '(yamechujwa kutoka _MAX_)',
                zeroRecords: 'Hakuna maduara yanayolingana.',
                emptyTable: @json($hali === 'imefungwa' ? 'Hakuna maduara yaliyofungwa.' : 'Hakuna maduara yaliyosajiliwa bado.'),
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
