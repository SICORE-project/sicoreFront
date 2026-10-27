@extends('layouts.app')

@section('title', 'SICORE - Édition de la paie')

@section('content')
  <x-module-page slug="paie-edition" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection