<div class="dashboard">
    <div id="masterListSectionBpjs">
        {{-- HEADER --}}
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h2><i class="ph ph-folder-open"></i> Kelompok Pendapatan BPJS</h2>
                <p>Kelola data klaim BPJS berdasar kelompok/tanggal</p>
            </div>

            <div class="dashboard-header-right" style="display: flex; gap: 8px;">
                @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE') || auth()->user()->hasPermission('PENDAPATAN_BPJS_POST'))
                    <button class="btn-toolbar btn-toolbar-info" onclick="bulkPostMasterBpjs()"
                        style="height: 44px; padding: 0 16px;">
                        <i class="ph ph-check-square-offset"></i>
                        <span>Posting Masal</span>
                    </button>
                    <button class="btn-toolbar btn-toolbar-outline" onclick="bulkUnpostMasterBpjs()"
                        style="height: 44px; padding: 0 16px; border-color: #f59e0b; color: #d97706;">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span>Batal Posting Masal</span>
                    </button>
                    @if(auth()->user()->hasPermission('REVENUE_SYNC'))
                        <button class="btn-toolbar btn-toolbar-outline" onclick="syncOldData()"
                            style="height: 44px; padding: 0 16px; border-color: #3b82f6; color: #2563eb;">
                            <i class="ph ph-arrows-counter-clockwise"></i>
                            <span>Sinkronisasi Data Lama</span>
                        </button>
                    @endif
                    <button class="btn-tambah-data" onclick="openMasterFormBpjs()">
                        <i class="ph-bold ph-plus"></i>
                        <span>Buat Kelompok Baru</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- SUMMARY CARDS (MASTER) --}}
        <style>
            .dashboard {
                gap: 16px !important;
            }

            .pendapatan-summary-container {
                display: flex;
                justify-content: center;
                margin-bottom: 0px;
            }

            .pendapatan-summary-container .dashboard-cards {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                width: 100%;
                max-width: 850px;
                gap: 16px;
            }
        </style>
        <div class="pendapatan-summary-container">
            <div class="dashboard-cards">
                <div class="dash-card blue">
                    <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Rumah Sakit</span>
                        <h3 id="masterSummaryRsBpjs">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card purple">
                    <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Pelayanan</span>
                        <h3 id="masterSummaryPelayananBpjs">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card green">
                    <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Total Klaim BPJS</span>
                        <h3 id="masterSummaryTotalBpjs" style="color: #16a34a;">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT (MASTER) --}}
        <div class="dashboard-box">
            <div class="box-header">
                <div class="flex items-center gap-4" style="width: 100%;">
                    <div class="search-wrapper flex-1">
                        <div class="input-group" style="position: relative;">
                            <i class="ph ph-magnifying-glass"
                                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                            <input type="text" id="searchMasterBpjs"
                                placeholder="Cari tanggal, no bukti, atau keterangan..."
                                style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                        </div>
                    </div>
                </div>
            </div>

            <div id="selectionBannerBpjs"
                style="display:none; background: #eff6ff; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #bfdbfe; text-align: center; font-size: 13px; color: #1e40af;">
                Semua <span id="countCurrentPageBpjs">-</span> kelompok di halaman ini telah terpilih.
            </div>
            <div id="selectionAllBannerBpjs"
                style="display:none; background: #ecfdf5; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #a7f3d0; text-align: center; font-size: 13px; color: #065f46;">
                Semua <span id="countTotalDraftSelectedBpjs">-</span> kelompok <span
                    id="labelSelectionAllBpjs">Pendapatan
                    BPJS</span>
                telah terpilih lintas halaman.
                telah terpilih lintas halaman.
                <a href="javascript:void(0)" onclick="clearSelectionAcrossBpjs()"
                    style="font-weight: 700; color: #059669; text-decoration: underline;">Batalkan pilihan</a>
            </div>

            <div class="table-container">
                <table id="masterTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">
                                <input type="checkbox" id="checkAllMasterBpjs" onclick="toggleAllMasterBpjs(this)" />
                            </th>
                            <th class="text-center" style="width: 140px;">Tanggal PDPT/RK</th>
                            <th class="text-center">Keterangan / No. Bukti</th>
                            <th class="text-right" style="width: 180px;">Jasa RS</th>
                            <th class="text-right" style="width: 180px;">Jasa Pelayanan</th>
                            <th class="text-right" style="width: 180px;">Total (Rp)</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBodyBpjs">
                        <tr>
                            <td colspan="8" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-2">
                <p id="paginationInfoMasterBpjs" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                    data
                </p>
                <div class="flex items-center gap-2">
                    <button id="prevPageMasterBpjs" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                    <span id="pageInfoMasterBpjs" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1
                        /
                        1</span>
                    <button id="nextPageMasterBpjs" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL SECTION --}}
    <div id="detailListSectionBpjs" style="display: none; animation: fadeIn 0.3s ease-out;">
        <div class="dashboard-header" style="margin-bottom: 24px;">
            <div class="dashboard-header-left">
                <button onclick="closeDetailBpjs()"
                    style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar Kelompok
                </button>
                <h2><i class="ph ph-list-numbers"></i> Rincian Klaim BPJS</h2>
                <p style="font-size: 14px; color: #64748b;">Grup: <span id="detailMasterInfoBpjs"
                        style="font-weight: 700; color: #1e293b;">-</span></p>
            </div>

            <div class="dashboard-header-right">
                <div class="bpjs-tab-group"
                    style="display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 10px; margin-bottom: 12px;">
                    <button class="bpjs-tab active" onclick="switchBpjsTab('REGULAR', this)" id="tabRegular"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;">
                        <i class="ph ph-clipboard-text"></i> Regular
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('EVAKUASI', this)" id="tabEvakuasi"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;">
                        <i class="ph ph-ambulance"></i> Evakuasi
                    </button>
                    <button class="bpjs-tab" onclick="switchBpjsTab('OBAT', this)" id="tabObat"
                        style="padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;">
                        <i class="ph ph-pill"></i> Obat
                    </button>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <div class="toolbar-group" style="display: flex; gap: 8px;">
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE'))
                            <a href="/dashboard/pendapatan/bpjs/template" class="btn-toolbar btn-toolbar-outline"
                                title="Download Template CSV"><i class="ph ph-download-simple"></i><span>Template</span></a>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE'))
                            <button class="btn-toolbar btn-toolbar-outline" id="btnImportBpjs"
                                title="Import Data dari CSV"><i class="ph ph-file-arrow-up"></i><span>Import</span></button>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_DELETE'))
                            <button class="btn-toolbar btn-toolbar-outline btn-bulk-delete" id="btnBulkDeleteBpjs"
                                title="Hapus Massal Rincian Pasien" style="color: #ef4444; border-color: #fca5a5;">
                                <i class="ph ph-trash"></i><span>Hapus Massal</span>
                            </button>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('PENDAPATAN_BPJS_CREATE'))
                        <button class="btn-tambah-data" id="btnTambahPendapatanBpjs"
                            style="background:#059669; height: 44px;">
                            <i class="ph-bold ph-plus"></i>
                            <span>Tambah Pasien</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- SUMMARY CARDS (DETAIL) --}}
        <div class="pendapatan-summary-container">
            <div class="dashboard-cards">
                <div class="dash-card blue">
                    <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Rumah Sakit</span>
                        <h3 id="detailSummaryRsBpjs">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card purple">
                    <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Pelayanan</span>
                        <h3 id="detailSummaryPelayananBpjs">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card green">
                    <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Total Klaim BPJS</span>
                        <h3 id="detailSummaryTotalBpjs" style="color: #16a34a;">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-box" style="padding: 0; overflow: hidden;">
            <div class="box-header" style="padding: 16px; border-bottom: 1px solid #f1f5f9;">
                <div class="input-group" style="position: relative;">
                    <i class="ph ph-magnifying-glass"
                        style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                    <input type="text" id="searchPendapatanBpjs" placeholder="Cari nama pasien, No SEP, ruangan..."
                        style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                </div>
            </div>
            <div class="table-container" style="margin-top: 0; border-radius: 0; border: none;">
                <style>
                    #pendapatanBpjsTable th,
                    #pendapatanBpjsTable td {
                        font-size: 12px !important;
                        white-space: nowrap !important;
                    }

                    .nominal-group {
                        display: flex;
                        flex-direction: column;
                        align-items: stretch;
                        gap: 2px;
                        width: 100%;
                    }

                    .nom-row {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        width: 100%;
                    }

                    .nom-label {
                        font-size: 8px;
                        font-weight: 700;
                        padding: 1px 4px;
                        border-radius: 3px;
                        text-transform: uppercase;
                        width: 35px;
                        text-align: center;
                        flex-shrink: 0;
                    }

                    .nom-val {
                        font-family: 'JetBrains Mono', monospace;
                        flex-grow: 1;
                        font-size: 11px;
                    }

                    .label-rs {
                        background: #eff6ff;
                        color: #2563eb;
                    }

                    .label-pelayanan {
                        background: #fdf4ff;
                        color: #a21caf;
                    }

                    .label-total {
                        background: #ecfdf5;
                        color: #059669;
                    }

                    .val-rs {
                        font-weight: 600;
                        color: #2563eb;
                    }

                    .val-pelayanan {
                        font-weight: 600;
                        color: #a21caf;
                    }

                    .val-total {
                        font-weight: 800;
                        color: #059669;
                    }
                </style>
                <table id="pendapatanBpjsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th class="text-center" style="width: 110px;">Tanggal</th>
                            <th id="thNoSep" class="text-center" style="width: 150px;">No SEP</th>
                            <th class="text-center">Nama Pasien</th>
                            <th class="text-center">Ruangan</th>
                            <th class="text-right" style="width: 200px;">RS / Pelayanan / Total</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pendapatanBpjsBody">
                        <tr>
                            <td colspan="7" class="text-center">Memuat rincian...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center" style="padding: 16px;">
                <p id="paginationInfoBpjs" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0 data
                </p>
                <div class="flex items-center gap-2">
                    <button id="prevPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                    <span id="pageInfoBpjs" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1 /
                        1</span>
                    <button id="nextPageBpjs" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MASTER FORM --}}
    <div id="modalMasterFormBpjs" class="confirm-overlay">
        <div class="confirm-box" style="max-width: 500px;">
            <h3 id="masterFormTitleBpjs"><i class="ph ph-folder-plus"></i> Tambah Kelompok BPJS</h3>
            <form id="formMasterBpjs" autocomplete="off">
                <input type="hidden" id="masterIdBpjs">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Tanggal Pendapatan</label>
                    <input type="date" id="masterTanggalBpjs" required class="form-input">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Tanggal Rekening Koran (Opsional)</label>
                    <input type="date" id="masterTanggalRkBpjs" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>No. Bukti (Opsional)</label>
                    <input type="text" id="masterNoBuktiBpjs" class="form-input"
                        placeholder="Masukkan nomor bukti jika ada">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Keterangan / Uraian</label>
                    <textarea id="masterKeteranganBpjs" class="form-input" rows="3"
                        placeholder="Contoh: Klaim BPJS Bulan Januari"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeMasterModalBpjs()">Batal</button>
                    <button type="submit" class="btn-primary" id="btnSimpanMasterBpjs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODALS DETAIL --}}
    @include('dashboard.partials.pendapatan-bpjs-detail')
    @include('dashboard.partials.pendapatan-bpjs-import')
</div>

<style>
    .bpjs-tab.active {
        background: #fff !important;
        color: #2563eb !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .bpjs-tab:not(.active) {
        background: transparent;
        color: #64748b;
    }

    .bpjs-tab:not(.active):hover {
        background: rgba(255, 255, 255, 0.5);
        color: #1e293b;
    }
</style>