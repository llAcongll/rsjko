/* =========================
   MODAL CONTROL
========================= */

window.openPendapatanModal = function () {
  const modal = document.getElementById('pendapatanUmumModal');
  if (!modal) return;

  modal.classList.add('show');
  loadRuangan();
};

function closePendapatanModal() {
  const modal = document.getElementById('pendapatanUmumModal');
  modal?.classList.remove('show');

  const form = document.getElementById('formPendapatanUmum');
  form?.reset();

  document.querySelectorAll('.nominal-display').forEach(i => i.value = '0');
  document.querySelectorAll('.nominal-value').forEach(i => i.value = 0);

  document.getElementById('totalPembayaran').innerText = 'Rp 0';

  const btn = document.getElementById('btnSimpanPendapatan');
  if (btn) {
    btn.disabled = true;
    btn.innerText = '💾 Simpan';
  }

  // 🔥 RESET MODE
  isEdit = false;
  editId = null;

  const title = document.querySelector('.modal-title');
  if (title) title.innerText = '➕ Tambah Pendapatan Umum';
}

/* =========================
   SUBMIT (ONSUBMIT)
========================= */
async function submitPendapatanUmum(event) {
  event.preventDefault();

  const form = document.getElementById('formPendapatanUmum');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const btnSimpan = document.getElementById('btnSimpanPendapatan');
  btnSimpan.disabled = true;
  btnSimpan.innerText = 'Menyimpan...';

  const formData = new FormData(form);

  // 🔥 KUNCI UTAMA: METHOD SPOOFING
  if (isEdit) {
    formData.append('_method', 'PUT');
  }

  const url = isEdit
    ? `/dashboard/pendapatan/umum/${editId}`
    : `/dashboard/pendapatan/umum`;

  try {
    const res = await fetch(url, {
      method: 'POST', // 🔥 SELALU POST
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!res.ok) throw new Error();

    toast(
      isEdit
        ? '✏️ Pendapatan berhasil diperbarui'
        : '💾 Pendapatan berhasil disimpan',
      'success'
    );

    closePendapatanModal();
    loadPendapatanUmum();

  } catch {
    toast('❌ Gagal menyimpan pendapatan', 'error');
    btnSimpan.disabled = false;
    btnSimpan.innerText = '💾 Simpan';
  }
}

/* ================= BANK LOGIC ================= */
const metodePembayaran = document.getElementById('metodePembayaran');
const bankSelect = document.getElementById('bank');
const metodeDetail = document.getElementById('metodeDetail');

const BANK = {
  BRK: { value: 'BRK', label: 'Bank Riau Kepri Syariah' },
  BSI: { value: 'BSI', label: 'Bank Syariah Indonesia' }
};

const METODE_DETAIL = [
  { value: 'QRIS', label: 'QRIS' },
  { value: 'TRANSFER', label: 'Transfer' }
];

function resetSelect(select, placeholder) {
  select.innerHTML = `<option value="">${placeholder}</option>`;
  select.disabled = true;
}

function addOption(select, item) {
  const opt = document.createElement('option');
  opt.value = item.value;
  opt.textContent = item.label;
  select.appendChild(opt);
}

metodePembayaran.addEventListener('change', () => {
  resetSelect(bankSelect, '-- Pilih Bank --');
  resetSelect(metodeDetail, '-- Metode Detail --');

  if (metodePembayaran.value === 'TUNAI') {
    bankSelect.disabled = false;
    addOption(bankSelect, BANK.BRK);
  }

  if (metodePembayaran.value === 'NON_TUNAI') {
    bankSelect.disabled = false;
    metodeDetail.disabled = false;

    addOption(bankSelect, BANK.BRK);
    addOption(bankSelect, BANK.BSI);

    METODE_DETAIL.forEach(m => addOption(metodeDetail, m));
  }

  cekSiapSimpan(); // 👈 WAJIB
});

function formatRibuan(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function parseAngka(val) {
  return parseInt(val.replace(/\D/g, '') || 0);
}

function formatRupiah(num) {
  return 'Rp ' + num.toLocaleString('id-ID');
}

function hitungTotal() {
  let total = 0;

  document.querySelectorAll('.nominal-value').forEach(i => {
    total += parseInt(i.value || 0);
  });

  document.getElementById('totalPembayaran').innerText = formatRupiah(total);
}

document.querySelectorAll('.nominal-display').forEach(input => {
  input.addEventListener('input', () => {
    const angka = parseAngka(input.value);

    input.value = angka ? formatRibuan(angka) : '0';

    input.closest('.input-group')
         .querySelector('.nominal-value')
         .value = angka;

    hitungTotal();
  });
});

/* ================= RUANGAN ================= */

async function loadRuangan() {
  const select = document.getElementById('ruanganSelect');
  if (!select) return;

  select.disabled = true;
  select.innerHTML = '<option value="">Memuat ruangan...</option>';

  try {
    const res = await fetch('/dashboard/ruangan-list', {
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) throw new Error();

    const data = await res.json();

    select.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
    data.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.id;
      opt.textContent = `${r.kode} — ${r.nama}`;
      select.appendChild(opt);
    });

    select.disabled = false;

  } catch {
    select.innerHTML = '<option value="">Gagal memuat ruangan</option>';
  }
}

function cekSiapSimpan() {
  // === RINCIAN PASIEN ===
  const tanggal     = document.querySelector('[name="tanggal"]')?.value;
  const namaPasien  = document.querySelector('[name="nama_pasien"]')?.value.trim();
  const ruangan     = document.querySelector('[name="ruangan_id"]')?.value;

  // === RINCIAN BANK ===
  const metode      = document.getElementById('metodePembayaran')?.value;
  const bank        = document.getElementById('bank')?.value;
  const metodeDetail= document.getElementById('metodeDetail')?.value;

  let valid = true;

  // Pasien wajib lengkap
  if (!tanggal || !namaPasien || !ruangan) valid = false;

  // Metode pembayaran wajib
  if (!metode) valid = false;

  // Non tunai wajib bank + metode detail
  if (metode === 'NON_TUNAI') {
    if (!bank || !metodeDetail) valid = false;
  }

  document.getElementById('btnSimpanPendapatan').disabled = !valid;
}

function initPendapatanUmum() {
  [
    'tanggal',
    'nama_pasien',
    'ruangan_id'
  ].forEach(name => {
    document.querySelector(`[name="${name}"]`)
      ?.addEventListener('input', cekSiapSimpan);
  });

  [
    'metodePembayaran',
    'bank',
    'metodeDetail'
  ].forEach(id => {
    document.getElementById(id)
      ?.addEventListener('change', cekSiapSimpan);
  });

  cekSiapSimpan();
}

function formatTanggal(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID');
}

function renderMetode(item) {
  if (item.metode_pembayaran === 'TUNAI') return 'Tunai';
  return `${item.metode_pembayaran} (${item.metode_detail ?? '-'})`;
}

function escapeHtml(str = '') {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function loadPendapatanUmum() {
  const tbody = document.getElementById('pendapatanUmumBody');
  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="6" style="text-align:center">⏳ Memuat data...</td>
    </tr>
  `;

  fetch('/dashboard/pendapatan/umum', {
    headers: { 'Accept': 'application/json' }
  })
    .then(res => res.json())
    .then(data => {
      if (!data.length) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" style="text-align:center">📭 Belum ada data</td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = '';
      data.forEach((item, index) => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr>
            <td style="text-align:center">${index + 1}</td>
            <td>${formatTanggal(item.tanggal)}</td>
            <td>${escapeHtml(item.nama_pasien)}</td>
            <td>${item.ruangan?.nama ?? '-'}</td>
            <td style="text-align:right">
              Rp ${Number(item.total).toLocaleString('id-ID')}
            </td>
            <td style="text-align:center">
              <div class="aksi-btn">
                <button class="btn-aksi detail" title="Detail" onclick="detailPendapatan(${item.id})">👁</button>
                <button class="btn-aksi edit" onclick="editPendapatan(${item.id})">✏️</button>
                <button class="btn-aksi delete" onclick="hapusPendapatan(${item.id})">🗑️</button>
              </div>
            </td>
          </tr>
        `);
      });
    })
    .catch(() => {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" style="text-align:center;color:red">
            ❌ Gagal memuat data
          </td>
        </tr>
      `;
    });
}

window.loadPendapatanUmum = loadPendapatanUmum;

function hapusPendapatan(id) {
  openConfirm(
    'Hapus Data',
    'Yakin ingin menghapus pendapatan ini?',
    async () => {
      try {
        const res = await fetch(`/dashboard/pendapatan/umum/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        });

        if (!res.ok) throw new Error();

        toast('🗑️ Data berhasil dihapus', 'success');
        loadPendapatanUmum();

      } catch {
        toast('❌ Gagal menghapus data', 'error');
      }
    }
  );
}

