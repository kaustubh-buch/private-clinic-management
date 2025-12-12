@extends('layouts.app')

@section('content')
<h2>Patient Details</h2>

<ul class="list-group mb-3">
    <li class="list-group-item"><strong>Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}</li>
    <li class="list-group-item"><strong>Email:</strong> {{ $patient->email }}</li>
    <li class="list-group-item"><strong>Contact:</strong> {{ $patient->contact_number }}</li>
    <li class="list-group-item"><strong>Created At:</strong> {{ $patient->created_at }}</li>
    <li class="list-group-item"><strong>Updated At:</strong> {{ $patient->updated_at }}</li>
</ul>

<a href="{{ route('patients.index') }}" class="btn btn-secondary">Back</a>
@endsection
