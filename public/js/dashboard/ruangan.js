let editingRuanganId = null;

/* =========================
   OPEN MODAL (ADD / EDIT)
========================= */
window.openRuanganForm = function (id = null, kode = '', nama = '') {
  editingRuanganId = id;

  const titleEl = document.getElementById('ruanganModalTitle');
  const kodeEl  = document.getElementById('ruanganKode');
  const namaEl  = document.getElementById('ruanganNama');

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
  modal.style.opacity = '1';
  modal.style.pointerEvents = 'auto';
};

/* =========================
   CLOSE MODAL
========================= */
window.closeRuanganModal = function () {
  const modal = document.getElementById('ruanganModal');
  modal.style.opacity = '0';
  modal.style.pointerEvents = 'none';
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

      // reload view + data (FLOW LAMA TETAP)
      loadContent('ruangan');
      setTimeout(() => {
        if (window.loadRuangan) loadRuangan();
      }, 300);

      toast('Ruangan berhasil disimpan', 'success');
    })
    .catch(err => toast(err.message, 'error'));
};

/* =========================
   LOAD DATA RUANGAN
========================= */
window.loadRuangan = function () {
  fetch('/dashboard/ruangans', {
    headers: { 'Accept': 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
      const tbody = document.querySelector('#ruanganTable tbody');
      if (!tbody) return;

      tbody.innerHTML = '';

      data.forEach((r, i) => {
        const kode = encodeURIComponent(r.kode);
        const nama = encodeURIComponent(r.nama);

        tbody.innerHTML += `
          <tr>
            <td class="col-no">${i + 1}</td>
            <td>${r.kode}</td>
            <td>${r.nama}</td>
            <td>
              <div class="action-group">
                <button class="btn-action btn-edit"
                  onclick="openRuanganForm(
                    ${r.id},
                    decodeURIComponent('${kode}'),
                    decodeURIComponent('${nama}')
                  )">✏️</button>

                <button class="btn-action btn-delete"
                  onclick="deleteRuangan(${r.id})">🗑️</button>
              </div>
            </td>
          </tr>
        `;
      });
    })
    .catch(err => toast(err.message, 'error'));
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
          loadRuangan(); // refresh table
          toast('Ruangan berhasil dihapus', 'success');
        })
        .catch(err => toast(err.message, 'error'));
    }
  );
};
