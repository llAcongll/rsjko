/* =====================================================
   MODE ANGGARAN PENDAPATAN (FINAL)
   - READ ONLY
   - TIDAK campur CRUD
===================================================== */

let activeAnggaranTahun = null;

/* =========================
   INIT — called after AJAX content load
========================= */
// Note: No DOMContentLoaded needed. Initialization is done by
// openAnggaranRekening() after loadContent() injects the HTML.


/* =========================
   EVENT
========================= */
window.reloadAnggaran = function () {
  const tahun = document.getElementById('anggaranTahun').value;
  loadAnggaran(tahun);
};

/* =========================
   LOAD DATA
========================= */
function loadAnggaran(tahun) {
  activeAnggaranTahun = tahun;

  fetch(`/dashboard/master/kode-rekening-anggaran/${tahun}`, {
    headers: { Accept: 'application/json' }
  })
    .then(r => {
      if (!r.ok) throw 'Gagal load anggaran';
      return r.json();
    })
    .then(data => {
      renderAnggaranTree(data);
    })
    .catch(e => toast(e, 'error'));
}

/* =========================
   RENDER
========================= */
function renderAnggaranTree(data) {
  const wrap = document.getElementById('kodeRekeningTree');
  if (!wrap) return;

  wrap.innerHTML = '';
  data.forEach(node => {
    wrap.appendChild(renderAnggaranNode(node, 0));
  });
}

function renderAnggaranNode(node, level) {
  const isHeader = node.tipe === 'header';
  const isDetail = node.tipe === 'detail';
  const row = document.createElement('div');
  row.className = `anggaran-row ${isHeader ? 'header-node' : 'detail-node'}`;
  row.style.paddingLeft = (level * 24 + 16) + 'px';

  const canCRUD = window.hasPermission('KODE_REKENING_CRUD');

  row.innerHTML = `
    <div class="col-kode">${node.kode}</div>
    <div class="col-uraian">
      <i class="node-type-icon ph-fill ${isHeader ? 'ph-folder' : 'ph-file-text'}"></i>
      <span class="nama-text">${node.nama}</span>
      ${node.sumber_data ? `<span class="badge-source">${node.sumber_data}</span>` : ''}
    </div>
    <div class="col-anggaran">${formatRupiah(node.total_anggaran)}</div>
    <div class="col-realisasi">
        <span>${formatRupiah(node.total_realisasi ?? 0)}</span>
        ${isDetail && canCRUD ? `<button class="btn-aksi edit" onclick="openAnggaranModal(${node.id}, '${node.nama}')" title="Edit Anggaran"><i class="ph ph-pencil-simple-line"></i></button>` : '<div style="width:28px"></div>'}
    </div>
  `;

  const wrap = document.createElement('div');
  wrap.appendChild(row);

  if (node.children?.length) {
    node.children.forEach(c => {
      wrap.appendChild(renderAnggaranNode(c, level + 1));
    });
  }

  return wrap;
}

/* =========================
   MODAL & RINCIAN LOGIC
 ========================= */
window.openAnggaranModal = function (id, nama) {
  const modal = document.getElementById('anggaranModal');
  if (!modal) return;

  document.getElementById('arKodeRekeningId').value = id;
  document.getElementById('arTahun').value = activeAnggaranTahun;
  if (document.getElementById('arTahunLabel')) document.getElementById('arTahunLabel').innerText = activeAnggaranTahun;
  document.getElementById('anggaranModalTitle').innerText = `💰 Anggaran: ${nama}`;
  document.getElementById('rincianBody').innerHTML = '';
  document.getElementById('arNilai').value = 'Rp 0';

  // Fetch existing rincian
  fetch(`/dashboard/anggaran/rincian/${id}/${activeAnggaranTahun}`)
    .then(r => r.json())
    .then(data => {
      if (data && data.rincian?.length) {
        data.rincian.forEach(item => addRincianRow(item));
      } else if (data && data.nilai) {
        // If only flat value exists (fallback)
        document.getElementById('arNilai').value = formatRupiah(data.nilai);
      }
      hitungTotalAnggaran();
    });

  modal.classList.add('show');
};