function detailPendapatan(id) {
  const modal = document.getElementById('pendapatanDetailModal');
  const content = document.getElementById('detailPendapatanContent');

  if (!modal || !content) {
    console.error('Modal detail belum ada di DOM');
    return;
  }

  modal.classList.add('show');
  content.innerHTML = '⏳ Memuat...';

  fetch(`/dashboard/pendapatan/umum/${id}`, {
    headers: { Accept: 'application/json' }
  })
    .then(res => res.json())
    .then(data => {
      content.innerHTML = `
        <div class="detail-row">
          <span class="label">Tanggal</span>
          <span class="value">${formatTanggal(data.tanggal)}</span>
        </div>

        <div class="detail-row">
          <span class="label">Nama Pasien</span>
          <span class="value">${escapeHtml(data.nama_pasien)}</span>
        </div>

        <div class="detail-row">
          <span class="label">Ruangan</span>
          <span class="value muted">${data.ruangan?.nama ?? '-'}</span>
        </div>

        <div class="detail-row">
          <span class="label">Metode</span>
          <span class="badge success">${data.metode_pembayaran}</span>
        </div>

        <div class="detail-total">
          <span>Total Pendapatan</span>
          <strong>Rp ${Number(data.total).toLocaleString('id-ID')}</strong>
        </div>
      `;
    })
    .catch(() => {
      content.innerHTML = '❌ Gagal memuat detail';
    });
}

