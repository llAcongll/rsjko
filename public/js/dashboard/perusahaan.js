let editingPerusahaanId = null;
let perusahaanData = [];
let filteredPerusahaan = [];
let currentPagePerusahaan = 1;
const perPagePerusahaan = 10;

/* =========================
   OPEN MODAL (ADD / EDIT)
========================= */
window.openPerusahaanForm = function (id = null, kode = '', nama = '') {
    editingPerusahaanId = id;

    const titleEl = document.getElementById('perusahaanModalTitle');
    const kodeEl = document.getElementById('perusahaanKode');
    const namaEl = document.getElementById('perusahaanNama');

    titleEl.innerText = id ? '✏️ Edit Perusahaan' : '🏢 Tambah Perusahaan';
    namaEl.value = nama || '';

    if (id) {
        // EDIT → kode tetap
        kodeEl.value = kode;
        kodeEl.readOnly = true;
    } else {
        // TAMBAH → auto-generate kode
        kodeEl.value = '';
        kodeEl.readOnly = true;

        fetch('/dashboard/perusahaans/next-kode', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(d => {
                kodeEl.value = d.kode;
            })
            .catch(() => {
                toast('Gagal mengambil kode perusahaan', 'error');
            });
    }

    const modal = document.getElementById('perusahaanModal');
    modal.classList.add('show');
};

/* =========================
   CLOSE MODAL
========================= */
window.closePerusahaanModal = function () {
    const modal = document.getElementById('perusahaanModal');
    modal.classList.remove('show');
    editingPerusahaanId = null;
};

/* =========================
   SUBMIT (CREATE / UPDATE)
========================= */
window.submitPerusahaan = function () {
    const kode = document.getElementById('perusahaanKode').value.trim();
    const nama = document.getElementById('perusahaanNama').value.trim();

    if (!kode || !nama) {
        toast('Kode dan nama perusahaan wajib diisi', 'error');
        return;
    }

    const url = editingPerusahaanId
        ? `/dashboard/perusahaans/${editingPerusahaanId}`
        : `/dashboard/perusahaans`;

    const method = editingPerusahaanId ? 'PUT' : 'POST';

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
            if (!r.ok) throw new Error(data.message || 'Gagal menyimpan perusahaan');
            return data;
        })
        .then(() => {
            closePerusahaanModal();
            if (typeof window.loadPerusahaanTable === 'function') {
                window.loadPerusahaanTable();
            }
            toast('Perusahaan berhasil disimpan', 'success');
        })
        .catch(err => toast(err.message, 'error'));
};

/* =========================
   LOAD DATA PERUSAHAAN
========================= */
window.loadPerusahaanTable = function () {
    console.log('🔥 loadPerusahaanTable DIPANGGIL');

    fetch('/dashboard/perusahaan-list', {
        headers: { 'Accept': 'application/json' }
    })
        .then(r => r.json())
        .then(data => {
            perusahaanData = data;
            filteredPerusahaan = [...data];
            currentPagePerusahaan = 1;

            renderPerusahaanPage();
            updatePaginationInfoPerusahaan(filteredPerusahaan.length);

            initSearchPerusahaan();
            initSortablePerusahaan();
            bindPaginationPerusahaan();
        });
};

