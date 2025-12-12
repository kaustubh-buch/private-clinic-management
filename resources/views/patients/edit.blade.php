@extends('layouts.app')

@section('content')
<h2>Edit Patient</h2>

<form action="{{ route('patients.update', $patient) }}" method="POST">
    @csrf
    @method('PUT')

    @include('patients.partials.form')

    <button class="btn btn-primary">Update</button>
@endsection
