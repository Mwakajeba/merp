@extends('layouts.main')

@section('title', 'Sajili Kompresa')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Usajili wa Kompresa', 'url' => route('milipuko.kompresa.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Sajili', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">SAJILI KOMPRESA</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.kompresa.form')
            </div>
        </div>
    </div>
</div>
@endsection
