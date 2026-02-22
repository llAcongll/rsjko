<div id="penyesuaianModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px; width: 90%;">
        <h3 id="penyesuaianModalTitle"><i class="ph ph-plus-circle"></i> Tambah Penyesuaian</h3>

        <form id="penyesuaianForm">
            <input type="hidden" id="penyesuaianId">

            <div class="form-group">
                <label>Tanggal Pelunasan / Input</label>
                <input type="date" id="penyesuaianTanggal" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select id="penyesuaianKategori" class="form-input" required onchange="handleKategoriChange()">
                    <option value="BPJS">BPJS</option>
                    <option value="JAMINAN">JAMINAN</option>
                </select>
            </div>

            <div class="form-group" id="groupSubKategori" style="display: none;">
                <label>Tipe BPJS</label>
                <select id="penyesuaianSubKategori" class="form-input">
                    <option value="">-- Pilih Tipe BPJS --</option>
                    <option value="REGULAR">REGULAR</option>
                    <option value="EVAKUASI">EVAKUASI</option>
                    <option value="OBAT">OBAT</option>
                </select>
            </div>

            <div class="form-group">
                <label>Perusahaan</label>
                <select id="penyesuaianPerusahaanId" class="form-input" required disabled>
                    <option value="">Pilih Perusahaan...</option>
                </select>
            </div>

            <div class="form-group">
                <label>Untuk Tagihan Piutang Tahun</label>
                <select id="penyesuaianTahunPiutang" class="form-input" required>
                    @php
                        $currentYear = date('Y');
                        $startYear = 2023; // Adjusted to capture historical data
                    @endphp
                    @for ($y = $currentYear; $y >= $startYear; $y--)
                        <option value="{{ $y }}" {{ $y == session('tahun_anggaran') ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
                <small style="color: #64748b;">Pilih tahun piutang yang dikurangi/dilunasi</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Pelunasan Tunai</label>
                    <input type="number" id="penyesuaianPelunasan" class="form-input" placeholder="0" min="0"
                        step="0.01" style="border-color: #22c55e; border-width: 2px;">
                </div>
                <div class="form-group">
                    <label>Potongan (70:30)</label>
                    <input type="number" id="penyesuaianPotongan" class="form-input" placeholder="0" min="0"
                        step="0.01">
                </div>
                <div class="form-group">
                    <label>Adm Bank</label>
                    <input type="number" id="penyesuaianAdm" class="form-input" placeholder="0" min="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <textarea id="penyesuaianKeterangan" class="form-input" rows="3"
                    placeholder="Contoh: Pelunasan sisa piutang tahun 2025"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closePenyesuaianModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" class="btn-primary">
                    <i class="ph ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>