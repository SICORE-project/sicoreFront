(() => {
    'use strict';
    function initialize() {
        const chart = document.getElementById('teacher-net-chart');
        if (!chart) return;
        const history = JSON.parse(chart.dataset.history || '[]');
        const caption = document.getElementById('teacher-chart-caption');
        const buttons = document.querySelectorAll('[data-teacher-range]');
        const formatter = new Intl.NumberFormat('fr-FR', {maximumFractionDigits: 0});
        const compact = new Intl.NumberFormat('fr-FR', {notation: 'compact', maximumFractionDigits: 1});
        const ns = 'http://www.w3.org/2000/svg';
        function node(tag, attributes, text) {
            const element = document.createElementNS(ns, tag);
            Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
            if (text !== undefined) element.textContent = text;
            return element;
        }
        function draw(count) {
            chart.replaceChildren();
            const points = history.slice(-count);
            buttons.forEach(button => button.setAttribute('aria-pressed', String(Number(button.dataset.teacherRange) === count)));
            if (!points.length) return;
            const description = `${points.length} période(s) disponible(s) · Montants nets en FCFA`;
            caption.textContent = description;
            const svg = node('svg', {viewBox: '0 0 680 265', role: 'group', 'aria-label': 'Évolution de mes montants nets par période'});
            const defs = node('defs', {});
            const gradient = node('linearGradient', {id: 'teacher-net-fill', x1: 0, y1: 0, x2: 0, y2: 1});
            gradient.append(node('stop', {offset: '0%', 'stop-color': '#14866d', 'stop-opacity': '.18'}), node('stop', {offset: '100%', 'stop-color': '#14866d', 'stop-opacity': '.01'}));
            defs.append(gradient); svg.append(defs);
            const min = Math.min(0, ...points.map(point => point.amount));
            const max = Math.max(1, ...points.map(point => point.amount));
            const y = value => 215 - (value - min) / (max - min) * 190;
            for (let i = 0; i <= 4; i++) {
                const value = min + (max - min) * i / 4;
                svg.append(node('line', {x1: 65, x2: 655, y1: y(value), y2: y(value), stroke: '#edf1f5', 'stroke-dasharray': '4 5'}));
                svg.append(node('text', {x: 55, y: y(value) + 4, 'text-anchor': 'end', fill: '#8b98a8', 'font-size': 11}, compact.format(value)));
            }
            const positions = points.map((point, index) => ({...point, x: points.length === 1 ? 360 : 75 + index * 570 / (points.length - 1), y: y(point.amount)}));
            const path = positions.map((point, index) => `${index ? 'L' : 'M'} ${point.x} ${point.y}`).join(' ');
            if (positions.length > 1) {
                svg.append(node('path', {d: `${path} L ${positions.at(-1).x} ${y(0)} L ${positions[0].x} ${y(0)} Z`, fill: 'url(#teacher-net-fill)'}));
                svg.append(node('path', {d: path, fill: 'none', stroke: '#14866d', 'stroke-width': 3, 'stroke-linejoin': 'round', 'stroke-linecap': 'round'}));
            }
            positions.forEach((point, index) => {
                if (positions.length <= 6 || index % 2 === 0 || index === positions.length - 1) {
                    svg.append(node('text', {x: point.x, y: 245, 'text-anchor': 'middle', fill: '#8b98a8', 'font-size': 11}, point.period));
                }
                const label = `${point.period} : ${formatter.format(point.amount)} FCFA`;
                const dot = node('circle', {cx: point.x, cy: point.y, r: 5, fill: '#fff', stroke: '#14866d', 'stroke-width': 2.5, tabindex: 0, role: 'img', 'aria-label': label});
                dot.append(node('title', {}, label));
                ['mouseenter', 'focus'].forEach(event => dot.addEventListener(event, () => { caption.textContent = label; }));
                ['mouseleave', 'blur'].forEach(event => dot.addEventListener(event, () => { caption.textContent = description; }));
                svg.append(dot);
            });
            chart.append(svg);
        }
        buttons.forEach(button => button.addEventListener('click', () => draw(Number(button.dataset.teacherRange))));
        draw(6);
    }
    document.addEventListener('DOMContentLoaded', initialize);
})();
