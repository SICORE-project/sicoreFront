@extends('layouts.app')
@section('title', 'SICORE - Double flux')
@section('content')
  <x-module-page slug="paie-double-flux" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
