@php
    $title = 'Pencairan Dana (SP2D)';
@endphp

<div id="disbursementMainList">
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h2><i class="ph ph-wallet"></i> {{ $title }}</h2>
                <p>Kelola pencairan dana (UP/GU/LS) &mdash; Alur: SPP &rarr; SPM &rarr; SP2D (Cair)</p>
            </div>

            <div class="dashboard-header-right">
                @if(auth()->user()->hasPermission('PENGELUARAN_CREATE') || auth()->user()->isAdmin())
                    <button class="btn-tambah-data" onclick="openDisbursementForm()">
                        <i class="ph-bold ph-plus"></i>
                        <span>Buat Pengajuan SPP</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="dashboard-box">
            <div class="table-container">
                <table id="tableDisbursement">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th width="80" class="text-center">Paket</th>
                            <th width="70" class="text-center">Tipe</th>
                            <th width="220" class="text-left">No. Dokumen</th>
                            <th width="100" class="text-center">Siklus</th>
                            <th width="100" class="text-center">Tanggal</th>
                            <th>Kegiatan</th>
                            <th class="text-right">Nilai (Rp)</th>
                            <th width="120" class="text-center">Status</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableDisbursementBody">
                        <tr>
                            <td colspan="10" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- SECTION DETAIL BELANJA (FULL WIDTH) --}}
