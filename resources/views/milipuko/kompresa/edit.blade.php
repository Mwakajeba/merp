@extends('layouts.main')

@section('title', 'Hariri Kompresa')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Usajili wa Kompresa', 'url' => route('milipuko.kompresa.index'), 'icon' => 'bx bx-cog'],
            ['label' => $kompresa->namba, 'url' => route('milipuko.kompresa.show', $kompresa), 'icon' => 'bx bx-cog'],
            ['label' => 'Hariri', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">HARIRI KOMPRESA</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.kompresa.form')
            </div>
        </div>
    </div>
</div>
@endsection
