@extends('layouts.main')
@section('title', 'Create User')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Create User', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW USER</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('users.form')
            </div>
        </div>
    </div>
</div>
@endsection
