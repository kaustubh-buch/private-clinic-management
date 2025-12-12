@extends('layouts.app')

@section('content')
<h2>Add Patient</h2>

<form action="{{ route('patients.store') }}" method="POST">
    @csrf

    @include('patients.partials.form')

    <button class="btn btn-primary">Save</button>
</form>
@endsection
