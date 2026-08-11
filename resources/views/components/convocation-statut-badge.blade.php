@props(['statut'])
 
@php
  $libelles = [
    'brouillon' => ['Brouillon', 'badge-pending'],
    'emise' => ['Emise', 'badge-primary'],
    'envoyee' => ['Envoyee', 'badge-active'],
    'cloturee' => ['Cloturee', 'badge-inactive'],
  ];
  [$libelle, $classe] = $libelles[$statut] ?? [ucfirst((string) $statut) ?: 'Inconnu', 'badge-pending'];
@endphp
 
<span class="badge {{ $classe }}">{{ $libelle }}</span>
 