@extends('layouts.main')

@section('title', 'Sajili Mlipuaji')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Walipuaji', 'url' => route('milipuko.walipuaji.index'), 'icon' => 'bx bx-user-check'],
            ['label' => 'Sajili Mlipuaji', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">SAJILI MLIPUAJI</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.walipuaji.form')
            </div>
        </div>
    </div>
</div>
@endsection
