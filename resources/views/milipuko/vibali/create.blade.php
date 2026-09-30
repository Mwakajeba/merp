@extends('layouts.main')

@section('title', 'Sajili Kibali')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali', 'url' => route('milipuko.vibali.index'), 'icon' => 'bx bx-id-card'],
            ['label' => 'Sajili Kibali', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">SAJILI KIBALI</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.vibali.form')
            </div>
        </div>
    </div>
</div>
@endsection
