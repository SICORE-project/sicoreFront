(() => {
  'use strict';
  const form = document.querySelector('[data-bulletin-form]');
  if (!form) return;
  const matricule = form.elements.matricule;
  const list = document.getElementById('bulletinSuggestions');
  const status = form.querySelector('[role="status"]');
  let timer, pending, version = 0;
  const close = () => { list.hidden = true; matricule.setAttribute('aria-expanded', 'false'); };
  const invalidate = () => {
    version++;
    clearTimeout(timer);
    pending?.abort();
    close();
    status.textContent = '';
  };
  const sync = () => {
    const ia = form.elements.ia_id.value;
    for (const option of form.elements.ief_id.options) {
      option.hidden = !!(option.value && ia && option.dataset.ia !== ia);
      option.disabled = option.hidden;
    }
    if (form.elements.ief_id.selectedOptions[0]?.disabled) form.elements.ief_id.value = '';
    const year = form.elements.academic_year_id.selectedOptions[0];
    for (const option of form.elements.period_id.options) {
      const date = option.dataset.code ? `${option.dataset.code}-01` : '';
      option.hidden = !!(date && year?.dataset.start && year?.dataset.end &&
        (date.slice(0, 7) < year.dataset.start.slice(0, 7) || date.slice(0, 7) > year.dataset.end.slice(0, 7)));
      option.disabled = option.hidden;
    }
    if (form.elements.period_id.selectedOptions[0]?.disabled) form.elements.period_id.value = '';
  };
  matricule.addEventListener('input', () => {
    invalidate();
    if (!matricule.value.trim()) return;
    if (!form.elements.period_id.value || !form.elements.academic_year_id.value || !form.elements.statut_enseignant_id.value) {
      status.textContent = 'Choisissez d’abord le corps, l’année et la période.';
      return;
    }
    const current = version;
    timer = setTimeout(async () => {
      pending = new AbortController();
      const params = new URLSearchParams(new FormData(form));
      params.delete('group');
      params.set('suggest', '1');
      try {
        status.textContent = 'Recherche…';
        const response = await fetch(`${form.action}?${params}`, {signal: pending.signal, headers: {'Accept': 'application/json'}});
        if (!response.ok) throw new Error('Suggestions indisponibles.');
        const payload = await response.json();
        if (current !== version) return;
        list.replaceChildren();
        for (const teacher of payload.teachers) {
          const button = document.createElement('button');
          button.type = 'button';
          button.setAttribute('role', 'option');
          button.textContent = `${teacher.matricule} — ${teacher.name}`;
          button.addEventListener('click', () => { matricule.value = teacher.matricule; invalidate(); matricule.focus(); });
          list.append(button);
        }
        status.textContent = payload.teachers.length ? '' : 'Aucun enseignant avec un bulletin disponible pour ces critères.';
        list.hidden = !payload.teachers.length;
        matricule.setAttribute('aria-expanded', String(!list.hidden));
      } catch (error) {
        if (error.name !== 'AbortError' && current === version) status.textContent = 'Suggestions indisponibles. Réessayez.';
      }
    }, 300);
  });
  form.addEventListener('change', event => {
    if (event.target === form.elements.group && event.target.checked) matricule.value = '';
    if (event.target !== matricule) { invalidate(); sync(); }
  });
  matricule.addEventListener('keydown', event => {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowDown' && !list.hidden) { event.preventDefault(); list.querySelector('button')?.focus(); }
  });
  list.addEventListener('keydown', event => {
    if (event.key === 'Escape') { close(); matricule.focus(); }
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      (event.key === 'ArrowDown' ? event.target.nextElementSibling : event.target.previousElementSibling)?.focus();
    }
  });
  document.addEventListener('click', event => { if (!event.target.closest('.bulletin-matricule')) close(); });
  sync();
})();