/* =========================
   DELETE PERUSAHAAN (CONFIRM)
========================= */
window.deletePerusahaan = function (id) {
    openConfirm(
        'Hapus Perusahaan',
        'Data perusahaan yang dihapus tidak dapat dikembalikan.',
        () => {
            fetch(`/dashboard/perusahaans/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.message || 'Gagal menghapus perusahaan');
                    return data;
                })
                .then(() => {
                    if (typeof window.loadPerusahaanTable === 'function') {
                        window.loadPerusahaanTable();
                    }
                    toast('Perusahaan berhasil dihapus', 'success');
                })
                .catch(err => toast(err.message, 'error'));
        }
    );
};

window.initPerusahaan = function () {
    console.log('initPerusahaan jalan');

    if (document.getElementById('perusahaanTable')) {
        loadPerusahaanTable();
    }
};

let perusahaanSort = {
    key: null,
    direction: 'asc'
};

function initSortablePerusahaan() {
    const table = document.getElementById('perusahaanTable');
    if (!table) return;

    const headers = table.querySelectorAll('th[data-sort]');

    headers.forEach((th, index) => {
        th.addEventListener('click', () => {
            const map = ['no', 'kode', 'nama'];
            const key = map[index];

            if (perusahaanSort.key === key) {
                perusahaanSort.direction =
                    perusahaanSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                perusahaanSort.key = key;
                perusahaanSort.direction = 'asc';
            }

            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            th.classList.add(
                perusahaanSort.direction === 'asc' ? 'sort-asc' : 'sort-desc'
            );

            applySortPerusahaan();
            currentPagePerusahaan = 1;
            renderPerusahaanPage();
            updatePaginationInfoPerusahaan(filteredPerusahaan.length);
        });
    });
}

function applySortPerusahaan() {
    if (!perusahaanSort.key) return;

    filteredPerusahaan.sort((a, b) => {
        let A, B;

        if (perusahaanSort.key === 'no') {
            A = a.id;
            B = b.id;
        } else {
            A = a[perusahaanSort.key].toLowerCase();
            B = b[perusahaanSort.key].toLowerCase();
        }

        if (A < B) return perusahaanSort.direction === 'asc' ? -1 : 1;
        if (A > B) return perusahaanSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function initSearchPerusahaan() {
    const input = document.getElementById('perusahaanSearch');
    if (!input) return;

    if (input.dataset.bound === '1') return;
    input.dataset.bound = '1';

    input.addEventListener('input', () => {
        const keyword = input.value.toLowerCase().trim();

        filteredPerusahaan = perusahaanData.filter(r =>
            r.kode.toLowerCase().includes(keyword) ||
            r.nama.toLowerCase().includes(keyword)
        );

        currentPagePerusahaan = 1;
        renderPerusahaanPage();
        updatePaginationInfoPerusahaan(filteredPerusahaan.length);
    });
}

function renderPerusahaanPage() {
    const tbody = document.querySelector('#perusahaanTable tbody');
    if (!tbody) return;

    const start = (currentPagePerusahaan - 1) * perPagePerusahaan;
    const end = start + perPagePerusahaan;

    const pageData = filteredPerusahaan.slice(start, end);

    tbody.innerHTML = '';

    const canCRUD = window.hasPermission('MASTER_CRUD');

    pageData.forEach((r, i) => {
        const escapedNama = r.nama.replace(/'/g, "\\'");
        tbody.innerHTML += `
      <tr>
        <td class="text-center">${start + i + 1}</td>
        <td>${r.kode}</td>
        <td>${r.nama}</td>
        <td>
          <div class="flex justify-center gap-2">
            ${canCRUD ? `
              <button class="btn-aksi edit" 
                onclick="openPerusahaanForm(${r.id}, '${r.kode}', '${escapedNama}')" title="Edit Perusahaan">
                <i class="ph ph-pencil"></i>
              </button>
              <button class="btn-aksi delete" 
                onclick="deletePerusahaan(${r.id})" title="Hapus Perusahaan">
                <i class="ph ph-trash"></i>
              </button>
            ` : '-'}
          </div>
        </td>
      </tr>
    `;
    });
}

function bindPaginationPerusahaan() {
    const prevBtn = document.getElementById('prevPagePerusahaan');
    const nextBtn = document.getElementById('nextPagePerusahaan');

    if (!prevBtn || !nextBtn) return;

    prevBtn.onclick = () => {
        if (currentPagePerusahaan > 1) {
            currentPagePerusahaan--;
            renderPerusahaanPage();
            updatePaginationInfoPerusahaan(filteredPerusahaan.length);
        }
    };

    nextBtn.onclick = () => {
        const totalPage = Math.ceil(filteredPerusahaan.length / perPagePerusahaan);
        if (currentPagePerusahaan < totalPage) {
            currentPagePerusahaan++;
            renderPerusahaanPage();
            updatePaginationInfoPerusahaan(filteredPerusahaan.length);
        }
    };
}

function updatePaginationInfoPerusahaan(total) {
    const pageInfo = document.getElementById('pageInfoPerusahaan');
    const perusahaanInfo = document.getElementById('perusahaanInfo');
    const prevBtn = document.getElementById('prevPagePerusahaan');
    const nextBtn = document.getElementById('nextPagePerusahaan');

    if (!pageInfo || !perusahaanInfo || !prevBtn || !nextBtn) return;

    const totalPage = Math.max(1, Math.ceil(total / perPagePerusahaan));

    const start = (currentPagePerusahaan - 1) * perPagePerusahaan + 1;
    const end = Math.min(currentPagePerusahaan * perPagePerusahaan, total);

    pageInfo.innerText = `Halaman ${currentPagePerusahaan} / ${totalPage}`;
    perusahaanInfo.innerText = total > 0
        ? `Menampilkan ${start}–${end} dari ${total} data`
        : 'Tidak ada data';

    prevBtn.disabled = currentPagePerusahaan === 1;
    nextBtn.disabled = currentPagePerusahaan === totalPage;
}
