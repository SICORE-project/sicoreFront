@extends('layouts.app')
@section('title', 'SICORE - Édition mutuelle de santé')
@section('content')
  <x-module-page slug="paie-mutuelles-sante" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
