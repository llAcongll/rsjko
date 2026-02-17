<div id="ruanganModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width:420px">
    <h3 id="ruanganModalTitle"><i class="ph ph-buildings"></i> Tambah Ruangan</h3>

    <div class="form-group">
      <label>Kode Ruangan</label>
      <input id="ruanganKode" class="form-input" readonly>
    </div>

    <div class="form-group">
      <label>Nama Ruangan</label>
      <input type="text" id="ruanganNama" class="form-input" placeholder="Contoh: Ruang Rawat Inap">
    </div>

    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeRuanganModal()">
        <i class="ph ph-x"></i> Batal
      </button>
      <button class="btn-primary" onclick="submitRuangan()">
        <i class="ph ph-floppy-disk"></i> Simpan
      </button>
    </div>
  </div>
</div>