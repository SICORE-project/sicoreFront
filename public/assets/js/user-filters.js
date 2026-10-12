document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('users-filter-form');
    if (!form) return;
    document.getElementById('utilisateurs-page-size')?.addEventListener('change', () => form.requestSubmit());
    const fields = Array.from(form.querySelectorAll('input[type="search"]'));
    const ia = document.getElementById('users-filter-ia');
    const ief = document.getElementById('users-filter-ief');
    const school = document.getElementById('users-filter-school');
    const focusKey = 'users-filter-focus';
    let timer;
    try {
        const saved = JSON.parse(sessionStorage.getItem(focusKey) || 'null');
        sessionStorage.removeItem(focusKey);
        const field = fields.find(item => item.id === saved?.id && item.value === saved.value);
        if (field) { field.focus(); field.setSelectionRange(saved.start, saved.end); }
    } catch (error) {}
    fields.forEach(field => {
        field.addEventListener('input', event => {
            clearTimeout(timer);
            if (!event.isComposing) timer = setTimeout(() => form.requestSubmit(), 450);
        });
        field.addEventListener('compositionend', () => {
            clearTimeout(timer);
            timer = setTimeout(() => form.requestSubmit(), 450);
        });
    });
    [ia, ief, school].forEach(select => {
        select.addEventListener('change', () => {
            if (select === ia) { ief.value = ''; ief.disabled = true; }
            if (select === ia || select === ief) { school.value = ''; school.disabled = true; }
            // The server reloads the dependent lists for the selected parent.
            form.requestSubmit();
        });
    });
    form.addEventListener('submit', () => {
        clearTimeout(timer);
        const field = fields.find(item => item === document.activeElement);
        if (field) {
            try { sessionStorage.setItem(focusKey, JSON.stringify({id: field.id, value: field.value, start: field.selectionStart, end: field.selectionEnd})); } catch (error) {}
        }
        document.getElementById('users-filter-status').textContent = 'Recherche en cours…';
    });
});