window.closeAnggaranModal = function () {
  const modal = document.getElementById('anggaranModal');
  if (!modal) return;
  modal.classList.remove('show');
};

window.addRincianRow = function (data = null) {
  const tbody = document.getElementById('rincianBody');
  const row = document.createElement('tr');

  row.innerHTML = `
        <td><input type="text" class="form-input row-uraian" value="${data?.uraian || ''}" placeholder="Nama Komponen / Uraian Detail..."></td>
        <td><input type="number" class="form-input row-vol" value="${data?.volume || 1}" oninput="hitungRow(this)" style="text-align: center;"></td>
        <td><input type="text" class="form-input row-satuan" value="${data?.satuan || 'Tahun'}" placeholder="Satuan"></td>
        <td><input type="number" class="form-input row-tarif" value="${data?.tarif || 0}" oninput="hitungRow(this)" style="text-align: right;"></td>
        <td class="row-subtotal" style="text-align: right;">Rp 0</td>
        <td>
          <button type="button" class="btn-aksi delete" onclick="this.closest('tr').remove(); hitungTotalAnggaran();" title="Hapus">
            <i class="ph ph-trash"></i>
          </button>
        </td>
    `;

  tbody.appendChild(row);
  hitungRow(row.querySelector('.row-vol'));
};

window.hitungRow = function (el) {
  const row = el.closest('tr');
  const vol = parseFloat(row.querySelector('.row-vol').value) || 0;
  const tarif = parseFloat(row.querySelector('.row-tarif').value) || 0;
  const subtotal = vol * tarif;

  row.querySelector('.row-subtotal').innerText = formatRupiah(subtotal);
  hitungTotalAnggaran();
};

window.hitungTotalAnggaran = function () {
  let total = 0;
  document.querySelectorAll('#rincianBody tr').forEach(row => {
    const vol = parseFloat(row.querySelector('.row-vol').value) || 0;
    const tarif = parseFloat(row.querySelector('.row-tarif').value) || 0;
    total += (vol * tarif);
  });
  document.getElementById('arNilai').value = formatRupiah(total);
};

window.submitAnggaran = function () {
  const id = document.getElementById('arKodeRekeningId').value;
  const tahun = document.getElementById('arTahun').value;

  const rincian = [];
  document.querySelectorAll('#rincianBody tr').forEach(row => {
    rincian.push({
      uraian: row.querySelector('.row-uraian').value,
      volume: row.querySelector('.row-vol').value,
      satuan: row.querySelector('.row-satuan').value,
      tarif: row.querySelector('.row-tarif').value,
    });
  });

  fetch('/dashboard/anggaran', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken()
    },
    body: JSON.stringify({
      kode_rekening_id: id,
      tahun: tahun,
      rincian: rincian
    })
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'ok') {
        toast('Anggaran berhasil disimpan', 'success');
        closeAnggaranModal();
        loadAnggaran(activeAnggaranTahun);
      } else {
        throw res;
      }
    })
    .catch(err => toast(err.message || 'Gagal menyimpan anggaran', 'error'));
};

/* =========================
   UTIL
 ========================= */


function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content;
}

/* =====================================================
   MENU HANDLER – ANGGARAN PENDAPATAN
   Uses loadContent() from app.js for proper AJAX loading
===================================================== */
window.openAnggaranRekening = async function (btn) {
  if (typeof window.setActiveMenu === 'function') {
    window.setActiveMenu(btn);
  }
  if (typeof window.closeOnMobile === 'function') {
    window.closeOnMobile();
  }

  // Muat konten halaman via AJAX
  const ok = await window.loadContent('master/kode-rekening-anggaran');
  if (!ok) return;

  // Default tahun from the select or fallback to 2026
  const tahun = document.getElementById('anggaranTahun')?.value || 2026;

  if (typeof loadAnggaran === 'function') {
    loadAnggaran(tahun);
  }
};

