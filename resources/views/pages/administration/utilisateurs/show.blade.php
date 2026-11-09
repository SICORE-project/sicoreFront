@extends('layouts.app')
@section('title', 'SICORE - Fiche utilisateur')
@section('content')
<main class="main-content">
  <x-topbar title="Fiche utilisateur" subtitle="Administration > Utilisateurs" icon="fa-solid fa-user-shield" />
  <section class="content-area">
    <div class="actions-group" style="margin-bottom: 20px">
      <a class="btn-secondary" href="{{ route('utilisateurs.index') }}">Retour</a>
      <a class="btn-primary" href="{{ route('utilisateurs.edit', data_get($user, 'id')) }}">Modifier</a>
    </div>
    @include('pages.administration.utilisateurs.profile')
    <script type="application/json" id="user-profile-record">@json($user)</script>
  </section>
</main>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-profile.css') }}?v={{ filemtime(public_path('assets/css/teacher-profile.css')) }}">
<style>#view-user-modal .modal-dialog { width: min(920px, calc(100% - 32px)); }</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/user-profile.js') }}?v={{ filemtime(public_path('assets/js/user-profile.js')) }}"></script>
@endpush
