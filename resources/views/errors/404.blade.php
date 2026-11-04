@extends('layouts.guest')

@section('title', 'Page not found')

@section('content')
    <p class="text-uppercase text-success fw-semibold small mb-2">404</p>
    <h1 class="h3 mb-2">Page not found</h1>
    <p class="text-secondary mb-4">This page or short URL does not exist.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-success">Back to dashboard</a>
@endsection