@if(session()->has('teacher_import_report'))
  @php($report = session('teacher_import_report'))
  <section class="panel" aria-label="Bilan de l’import">
    <h2>Bilan de l’import</h2>
    <p>Les numéros correspondent aux lignes du CSV, en comptant l’en-tête comme ligne 1. Les lignes bloquées doivent être corrigées puis réimportées seules.</p>
    <h3>{{ ($report['status'] ?? '') === 'pending' ? 'Lignes soumises à la DAGE (en attente de validation)' : 'Lignes insérées' }} : {{ count($report['accepted'] ?? []) }}</h3>
    <ul>
      @forelse($report['accepted'] ?? [] as $row)
        <li>Ligne {{ $row['line'] }} — {{ $row['matricule'] }} — {{ $row['prenom'] }} {{ $row['nom'] }}</li>
      @empty<li>Aucune ligne acceptée.</li>@endforelse
    </ul>
    <div @if(count($report['blocked'] ?? [])) style="color: #b91c1c; background: #fef2f2; border-left: 4px solid #dc2626; padding: 14px 18px; border-radius: 8px;" role="alert" @endif>
    <h3 style="color: inherit;">Lignes bloquées : {{ count($report['blocked'] ?? []) }}</h3>
    @forelse($report['blocked'] ?? [] as $row)
      <p style="color: inherit;"><strong>Ligne {{ $row['line'] }} — {{ $row['matricule'] ?? 'Matricule indisponible' }} — {{ $row['prenom'] ?? '' }} {{ $row['nom'] ?? '' }}</strong></p>
      <ul>
        @foreach($row['errors'] as $field => $messages)
          @foreach((array) $messages as $message)<li><strong>{{ $field }}</strong> : {{ $message }}</li>@endforeach
        @endforeach
      </ul>
    @empty<p>Aucune ligne bloquée.</p>@endforelse
    </div>
  </section>
@endif
