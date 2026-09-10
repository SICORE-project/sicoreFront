@extends('layouts.app')
@section('title', 'SICORE - Cumul des enseignants par IEF')
@section('content')
  <x-module-page slug="paie-cumul-enseignants-ief" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
