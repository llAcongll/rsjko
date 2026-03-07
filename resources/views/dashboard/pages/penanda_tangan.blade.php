<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-signature"></i> Penanda Tangan Laporan</h2>
            <p>Kelola daftar Penanda Tangan untuk laporan operasional</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_CRUD') || auth()->user()->isAdmin())
            <div class="page-header-right">
                <button class="btn-tambah-data" onclick="openPenandaTanganForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Penanda Tangan</span>
                </button>
            </div>
        @endif
    </div>

    {{-- TABLE BOX --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="penandaTanganSearch" class="table-search"
                    placeholder="Cari jabatan, nama, atau NIP...">
            </div>
        </div>

        <div class="table-container">
            <table id="penandaTanganTable" class="table universal-table table-mobile-cards">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col">No</th>
                        <th class="text-center sortable">Jabatan</th>
                        <th class="text-center sortable">Pangkat/Gol</th>
                        <th class="text-center sortable">Nama</th>
                        <th class="text-center sortable">NIP</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="penandaTanganInfo" class="text-slate-500" style="font-size: 13px;"></p>

            <div class="flex items-center gap-2">
                <button id="prevPageBtn" class="btn-aksi">
                    <i class="ph ph-caret-left"></i>
                </button>
                <span id="pageInfoText" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;"></span>
                <button id="nextPageBtn" class="btn-aksi">
                    <i class="ph ph-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

</div>