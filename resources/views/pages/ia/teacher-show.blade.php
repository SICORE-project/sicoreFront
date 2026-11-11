@extends('layouts.app')
@section('title', 'SICORE - '.$title)
@section('content')
<main class="main-content">
  <x-topbar :title="$title" :subtitle="$scopeLabel" icon="fa-solid fa-user" />
  <section class="content-area">
    <a class="btn-secondary" href="{{ route($backRoute) }}">Retour à la liste</a>
    @if ($error)
      <p class="alert alert-warning" role="alert">{{ $error }}</p>
    @else
      <div id="teacher-view-modal" style="margin-top: 20px">
        @include('pages.enseignants.partials.profile')
      </div>
      <script type="application/json" id="teacher-profile-record">@json($record)</script>
    @endif
  </section>
</main>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-profile.css') }}?v={{ filemtime(public_path('assets/css/teacher-profile.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/teacher-profile.js') }}?v={{ filemtime(public_path('assets/js/teacher-profile.js')) }}"></script>
@endpush
