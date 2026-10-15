(() => {
    const source = document.getElementById('sa-history-data');
    const container = document.getElementById('sa-history-chart');
    if (!source || !container) return;
    const history = JSON.parse(source.textContent);
    const ns = 'http://www.w3.org/2000/svg';
    const element = (name, attrs, text) => {
        const node = document.createElementNS(ns, name);
        Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, value));
        if (text !== undefined) node.textContent = text;
        return node;
    };
    function render(months) {
        const points = history.slice(-months);
        const svg = element('svg', {viewBox: '0 0 720 260', role: 'img', 'aria-label': `Créations mensuelles sur ${months} mois`});
        svg.append(element('title', {}, 'Enseignants ajoutés et comptes utilisateurs créés par mois'));
        const max = Math.max(4, ...points.flatMap(p => [p.teachers, p.users]));
        const ceiling = Math.ceil(max / 4) * 4;
        for (let i = 0; i <= 4; i++) {
            const y = 220 - i * 48;
            svg.append(element('line', {x1: 45, x2: 708, y1: y, y2: y, stroke: '#dce1e5', 'stroke-dasharray': '2 4'}));
            svg.append(element('text', {x: 36, y: y + 4, 'text-anchor': 'end', fill: '#77808b', 'font-size': 10}, new Intl.NumberFormat('fr').format(ceiling * i / 4)));
        }
        const width = 660 / Math.max(1, points.length);
        points.forEach((point, index) => {
            const x = 48 + index * width;
            [['teachers', '#e4c600', 'Enseignants'], ['users', '#007451', 'Comptes utilisateurs']].forEach(([key, color, label], series) => {
                const height = point[key] / ceiling * 192;
                const bar = element('rect', {x: x + width * (.16 + series * .34), y: 220 - height, width: width * .28, height, rx: 2, fill: color, tabindex: 0, 'aria-label': `${point.label} · ${label} : ${point[key]}`});
                bar.append(element('title', {}, `${point.label} · ${label} : ${point[key]}`));
                svg.append(bar);
            });
            svg.append(element('text', {x: x + width / 2, y: 242, 'text-anchor': 'middle', fill: '#77808b', 'font-size': 9}, point.label));
        });
        container.replaceChildren(svg);
        if (!points.some(p => p.teachers || p.users)) {
            const message = document.createElement('p');
            message.textContent = 'Aucune création enregistrée sur cette période.';
            message.className = 'dashboard-muted';
            container.append(message);
        }
    }
    document.querySelectorAll('.sa-switch button').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('.sa-switch button').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
        render(Number(button.dataset.months));
    }));
    render(12);
})();
