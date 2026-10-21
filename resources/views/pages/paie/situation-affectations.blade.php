@extends('layouts.app')
@section('title', 'SICORE - Situation des affectations')
@section('content')
  <x-module-page slug="paie-situation-affectations" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
