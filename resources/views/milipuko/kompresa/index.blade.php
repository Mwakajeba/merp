@extends('layouts.main')

@section('title', 'Usajili wa Kompresa')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Usajili wa Kompresa', 'url' => '#', 'icon' => 'bx bx-cog']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">USAJILI WA KOMPRESA</h6>
            <a href="{{ route('milipuko.kompresa.create') }}" class="btn btn-dark">
                <i class="bx bx-plus me-1"></i> Sajili Kompresa
            </a>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="kompresa-table" class="table table-striped align-middle w-100">
                        <thead>
                            <tr>
                                <th>Picha</th>
                                <th>Jina la mmiliki</th>
                                <th>Namba ya kompresa</th>
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
        $('#kompresa-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route('milipuko.kompresa.data')),
                type: 'GET'
            },
            order: [[1, 'asc']],
            pageLength: 25,
            columns: [
                { data: 'picha_html', name: 'picha_html', orderable: false, searchable: false },
                { data: 'jina', name: 'jina' },
                { data: 'namba', name: 'namba' },
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
                infoEmpty: 'Hakuna kompresa',
                infoFiltered: '(zimechujwa kutoka _MAX_)',
                zeroRecords: 'Hakuna kompresa zinazolingana.',
                emptyTable: 'Hakuna kompresa zilizosajiliwa bado.',
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
