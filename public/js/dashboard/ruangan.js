let editingRuanganId = null;
let ruanganData = [];
let filteredRuangan = [];
let currentPage = 1;
const perPage = 10;

/* =========================
   OPEN MODAL (ADD / EDIT)
========================= */
window.openRuanganForm = function (id = null, kode = '', nama = '') {
  editingRuanganId = id;

  const titleEl = document.getElementById('ruanganModalTitle');
  const kodeEl = document.getElementById('ruanganKode');
  const namaEl = document.getElementById('ruanganNama');

  titleEl.innerText = id ? '✏️ Edit Ruangan' : '🏥 Tambah Ruangan';
  namaEl.value = nama || '';

  if (id) {
    // EDIT → kode tetap
    kodeEl.value = kode;
    kodeEl.readOnly = true;
  } else {
    // TAMBAH → auto-generate kode
    kodeEl.value = '';
    kodeEl.readOnly = true;

    fetch('/dashboard/ruangans/next-kode', {
      headers: { 'Accept': 'application/json' }
    })
      .then(r => r.json())
      .then(d => {
        kodeEl.value = d.kode;
      })
      .catch(() => {
        toast('Gagal mengambil kode ruangan', 'error');
      });
  }

  const modal = document.getElementById('ruanganModal');
  modal.classList.add('show');
};

/* =========================
   CLOSE MODAL
========================= */
window.closeRuanganModal = function () {
  const modal = document.getElementById('ruanganModal');
  modal.classList.remove('show');
  editingRuanganId = null;
};

/* =========================
   SUBMIT (CREATE / UPDATE)
========================= */
window.submitRuangan = function () {
  const kode = document.getElementById('ruanganKode').value.trim();
  const nama = document.getElementById('ruanganNama').value.trim();

  if (!kode || !nama) {
    toast('Kode dan nama ruangan wajib diisi', 'error');
    return;
  }

  const url = editingRuanganId
    ? `/dashboard/ruangans/${editingRuanganId}`
    : `/dashboard/ruangans`;

  const method = editingRuanganId ? 'PUT' : 'POST';

  fetch(url, {
    method,
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ kode, nama })
  })
    .then(async r => {
      const data = await r.json();
      if (!r.ok) throw new Error(data.message || 'Gagal menyimpan ruangan');
      return data;
    })
    .then(() => {
      closeRuanganModal();
      if (typeof window.loadRuanganTable === 'function') {
        window.loadRuanganTable(); // 🔥 REFRESH DATA SAJA
      }
      toast('Ruangan berhasil disimpan', 'success');
    })
    .catch(err => toast(err.message, 'error'));
};

