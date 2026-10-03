@extends('layouts.main')

@section('title', 'Sajili Kibali cha Mawe')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashibodi', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Milipuko', 'url' => route('milipuko.index'), 'icon' => 'bx bx-bomb'],
            ['label' => 'Vibali vya Mawe', 'url' => route('milipuko.mawe.index'), 'icon' => 'bx bx-package'],
            ['label' => 'Sajili kibali', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">SAJILI KIBALI CHA MAWE</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @include('milipuko.mawe.form')
            </div>
        </div>
    </div>
</div>
@endsection
