<div id="rekeningModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width:520px">

    <h3 id="rekeningModalTitle">➕ Tambah Rekening</h3>

    <div class="form-group">
      <label>Tanggal</label>
      <input type="date" id="rkTanggal" class="form-input">
    </div>

    <div class="form-group">
      <label>Bank</label>
      <select id="rkBank" class="form-input">
        <option value="">-- Pilih Bank --</option>
      </select>
    </div>

    <div class="form-group">
      <label>Keterangan</label>
      <input type="text" id="rkKeterangan" class="form-input">
    </div>

    <div class="form-group">
      <label>C / D</label>
      <select id="rkCD" class="form-input">
        <option value="">-- Pilih Jenis --</option>
        <option value="C">C (Credit)</option>
        <option value="D">D (Debit)</option>
      </select>
    </div>

    <div class="form-group">
      <label>Jumlah</label>

      <div class="input-currency">
        <span class="currency-prefix">Rp</span>
        <input
          type="text"
          id="rkJumlah"
          class="form-input currency-input"
          placeholder="0,00"
          inputmode="numeric"
          autocomplete="off"
          oninput="formatRupiahInput(this)"
        >
      </div>
    </div>

    <div class="confirm-actions">
      <button class="btn-secondary" onclick="closeRekeningModal()">Batal</button>
      <button class="btn-primary" onclick="submitRekening()">Simpan</button>
    </div>

  </div>
</div>
