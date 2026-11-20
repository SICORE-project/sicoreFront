(() => {
  'use strict';
  const names = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
  document.querySelectorAll('select[data-payroll-months]').forEach(select => {
    const periods = JSON.parse(select.dataset.payrollMonths);
    const years = JSON.parse(select.dataset.payrollYears);
    const yearSelect = select.form?.querySelector('[name="academic_year_id"], [name="annee_academique_id"]');
    const sync = () => {
      const old = periods.find(p => String(p.id) === select.value);
      const year = years.find(y => String(y.id) === yearSelect?.value);
      const candidates = periods.filter(p => !year || (p.code >= year.start_date.slice(0, 7) && p.code <= year.end_date.slice(0, 7)));
      const byMonth = new Map();
      candidates.forEach(p => {
        const month = Number(p.code.slice(5, 7));
        if (!byMonth.has(month) || String(p.id) === select.value) byMonth.set(month, p);
      });
      const placeholder = select.querySelector('option[value=""]')?.cloneNode(true);
      select.replaceChildren();
      if (placeholder) select.append(placeholder);
      [...byMonth].sort((a,b) => a[0]-b[0]).forEach(([month,p]) => select.add(new Option(names[month-1], p.id)));
      const equivalent = old ? byMonth.get(Number(old.code.slice(5, 7))) : null;
      if (equivalent) select.value = String(equivalent.id);
      else if (!placeholder && select.options.length) select.selectedIndex = 0;
    };
    yearSelect?.addEventListener('change', sync);
    sync();
  });
})();
