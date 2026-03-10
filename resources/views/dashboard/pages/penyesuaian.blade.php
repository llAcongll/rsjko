<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-hand-coins"></i> Pelunasan & Potongan</h2>
            <p>Kelola uang masuk, potongan tagihan, dan administrasi bank</p>
        </div>

        <div class="page-header-right">
            @if(auth()->user()->hasPermission('PENYESUAIAN_MANAGE'))
                <button class="btn-tambah-data" id="btnTambahPenyesuaian">
                    <i class="ph-bold ph-plus"></i>
                    <span>Penyesuaian</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="pendapatan-summary-container">
        <div class="dashboard-cards" style="grid-template-columns: repeat(3, 1fr); max-width: 900px; width: 100%;">
            <div class="dash-card green">
                <div class="dash-card-icon">
                    <i class="ph ph-coins"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Pelunasan Tunai</span>
                    <h3 id="summaryTotalPelunasanPenyesuaian">Rp 0</h3>
                </div>
            </div>

            <div class="dash-card indigo">
                <div class="dash-card-icon">
                    <i class="ph ph-scissors"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Potongan</span>
                    <h3 id="summaryTotalPotonganPenyesuaian">Rp 0</h3>
                </div>
            </div>

            <div class="dash-card orange">
                <div class="dash-card-icon">
                    <i class="ph ph-bank"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Biaya Admin</span>
                    <h3 id="summaryTotalAdmPenyesuaian">Rp 0</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchPenyesuaian" class="table-search"
                    placeholder="Cari keterangan atau perusahaan...">
            </div>

            <div class="filter-wrapper">
                <select id="filterKategoriPenyesuaian" class="form-input"
                    style="width: 180px; margin-bottom: 0; height: 48px; border-radius: 12px;">
                    <option value="">Semua Kategori</option>
                    <option value="BPJS">BPJS</option>
                    <option value="JAMINAN">JAMINAN</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table id="penyesuaianTable" class="table universal-table">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col">No</th>
                        <th class="text-center sortable">Tanggal</th>
                        <th class="text-center sortable">Keterangan</th>
                        <th class="text-center sortable">Tahun</th>
                        <th class="text-right sortable">Pelunasan</th>
                        <th class="text-right sortable">Potongan</th>
                        <th class="text-right sortable">Adm Bank</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="penyesuaianBody">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="ph ph-tray" style="font-size: 32px; margin-bottom: 8px;"></i>
                            <p>Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="paginationInfoPenyesuaian" class="text-slate-500" style="font-size: 13px;">Menampilkan 0-0 dari 0
                data</p>

            <div class="flex items-center gap-2">
                <button id="prevPagePenyesuaian" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoPenyesuaian" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPagePenyesuaian" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>
</div>





