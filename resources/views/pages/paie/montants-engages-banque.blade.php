@extends('layouts.app')
@section('title', 'SICORE - Montants engagés par banque')
@section('content')
  <x-module-page slug="paie-montants-engages-banque" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
