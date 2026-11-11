@extends('layouts.app')
@section('title', 'SICORE - Consultation des bulletins')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/payroll-bulletin.css') }}?v={{ filemtime(public_path('assets/css/payroll-bulletin.css')) }}">
@endpush
@section('content')
<main class="main-content solde-page solde-group-page">
  <x-topbar title="Bulletins de salaire" :subtitle="count($bulletins).' bulletin(s) disponible(s)'" icon="fa-solid fa-file-invoice-dollar">
    <div class="actions-group"><a class="btn-secondary" href="{{ route('paie.bulletins', array_diff_key($filters, array_flip(['consult', 'suggest']))) }}">Retour aux filtres</a>
    <button class="btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button></div>
  </x-topbar>
  <section class="content-area solde-canvas">
    @foreach ($bulletins as $data)
      @include('pages.paie.partials.bulletin-document', ['data' => $data])
    @endforeach
  </section>
</main>
@endsection
