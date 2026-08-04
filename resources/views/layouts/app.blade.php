@extends('layouts.base')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
@endpush

@section('body_attributes')
class="app-body" @yield('page_attributes')
@endsection

@section('body')
  <x-sidebar />
  <x-flash-messages />

  @yield('content')

  <div class="mobile-overlay" data-sidebar-close aria-hidden="true"></div>
@endsection

