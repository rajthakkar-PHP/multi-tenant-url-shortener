@extends('layouts.guest')

@section('title', 'Link unavailable')

@section('content')
    <p class="text-uppercase text-success fw-semibold small mb-2">410</p>
    <h1 class="h3 mb-2">Link unavailable</h1>
    <p class="text-secondary mb-4">This invitation has expired or has already been used.</p>
    <a href="{{ route('login') }}" class="btn btn-success">Back to sign in</a>
@endsection