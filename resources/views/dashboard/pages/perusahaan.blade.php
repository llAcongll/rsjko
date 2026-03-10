<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-buildings"></i> Manajemen Perusahaan</h2>
            <p>Kelola daftar Master Perusahaan rekanan/jaminan</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_MANAGE'))
            <div class="page-header-right">
                <button class="btn-tambah-data" onclick="openPerusahaanForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Perusahaan</span>
                </button>
            </div>
        @endif
    </div>

    {{-- TABLE BOX --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="perusahaanSearch" class="table-search"
                    placeholder="Cari kode atau nama perusahaan...">
            </div>
        </div>

        <div class="table-container">
            <table id="perusahaanTable" class="table universal-table table-mobile-cards">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col">No</th>
                        <th class="text-center sortable">Kode</th>
                        <th class="text-center sortable">Nama Perusahaan</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="perusahaanInfo" class="text-slate-500" style="font-size: 13px;"></p>

            <div class="flex items-center gap-2">
                <button id="prevPagePerusahaan" class="btn-aksi">
                    <i class="ph ph-caret-left"></i>
                </button>
                <span id="pageInfoPerusahaan" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;"></span>
                <button id="nextPagePerusahaan" class="btn-aksi">
                    <i class="ph ph-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

</div>