<div id="sectionBelanjaItems" style="display: none; animation: fadeIn 0.3s ease-out;">
    <div class="dashboard-header" style="margin-bottom: 24px;">
        <div class="dashboard-header-left">
            <button onclick="closeBelanjaItemsModal()"
                style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
                <i class="ph ph-arrow-left"></i> Kembali ke Daftar
            </button>
            <h2 id="belanjaItemsTitle"><i class="ph ph-shopping-cart"></i> Detail Belanja / Kegiatan</h2>
            <p id="belanjaItemsSubtitle" style="font-size: 14px; color: #64748b;">Kelola rincian kegiatan untuk
                dokumen: <span id="belanjaRefNo" style="font-weight: 700; color: #1e293b;">-</span></p>
        </div>
        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CAIR_CREATE') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="addNewBelanjaItem()" style="background:#059669; height: 44px;">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Rincian Kegiatan</span>
                </button>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px;">
        <div
            style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div
                style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                Total Dana Cair (SP2D)</div>
            <div style="font-size: 24px; font-weight: 800; color: #1e40af;">
                <small style="font-size: 14px; font-weight: 600; opacity: 0.6;">Rp</small> <span
                    id="belanjaTotalValue">0,00</span>
            </div>
        </div>
        <div
            style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div
                style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                Total Realisasi (Belanja)</div>
            <div style="font-size: 24px; font-weight: 800; color: #dc2626;">
                <small style="font-size: 14px; font-weight: 600; opacity: 0.6;">Rp</small> <span
                    id="belanjaUsedValue">0,00</span>
            </div>
        </div>
        <div
            style="background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div
                style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                Sisa (Belum Direalisasikan)</div>
            <div style="font-size: 24px; font-weight: 800; color: #059669;">
                <small style="font-size: 14px; font-weight: 600; opacity: 0.6;">Rp</small> <span
                    id="belanjaRemainingValue">0,00</span>
            </div>
        </div>
    </div>

    <div class="dashboard-box" style="padding: 0; overflow: hidden;">
        <div class="table-container" style="margin-top: 0; border-radius: 0; border: none;">
            <table>
                <thead>
                    <tr>
                        <th class="text-center" width="60">No</th>
                        <th style="white-space:nowrap" width="140">Tanggal</th>
                        <th style="white-space:nowrap" width="260">No. Bukti</th>
                        <th>Uraian Kegiatan</th>
                        <th class="text-right" width="180">Nilai (Rp)</th>
                        <th class="text-center" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody id="belanjaItemsTableBody">
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <i class="ph ph-mask-sad" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                            Belum ada rincian kegiatan untuk pencairan ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DISBURSEMENT FORM --}}
<div id="disbursementFormModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 650px;">
        <h3 class="modal-title">
            <i class="ph ph-file-text"></i>
            <span id="disbursementModalTitle">Buat Pengajuan SPP</span>
        </h3>
        <p style="font-size: 13px; color: #64748b; margin-top: -12px; margin-bottom: 20px;">
            Lengkapi detail pengajuan pencairan dana. Nomor SPP dibuat otomatis.</p>

        <form id="formDisbursement" onsubmit="submitDisbursement(event)" autocomplete="off">
            <input type="hidden" name="id" id="disbursementId">
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label>Tipe Pencairan</label>
                    <select name="type" id="disbursementType" class="form-input" required>
                        <option value="UP">Uang Persediaan (UP)</option>
                        <option value="GU">Ganti Uang (GU)</option>
                        <option value="LS">Langsung (LS)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pengajuan</label>
                    <input type="date" name="sp2d_date" class="form-input" id="disbursementDate" required>
                </div>
            </div>

            <div class="form-group" id="siklusGroup" style="display:none;">
                <label>Siklus Ke (misal: 1 untuk GU-1)</label>
                <input type="number" name="siklus_up" id="disbursementSiklus" class="form-input" placeholder="1"
                    min="1">
            </div>

            {{-- SALDO KAS UP/GU --}}
            <div id="saldoKasInfo"
                style="display:none; background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #93c5fd; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: #1e40af; margin-bottom: 8px;">
                    <i class="ph ph-wallet"></i> Saldo Kas <span id="saldoKasType">UP</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>
                        <span style="color: #64748b;">Total Dana Cair:</span>
                        <div style="font-weight: 700; color: #0f172a;" id="saldoTotalCair">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Total Belanja:</span>
                        <div style="font-weight: 700; color: #dc2626;" id="saldoTotalBelanja">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">SPP Dalam Proses:</span>
                        <div style="font-weight: 700; color: #b45309;" id="saldoSppPending">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Sisa Saldo Kas:</span>
                        <div style="font-weight: 800; font-size: 15px;" id="saldoSisaKas">-</div>
                    </div>
                </div>
            </div>

            {{-- KEGIATAN / KODE REKENING --}}
            <div class="form-group" id="rekeningGroup">
                <label><i class="ph ph-list-numbers" style="color:#2563eb"></i> Kegiatan / Kode Rekening</label>
                <select name="kode_rekening_id" id="disbursementRekening" class="form-input">
                    <option value="">-- Pilih Kegiatan --</option>
                </select>
            </div>

            {{-- SISA SALDO INFO --}}
            <div id="sisaSaldoInfo"
                style="display:none; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 8px;">
                    <i class="ph ph-info"></i> Informasi Anggaran
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>
                        <span style="color: #64748b;">Pagu Anggaran:</span>
                        <div style="font-weight: 700; color: #0f172a;" id="infoAnggaran">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Realisasi:</span>
                        <div style="font-weight: 700; color: #dc2626;" id="infoRealisasi">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">SPP Dalam Proses:</span>
                        <div style="font-weight: 700; color: #b45309;" id="infoSppPending">-</div>
                    </div>
                    <div>
                        <span style="color: #64748b;">Sisa Tersedia:</span>
                        <div style="font-weight: 800; font-size: 15px;" id="infoSisa">-</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Uraian Kegiatan</label>
                <input type="text" name="uraian" id="disbursementUraian" class="form-input"
                    placeholder="Uraian kegiatan yang akan dibayarkan...">
            </div>

            <div class="form-group">
                <label>Nilai Pencairan (Rp)</label>
                <input type="number" name="value" class="form-input" id="disbursementValue" placeholder="0" required
                    min="0">
            </div>

            <div class="form-group">
                <label>Keterangan Tambahan</label>
                <textarea name="description" id="disbursementDescription" class="form-input" rows="2"
                    placeholder="Keterangan tambahan (opsional)..."></textarea>
            </div>



            {{-- Hidden: status SPP saat buat baru, diatur ulang saat edit --}}
            <input type="hidden" name="status" id="disbursementStatus" value="SPP">

            <div class="confirm-actions">
                <button type="button" class="btn-secondary" onclick="closeDisbursementModal()">
                    <i class="ph ph-x"></i> Batal
                </button>
                <button type="submit" id="btnSimpanDisbursement" class="btn-primary">
                    <i class="ph ph-paper-plane-tilt"></i> Ajukan SPP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Pengeluaran (Preview Belanja) -->
<div id="pengeluaranDetailModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 500px;">
        <h3 class="modal-title">
            <i class="ph ph-receipt"></i> Detail Realisasi Belanja
        </h3>
        <div id="detailPengeluaranContent" class="detail-grid" style="margin-top: 20px;">
            <!-- Content will be injected by JS -->
        </div>
        <div class="modal-actions" style="margin-top: 30px;">
            <button type="button" class="btn-secondary" onclick="closeDetailPengeluaran()" style="width: 100%;">
                Tutup
            </button>
        </div>
    </div>
</div>



<!-- Modal Detail Realisasi Pencairan (Disbursement View Only) -->
<div id="disbursementDetailModal" class="confirm-overlay">
    <div class="confirm-box" style="max-width: 600px;">
        <h3 class="modal-title">
            <i class="ph ph-receipt"></i> Detail Realisasi Pencairan
        </h3>
        <div id="detailDisbursementContent" class="detail-grid" style="margin-top: 20px;">
            <!-- Content will be injected by JS -->
        </div>
        <div class="modal-actions" style="margin-top: 30px;">
            <button type="button" class="btn-secondary" onclick="closeDetailDisbursement()" style="width: 100%;">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    if (typeof initDisbursement === 'function') {
        initDisbursement();
    }
</script>