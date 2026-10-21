/*
 * GRAPHIQUES DU TABLEAU DE BORD
 * Chargé uniquement par resources/views/pages/dashboard/index.blade.php.
 * Les graphiques sont dessinés directement dans des éléments <canvas>, sans
 * bibliothèque externe. Les valeurs sont actuellement de présentation.
 */
(function () {
  "use strict";

  // Palette commune garantissant la cohérence avec le thème SICORE.
  var palette = {
    primary: "#166534",
    blue: "#2563eb",
    green: "#22c55e",
    grid: "#e5e7eb",
    muted: "#64748b",
    text: "#111827",
    card: "#ffffff"
  };

  /** Adapte la résolution du canvas à sa taille CSS et à la densité de l'écran. */
  function sizeCanvas(canvas, fallbackHeight) {
    var parent = canvas.parentElement;
    var rect = parent.getBoundingClientRect();
    var width = Math.max(rect.width, 280);
    var height = Math.max(rect.height || fallbackHeight, fallbackHeight);
    var ratio = window.devicePixelRatio || 1;
    canvas.width = width * ratio;
    canvas.height = height * ratio;
    canvas.style.width = width + "px";
    canvas.style.height = height + "px";
    var context = canvas.getContext("2d");
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    return {
      ctx: context,
      width: width,
      height: height
    };
  }

  /** Dessine les lignes horizontales servant de repères aux graphiques. */
  function drawGrid(ctx, width, height, padding) {
    ctx.strokeStyle = palette.grid;
    ctx.lineWidth = 1;
    ctx.font = "12px Segoe UI, Arial, sans-serif";
    ctx.fillStyle = palette.muted;

    for (var i = 0; i <= 4; i += 1) {
      var y = padding.top + ((height - padding.top - padding.bottom) / 4) * i;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(width - padding.right, y);
      ctx.stroke();
    }
  }

  /** Prépare un rectangle à coins arrondis dans le contexte Canvas. */
  function roundRect(ctx, x, y, width, height, radius) {
    var r = Math.min(radius, Math.abs(height) / 2, width / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + width, y, x + width, y + height, r);
    ctx.arcTo(x + width, y + height, x, y + height, r);
    ctx.arcTo(x, y + height, x, y, r);
    ctx.arcTo(x, y, x + width, y, r);
    ctx.closePath();
  }

  /** Dessine plusieurs séries de barres regroupées par libellé. */
  function drawGroupedBars(canvas, labels, series) {
    var sized = sizeCanvas(canvas, 260);
    var ctx = sized.ctx;
    var width = sized.width;
    var height = sized.height;
    var padding = { top: 22, right: 18, bottom: 42, left: 42 };
    var plotWidth = width - padding.left - padding.right;
    var plotHeight = height - padding.top - padding.bottom;
    var maxValue = Math.max.apply(null, series.reduce(function (values, item) {
      return values.concat(item.values);
    }, []));
    var groupWidth = plotWidth / labels.length;
    var barWidth = Math.min(18, (groupWidth - 16) / series.length);

    ctx.clearRect(0, 0, width, height);
    drawGrid(ctx, width, height, padding);

    labels.forEach(function (label, index) {
      var groupStart = padding.left + index * groupWidth + groupWidth / 2;
      series.forEach(function (item, seriesIndex) {
        var value = item.values[index];
        var barHeight = (value / maxValue) * plotHeight;
        var x = groupStart - ((series.length * barWidth) / 2) + seriesIndex * barWidth;
        var y = padding.top + plotHeight - barHeight;
        ctx.fillStyle = item.color;
        roundRect(ctx, x, y, barWidth - 3, barHeight, 5);
        ctx.fill();
      });

      ctx.fillStyle = palette.muted;
      ctx.textAlign = "center";
      ctx.fillText(label, groupStart, height - 16);
    });
  }

  /** Dessine un graphique simple avec une seule série de barres. */
  function drawSimpleBars(canvas, labels, values, color) {
    var sized = sizeCanvas(canvas, 260);
    var ctx = sized.ctx;
    var width = sized.width;
    var height = sized.height;
    var padding = { top: 24, right: 20, bottom: 38, left: 34 };
    var plotWidth = width - padding.left - padding.right;
    var plotHeight = height - padding.top - padding.bottom;
    var maxValue = Math.max.apply(null, values);
    var groupWidth = plotWidth / values.length;
    var barWidth = Math.min(34, groupWidth * 0.52);

    ctx.clearRect(0, 0, width, height);
    drawGrid(ctx, width, height, padding);

    values.forEach(function (value, index) {
      var barHeight = (value / maxValue) * plotHeight;
      var x = padding.left + index * groupWidth + (groupWidth - barWidth) / 2;
      var y = padding.top + plotHeight - barHeight;
      ctx.fillStyle = color;
      roundRect(ctx, x, y, barWidth, barHeight, 6);
      ctx.fill();
      ctx.fillStyle = palette.muted;
      ctx.textAlign = "center";
      ctx.fillText(labels[index], x + barWidth / 2, height - 14);
    });
  }

  /** Dessine le graphique circulaire de répartition. */
  function drawDonut(canvas) {
    var sized = sizeCanvas(canvas, 260);
    var ctx = sized.ctx;
    var width = sized.width;
    var height = sized.height;
    var centerX = width / 2;
    var centerY = height / 2;
    var radius = Math.min(width, height) * 0.32;
    var value = Math.max(0, Math.min(100, percentage));
    var segments = [
      { value: value, color: palette.primary },
      { value: 100 - value, color: palette.grid }
    ];
    var total = segments.reduce(function (sum, item) {
      return sum + item.value;
    }, 0);
    var start = -Math.PI / 2;

    ctx.clearRect(0, 0, width, height);
    segments.forEach(function (segment) {
      var angle = (segment.value / total) * Math.PI * 2;
      ctx.beginPath();
      ctx.strokeStyle = segment.color;
      ctx.lineWidth = 26;
      ctx.lineCap = "round";
      ctx.arc(centerX, centerY, radius, start, start + angle - 0.05);
      ctx.stroke();
      start += angle;
    });

    ctx.beginPath();
    ctx.fillStyle = palette.card;
    ctx.arc(centerX, centerY, radius - 26, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = palette.text;
    ctx.font = "800 26px Segoe UI, Arial, sans-serif";
    ctx.textAlign = "center";
    ctx.fillText(value + "%", centerX, centerY + 6);
    ctx.fillStyle = palette.muted;
    ctx.font = "12px Segoe UI, Arial, sans-serif";
    ctx.fillText("comptes actifs", centerX, centerY + 27);
  }

  /** Recherche les canvas attendus dans la page et lance tous les dessins. */
  function renderCharts() {
    document.querySelectorAll("[data-chart]").forEach(function (canvas) {
      var chart = canvas.getAttribute("data-chart");
      if (chart === "main-bars") {
        drawSimpleBars(
          canvas,
          JSON.parse(canvas.getAttribute("data-labels") || "[]"),
          JSON.parse(canvas.getAttribute("data-values") || "[]"),
          palette.blue
        );
      }
      if (chart === "main-donut") {
        drawDonut(canvas, Number(canvas.getAttribute("data-percentage") || 0));
      }
      if (chart === "teacher-bars") {
        drawGroupedBars(
          canvas,
          ["Jan", "Fev", "Mar", "Avr", "Mai", "Juin", "Juil", "Aout", "Sept", "Oct", "Nov", "Dec"],
          [
            { label: "Enseignants", color: palette.blue, values: [820, 860, 890, 930, 975, 1010, 1040, 1088, 1130, 1180, 1218, 1247] },
            { label: "Nouveaux", color: palette.primary, values: [18, 22, 24, 28, 35, 31, 26, 38, 42, 46, 33, 49] }
          ]
        );
      }
    });
  }

  var resizeTimer = null;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(renderCharts, 120);
  });

  document.addEventListener("DOMContentLoaded", renderCharts);
})();