/* =========================
   LOAD DATA RUANGAN
========================= */
window.loadRuanganTable = function () {
  console.log('🔥 loadRuanganTable DIPANGGIL');

  fetch('/dashboard/ruangan-list', {
    headers: { 'Accept': 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
      // 🔑 SIMPAN KE STATE
      ruanganData = data;
      filteredRuangan = [...data];
      currentPage = 1;

      // 🔥 RENDER LEWAT SATU PINTU
      renderRuanganPage();
      updatePaginationInfo(filteredRuangan.length);
      updateSortIconsRuangan();

      // init fitur
      initSearchRuangan();
      initSortableRuangan();
      bindPaginationRuangan();

      setTimeout(syncRuanganScrollbar, 0);
    });
};

/* =========================
   DELETE RUANGAN (CONFIRM)
========================= */
window.deleteRuangan = function (id) {
  openConfirm(
    'Hapus Ruangan',
    'Data ruangan yang dihapus tidak dapat dikembalikan.',
    () => {
      fetch(`/dashboard/ruangans/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        }
      })
        .then(async r => {
          const data = await r.json();
          if (!r.ok) throw new Error(data.message || 'Gagal menghapus ruangan');
          return data;
        })
        .then(() => {
          if (typeof window.loadRuanganTable === 'function') {
            window.loadRuanganTable();
          }
          toast('Ruangan berhasil dihapus', 'success');
        })
        .catch(err => toast(err.message, 'error'));
    }
  );
};

function syncRuanganScrollbar() {
  const body = document.querySelector('.ruangan-table-body');
  const header = document.querySelector('.ruangan-table-wrapper > table');

  if (!body || !header) return;

  const scrollbarWidth = body.offsetWidth - body.clientWidth;
  header.style.setProperty('--scrollbar-width', scrollbarWidth + 'px');
}

window.initRuangan = function () {
  console.log('initRuangan jalan');
  console.log('table:', document.getElementById('ruanganTable'));

  if (document.getElementById('ruanganTable')) {
    loadRuanganTable();
  }
};

let ruanganSort = {
  key: null,
  direction: 'asc'
};

function initSortableRuangan() {
  const table = document.getElementById('ruanganTable');
  if (!table) return;

  const headers = table.querySelectorAll('th.sortable');

  headers.forEach((th) => {
    th.addEventListener('click', () => {
      const key = th.dataset.sort;

      // toggle arah
      if (ruanganSort.key === key) {
        ruanganSort.direction = (ruanganSort.direction === 'asc' ? 'desc' : 'asc');
      } else {
        ruanganSort.key = key;
        ruanganSort.direction = (key === 'id' ? 'asc' : 'asc');
      }

      applySortRuangan();
      currentPage = 1;
      renderRuanganPage();
      updatePaginationInfo(filteredRuangan.length);
      updateSortIconsRuangan();
    });
  });
}

function updateSortIconsRuangan() {
  document.querySelectorAll('#ruanganTable th.sortable i').forEach(i => {
    i.className = 'ph ph-caret-up-down text-slate-400';
  });
  const activeHeader = document.querySelector(`#ruanganTable th.sortable[data-sort="${ruanganSort.key}"]`);
  if (activeHeader) {
    const i = activeHeader.querySelector('i');
    if (i) {
      i.className = ruanganSort.direction === 'asc' ? 'ph ph-caret-up text-blue-600' : 'ph ph-caret-down text-blue-600';
    }
  }
}

function applySortRuangan() {
  if (!ruanganSort.key) return;

  filteredRuangan.sort((a, b) => {
    let A, B;

    if (ruanganSort.key === 'id') {
      A = Number(a.id);
      B = Number(b.id);
    } else {
      A = String(a[ruanganSort.key]).toLowerCase();
      B = String(b[ruanganSort.key]).toLowerCase();
    }

    if (A < B) return ruanganSort.direction === 'asc' ? -1 : 1;
    if (A > B) return ruanganSort.direction === 'asc' ? 1 : -1;
    return 0;
  });
}

function initSearchRuangan() {
  const input = document.getElementById('ruanganSearch');
  if (!input) return;

  if (input.dataset.bound === '1') return;
  input.dataset.bound = '1';

  input.addEventListener('input', () => {
    const keyword = input.value.toLowerCase().trim();

    filteredRuangan = ruanganData.filter(r =>
      r.kode.toLowerCase().includes(keyword) ||
      r.nama.toLowerCase().includes(keyword)
    );

    applySortRuangan();
    currentPage = 1;
    renderRuanganPage();
    updatePaginationInfo(filteredRuangan.length);
    updateSortIconsRuangan();
  });
}

function renderRuanganPage() {
  const tbody = document.querySelector('#ruanganTable tbody');
  if (!tbody) return;

  const start = (currentPage - 1) * perPage;
  const end = start + perPage;

  const pageData = filteredRuangan.slice(start, end);

  tbody.innerHTML = '';

  const canCRUD = window.hasPermission('MASTER_CRUD');

  pageData.forEach((r, i) => {
    tbody.innerHTML += `
      <tr>
        <td class="text-center" data-label="No">${start + i + 1}</td>
        <td data-label="Kode">${r.kode}</td>
        <td data-label="Nama">${r.nama}</td>
        <td data-label="Aksi">
          <div class="flex justify-center gap-2">
            ${canCRUD ? `
              <button class="btn-aksi edit" 
                onclick="openRuanganForm(${r.id}, '${r.kode}', '${r.nama}')" title="Edit Ruangan">
                <i class="ph ph-pencil"></i>
              </button>
              <button class="btn-aksi delete" 
                onclick="deleteRuangan(${r.id})" title="Hapus Ruangan">
                <i class="ph ph-trash"></i>
              </button>
            ` : '-'}
          </div>
        </td>
      </tr>
    `;
  });
}

function bindPaginationRuangan() {
  const prevBtn = document.getElementById('prevPage');
  const nextBtn = document.getElementById('nextPage');

  if (!prevBtn || !nextBtn) return;

  prevBtn.onclick = () => {
    if (currentPage > 1) {
      currentPage--;
      renderRuanganPage();
    }
  };

  nextBtn.onclick = () => {
    const totalPage = Math.ceil(filteredRuangan.length / perPage);
    if (currentPage < totalPage) {
      currentPage++;
      renderRuanganPage();
      updatePaginationInfo(filteredRuangan.length);
    }
  };

}

function updatePaginationInfo(total) {
  const pageInfo = document.getElementById('pageInfo');
  const ruanganInfo = document.getElementById('ruanganInfo');
  const prevBtn = document.getElementById('prevPage');
  const nextBtn = document.getElementById('nextPage');

  if (!pageInfo || !ruanganInfo || !prevBtn || !nextBtn) return;

  const totalPage = Math.max(1, Math.ceil(total / perPage));

  const start = (currentPage - 1) * perPage + 1;
  const end = Math.min(currentPage * perPage, total);

  pageInfo.innerText = `Halaman ${currentPage} / ${totalPage}`;
  ruanganInfo.innerText = `Menampilkan ${start}–${end} dari ${total} data`;

  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === totalPage;
}
