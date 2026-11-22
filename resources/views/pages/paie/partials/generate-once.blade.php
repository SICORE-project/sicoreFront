{{--
  « Générer » une seule fois par mois : pour une IA, un mois déjà traité
  affiche « Consulter ». Le libellé suit le mois et l'année choisis dans le
  formulaire.
  Paramètres : $selector (bouton), $states (edition_months du backend),
  $generate / $consult (libellés), $rule ('validated' pour l'Édition,
  'generated' pour l'État des salaires).
--}}
@if (!empty($states))
@push('scripts')
<script>
  (function () {
    var states = @json($states);
    var rule = @json($rule);
    var labels = { generate: @json($generate), consult: @json($consult) };
    document.querySelectorAll(@json($selector)).forEach(function (button) {
      var form = button.form || button.closest('form');
      if (!form) return;
      var text = button.querySelector('[data-generate-label]');
      var icon = button.querySelector('i');
      function done(state) {
        if (!state) return false;
        return rule === 'generated' ? state.statement_generated : state.status === 'validee';
      }
      function refresh() {
        var month = form.querySelector('[name="payroll_month"]');
        var year = form.querySelector('[name="annee_academique_id"], [name="academic_year_id"]');
        var period = form.querySelector('[name="period_id"]');
        var state = (month && year && month.value && year.value) ? states[year.value + '-' + month.value]
          : (period && period.value ? states['p' + period.value] : null);
        var consult = done(state);
        if (text) text.textContent = consult ? labels.consult : labels.generate;
        if (icon) icon.className = consult ? 'fa-solid fa-eye' : (icon.dataset.generateIcon || icon.className);
        button.title = consult ? 'Ce mois a déjà été traité : consultation uniquement.' : '';
      }
      if (icon) icon.dataset.generateIcon = icon.className;
      form.addEventListener('change', refresh);
      refresh();
    });
  }());
</script>
@endpush
@endif
