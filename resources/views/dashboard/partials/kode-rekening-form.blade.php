<div id="kodeRekeningModal" class="confirm-overlay">
  <div class="confirm-box" style="max-width: 500px;">
    <h3 id="kodeModalTitle"><i class="ph ph-list-numbers"></i> Tambah Kode Rekening</h3>

    <div class="form-grid grid-2">
      <div class="form-group">
        <label>Kode</label>
        <input id="krKode" class="form-input" placeholder="Contoh: 4.1.1">
      </div>
      <div class="form-group">
        <label>Nama Rekening</label>
        <input id="krNama" class="form-input" placeholder="Contoh: Pendapatan BLUD">
      </div>
    </div>

    <div class="form-group">
      <label>Tipe Rekening</label>
      <select id="krTipe" class="form-input" onchange="toggleSumberDataField()">
        <option value="header">Header (Grup)</option>
        <option value="detail">Detail (Postable)</option>
      </select>
    </div>

    <div id="krSumberDataWrapper" style="display:none; margin-top:10px;">
      <div class="form-group">
        <label>Sumber Data Realisasi</label>
        <select id="krSumberData" class="form-input">
          <option value="">-- Tanpa Mapping --</option>
          <option value="PASIEN_UMUM">PASIEN UMUM</option>
          <option value="BPJS_JAMINAN">BPJS DAN JAMINAN</option>
          <option value="KERJASAMA">KERJASAMA</option>
          <option value="PKL">PRAKTEK KERJA LAPANGAN</option>
          <option value="MAGANG">PRAKTEK MAGANG</option>
          <option value="LAIN_LAIN">LAIN-LAIN</option>
          <option value="PENELITIAN">PENELITIAN</option>
          <option value="PERMINTAAN_DATA">PERMINTAAN DATA</option>
          <option value="STUDY_BANDING">STUDY BANDING</option>
        </select>
      </div>
    </div>

    <input type="hidden" id="krParentId">
    <input type="hidden" id="krLevel">

    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeKodeRekeningModal()">Batal</button>
      <button class="btn-primary" onclick="submitKodeRekening()">Simpan</button>
    </div>
  </div>
</div>