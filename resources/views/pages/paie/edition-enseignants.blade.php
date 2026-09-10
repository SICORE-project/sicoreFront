@extends('layouts.app')
@section('title', 'SICORE - Édition des enseignants')
@section('content')
  <x-module-page slug="paie-edition-enseignants" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
