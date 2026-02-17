<div id="pendapatanKerjasamaModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 800px;">
        <h3 class="modal-title"><i class="ph ph-plus-circle"></i> Tambah Pendapatan Kerjasama</h3>
        <form id="formPendapatanKerjasama" onsubmit="submitPendapatanKerjasama(event)" autocomplete="off">
            <h4 class="section-title">Rincian Pasien</h4>
            <div class="form-grid grid-3">
                <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-input"
                        required></div>
                <div class="form-group"><label>Nama Pasien</label><input type="text" name="nama_pasien"
                        class="form-input" required></div>
                <div class="form-group"><label>Ruangan</label><select name="ruangan_id" id="kerjasamaRuanganSelect"
                        class="form-input" required>
                        <option value="">-- Pilih Ruangan --</option>
                    </select></div>
            </div>
            <div class="form-grid grid-1">
                <div class="form-group"><label>MOU / Instansi Kerjasama</label>
                    <select name="mou_id" id="kerjasamaMouSelect" class="form-input" required>
                        <option value="">-- Pilih MOU --</option>
                    </select>
                    <input type="hidden" name="transaksi" id="kerjasamaTransaksiHidden">
                </div>
            </div>
            <h4 class="section-title">Rincian Bank</h4>
            <div class="form-grid grid-3">
                <div class="form-group"><label>Metode Pembayaran</label><select id="kerjasamaMetodePembayaran"
                        name="metode_pembayaran" class="form-input" required>
                        <option value="NON_TUNAI" selected>Non Tunai</option>
                    </select></div>
                <div class="form-group"><label>Bank</label><select id="kerjasamaBank" name="bank" class="form-input"
                        disabled>
                        <option value="">-- Pilih Bank --</option>
                    </select></div>
                <div class="form-group"><label>Metode Detail</label><select id="kerjasamaMetodeDetail"
                        name="metode_detail" class="form-input" disabled>
                        <option value="">-- Detail --</option>
                    </select></div>
            </div>
            <h4 class="section-title">Rincian Nominal</h4>
            <div class="form-grid grid-2">
                <div class="form-group"><label>RS Tindakan</label>
                    <div class="input-group"><span>Rp</span><input type="text"
                            class="form-input nominal-display-kerjasama" placeholder="0"><input type="hidden"
                            name="rs_tindakan" class="nominal-value-kerjasama" value="0"></div>
                </div>
                <div class="form-group"><label>RS Obat</label>
                    <div class="input-group"><span>Rp</span><input type="text"
                            class="form-input nominal-display-kerjasama" placeholder="0"><input type="hidden"
                            name="rs_obat" class="nominal-value-kerjasama" value="0"></div>
                </div>
                <div class="form-group"><label>Pelayanan Tindakan</label>
                    <div class="input-group"><span>Rp</span><input type="text"
                            class="form-input nominal-display-kerjasama" placeholder="0"><input type="hidden"
                            name="pelayanan_tindakan" class="nominal-value-kerjasama" value="0"></div>
                </div>
                <div class="form-group"><label>Pelayanan Obat</label>
                    <div class="input-group"><span>Rp</span><input type="text"
                            class="form-input nominal-display-kerjasama" placeholder="0"><input type="hidden"
                            name="pelayanan_obat" class="nominal-value-kerjasama" value="0"></div>
                </div>
            </div>
            <div class="total-box"><span>Total Pembayaran</span><strong id="totalPembayaranKerjasama">Rp 0</strong>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePendapatanKerjasamaModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanPendapatanKerjasama" class="btn-primary" disabled>
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="pendapatanKerjasamaDetailModal" class="confirm-overlay">
    <div class="confirm-box detail-box" style="max-width: 600px;">
        <h3 class="modal-title"><i class="ph ph-file-text"></i> Detail Kerjasama</h3>
        <div id="detailPendapatanKerjasamaContent" class="detail-grid"></div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeDetailPendapatanKerjasama()">Tutup</button>
        </div>
    </div>
</div>