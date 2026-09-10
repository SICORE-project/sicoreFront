@extends('layouts.app')
@section('title', 'SICORE - Directeurs par intérim')
@section('content')
  <x-module-page slug="paie-directeurs-interim" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
