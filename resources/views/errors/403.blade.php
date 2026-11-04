@extends('layouts.guest')

@section('title', 'Access denied')

@section('content')
    <p class="text-uppercase text-success fw-semibold small mb-2">403</p>
    <h1 class="h3 mb-2">Access denied</h1>
    <p class="text-secondary mb-4">You do not have permission to view this page.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-success">Back to dashboard</a>
@endsection