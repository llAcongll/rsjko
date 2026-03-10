<div id="modalBankLedger" class="confirm-overlay">
    <div class="confirm-box" style="max-width:500px">
        <h3 id="bankLedgerModalTitle"><i class="ph ph-bank"></i> Tambah Saldo Rekening</h3>

        <form id="formBankLedger" onsubmit="submitBankLedger(event)" autocomplete="off">
            <input type="hidden" id="bankLedgerId">
            <div class="form-group">
                <label>Bank / Rekening</label>
                <select name="bank" id="bankLedgerDepositBank" class="form-input" required>
                    <option value="" disabled selected>-- Pilih Bank --</option>
                    <option value="BRK">Bank Riau Kepri Syariah</option>
                    <option value="BSI">Bank Syariah Indonesia</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal Setor</label>
                <input type="date" name="date" id="bankLedgerDate" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Keterangan / Uraian</label>
                <input type="text" name="description" id="bankLedgerDescription" class="form-input"
                    placeholder="Contoh: Titipan Dana BLUD Bulan Februari..." required>
            </div>

            <div class="form-group">
                <label>Nominal Setor (Rp)</label>
                <div class="input-currency">
                    <span class="currency-prefix">Rp</span>
                    <input type="text" id="bankLedgerAmountDisplay" class="form-input currency-input" placeholder="0"
                        required>
                    <input type="hidden" name="amount" id="bankLedgerAmountValue">
                </div>
            </div>

            <div class="modal-actions" style="margin-top:24px;">
                <button type="button" class="btn-secondary" onclick="closeBankLedgerModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary" style="background:#166534;">
                    <i class="ph ph-check"></i> Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>





