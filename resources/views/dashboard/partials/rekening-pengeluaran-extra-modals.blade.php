{{-- REKENING KORAN PENGELUARAN (BANK LEDGER) MODALS --}}
<div id="modalBankLedgerSaldoAwal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 440px; padding: 30px;">
        <h3 style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <i class="ph ph-wallet" style="font-size: 24px; color: #0f766e;"></i>
            <span style="font-size: 20px; font-weight: 800; color: #1e293b;">Set Saldo Awal Tahun</span>
        </h3>

        <div class="alert alert-info"
            style="margin-bottom: 1.5rem; font-size: 0.9rem; background: #f0fdf4; border-left: 4px solid #0f766e; padding: 12px; color: #064e3b; border-radius: 8px;">
            <i class="ph-fill ph-info"></i> Saldo awal hanya perlu di set 1x di awal tahun. Nilai ini akan
            diakumulasikan ke perhitungan mutasi.
        </div>

        <form id="formBankLedgerSaldoAwal" onsubmit="submitBankLedgerSaldoAwal(event)">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">
                    Bank / Rekening
                </label>
                <select id="bankLedgerSaldoAwalBank" name="bank" class="form-input" style="height: 42px;" required>
                    <option value="" disabled selected>-- Pilih Bank --</option>
                    <option value="BRK">Bank Riau Kepri Syariah</option>
                    <option value="BSI">Bank Syariah Indonesia</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">
                    Nominal Saldo Awal
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 11px; color: #64748b; font-weight: 500;">Rp</span>
                    <input type="text" id="bankLedgerSaldoAwalDisplayInput" class="form-input"
                        style="padding-left: 35px; font-weight: 700; height: 42px;" required placeholder="0">
                    <input type="hidden" id="bankLedgerSaldoAwalValue" name="amount" required>
                </div>
            </div>

            <div class="confirm-actions" style="margin-top: 30px;">
                <button type="button" class="btn-secondary" onclick="closeBankLedgerSaldoAwalModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="button" id="btnHapusBankLedgerSaldoAwal" class="btn-danger" style="display: none;"
                    onclick="deleteBankLedgerSaldoAwal()">
                    <i class="ph ph-trash"></i> Hapus
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan Saldo
                </button>
            </div>
        </form>
    </div>
</div>