{{--
  Barre de filtre des permissions à cocher (création / modification d'un rôle).
  À placer juste avant la liste des groupes de permissions dans le formulaire :
  elle agit sur les cases "permissions[]" et sur leurs cartes de groupe (.objective-card).
--}}
<div class="permission-filter" data-permission-filter style="display:flex; flex-wrap:wrap; align-items:center; gap:12px; margin-bottom:16px;">
    <input
        type="search"
        class="form-control permission-filter__search"
        placeholder="Rechercher une permission ou un groupe…"
        autocomplete="off"
        style="flex:1 1 260px; max-width:420px;"
    >
    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:14px;">
        <input type="checkbox" class="permission-filter__checked-only"> Cochées seulement
    </label>
    <button type="button" class="btn-secondary permission-filter__check-visible">Tout cocher (résultats)</button>
    <button type="button" class="btn-secondary permission-filter__uncheck-visible">Tout décocher (résultats)</button>
    <span class="permission-filter__count" style="font-size:13px; color:#6b7280;"></span>
</div>

<script>
    (function () {
        const bar = document.querySelector('[data-permission-filter]:not([data-initialise])');
        if (!bar) { return; }
        bar.setAttribute('data-initialise', '1');
        const form = bar.closest('form');
        if (!form) { return; }

        const search = bar.querySelector('.permission-filter__search');
        const checkedOnly = bar.querySelector('.permission-filter__checked-only');
        const count = bar.querySelector('.permission-filter__count');
        const boxes = Array.from(form.querySelectorAll('input[type="checkbox"][name="permissions[]"]'));

        const normaliser = function (texte) {
            return (texte || '').toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');
        };

        const items = boxes.map(function (box) {
            const label = box.closest('label');
            const carte = box.closest('.objective-card');
            const titre = carte ? carte.querySelector('h4') : null;
            return { box: box, label: label, carte: carte, texte: normaliser((label ? label.textContent : '') + ' ' + (titre ? titre.textContent : '')) };
        });

        const visible = function (item) { return item.label && item.label.style.display !== 'none'; };

        const majCompteur = function () {
            const cochees = boxes.filter(function (b) { return b.checked; }).length;
            count.textContent = cochees + ' sélectionnée(s) sur ' + boxes.length;
        };

        const filtrer = function () {
            const termes = normaliser(search.value).split(/\s+/).filter(Boolean);
            items.forEach(function (item) {
                const ok = termes.every(function (t) { return item.texte.includes(t); })
                    && (!checkedOnly.checked || item.box.checked);
                if (item.label) { item.label.style.display = ok ? '' : 'none'; }
            });
            new Set(items.map(function (i) { return i.carte; })).forEach(function (carte) {
                if (!carte) { return; }
                const reste = items.some(function (i) { return i.carte === carte && visible(i); });
                carte.style.display = reste ? '' : 'none';
            });
        };

        search.addEventListener('input', filtrer);
        checkedOnly.addEventListener('change', filtrer);
        bar.querySelector('.permission-filter__check-visible').addEventListener('click', function () {
            items.filter(visible).forEach(function (i) { i.box.checked = true; });
            majCompteur();
        });
        bar.querySelector('.permission-filter__uncheck-visible').addEventListener('click', function () {
            items.filter(visible).forEach(function (i) { i.box.checked = false; });
            majCompteur();
            filtrer();
        });
        boxes.forEach(function (b) { b.addEventListener('change', function () { majCompteur(); if (checkedOnly.checked) { filtrer(); } }); });

        // Entrée dans la recherche ne doit pas envoyer le formulaire.
        search.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });

        majCompteur();
    })();
</script>
