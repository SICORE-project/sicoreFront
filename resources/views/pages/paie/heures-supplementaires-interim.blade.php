@extends('layouts.app')
@section('title', 'SICORE - Heures supplémentaires et intérim')
@section('content')
  <x-module-page slug="paie-heures-supplementaires-interim" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
