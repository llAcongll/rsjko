<!-- MODAL PENDAPATAN UMUM -->
<div id="pendapatanUmumModal" class="pendapatan-modal">

  <div class="pendapatan-box">
    <h3 class="modal-title">➕ Tambah Pendapatan Umum</h3>

    <form id="formPendapatanUmum" onsubmit="submitPendapatanUmum(event)">

      <!-- ================= RINCIAN PASIEN ================= -->
      <h4 class="section-title">Rincian Pasien</h4>

      <div class="form-grid grid-3">
        <div class="form-group">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-input" required>
        </div>

        <div class="form-group">
          <label>Nama Pasien</label>
          <input type="text" name="nama_pasien" class="form-input" required>
        </div>

        <div class="form-group">
          <label>Ruangan</label>
          <select name="ruangan_id" id="ruanganSelect" class="form-input" required>
            <!-- diisi via JS -->
            <option value="">-- Pilih Ruangan --</option>
          </select>
        </div>
      </div>

      <!-- ================= RINCIAN BANK ================= -->
      <h4 class="section-title">Rincian Bank</h4>

      <div class="form-grid grid-3">
        <div class="form-group">
          <label>Metode Pembayaran</label>
          <select id="metodePembayaran" name="metode_pembayaran" class="form-input" required>
            <option value="">-- Pilih Metode --</option>
            <option value="TUNAI">Tunai</option>
            <option value="NON_TUNAI">Non Tunai</option>
          </select>
        </div>

        <div class="form-group">
          <label>Bank</label>
          <select id="bank" name="bank_id" class="form-input" disabled required>
            <option value="">-- Pilih Bank --</option>
          </select>
        </div>

        <div class="form-group">
          <label>Metode Detail</label>
          <select id="metodeDetail" name="metode_detail" class="form-input" disabled>
            <option value="">-- Metode Detail --</option>
          </select>
        </div>
      </div>

<!-- ================= RINCIAN NOMINAL ================= -->
<h4 class="section-title">Rincian Nominal</h4>

<div class="form-grid grid-2">

  <div class="form-group">
    <label>Tindakan Jasa Rumah Sakit</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text"
             class="form-input nominal-display"
             placeholder="0"
             inputmode="numeric">
      <input type="hidden"
             name="rs_tindakan"
             class="nominal-value"
             value="0">
    </div>
  </div>

  <div class="form-group">
    <label>Tindakan Jasa Pelayanan</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text"
             class="form-input nominal-display"
             placeholder="0"
             inputmode="numeric">
      <input type="hidden"
             name="pelayanan_tindakan"
             class="nominal-value"
             value="0">
    </div>
  </div>

  <div class="form-group">
    <label>Obat Jasa Rumah Sakit</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text"
             class="form-input nominal-display"
             placeholder="0"
             inputmode="numeric">
      <input type="hidden"
             name="rs_obat"
             class="nominal-value"
             value="0">
    </div>
  </div>

  <div class="form-group">
    <label>Obat Jasa Pelayanan</label>
    <div class="input-group">
      <span class="input-group-text">Rp</span>
      <input type="text"
             class="form-input nominal-display"
             placeholder="0"
             inputmode="numeric">
      <input type="hidden"
             name="pelayanan_obat"
             class="nominal-value"
             value="0">
    </div>
  </div>

</div>

      <!-- ================= TOTAL ================= -->
      <div class="total-box">
        <span>Total Pembayaran</span>
        <strong id="totalPembayaran">Rp 0</strong>
      </div>

<!-- ================= ACTION ================= -->
<div class="modal-actions">
  <button type="button"
          class="btn btn-outline"
          onclick="closePendapatanModal()">
    Batal
  </button>

  <button type="submit"
          id="btnSimpanPendapatan"
          class="btn btn-primary">
    💾 Simpan
  </button>
</div>

    </form>
  </div>

</div>