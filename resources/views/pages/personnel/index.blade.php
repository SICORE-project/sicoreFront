@extends('layouts.app')

@section('title', 'SICORE - '.$title)

@section('content')
<main class="main-content">
  <x-topbar :title="$title" :subtitle="'Gestion du personnel > '.$title" :icon="$icon" />
  <section class="content-area">
    <section class="table-card">
      <div class="table-card-header">
        <div>
          <h2>{{ $title }}</h2>
          <p class="table-card-subtitle">Les fonctionnalités de cette rubrique seront disponibles prochainement.</p>
        </div>
      </div>
    </section>
  </section>
</main>
@endsection
