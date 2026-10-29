@extends('layouts.base')

{{-- Page connectée autonome, sans navigation latérale. --}}
@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
@endpush

@section('body_attributes')
class="standalone-body"
@endsection

@section('body')
  <x-flash-messages />
  @yield('content')
@endsection
