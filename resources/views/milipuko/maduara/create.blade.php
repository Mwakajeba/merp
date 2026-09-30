@extends('layouts.main')

@section('title', 'Sajili Duara')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Maduara', 'url' => route('milipuko.maduara.index'), 'icon' => 'bx bx-grid-alt'],
            ['label' => 'Sajili Duara', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">SAJILI DUARA</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.maduara.form')
            </div>
        </div>
    </div>
</div>
@endsection
