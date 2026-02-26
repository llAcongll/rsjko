<div id="pengeluaranModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 850px; padding: 30px;">
        <h3 class="modal-title" style="margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
            <i class="ph ph-plus-circle" style="font-size: 24px; color: #6366f1;"></i>
            <span id="pengeluaranModalTitle" style="font-size: 20px; font-weight: 800; color: #1e293b;">Tambah
                Pengeluaran</span>
        </h3>

        <form id="formPengeluaran" onsubmit="submitPengeluaran(event)" autocomplete="off">
            <input type="hidden" name="id" id="pengeluaranId">
            <input type="hidden" name="kategori" id="pengeluaranKategori">
            <input type="hidden" name="fund_disbursement_id" id="pengeluaranFundDisbursementId">

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label
                        style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Tanggal
                        Transaksi</label>
                    <input type="date" name="tanggal" id="pengeluaranTanggal" class="form-input" required
                        style="height: 42px;">
                </div>

                <div class="form-group">
                    <label
                        style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Kode
                        Rekening (Kegiatan)</label>
                    <select name="kode_rekening_id" id="pengeluaranRekening" class="form-input" required
                        style="height: 42px;">
                        <option value="">-- Pilih Rekening --</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Metode
                    Pembayaran</label>
                <select name="metode_pembayaran" id="pengeluaranMetode" class="form-input" required
                    style="height: 42px;">
                    <option value="">-- Pilih Metode --</option>
                    <option value="UP">Uang Persediaan (UP)</option>
                    <option value="GU">Ganti Uang (GU)</option>
                    <option value="LS">Langsung (LS)</option>
                </select>
            </div>

            <div class="form-group">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Vendor
                    / Penerima Pembayaran</label>
                <input type="text" name="vendor" id="pengeluaranVendor" class="form-input" style="height: 42px;"
                    placeholder="Nama toko, rekanan, atau perorangan...">
            </div>

            <div class="form-group" id="guCycleSection" style="display:none;">
                <label>Pilih Batch GU</label>
                <select name="siklus_up" id="pengeluaranSiklus" class="form-input">
                    <option value="">-- Pilih Batch GU --</option>
                </select>
            </div>

            <div class="form-group">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Uraian
                    Belanja / Kegiatan</label>
                <input type="text" name="uraian" id="pengeluaranUraian" class="form-input" style="height: 42px;"
                    placeholder="Masukkan rincian kegiatan atau tujuan belanja..." required>
            </div>

            <div class="form-grid grid-2">
                <div class="form-group">
                    <label
                        style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Jumlah
                        yang diminta</label>
                    <div class="input-group">
                        <span class="input-group-text"
                            style="height: 42px; background: #f8fafc; font-weight: 600;">Rp</span>
                        <input type="text" id="pengeluaranNominalDisplay" class="form-input nominal-display"
                            placeholder="0" inputmode="numeric" required style="height: 42px; font-weight: 600;">
                        <input type="hidden" name="nominal" id="pengeluaranNominalValue" class="nominal-value"
                            value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label
                        style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Potongan
                        Pajak</label>
                    <div class="input-group">
                        <span class="input-group-text"
                            style="height: 42px; background: #f8fafc; font-weight: 600;">Rp</span>
                        <input type="text" id="pengeluaranPotonganPajakDisplay" class="form-input nominal-display"
                            placeholder="0" inputmode="numeric" style="height: 42px; font-weight: 600; color: #ef4444;">
                        <input type="hidden" name="potongan_pajak" id="pengeluaranPotonganPajakValue"
                            class="nominal-value" value="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label
                    style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block; font-size: 13px;">Total
                    Dibayarkan (Neto)</label>
                <div class="input-group">
                    <span class="input-group-text"
                        style="height: 50px; background: #ecfdf5; color: #059669; font-weight: 800; border-color: #10b981; font-size: 18px;">Rp</span>
                    <input type="text" id="pengeluaranTotalDibayarkanDisplay" class="form-input" placeholder="0"
                        readonly
                        style="background: #f0fdf4; cursor: not-allowed; font-weight: 900; color: #059669; font-size: 22px; height: 50px; border-color: #10b981; border-left: none;">
                    <input type="hidden" name="total_dibayarkan" id="pengeluaranTotalDibayarkanValue" value="0">
                </div>
            </div>

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closePengeluaranModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanPengeluaran" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>