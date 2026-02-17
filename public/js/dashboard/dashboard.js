console.log('🔥 dashboard.js loaded');

/* ======================================================
   DASHBOARD JS – FINAL STABLE VERSION
====================================================== */

let dashboardData = {
  summary: {},
  distribution: {},
  todaySummary: {}
};

/* =========================
   INIT DASHBOARD
========================= */
window.initDashboard = function () {
  console.log('initDashboard jalan');

  if (document.querySelector('.dashboard')) {
    loadDashboardData();
  }
};

/* =========================
   LOAD DATA (REAL API)
========================= */
function loadDashboardData() {
  fetch('/dashboard/summary', {
    headers: { Accept: 'application/json' }
  })
    .then(r => {
      if (!r.ok) throw new Error('API error');
      return r.json();
    })
    .then(data => {
      dashboardData = data;
      renderDashboard(); // ✅ SEKARANG ADA
    })
    .catch(err => {
      console.error(err);
      toast('Gagal memuat data dashboard', 'error');
    });
}

/* =========================
   RENDER DASHBOARD (KUNCI)
========================= */
function renderDashboard() {
  renderSummaryCards();
  renderDistribution(dashboardData.distribution);
  renderTodaySummary(dashboardData.todaySummary);
  loadIncomeChart();
}

/* =========================
   SUMMARY CARDS
========================= */
function renderSummaryCards() {
  const s = dashboardData.summary;

  setText('[data-key="todayIncome"]', formatRupiah(s.todayIncome));
  setText('[data-key="monthIncome"]', formatRupiah(s.monthIncome));
  setText('[data-key="todayTransaction"]', s.todayTransaction + ' Transaksi');
  setText('[data-key="activeRoom"]', s.activeRoom + ' Ruangan');

  if (s.todayGrowth !== undefined) {
    const isUp = s.todayGrowth >= 0;
    const growthHtml = `
      <span class="${isUp ? 'growth-up' : 'growth-down'}">
        <i class="ph ph-caret-${isUp ? 'up' : 'down'}"></i>
        ${Math.abs(s.todayGrowth)}%
      </span>
      <span style="margin-left:4px">vs kemarin</span>
    `;
    const growthEl = document.querySelector('[data-key="todayGrowth"]');
    if (growthEl) growthEl.innerHTML = growthHtml;
  }
}

/* =========================
   DISTRIBUSI PASIEN
========================= */
function renderDistribution(dist) {
  const list = document.getElementById('distributionList');
  if (!list) return;

  if (!dist || Object.keys(dist).length === 0) {
    list.innerHTML = '<li style="justify-content:center;color:#94a3b8;padding:20px 0;">Tidak ada data pasien</li>';
    return;
  }

  const colors = {
    'umum': '#2563eb',
    'bpjs': '#16a34a',
    'jaminan': '#7c3aed',
    'kerjasama': '#ea580c',
    'lainnya': '#06b6d4'
  };

  const labels = {
    'umum': 'Pasien Umum',
    'bpjs': 'BPJS Kesehatan',
    'jaminan': 'Jaminan Lain',
    'kerjasama': 'Kerja Sama',
    'lainnya': 'Lain-lain'
  };

  list.innerHTML = Object.entries(dist).map(([key, val]) => `
    <li>
      <div class="stat-label">
        <span class="dot" style="background:${colors[key] || '#94a3b8'}"></span>
        <span>${labels[key] || key}</span>
      </div>
      <span class="stat-value">${val}%</span>
    </li>
  `).join('');
}

/* =========================
   RINGKASAN HARI INI
========================= */
function renderTodaySummary(summary) {
  const list = document.getElementById('todaySummaryList');
  if (!list) return;

  if (!summary || Object.keys(summary).length === 0) {
    list.innerHTML = '<li style="justify-content:center;color:#94a3b8;padding:20px 0;">Belum ada aktivitas</li>';
    return;
  }

  const items = [
    { icon: 'ph-receipt', label: 'Total Transaksi', value: summary.totalTransaction + ' Pasien' },
    { icon: 'ph-trend-up', label: 'Tipe Terpopuler', value: summary.topIncomeType },
    { icon: 'ph-hospital', label: 'Unit Teramai', value: summary.topRoom },
    { icon: 'ph-users', label: 'Dominasi Pasien', value: summary.dominantPatient }
  ];

  list.innerHTML = items.map(item => `
    <li>
      <div class="stat-label">
        <i class="ph ${item.icon}" style="font-size:16px;color:#64748b"></i>
        <span>${item.label}</span>
      </div>
      <span class="stat-value">${item.value}</span>
    </li>
  `).join('');
}

/* =========================
   HELPERS
========================= */
function setText(selector, value) {
  const el = document.querySelector(selector);
  if (el) el.innerText = value;
}



let incomeChart = null;

function loadIncomeChart() {
  fetch('/dashboard/chart-7-days')
    .then(r => r.json())
    .then(data => renderIncomeChart(data))
    .catch(err => console.error(err));
}

function renderIncomeChart(data) {
  if (typeof Chart === 'undefined') {
    console.error('Chart.js belum dimuat');
    return;
  }

  const canvas = document.getElementById('incomeChart');
  if (!canvas) return;

  if (incomeChart) incomeChart.destroy();

  const ctx = canvas.getContext('2d');

  // Update title with year
  const titleEl = canvas.closest('.dashboard-box')?.querySelector('h4');
  if (titleEl && data.year) {
    titleEl.innerHTML = `<i class="ph ph-chart-bar"></i> Tren Pendapatan Bulanan ${data.year}`;
  }

  incomeChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Pendapatan',
        data: data.values,
        backgroundColor: 'rgba(37,99,235,.7)',
        hoverBackgroundColor: 'rgba(37,99,235,.9)',
        borderRadius: 6,
        borderSkipped: false,
        maxBarThickness: 48
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (context) {
              return 'Pendapatan: ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(context.parsed.y);
            }
          }
        }
      },
      scales: {
        y: {
          ticks: {
            callback: v => formatRupiah(v),
            font: { size: 11 }
          },
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,.04)' }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 12, weight: '500' } }
        }
      }
    }
  });
}


