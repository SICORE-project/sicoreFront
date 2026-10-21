@extends('layouts.app')
@section('title', 'SICORE - Récapitulatif des éléments par corps')
@section('content')
  <x-module-page slug="paie-recap-elements-corps" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
