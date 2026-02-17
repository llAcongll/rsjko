<div id="modalPiutang" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 600px;">
        <h3 class="modal-title"><i class="ph ph-plus-circle"></i> Catat Piutang Baru</h3>

        <form id="formPiutang" onsubmit="submitPiutang(event)" autocomplete="off">
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Tanggal Pencatatan</label>
                    <input type="date" name="tanggal" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Bulan Pelayanan</label>
                    <input type="text" name="bulan_pelayanan" class="form-input" placeholder="Contoh: Januari 2024"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label>Perusahaan / Debitur</label>
                <select name="perusahaan_id" id="piutangPerusahaanSelect" class="form-input" required>
                    <option value="">-- Pilih Perusahaan --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Keterangan (Opsional)</label>
                <input type="text" name="keterangan" class="form-input" placeholder="Keterangan tambahan...">
            </div>

            <hr style="margin: 16px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <div class="form-group">
                <label>Jumlah Tagihan (Rp)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-input nominal-display-piutang" placeholder="0" inputmode="numeric">
                    <input type="hidden" name="jumlah_piutang" class="nominal-value-piutang" value="0">
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label>Status Pembayaran</label>
                <select name="status" class="form-input" required>
                    <option value="BELUM_LUNAS">Belum Lunas</option>
                    <option value="LUNAS">Lunas</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePiutangModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>