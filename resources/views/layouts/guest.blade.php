@extends('layouts.base')

@section('body_attributes')
class="guest-body"
@endsection

@section('body')
  <x-flash-messages />
  @yield('content')
@endsection
