@extends('layouts.app')
@section('title', 'SICORE - Prime scolaire')
@section('content')
  <x-module-page slug="paie-prime-scolaire" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
