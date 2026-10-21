@extends('layouts.app')
@section('title', 'SICORE - Reliquats')
@section('content')
  <x-module-page slug="paie-reliquats" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
