<div id="modalAdjustment" class="confirm-overlay">
    <div class="confirm-box" style="max-width:500px">
        <h3 id="adjustmentModalTitle"><i class="ph ph-sliders"></i> Penyesuaian Saldo</h3>

        <form id="formAdjustment" onsubmit="submitAdjustment(event)" autocomplete="off">
            <input type="hidden" id="adjustmentId">
            <div class="form-group">
                <label>Bank / Rekening</label>
                <select name="bank" id="adjustmentBank" class="form-input" required>
                    <option value="" disabled selected>-- Pilih Bank --</option>
                    <option value="BRK">Bank Riau Kepri Syariah</option>
                    <option value="BSI">Bank Syariah Indonesia</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal Penyesuaian</label>
                <input type="date" name="date" id="adjustmentDate" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Jenis Penyesuaian</label>
                <select name="direction" id="adjustmentDirection" class="form-input" required>
                    <option value="DEBIT">Debit (Masuk / Menambah Saldo)</option>
                    <option value="CREDIT">Kredit (Keluar / Mengurangi Saldo)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nominal (Rp)</label>
                <div class="input-currency">
                    <span class="currency-prefix">Rp</span>
                    <input type="text" id="adjustmentAmountDisplay" class="form-input currency-input" placeholder="0"
                        required>
                    <input type="hidden" name="amount" id="adjustmentAmountValue">
                </div>
            </div>

            <div class="form-group">
                <label>Keterangan / Uraian</label>
                <input type="text" name="description" id="adjustmentDescription" class="form-input"
                    placeholder="Contoh: Penyesuaian Pajak / Setoran Sisa Kas..." required>
            </div>

            <div class="modal-actions" style="margin-top:24px;">
                <button type="button" class="btn-secondary" onclick="closeAdjustmentModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary" style="background:#0369a1;">
                    <i class="ph ph-check"></i> Simpan Penyesuaian
                </button>
            </div>
        </form>
    </div>
</div>





