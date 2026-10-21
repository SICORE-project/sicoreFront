@extends('layouts.app')
@section('title', 'SICORE - Édition des fonctionnaires')
@section('content')
  <x-module-page slug="paie-edition-fonctionnaires" :data="$moduleData ?? []" :error="$apiError ?? null" />
@endsection
