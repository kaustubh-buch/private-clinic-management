@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Patients</h2>
    <a href="{{ route('patients.create') }}" class="btn btn-primary">Add Patient</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($patients as $patient)
        <tr>
            <td>{{ $patient->id }}</td>
            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
            <td>{{ $patient->email }}</td>
            <td>{{ $patient->contact_number }}</td>
            <td>{{ $patient->created_at->format('Y-m-d') }}</td>
            <td class="d-flex gap-2">
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('patients.destroy', $patient) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $patients->links() }}
@endsection