<div class="page-container">
    <div id="masterListSectionJaminan">
        {{-- HEADER --}}
        <div class="page-header">
            <div class="page-header-left">
                <h2><i class="ph ph-folder-open"></i> Kelompok Pendapatan Jaminan</h2>
                <p>Kelola data pendapatan Jaminan/Asuransi berdasar kelompok</p>
            </div>

            <div class="page-header-right">
                @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CRUD') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_POST'))
                    <button class="btn-toolbar btn-toolbar-info" onclick="bulkPostMasterJaminan()">
                        <i class="ph ph-check-square-offset"></i>
                        <span>Posting Masal</span>
                    </button>
                    <button class="btn-toolbar btn-toolbar-outline" onclick="bulkUnpostMasterJaminan()"
                        style="border-color: #f59e0b; color: #d97706;">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span>Batal Posting Masal</span>
                    </button>
                    @if(auth()->user()->hasPermission('REVENUE_SYNC'))
                        <button class="btn-toolbar btn-toolbar-outline" onclick="syncOldData()"
                            style="border-color: #3b82f6; color: #2563eb;">
                            <i class="ph ph-arrows-counter-clockwise"></i>
                            <span>Sinkronisasi</span>
                        </button>
                    @endif
                    <button class="btn-tambah-data" onclick="openMasterFormJaminan()">
                        <i class="ph-bold ph-plus"></i>
                        <span>Kelompok Baru</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- SUMMARY CARDS (MASTER) --}}
        <div class="grid-responsive grid-3 mb-4" style="max-width: 850px; margin-inline: auto;">
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

        {{-- MAIN CONTENT (MASTER) --}}
        <div class="dashboard-box">
            <div class="table-toolbar">
                <div class="table-search-wrapper">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" id="searchMasterJaminan" class="table-search"
                        placeholder="Cari tanggal, no bukti, atau keterangan..." data-table="masterTable">
                </div>
                <div class="filter-wrapper">
                    <select id="filterStatusMasterJaminan" class="filter-select" style="width:220px;">
                        <option value="">Semua Status</option>
                        <option value="DRAFT">📑 Draft</option>
                        <option value="POSTED">✅ Diposting</option>
                    </select>
                </div>
                <div class="filter-wrapper">
                    <input type="month" id="filterMonthMasterJaminan" class="filter-select" style="max-width: 160px;">
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
                <table class="table universal-table table-mobile-cards" id="masterTable">
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="checkAllMasterJaminan"
                                    onclick="toggleAllMasterJaminan(this)" />
                            </th>
                            <th class="text-center sortable" onclick="sortMasterJaminan('tanggal')" data-sort="tanggal">
                                Tanggal PDPT/RK <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortMasterJaminan('keterangan')"
                                data-sort="keterangan">Keterangan / No. Bukti <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterJaminan('total_rs')"
                                data-sort="total_rs">Jasa RS <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterJaminan('total_pelayanan')"
                                data-sort="total_pelayanan">Jasa Pelayanan <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortMasterJaminan('total_all')"
                                data-sort="total_all">Total (Rp) <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortMasterJaminan('is_posted')"
                                data-sort="is_posted">Status <i class="ph ph-caret-up-down"></i></th>
                            <th class="action-col">Aksi</th>
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
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CRUD'))
                            <a href="/dashboard/pendapatan/jaminan/template" class="btn-toolbar btn-toolbar-outline"
                                title="Download Template CSV"><i class="ph ph-download-simple"></i><span>Template</span></a>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CRUD'))
                            <button class="btn-toolbar btn-toolbar-outline" id="btnImportJaminan"
                                title="Import Data dari CSV"><i class="ph ph-file-arrow-up"></i><span>Import</span></button>
                        @endif
                        @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_DELETE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CRUD'))
                            <button class="btn-toolbar btn-toolbar-outline btn-bulk-delete" id="btnBulkDeleteJaminan"
                                title="Hapus massal rincian" style="color: #ef4444; border-color: #fca5a5;">
                                <i class="ph ph-trash"></i><span>Hapus Massal</span>
                            </button>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CREATE') || auth()->user()->hasPermission('PENDAPATAN_JAMINAN_CRUD'))
                        <button class="btn-tambah-data" id="btnTambahPendapatanJaminan"
                            style="background:#059669; height: 44px;" onclick="openPendapatanJaminanModal()">
                            <i class="ph-bold ph-plus"></i>
                            <span>Tambah Data</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- SUMMARY CARDS (DETAIL) --}}
        <div class="grid-responsive grid-3 mb-4" style="max-width: 850px; margin-inline: auto;">
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

        <div class="dashboard-box" style="padding: 0; overflow: hidden;">
            <div class="table-toolbar" style="padding: 16px; border-bottom: 1px solid #f1f5f9;">
                <div class="table-search-wrapper" style="flex: 1;">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" id="searchPendapatanJaminan" class="table-search"
                        placeholder="Cari nama pasien, perusahaan Mitra, ruangan..."
                        data-table="pendapatanJaminanTable">
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
                <table id="pendapatanJaminanTable" class="table universal-table table-mobile-cards">
                    <thead>
                        <tr>
                            <th class="text-center checkbox-col">No</th>
                            <th class="text-center sortable" onclick="sortJaminan('tanggal')" data-sort="tanggal">
                                Tanggal <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortJaminan('nama_pasien')"
                                data-sort="nama_pasien">Nama Pasien <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortJaminan('perusahaan_id')"
                                data-sort="perusahaan_id">Perusahaan <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-center sortable" onclick="sortJaminan('ruangan_id')" data-sort="ruangan_id">
                                Ruangan <i class="ph ph-caret-up-down"></i></th>
                            <th class="text-right sortable" onclick="sortJaminan('total')" data-sort="total">RS /
                                Pelayanan / Total <i class="ph ph-caret-up-down"></i></th>
                            <th class="action-col">Aksi</th>
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

</div>