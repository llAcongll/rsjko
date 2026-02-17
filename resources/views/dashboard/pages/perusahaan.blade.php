<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-buildings"></i> Manajemen Perusahaan</h2>
            <p>Kelola daftar Master Perusahaan rekanan/jaminan</p>
        </div>

        @if(auth()->user()->hasPermission('MASTER_CRUD'))
            <div class="dashboard-header-right">
                <button class="btn-tambah-data" onclick="openPerusahaanForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Perusahaan</span>
                </button>
            </div>
        @endif
    </div>

    {{-- SEARCH BOX --}}
    <div class="dashboard-box mb-4">
        <div class="search-wrapper" style="position: relative; max-width: 420px;">
            <i class="ph ph-magnifying-glass"
                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
            <input type="text" id="perusahaanSearch" placeholder="Cari kode atau nama perusahaan..." autocomplete="off"
                style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
        </div>
    </div>

    {{-- TABLE BOX --}}
    <div class="dashboard-box">
        <div class="table-container">
            <table id="perusahaanTable" class="users-table">
                <thead>
                    <tr>
                        <th data-sort="number" style="width: 60px;" class="text-center">No</th>
                        <th data-sort="string" style="width: 140px;">Kode</th>
                        <th data-sort="string">Nama Perusahaan</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
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