<div class="dashboard">
    <div id="masterListSectionJaminan">
        {{-- HEADER --}}
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h2><i class="ph ph-folder-open"></i> Kelompok Pendapatan Jaminan</h2>
                <p>Kelola data pendapatan Jaminan/Asuransi berdasar kelompok</p>
            </div>

            <div class="dashboard-header-right" style="display: flex; gap: 8px;">
                @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_POST'))
                    <button class="btn-toolbar btn-toolbar-info" onclick="bulkPostMasterJaminan()"
                        style="height: 44px; padding: 0 16px;">
                        <i class="ph ph-check-square-offset"></i>
                        <span>Posting Masal</span>
                    </button>
                    <button class="btn-toolbar btn-toolbar-outline" onclick="bulkUnpostMasterJaminan()"
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
                    <button class="btn-tambah-data" onclick="openMasterFormJaminan()">
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

            th.sortable i {
                margin-left: 4px;
                font-size: 14px;
                vertical-align: middle;
                transition: color 0.2s;
            }

            th.sortable:hover i {
                color: #64748b !important;
            }
        </style>
        <div class="pendapatan-summary-container">
            <div class="dashboard-cards">
                <div class="dash-card blue">
                    <div class="dash-card-icon"><i class="ph ph-hospital"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Rumah Sakit</span>
                        <h3 id="masterSummaryRsJaminan">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card purple">
                    <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Pelayanan</span>
                        <h3 id="masterSummaryPelayananJaminan">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card green">
                    <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Total Jaminan</span>
                        <h3 id="masterSummaryTotalJaminan" style="color: #16a34a;">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT (MASTER) --}}
        <div class="dashboard-box">
            <div class="box-header">
                <div class="flex items-center gap-3" style="width: 100%;">
                    <div class="search-wrapper flex-1">
                        <div class="input-group" style="position: relative;">
                            <i class="ph ph-magnifying-glass"
                                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                            <input type="text" id="searchMasterJaminan"
                                placeholder="Cari tanggal, no bukti, atau keterangan..."
                                style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                        </div>
                    </div>
                    <div class="filter-wrapper">
                        <select id="filterStatusMasterJaminan"
                            style="height: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px; padding: 0 16px; background: #fff; color: #475569; font-weight: 600; cursor: pointer; outline: none; transition: all 0.2s;">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">📑 Draft</option>
                            <option value="POSTED">✅ Diposting</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="selectionBannerJaminan"
                style="display:none; background: #eff6ff; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #bfdbfe; text-align: center; font-size: 13px; color: #1e40af;">
                Semua <span id="countCurrentPageJaminan">-</span> kelompok di halaman ini telah terpilih.
            </div>
            <div id="selectionAllBannerJaminan"
                style="display:none; background: #ecfdf5; padding: 12px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #a7f3d0; text-align: center; font-size: 13px; color: #065f46;">
                Semua <span id="countTotalDraftSelectedJaminan">-</span> kelompok <span
                    id="labelSelectionAllJaminan">Pendapatan
                    Jaminan</span>
                telah terpilih lintas halaman.
                <a href="javascript:void(0)" onclick="clearSelectionAcrossJaminan()"
                    style="font-weight: 700; color: #059669; text-decoration: underline;">Batalkan pilihan</a>
            </div>

            <div class="table-container">
                <table id="masterTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">
                                <input type="checkbox" id="checkAllMasterJaminan"
                                    onclick="toggleAllMasterJaminan(this)" />
                            </th>
                            <th class="text-center sortable" data-sort="tanggal" onclick="sortMasterJaminan('tanggal')"
                                style="width: 140px; cursor: pointer;">
                                Tanggal PDPT/RK <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center sortable" data-sort="keterangan"
                                onclick="sortMasterJaminan('keterangan')" style="cursor: pointer;">
                                Keterangan / No. Bukti <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-right sortable" data-sort="total_rs" onclick="sortMasterJaminan('total_rs')"
                                style="width: 180px; cursor: pointer;">
                                Jasa RS <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-right sortable" data-sort="total_pelayanan"
                                onclick="sortMasterJaminan('total_pelayanan')" style="width: 180px; cursor: pointer;">
                                Jasa Pelayanan <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-right sortable" data-sort="total_all"
                                onclick="sortMasterJaminan('total_all')" style="width: 180px; cursor: pointer;">
                                Total (Rp) <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center sortable" data-sort="is_posted"
                                onclick="sortMasterJaminan('is_posted')" style="width: 120px; cursor: pointer;">
                                Status <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="masterTableBodyJaminan">
                        <tr>
                            <td colspan="8" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-2">
                <p id="paginationInfoMasterJaminan" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari
                    0 data
                </p>
                <div class="flex items-center gap-2">
                    <button id="prevPageMasterJaminan" class="btn-aksi" disabled><i
                            class="ph ph-caret-left"></i></button>
                    <span id="pageInfoMasterJaminan" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1
                        /
                        1</span>
                    <button id="nextPageMasterJaminan" class="btn-aksi" disabled><i
                            class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL SECTION --}}
    <div id="detailListSectionJaminan" style="display: none; animation: fadeIn 0.3s ease-out;">
        <div class="dashboard-header" style="margin-bottom: 24px;">
            <div class="dashboard-header-left">
                <button onclick="closeDetailJaminan()"
                    style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar Kelompok
                </button>
                <h2><i class="ph ph-list-numbers"></i> Rincian Pendapatan Jaminan</h2>
                <p style="font-size: 14px; color: #64748b;">Grup: <span id="detailMasterInfoJaminan"
                        style="font-weight: 700; color: #1e293b;">-</span></p>
            </div>

            <div class="dashboard-header-right">
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <div class="toolbar-group" style="display: flex; gap: 8px;">
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE'))
                            <a href="/dashboard/pendapatan/jaminan/template" class="btn-toolbar btn-toolbar-outline"
                                title="Download Template CSV"><i class="ph ph-download-simple"></i><span>Template</span></a>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE'))
                            <button class="btn-toolbar btn-toolbar-outline" id="btnImportJaminan"
                                title="Import Data dari CSV"><i class="ph ph-file-arrow-up"></i><span>Import</span></button>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_DELETE'))
                            <button class="btn-toolbar btn-toolbar-outline btn-bulk-delete" id="btnBulkDeleteJaminan"
                                title="Hapus massal rincian" style="color: #ef4444; border-color: #fca5a5;">
                                <i class="ph ph-trash"></i><span>Hapus Massal</span>
                            </button>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE'))
                        <button class="btn-tambah-data" id="btnTambahPendapatanJaminan"
                            style="background:#059669; height: 44px;">
                            <i class="ph-bold ph-plus"></i>
                            <span>Tambah Data</span>
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
                        <h3 id="detailSummaryRsJaminan">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card purple">
                    <div class="dash-card-icon"><i class="ph ph-user-gear"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Jasa Pelayanan</span>
                        <h3 id="detailSummaryPelayananJaminan">Rp 0</h3>
                    </div>
                </div>
                <div class="dash-card green">
                    <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                    <div class="dash-card-content">
                        <span class="label">Total Jaminan</span>
                        <h3 id="detailSummaryTotalJaminan" style="color: #16a34a;">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-box" style="padding: 0; overflow: hidden;">
            <div class="box-header" style="padding: 16px; border-bottom: 1px solid #f1f5f9;">
                <div class="input-group" style="position: relative;">
                    <i class="ph ph-magnifying-glass"
                        style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                    <input type="text" id="searchPendapatanJaminan"
                        placeholder="Cari nama pasien, perusahaan Mitra, ruangan..."
                        style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                </div>
            </div>
            <div class="table-container" style="margin-top: 0; border-radius: 0; border: none;">
                <style>
                    #pendapatanJaminanTable th,
                    #pendapatanJaminanTable td {
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
                <table id="pendapatanJaminanTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th class="text-center sortable" data-sort="tanggal" onclick="sortJaminan('tanggal')"
                                style="width: 110px; cursor: pointer;">
                                Tanggal <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center sortable" data-sort="nama_pasien"
                                onclick="sortJaminan('nama_pasien')" style="cursor: pointer;">
                                Nama Pasien <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center sortable" data-sort="perusahaan" onclick="sortJaminan('perusahaan')"
                                style="cursor: pointer;">
                                Perusahaan <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center sortable" data-sort="ruangan" onclick="sortJaminan('ruangan')"
                                style="cursor: pointer;">
                                Ruangan <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-right sortable" data-sort="total" onclick="sortJaminan('total')"
                                style="width: 200px; cursor: pointer;">
                                RS / Pelayanan / Total <i class="ph ph-caret-up-down text-slate-400"></i>
                            </th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pendapatanJaminanBody">
                        <tr>
                            <td colspan="7" class="text-center">Memuat rincian...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center" style="padding: 16px;">
                <p id="paginationInfoJaminan" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                    data</p>
                <div class="flex items-center gap-2">
                    <button id="prevPageJaminan" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                    <span id="pageInfoJaminan" class="font-medium"
                        style="font-size: 14px; min-width: 100px; text-align: center;">1 /
                        1</span>
                    <button id="nextPageJaminan" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MASTER FORM --}}
    <div id="modalMasterFormJaminan" class="confirm-overlay">
        <div class="confirm-box" style="max-width: 500px;">
            <h3 id="masterFormTitleJaminan"><i class="ph ph-folder-plus"></i> Tambah Kelompok Jaminan</h3>
            <form id="formMasterJaminan" autocomplete="off">
                <input type="hidden" id="masterIdJaminan">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Tanggal Pendapatan</label>
                    <input type="date" id="masterTanggalJaminan" required class="form-input">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>Tanggal Rekening Koran (Opsional)</label>
                    <input type="date" id="masterTanggalRkJaminan" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>No. Bukti (Opsional)</label>
                    <input type="text" id="masterNoBuktiJaminan" class="form-input"
                        placeholder="Masukkan nomor bukti jika ada">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Keterangan / Uraian</label>
                    <textarea id="masterKeteranganJaminan" class="form-input" rows="3"
                        placeholder="Contoh: Pendapatan Jaminan Bulan Januari"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeMasterModalJaminan()">Batal</button>
                    <button type="submit" class="btn-primary" id="btnSimpanMasterJaminan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODALS DETAIL --}}
    @include('dashboard.partials.pendapatan-jaminan-detail')
    @include('dashboard.partials.pendapatan-jaminan-import')
</div>