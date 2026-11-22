@php
  $exportUrl = route('ia.payroll.export', array_filter(array_merge(
    request()->only([
      'academic_year_id', 'statut_enseignant_id', 'ief_id', 'matricule',
      'payment_place_id', 'training_center_id', 'tabaski_only',
    ]),
    ['slug' => 'paie-etat-salaires', 'period_id' => data_get($moduleData, 'period.id')]
  ), fn ($value) => $value !== null && $value !== ''));
@endphp

<section class="salary-ia-statement" data-salary-ia aria-labelledby="salaryIaTitle">
  <header class="salary-ia-print-header">
    <h3 id="salaryIaTitle">{{ $statement['heading'] ?? 'État des salaires' }}</h3>
    <p class="salary-ia-print-meta">
      <span>N° Carton : <strong data-salary-ia-print="carton">—</strong></span>
      <span>N° Bon d'engagement : <strong data-salary-ia-print="bon">—</strong></span>
    </p>
  </header>

  <div class="salary-ia-toolbar">
    <button class="btn-secondary" type="button" data-salary-ia-check="1"><i class="fa-solid fa-square-check" aria-hidden="true"></i> Tous cocher</button>
    <button class="btn-secondary" type="button" data-salary-ia-check="0"><i class="fa-regular fa-square" aria-hidden="true"></i> Tous décocher</button>
    <span class="salary-ia-selection"><strong data-salary-ia-count>0</strong> / {{ $statementRows->count() }} agent(s) coché(s)</span>
  </div>

  <div class="salary-statement-table-wrap salary-ia-table-wrap">
    <table class="salary-statement-table salary-ia-table">
      <thead>
        <tr>
          <th class="salary-ia-check" scope="col"><span class="sr-only">Sélection</span></th>
          <th scope="col">N° Ordre</th>
          <th scope="col">Prénom(s)</th>
          <th scope="col">Nom</th>
          <th class="salary-statement-amount" scope="col">Net perçu</th>
          <th scope="col">N° CNI</th>
          <th scope="col">Banque</th>
          <th scope="col">N° Compte</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($statementRows as $row)
          <tr data-salary-ia-row data-reference="{{ $row['reference'] ?? '' }}" data-net="{{ (float) ($row['net'] ?? 0) }}">
            <td class="salary-ia-check">
              {{-- Comme FINPRONET : toutes les lignes sont cochées à l'ouverture. --}}
              <input type="checkbox" checked aria-label="Inclure {{ $row['first_name'] ?? '' }} {{ $row['last_name'] ?? '' }}" data-salary-ia-box>
            </td>
            <td>{{ $row['sequence'] ?? $loop->iteration }}</td>
            <td>{{ $row['first_name'] ?? '—' }}</td>
            <td>{{ $row['last_name'] ?? '—' }}</td>
            <td class="salary-statement-amount">{{ $row['net_display'] ?? '0' }}</td>
            <td>{{ $row['cni'] ?? '—' }}</td>
            <td>{{ $row['bank'] ?? '—' }}</td>
            <td>{{ $row['account'] ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="8">Aucun agent ne correspond aux critères sélectionnés.</td></tr>
        @endforelse
      </tbody>
      @if ($statementRows->isNotEmpty())
        <tfoot>
          <tr>
            <td class="salary-ia-check"></td>
            <td colspan="3">TOTAL DES AGENTS COCHÉS</td>
            <td class="salary-statement-amount" data-salary-ia-total-cell>0</td>
            <td colspan="3"></td>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <footer class="salary-statement-footer salary-ia-footer">
    <div>
      @if (! empty($statement['service_done']))
        <strong>Certification du service fait</strong><span>Les éléments portés sur le présent état correspondent aux bulletins calculés pour la période.</span>
      @else
        <strong>État édité sans mention de service fait</strong><span>Option d’impression sélectionnée par l’utilisateur.</span>
      @endif
    </div>
    <div class="salary-statement-signatory" data-salary-ia-visa>
      <span>Ordonnateur de crédit</span>
      <strong data-salary-ia-print="ordonnateur">L’Inspecteur d’Académie (IA)</strong>
    </div>
    <div class="salary-statement-signatory">
      <span>Visa et signature</span>
      <strong data-salary-ia-print="signataire">L’Inspecteur d’Académie (IA)</strong>
    </div>
  </footer>

  <div class="salary-ia-actions">
    <div class="salary-ia-choices">
      <div class="salary-statement-ordonnateur" role="radiogroup" aria-labelledby="salaryIaSignataireLabel">
        <span id="salaryIaSignataireLabel">Signataire</span>
        <div class="salary-statement-segmented">
          <label><input type="radio" name="salary_ia_signataire" value="IA" checked data-salary-ia-signataire><span>IA</span></label>
          <label><input type="radio" name="salary_ia_signataire" value="IEF" data-salary-ia-signataire><span>IEF</span></label>
        </div>
      </div>
      <div class="salary-statement-ordonnateur" role="radiogroup" aria-labelledby="salaryIaOrdonnateurLabel">
        <span id="salaryIaOrdonnateurLabel">Ordonnateur de crédit</span>
        <div class="salary-statement-segmented">
          <label><input type="radio" name="salary_ia_ordonnateur" value="IEF" data-salary-ia-ordonnateur><span>IEF</span></label>
          <label><input type="radio" name="salary_ia_ordonnateur" value="IA" checked data-salary-ia-ordonnateur><span>IA</span></label>
        </div>
      </div>
      <label class="salary-ia-visa-option">
        <input type="checkbox" data-salary-ia-hide-visa>
        Ne pas afficher « VISA ORDONNATEUR DE CRÉDIT » sur l'état
      </label>
    </div>

    <div class="salary-ia-fields">
      <div class="form-group">
        <label for="salaryIaCarton">N° Carton</label>
        <input class="form-control" id="salaryIaCarton" type="text" maxlength="50" autocomplete="off" data-salary-ia-input="carton">
      </div>
      <div class="form-group">
        <label for="salaryIaBon">N° Bon d'engagement</label>
        <input class="form-control" id="salaryIaBon" type="text" maxlength="50" autocomplete="off" data-salary-ia-input="bon">
      </div>
    </div>

    <div class="salary-ia-buttons">
      <button class="btn-secondary" type="button" data-salary-ia-recalc><i class="fa-solid fa-calculator" aria-hidden="true"></i> Recalculer montant</button>
      <output class="salary-ia-total" data-salary-ia-total>0</output>
      <span class="salary-ia-buttons-spacer"></span>
      <button class="btn-secondary" type="button" onclick="window.close()"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Fermer</button>
      <a class="btn-secondary is-disabled" href="{{ $exportUrl }}" data-salary-ia-sica><i class="fa-solid fa-file-csv" aria-hidden="true"></i> Fichier SICA</a>
      <button class="btn-primary" type="button" data-salary-ia-print-button><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimer</button>
    </div>
  </div>
</section>

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const root = document.querySelector('[data-salary-ia]');
      if (!root) return;

      // Active la mise en page d'impression IA, y compris pour Ctrl+P.
      document.body.classList.add('salary-ia-printing');

      const rows = Array.from(root.querySelectorAll('[data-salary-ia-row]'));
      const totalOutput = root.querySelector('[data-salary-ia-total]');
      const totalCell = root.querySelector('[data-salary-ia-total-cell]');
      const countOutput = root.querySelector('[data-salary-ia-count]');
      const sica = root.querySelector('[data-salary-ia-sica]');
      const printButton = root.querySelector('[data-salary-ia-print-button]');
      const money = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 });
      const titles = {
        IA: 'L’Inspecteur d’Académie (IA)',
        IEF: 'L’Inspecteur de l’Éducation et de la Formation (IEF)',
      };

      // Total et nombre des agents cochés ; les lignes décochées ne s'impriment pas.
      const recalc = () => {
        let total = 0;
        let count = 0;
        rows.forEach((row) => {
          const checked = row.querySelector('[data-salary-ia-box]').checked;
          row.classList.toggle('is-selected', checked);
          if (checked) {
            total += Number(row.dataset.net) || 0;
            count += 1;
          }
        });
        const formatted = money.format(total);
        if (totalOutput) totalOutput.textContent = formatted;
        if (totalCell) totalCell.textContent = formatted;
        if (countOutput) countOutput.textContent = count;
        if (sica) sica.classList.toggle('is-disabled', count === 0);
        if (printButton) printButton.disabled = count === 0;
      };

      root.querySelectorAll('[data-salary-ia-check]').forEach((button) => {
        button.addEventListener('click', () => {
          const state = button.dataset.salaryIaCheck === '1';
          rows.forEach((row) => { row.querySelector('[data-salary-ia-box]').checked = state; });
          recalc();
        });
      });
      root.addEventListener('change', (event) => {
        if (event.target.matches('[data-salary-ia-box]')) recalc();
      });
      root.querySelector('[data-salary-ia-recalc]')?.addEventListener('click', recalc);

      // Fichier SICA : les bulletins décochés sont exclus de l'export.
      sica?.addEventListener('click', (event) => {
        const unchecked = rows.filter((row) => !row.querySelector('[data-salary-ia-box]').checked);
        if (unchecked.length === rows.length) {
          event.preventDefault();
          return;
        }
        const url = new URL(sica.href, window.location.origin);
        url.searchParams.delete('exclude[]');
        unchecked.forEach((row) => url.searchParams.append('exclude[]', row.dataset.reference));
        sica.href = url.toString();
      });

      // Champs et choix reportés sur le pied de l'état (non enregistrés).
      root.querySelectorAll('[data-salary-ia-input]').forEach((input) => {
        const target = root.querySelector('[data-salary-ia-print="' + input.dataset.salaryIaInput + '"]');
        input.addEventListener('input', () => { target.textContent = input.value.trim() || '—'; });
      });
      const bindRadio = (selector, key) => {
        root.querySelectorAll(selector).forEach((radio) => {
          radio.addEventListener('change', () => {
            if (radio.checked) root.querySelector('[data-salary-ia-print="' + key + '"]').textContent = titles[radio.value];
          });
        });
      };
      bindRadio('[data-salary-ia-signataire]', 'signataire');
      bindRadio('[data-salary-ia-ordonnateur]', 'ordonnateur');
      root.querySelector('[data-salary-ia-hide-visa]')?.addEventListener('change', (event) => {
        root.querySelector('[data-salary-ia-visa]').hidden = event.target.checked;
      });

      printButton?.addEventListener('click', () => {
        recalc();
        window.print();
      });

      recalc();
    });
  </script>
@endpush
