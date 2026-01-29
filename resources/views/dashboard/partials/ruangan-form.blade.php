<div id="ruanganModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width:420px">

    <h3 id="ruanganModalTitle">🏥 Tambah Ruangan</h3>

    <div class="form-group">
      <label>Kode Ruangan</label>
      <input id="ruanganKode" class="form-input" readonly>
    </div>

    <div class="form-group">
      <label>Nama Ruangan</label>
      <input
        type="text"
        id="ruanganNama"
        class="form-input"
        placeholder="Contoh: Ruang Rawat Inap"
      >
    </div>

    <div class="confirm-actions">
      <button class="btn-secondary" onclick="closeRuanganModal()">Batal</button>
      <button class="btn-primary" onclick="submitRuangan()">Simpan</button>
    </div>

  </div>
</div>
