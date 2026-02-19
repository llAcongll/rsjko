console.log('🔥 dashboard.js loaded');

let dashboardData = {
  summary: {},
  distribution: {}
};

let incomeChart = null;
let roomChart = null;
let expenditureChart = null;
let patientPieChart = null;

/* =========================
   INIT DASHBOARD
 ========================= */
window.initDashboard = function () {
  if (document.querySelector('.dashboard')) {
    loadDashboardData();
  }
};

/* =========================
   LOAD DATA
 ========================= */
function loadDashboardData() {
  fetch('/dashboard/summary', {
    headers: { Accept: 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
      dashboardData = data;
      renderDashboard();
    })
    .catch(err => console.error('Summary Error:', err));
}

/* =========================
   RENDER DASHBOARD
 ========================= */
function renderDashboard() {
  renderSummaryCards();
  renderDistributionList(dashboardData.distribution);

  loadIncomeChart();
  loadRoomChart();
  loadExpenditureChart();
  renderPatientPieChart(dashboardData.distribution);
}

/* =========================
   SUMMARY CARDS
 ========================= */
function renderSummaryCards() {
  const s = dashboardData.summary;

  setText('[data-key="targetPendapatan"]', formatRupiah(s.targetPendapatan));
  setText('[data-key="realisasiPendapatan"]', formatRupiah(s.realisasiPendapatan));
  setText('[data-key="persenCapaian"]', `(${s.persenCapaian || 0}%)`);

  // New Pengeluaran fields
  setText('[data-key="targetPengeluaran"]', formatRupiah(s.targetPengeluaran));
  setText('[data-key="realisasiPengeluaran"]', formatRupiah(s.realisasiPengeluaran));
  setText('[data-key="persenCapaianPengeluaran"]', `(${s.persenCapaianPengeluaran || 0}%)`);
}

/* =========================
   Charts
 ========================= */

function loadIncomeChart() {
  fetch('/dashboard/chart-7-days')
    .then(r => r.json())
    .then(data => renderIncomeChart(data))
    .catch(err => console.error('Income Chart Error:', err));
}

function renderIncomeChart(data) {
  const canvas = document.getElementById('incomeChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (incomeChart) incomeChart.destroy();
  const ctx = canvas.getContext('2d');

  incomeChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        {
          label: 'Pendapatan Bulanan',
          data: data.values,
          backgroundColor: '#3b82f6',
          borderRadius: 4
        },
        {
          label: 'Pengeluaran Bulanan',
          data: data.valuesPengeluaran,
          backgroundColor: '#ef4444',
          borderRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: {
          callbacks: {
            label: (ctx) => `${ctx.dataset.label}: ${formatRupiah(ctx.parsed.y)}`
          }
        }
      },
      scales: {
        y: { ticks: { callback: v => formatRupiah(v) }, beginAtZero: true }
      }
    }
  });
}

function loadRoomChart() {
  fetch('/dashboard/chart-rooms')
    .then(r => r.json())
    .then(data => renderRoomChart(data))
    .catch(err => console.error('Room Chart Error:', err));
}

function renderRoomChart(data) {
  const canvas = document.getElementById('roomChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (roomChart) roomChart.destroy();
  const ctx = canvas.getContext('2d');

  roomChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Pendapatan Per Unit',
        data: data.values,
        backgroundColor: '#10b981',
        borderRadius: 4
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => formatRupiah(ctx.parsed.x) } }
      },
      scales: {
        x: { ticks: { callback: v => formatRupiah(v) }, beginAtZero: true }
      }
    }
  });
}

function loadExpenditureChart() {
  fetch('/dashboard/chart-expenditure')
    .then(r => r.json())
    .then(data => renderExpenditureChart(data))
    .catch(err => console.error('Expenditure Chart Error:', err));
}

function renderExpenditureChart(data) {
  const canvas = document.getElementById('expenditureChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (expenditureChart) expenditureChart.destroy();
  const ctx = canvas.getContext('2d');

  expenditureChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Total Pengeluaran',
        data: data.values,
        backgroundColor: '#ef4444',
        borderRadius: 4
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => formatRupiah(ctx.parsed.x) } }
      },
      scales: {
        x: { ticks: { callback: v => formatRupiah(v) }, beginAtZero: true }
      }
    }
  });
}

function renderPatientPieChart(dist) {
  const canvas = document.getElementById('patientPieChart');
  if (!canvas || typeof Chart === 'undefined') return;

  if (patientPieChart) patientPieChart.destroy();
  const ctx = canvas.getContext('2d');

  const keys = Object.keys(dist || {});
  const vals = Object.values(dist || {});
  const colors = ['#2563eb', '#16a34a', '#7c3aed', '#ea580c', '#06b6d4'];

  patientPieChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: keys.map(k => k.toUpperCase()),
      datasets: [{
        data: vals,
        backgroundColor: colors
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
}

function renderDistributionList(dist) {
  const list = document.getElementById('distributionList');
  if (!list) return;

  const labels = {
    'umum': 'Pasien Umum',
    'bpjs': 'BPJS Kesehatan',
    'jaminan': 'Jaminan Lain',
    'kerjasama': 'Kerja Sama',
    'lainnya': 'Lain-lain'
  };
  const colors = ['#2563eb', '#16a34a', '#7c3aed', '#ea580c', '#06b6d4'];
  const entries = Object.entries(dist || {});

  if (entries.length === 0) {
    list.innerHTML = '<li>Tidak ada data</li>';
    return;
  }

  list.innerHTML = entries.map(([key, val], idx) => `
    <li>
      <div class="stat-label">
        <span class="dot" style="background:${colors[idx % colors.length]}"></span>
        <span>${labels[key] || key}</span>
      </div>
      <span class="stat-value">${val}%</span>
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
