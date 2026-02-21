<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-signature"></i> Penanda Tangan Laporan</h2>
            <p>Kelola daftar Penanda Tangan untuk laporan operasional</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_CRUD') || auth()->user()->isAdmin())
            <div class="dashboard-header-right">
                <button class="btn-tambah-data" onclick="openPenandaTanganForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Penanda Tangan</span>
                </button>
            </div>
        @endif
    </div>

    {{-- SEARCH BOX --}}
    <div class="dashboard-box mb-4">
        <div class="search-wrapper" style="position: relative; max-width: 420px;">
            <i class="ph ph-magnifying-glass"
                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
            <input type="text" id="penandaTanganSearch" placeholder="Cari jabatan, nama, atau NIP..." autocomplete="off"
                style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
        </div>
    </div>

    {{-- TABLE BOX --}}
    <div class="dashboard-box">
        <div class="table-container">
            <table id="penandaTanganTable" class="users-table">
                <thead>
                    <tr>
                        <th data-sort="number" style="width: 60px;" class="text-center">No</th>
                        <th data-sort="string" class="text-center">Jabatan</th>
                        <th data-sort="string" class="text-center">Pangkat/Gol</th>
                        <th data-sort="string" class="text-center">Nama</th>
                        <th data-sort="string" class="text-center">NIP</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
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