function closeDetailPendapatan() {
  document
    .getElementById('pendapatanDetailModal')
    ?.classList.remove('show');
}

let isEdit = false;
let editId = null;

function editPendapatan(id) {
  isEdit = true;
  editId = id;

  openPendapatanModal();

  const title = document.querySelector('.modal-title');
  const btn   = document.getElementById('btnSimpanPendapatan');

  if (title) title.innerText = '✏️ Edit Pendapatan Umum';
  if (btn)   btn.innerText   = '💾 Simpan Perubahan';

  fetch(`/dashboard/pendapatan/umum/${id}`, {
    headers: { Accept: 'application/json' }
  })
    .then(res => res.json())
    .then(data => {
      // === ISI FORM ===
      document.querySelector('[name="tanggal"]').value =
        data.tanggal.substring(0, 10);

      document.querySelector('[name="nama_pasien"]').value =
        data.nama_pasien;

      document.querySelector('[name="ruangan_id"]').value =
        data.ruangan_id;

      document.querySelector('[name="rs_tindakan"]').value =
        data.rs_tindakan;

      document.querySelector('[name="pelayanan_tindakan"]').value =
        data.pelayanan_tindakan;

      document.querySelector('[name="rs_obat"]').value =
        data.rs_obat;

      document.querySelector('[name="pelayanan_obat"]').value =
        data.pelayanan_obat;

      // 🔥 update tampilan nominal + total
      document.querySelectorAll('.nominal-value').forEach((input, i) => {
        const display = document.querySelectorAll('.nominal-display')[i];
        display.value = formatRibuan(input.value);
      });

      hitungTotal();
      cekSiapSimpan();
    });
}
