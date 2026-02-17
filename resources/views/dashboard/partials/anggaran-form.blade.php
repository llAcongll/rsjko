<div id="anggaranModal" class="confirm-overlay">
  <div class="confirm-box" style="width: 850px; max-width: 95vw;">
    <h3 id="anggaranModalTitle"><i class="ph-fill ph-coin"></i> Input Anggaran</h3>
    <input type="hidden" id="arKodeRekeningId">

    <div class="modal-total-box">
      <div class="total-left">
        <label class="modal-total-label">Subtotal Anggaran (Rp)</label>
        <div class="total-tahun">Tahun Anggaran: <span id="arTahunLabel" style="font-weight: 700;">2026</span></div>
      </div>
      <input id="arNilai" class="modal-total-value"
        style="background:transparent; border:none; text-align:right; pointer-events:none;" value="Rp 0" readonly>
    </div>

    <input type="hidden" id="arTahun">

    <div class="rincian-section">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <label style="font-weight: 700; color: #1e293b; font-size: 14px;">
          <i class="ph ph-list-checks"></i> Komponen Perhitungan (RKA)
        </label>
        <button type="button" class="btn-primary btn-sm" onclick="addRincianRow()">
          <i class="ph ph-plus"></i> Tambah Komponen
        </button>
      </div>

      <div
        style="max-height: 350px; overflow-y: auto; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;">
        <table id="rincianTable">
          <thead>
            <tr>
              <th>Uraian Komponen</th>
              <th style="width: 80px; text-align: center;">Vol</th>
              <th style="width: 100px;">Satuan</th>
              <th style="width: 150px; text-align: right;">Tarif Satuan</th>
              <th style="width: 150px; text-align: right;">Subtotal</th>
              <th style="width: 50px;"></th>
            </tr>
          </thead>
          <tbody id="rincianBody">
            <!-- Rincian rows will be added here -->
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-actions" style="margin-top: 24px;">
      <button class="btn-secondary" onclick="closeAnggaranModal()">Batal</button>
      <button class="btn-primary" onclick="submitAnggaran()">
        <i class="ph ph-check-circle"></i> Simpan Anggaran
      </button>
    </div>
  </div>
</div>