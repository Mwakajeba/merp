@extends('layouts.main')

@section('title', 'Hariri Kibali '.$kibali->namba)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali vya Mawe', 'url' => route('milipuko.mawe.index'), 'icon' => 'bx bx-package'],
            ['label' => $kibali->namba, 'url' => route('milipuko.mawe.show', $kibali), 'icon' => 'bx bx-file'],
            ['label' => 'Hariri', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">HARIRI KIBALI CHA MAWE {{ $kibali->namba }}</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.mawe.form')
            </div>
        </div>
    </div>
</div>
@endsection
