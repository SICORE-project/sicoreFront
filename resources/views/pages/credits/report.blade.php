<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }} - SICORE</title>
  <style>
    :root { color-scheme: light; font-family: Arial, sans-serif; color: #14261e; }
    * { box-sizing: border-box; }
    body { margin: 0; background: #edf4f0; }
    .report-toolbar { display: flex; justify-content: space-between; gap: 12px; max-width: 1180px; margin: 24px auto 0; padding: 0 18px; }
    .report-button { display: inline-flex; align-items: center; min-height: 42px; padding: 10px 18px; border: 1px solid #cbd8d1; border-radius: 10px; color: #163c2b; background: #fff; font-weight: 800; text-decoration: none; cursor: pointer; }
    .report-button-primary { color: #fff; border-color: #116b42; background: #116b42; }
    .report-sheet { max-width: 1180px; margin: 18px auto 32px; padding: 42px; background: #fff; box-shadow: 0 18px 50px rgba(24, 61, 43, .12); }
    .report-header { display: flex; justify-content: space-between; gap: 30px; padding-bottom: 24px; border-bottom: 4px solid #15804f; }
    .report-kicker { margin: 0 0 8px; color: #15804f; font-size: 13px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
    h1 { margin: 0; font-size: 29px; }
    .report-subtitle { margin: 8px 0 0; color: #607269; }
    .report-meta { text-align: right; font-size: 13px; font-weight: 700; }
    .report-table-wrap { margin-top: 28px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 13px 12px; border: 1px solid #dbe5df; text-align: left; vertical-align: top; }
    th { color: #fff; background: #176e48; font-size: 12px; text-transform: uppercase; }
    tbody tr:nth-child(even) { background: #f5f9f7; }
    .report-footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #dbe5df; color: #607269; font-size: 12px; }
    @media (max-width: 720px) {
      .report-sheet { margin: 12px; padding: 24px 18px; }
      .report-header { display: block; }
      .report-meta { margin-top: 16px; text-align: left; }
      .report-toolbar { margin-top: 12px; }
    }
    @media print {
      @page { size: A4 landscape; margin: 12mm; }
      body { background: #fff; }
      .report-toolbar { display: none; }
      .report-sheet { max-width: none; margin: 0; padding: 0; box-shadow: none; }
      thead { display: table-header-group; }
      tr { break-inside: avoid; }
    }
  </style>
</head>
<body>
  <nav class="report-toolbar" aria-label="Actions du document">
    <a class="report-button" href="{{ $backUrl }}">Retour à la liste</a>
    <button class="report-button report-button-primary" type="button" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
  </nav>

  <main class="report-sheet">
    <header class="report-header">
      <div>
        <p class="report-kicker">République du Sénégal · SICORE</p>
        <h1>{{ $title }}</h1>
        <p class="report-subtitle">{{ $subtitle }}</p>
      </div>
      <div class="report-meta">
        <p>Date d’édition : {{ now()->format('d/m/Y') }}</p>
        <p>Nombre d’enregistrements : {{ count($rows) }}</p>
      </div>
    </header>

    <div class="report-table-wrap">
      <table>
        <thead>
          <tr>
            @foreach ($columns as $column)
              <th>{{ $column }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($rows as $row)
            <tr>
              @foreach ($row as $cell)
                <td>{{ $cell }}</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <footer class="report-footer">
      Document généré par le Système intégré des corps émergents (SICORE).
    </footer>
  </main>
</body>
</html>
