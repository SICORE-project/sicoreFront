document.querySelectorAll('[data-interface-preview]').forEach(function (preview) {
    const form = preview.closest('form');
    if (!form) return;
    const catalogue = JSON.parse(preview.dataset.catalogue);
    const pages = JSON.parse(preview.dataset.pages);
    const list = preview.querySelector('[data-interface-pages]');
    const empty = preview.querySelector('[data-interface-empty]');
    function render() {
        const permissions = new Set(Array.from(form.querySelectorAll('input[name="permissions[]"]:checked'))
            .map(input => catalogue[input.value]).filter(Boolean));
        const visible = pages.filter(page => page.permissions.some(permission => permissions.has(permission)));
        const home = document.createElement('li');
        home.textContent = 'Tableau de bord';
        list.replaceChildren(home);
        visible.forEach(page => {
            const item = document.createElement('li');
            item.textContent = page.label;
            list.appendChild(item);
        });
        empty.hidden = visible.length > 0;
    }
    form.addEventListener('change', render);
    render();
});
