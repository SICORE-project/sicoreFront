@extends('layouts.app')
@section('title', 'SICORE - '.$title)
@section('content')
@php
  $teacherName = trim(($record['prenom'] ?? '').' '.($record['nom'] ?? ''));
  $canUpdate = !$error && !empty($record['id']) && \App\Support\SicoreAccess::can(request(), 'enseignants.update');
@endphp
<main class="main-content">
  <x-topbar title="Gestion du personnel" subtitle="Enseignants de votre Inspection d’Académie" icon="fa-solid fa-users" />
  <section class="content-area">
    <div class="actions-row teacher-list-actions">
      <p class="breadcrumb">Gestion du personnel &gt; Enseignants &gt; {{ $teacherName ?: 'Dossier' }} — Périmètre : {{ $scopeLabel }}</p>
      <div class="actions-group">
        <a class="btn-secondary" href="{{ route($backRoute) }}">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour à la liste
        </a>
        @if ($canUpdate)
          <a class="btn-primary" href="{{ route('ia.teachers.edit', $record['id']) }}">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Modifier
          </a>
        @endif
      </div>
    </div>

    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if ($error)
      <p class="alert alert-warning" role="alert">{{ $error }}</p>
    @else
      <section class="table-card teacher-show-card">
        <div class="panel-header">
          <div>
            <h2><i class="fa-solid fa-id-card" aria-hidden="true"></i> Dossier de l’enseignant</h2>
            <p>{{ $teacherName ?: '—' }}@if (!empty($record['matricule'])) · Matricule {{ $record['matricule'] }}@endif</p>
          </div>
        </div>
        <div id="teacher-view-modal" class="teacher-show-body">
          @include('pages.enseignants.partials.profile')
        </div>
      </section>
      <script type="application/json" id="teacher-profile-record">@json($record)</script>
    @endif
  </section>
</main>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-profile.css') }}?v={{ filemtime(public_path('assets/css/teacher-profile.css')) }}">
<style>
  .teacher-show-card .panel-header h2 { display: flex; align-items: center; gap: 8px; }
  .teacher-show-body { padding: 0 20px 20px; }
</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/teacher-profile.js') }}?v={{ filemtime(public_path('assets/js/teacher-profile.js')) }}"></script>
@endpush
