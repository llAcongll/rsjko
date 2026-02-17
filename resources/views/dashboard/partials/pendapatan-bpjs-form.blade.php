<div id="pendapatanBpjsModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 800px;">
        <h3 class="modal-title"><i class="ph ph-plus-circle"></i> Tambah Pendapatan BPJS</h3>

        <form id="formPendapatanBpjs" onsubmit="submitPendapatanBpjs(event)" autocomplete="off">
            <h4 class="section-title">Rincian Pasien</h4>

            <div class="form-grid grid-3">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Jenis BPJS</label>
                    <select name="jenis_bpjs" id="bpjsJenisSelect" class="form-input" required>
                        <option value="REGULAR">Regular</option>
                        <option value="EVAKUASI">Evakuasi</option>
                        <option value="OBAT">Obat</option>
                    </select>
                </div>

                <div class="form-group" id="noSepGroup">
                    <label>No SEP</label>
                    <input type="text" name="no_sep" id="bpjsNoSep" class="form-input" placeholder="Masukkan No SEP">
                </div>
            </div>

            <div class="form-grid grid-3">
                <div class="form-group">
                    <label>Nama Pasien</label>
                    <input type="text" name="nama_pasien" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>Ruangan</label>
                    <select name="ruangan_id" id="bpjsRuanganSelect" class="form-input" required>
                        <option value="">-- Pilih Ruangan --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Perusahaan</label>
                    <select name="perusahaan_id" id="bpjsPerusahaanSelect" class="form-input">
                        <option value="">-- Pilih Perusahaan --</option>
                    </select>
                    <input type="hidden" name="transaksi" id="bpjsTransaksiHidden">
                </div>
            </div>

            <h4 class="section-title">Rincian Bank</h4>

            <div class="form-grid grid-3">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select id="bpjsMetodePembayaran" name="metode_pembayaran" class="form-input" required>
                        <option value="NON_TUNAI" selected>Non Tunai</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bank</label>
                    <select id="bpjsBank" name="bank" class="form-input" disabled>
                        <option value="">-- Pilih Bank --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Metode Detail</label>
                    <select id="bpjsMetodeDetail" name="metode_detail" class="form-input" disabled>
                        <option value="">-- Metode Detail --</option>
                    </select>
                </div>
            </div>

            <h4 class="section-title">Rincian Nominal</h4>

            <div id="groupTindakan" class="form-grid grid-2">
                <div class="form-group">
                    <label>Tindakan Jasa Rumah Sakit</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-input nominal-display-bpjs" placeholder="0" inputmode="numeric">
                        <input type="hidden" name="rs_tindakan" class="nominal-value-bpjs" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Tindakan Jasa Pelayanan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-input nominal-display-bpjs" placeholder="0" inputmode="numeric">
                        <input type="hidden" name="pelayanan_tindakan" class="nominal-value-bpjs" value="0">
                    </div>
                </div>
            </div>

            <div id="groupObat" class="form-grid grid-2">
                <div class="form-group">
                    <label>Obat Jasa Rumah Sakit</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-input nominal-display-bpjs" placeholder="0" inputmode="numeric">
                        <input type="hidden" name="rs_obat" class="nominal-value-bpjs" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Obat Jasa Pelayanan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-input nominal-display-bpjs" placeholder="0" inputmode="numeric">
                        <input type="hidden" name="pelayanan_obat" class="nominal-value-bpjs" value="0">
                    </div>
                </div>
            </div>

            <div class="total-box">
                <span>Total Pembayaran</span>
                <strong id="totalPembayaranBpjs">Rp 0</strong>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePendapatanBpjsModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanPendapatanBpjs" class="btn-primary" disabled>
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>