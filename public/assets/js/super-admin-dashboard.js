/**
 * Tableau de bord super-admin : histogramme interactif, résumé de période,
 * compteurs animés, survol des répartitions et bouton d'actualisation.
 * Vue : resources/views/pages/dashboard/super-admin.blade.php
 */
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const fmt = new Intl.NumberFormat('fr-FR');

    /* ---------- Compteurs animés des cartes ---------- */
    if (!reduceMotion) {
        document.querySelectorAll('.sa-dashboard [data-sa-count]').forEach(node => {
            const target = Number(node.dataset.saCount);
            const textNode = node.firstChild;
            if (!Number.isFinite(target) || target <= 0 || !textNode || textNode.nodeType !== Node.TEXT_NODE) return;
            const start = performance.now();
            const duration = 800;
            const step = now => {
                const progress = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);
                textNode.textContent = fmt.format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            };
            textNode.textContent = '0';
            requestAnimationFrame(step);
        });
    }

    /* ---------- Bouton Actualiser ---------- */
    document.querySelectorAll('[data-sa-refresh]').forEach(link => link.addEventListener('click', () => {
        link.classList.add('is-loading');
        link.setAttribute('aria-busy', 'true');
    }));

    /* ---------- Répartitions : survol légende <-> segment ---------- */
    document.querySelectorAll('[data-sa-distribution]').forEach(panel => {
        const setActive = index => {
            panel.classList.toggle('is-highlighting', index !== null);
            panel.querySelectorAll('[data-index]').forEach(el => el.classList.toggle('is-active', el.dataset.index === index));
        };
        panel.querySelectorAll('.sa-legend li, .sa-donut-segment').forEach(el => {
            el.addEventListener('mouseenter', () => setActive(el.dataset.index));
            el.addEventListener('focus', () => setActive(el.dataset.index));
            el.addEventListener('mouseleave', () => setActive(null));
            el.addEventListener('blur', () => setActive(null));
        });
    });

    /* ---------- Histogramme ---------- */
    const source = document.getElementById('sa-history-data');
    const container = document.getElementById('sa-history-chart');
    const tooltip = document.getElementById('sa-chart-tooltip');
    if (!source || !container) return;

    const history = JSON.parse(source.textContent || '[]');
    const ns = 'http://www.w3.org/2000/svg';
    const series = [
        {key: 'teachers', color: '#e4c600', label: 'Enseignants'},
        {key: 'users', color: '#007451', label: 'Comptes utilisateurs'},
    ];
    const element = (name, attrs, text) => {
        const node = document.createElementNS(ns, name);
        Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, value));
        if (text !== undefined) node.textContent = text;
        return node;
    };

    function showTooltip(point, anchor) {
        if (!tooltip) return;
        tooltip.innerHTML = '';
        const title = document.createElement('strong');
        title.textContent = point.label;
        tooltip.append(title);
        series.forEach(s => {
            const row = document.createElement('span');
            const dot = document.createElement('i');
            dot.style.background = s.color;
            row.append(dot, `${s.label} : ${fmt.format(point[s.key] || 0)}`);
            tooltip.append(row);
        });
        tooltip.hidden = false;
        const panel = container.closest('.sa-panel').getBoundingClientRect();
        const rect = anchor.getBoundingClientRect();
        const tipWidth = tooltip.offsetWidth;
        let left = rect.left - panel.left + rect.width / 2 - tipWidth / 2;
        left = Math.max(8, Math.min(left, panel.width - tipWidth - 8));
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${rect.top - panel.top - tooltip.offsetHeight - 10}px`;
    }
    const hideTooltip = () => { if (tooltip) tooltip.hidden = true; };

    function updateSummary(points) {
        const totals = {teachers: 0, users: 0};
        let peak = null;
        points.forEach(p => {
            totals.teachers += p.teachers || 0;
            totals.users += p.users || 0;
            const sum = (p.teachers || 0) + (p.users || 0);
            if (sum > 0 && (!peak || sum > peak.sum)) peak = {label: p.label, sum};
        });
        document.querySelectorAll('[data-sa-total]').forEach(node => {
            node.textContent = fmt.format(totals[node.dataset.saTotal] || 0);
        });
        const peakNode = document.querySelector('[data-sa-peak]');
        if (peakNode) peakNode.textContent = peak ? peak.label : '—';
    }

    function render(months) {
        hideTooltip();
        const points = history.slice(-months);
        updateSummary(points);

        const W = 720, H = 270, top = 16, bottom = 228, left = 48, right = 708;
        const svg = element('svg', {viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': `Créations mensuelles sur ${months} mois`});
        svg.append(element('title', {}, 'Enseignants ajoutés et comptes utilisateurs créés par mois'));

        const max = Math.max(4, ...points.flatMap(p => [p.teachers || 0, p.users || 0]));
        const ceiling = Math.ceil(max / 4) * 4;
        const plotH = bottom - top;

        for (let i = 0; i <= 4; i++) {
            const y = bottom - i * plotH / 4;
            svg.append(element('line', {x1: left, x2: right, y1: y, y2: y, stroke: i === 0 ? '#cbd5e1' : '#e5e9ee', 'stroke-dasharray': i === 0 ? '' : '3 5'}));
            svg.append(element('text', {x: left - 10, y: y + 4, 'text-anchor': 'end', fill: '#77808b', 'font-size': 11}, fmt.format(ceiling * i / 4)));
        }

        const slot = (right - left) / Math.max(1, points.length);
        const barW = Math.min(26, slot * 0.3);
        points.forEach((point, index) => {
            const x = left + index * slot;
            const isLast = index === points.length - 1;

            // Zone de survol de toute la colonne (souris, clavier, tactile).
            const hit = element('rect', {x, y: top, width: slot, height: plotH, fill: 'transparent', class: 'sa-bar-hit', tabindex: 0,
                'aria-label': `${point.label} · Enseignants : ${point.teachers || 0} · Comptes : ${point.users || 0}`});

            const group = element('g', {class: 'sa-bar-group'});
            group.append(element('rect', {x, y: top, width: slot, height: plotH, class: 'sa-bar-bg', rx: 6}));
            series.forEach((s, i) => {
                const value = point[s.key] || 0;
                const h = Math.max(value > 0 ? 2 : 0, value / ceiling * plotH);
                const bx = x + slot / 2 - barW - 2 + i * (barW + 4);
                const bar = element('rect', {x: bx, y: bottom - h, width: barW, height: h, rx: Math.min(4, barW / 3), fill: s.color, class: 'sa-bar'});
                if (!reduceMotion) bar.style.animationDelay = `${index * 35}ms`;
                group.append(bar);
            });
            group.append(element('text', {x: x + slot / 2, y: bottom + 22, 'text-anchor': 'middle', fill: isLast ? '#202528' : '#77808b',
                'font-size': 11, 'font-weight': isLast ? 700 : 400}, point.label));
            svg.append(group);

            const enter = () => { group.classList.add('is-hover'); showTooltip(point, hit); };
            const leave = () => { group.classList.remove('is-hover'); hideTooltip(); };
            hit.addEventListener('mouseenter', enter);
            hit.addEventListener('focus', enter);
            hit.addEventListener('mouseleave', leave);
            hit.addEventListener('blur', leave);
            hit.addEventListener('touchstart', enter, {passive: true});
            svg.append(hit);
        });

        container.replaceChildren(svg);
        if (!points.some(p => p.teachers || p.users)) {
            const message = document.createElement('p');
            message.textContent = 'Aucune création enregistrée sur cette période.';
            message.className = 'dashboard-muted sa-chart-empty';
            container.append(message);
        }
    }

    document.querySelectorAll('.sa-switch button').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.sa-switch button').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
        render(Number(button.dataset.months));
    }));
    document.addEventListener('scroll', hideTooltip, {passive: true});
    render(12);
})();
