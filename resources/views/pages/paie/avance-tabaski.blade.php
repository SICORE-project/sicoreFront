@extends('layouts.app')

@section('title', 'SICORE - Avance Tabaski')
@section('content')
  <x-module-page slug="paie-avance-tabaski" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
