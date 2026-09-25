document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('teacher-account-form');
    if (!form) return;
    const modal = document.getElementById('teacher-account-modal');
    const picker = document.getElementById('teacher-picker');
    const input = document.getElementById('teacher-candidates');
    const selectedId = document.getElementById('teacher-selected-id');
    const dropdown = document.getElementById('teacher-picker-dropdown');
    const list = document.getElementById('teacher-options');
    const email = document.getElementById('teacher-account-email');
    const status = document.getElementById('teacher-search-status');
    const more = document.getElementById('teacher-load-more');
    let controller, timer;
    let page = 1, revision = 0, active = -1;
    let teachers = [], query = '';

    function open() {
        dropdown.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }
    function close() {
        dropdown.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    }
    function highlight(index) {
        active = index;
        Array.from(list.children).forEach((option, i) => option.setAttribute('aria-selected', String(i === index)));
        if (list.children[index]) {
            input.setAttribute('aria-activedescendant', list.children[index].id);
            list.children[index].scrollIntoView({block: 'nearest'});
        }
    }
    function choose(teacher) {
        if (teacher.has_account) return;
        selectedId.value = teacher.id;
        input.value = `${teacher.prenom} ${teacher.nom} — ${teacher.matricule}`;
        input.setCustomValidity('');
        email.value = teacher.email || '';
        input.focus();
        close();
    }
    function render() {
        list.replaceChildren();
        teachers.forEach((teacher, index) => {
            const option = document.createElement('div');
            option.id = `teacher-option-${index}`;
            option.className = 'teacher-picker-option';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            option.setAttribute('aria-disabled', String(Boolean(teacher.has_account)));
            const name = document.createElement('strong');
            name.textContent = `${teacher.prenom} ${teacher.nom}`;
            const detail = document.createElement('small');
            detail.textContent = teacher.matricule;
            option.append(name, detail);
            if (teacher.has_account) {
                const badge = document.createElement('span');
                badge.className = 'teacher-account-exists-badge';
                badge.textContent = 'Déjà un compte';
                option.append(badge);
            }
            option.addEventListener('mousedown', event => event.preventDefault());
            option.addEventListener('click', () => choose(teacher));
            list.append(option);
        });
        active = -1;
        input.removeAttribute('aria-activedescendant');
    }
    async function load(append = false) {
        const version = ++revision;
        controller?.abort();
        controller = new AbortController();
        more.hidden = true;
        status.textContent = 'Recherche en cours…';
        if (!append) { page = 1; teachers = []; render(); }
        try {
            const url = new URL(input.dataset.url, window.location.origin);
            url.searchParams.set('search', query);
            url.searchParams.set('page', String(page));
            const response = await fetch(url, {signal: controller.signal, headers: {Accept: 'application/json'}});
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'La recherche est indisponible.');
            if (version !== revision) return;
            teachers.push(...data.data);
            render();
            status.textContent = data.total ? `${data.total} enseignant(s) — les comptes existants sont grisés` : 'Aucun enseignant ne correspond à votre recherche.';
            more.hidden = data.current_page >= data.last_page;
            page = data.current_page + 1;
        } catch (error) {
            if (error.name !== 'AbortError' && version === revision) status.textContent = error.message || 'Recherche impossible. Réessayez.';
        }
    }
    input.setCustomValidity('Sélectionnez un enseignant dans la liste.');
    input.addEventListener('focus', open);
    input.addEventListener('click', open);
    input.addEventListener('input', () => {
        clearTimeout(timer);
        controller?.abort();
        revision++;
        selectedId.value = '';
        input.setCustomValidity('Sélectionnez un enseignant dans la liste.');
        query = input.value.trim();
        teachers = [];
        render();
        more.hidden = true;
        status.textContent = 'Recherche en cours…';
        open();
        timer = setTimeout(() => load(), 300);
    });
    input.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            open();
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            let next = active < 0 ? (direction === 1 ? 0 : teachers.length - 1) : active + direction;
            while (next >= 0 && next < teachers.length && teachers[next].has_account) next += direction;
            if (next >= 0 && next < teachers.length) highlight(next);
        } else if (event.key === 'Enter' && !dropdown.hidden) {
            event.preventDefault();
            if (teachers[active]) choose(teachers[active]);
        } else if (event.key === 'Escape' && !dropdown.hidden) {
            event.preventDefault();
            event.stopPropagation();
            close();
        }
    });
    document.getElementById('teacher-picker-toggle').addEventListener('click', () => {
        const wasOpen = !dropdown.hidden;
        input.focus();
        if (wasOpen) close(); else open();
    });
    picker.addEventListener('focusout', event => { if (!picker.contains(event.relatedTarget)) close(); });
    document.addEventListener('click', event => { if (!picker.contains(event.target)) close(); });
    more.addEventListener('click', () => load(true));
    form.addEventListener('submit', event => {
        if (!selectedId.value) { event.preventDefault(); input.reportValidity(); return; }
        const button = document.getElementById('teacher-account-submit');
        button.disabled = true;
        button.textContent = 'Création et envoi en cours…';
    });
    modal.addEventListener('cancel', event => {
        event.preventDefault();
        window.location.assign(modal.dataset.returnUrl);
    });
    modal.showModal();
    load();
});
