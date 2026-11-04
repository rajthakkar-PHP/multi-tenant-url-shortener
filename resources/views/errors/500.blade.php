@extends('layouts.guest')

@section('title', 'Something went wrong')

@section('content')
    <p class="text-uppercase text-success fw-semibold small mb-2">500</p>
    <h1 class="h3 mb-2">Something went wrong</h1>
    <p class="text-secondary mb-4">The request could not be completed. Please try again.</p>
    <a href="{{ route('login') }}" class="btn btn-success">Back to sign in</a>
@endsection