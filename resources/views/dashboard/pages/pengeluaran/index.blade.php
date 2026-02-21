@php
    $title = 'Pengeluaran';
    if ($param == 'PEGAWAI')
        $title = 'Pengeluaran Pegawai';
    elseif ($param == 'BARANG_JASA')
        $title = 'Pengeluaran Barang dan Jasa';
    elseif ($param == 'MODAL')
        $title = 'Pengeluaran Modal & Aset';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-hand-coins"></i> {{ $title }}</h2>
            <p>Kelola data transaksi {{ strtolower($title) }}</p>
        </div>

        <div class="dashboard-header-right">
            @if(auth()->user()->hasPermission('PENGELUARAN_CREATE') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openPengeluaranForm('{{ $param }}')">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Data</span>
                </button>
            @endif
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <style>
        /* Tighten page layout */
        .dashboard {
            gap: 16px !important;
        }

        .dashboard-cards.grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            /* Tighter gap */
        }

        .dashboard-cards.grid-3 .dash-card {
            background: #fff;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
            display: block;
        }

        .dashboard-cards.grid-3 .dash-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
            border-color: #e2e8f0;
        }

        .dashboard-cards.grid-3 .dash-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .dashboard-cards.grid-3 .dash-card-content .label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: block;
            margin-bottom: 2px;
        }

        .dashboard-cards.grid-3 .dash-card-content h3 {
            font-size: 16px;
            /* Similar to pendapatan but scaled for compact */
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.02em;
            line-height: normal;
        }

        .dashboard-cards.grid-3 .dash-card-content small,
        .dashboard-cards.grid-3 .dash-card-content p {
            margin-top: 2px;
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            display: block;
            line-height: normal;
        }

        /* Colors */
        .dash-card.blue .dash-card-icon {
            background: #eff6ff;
            color: #2563eb;
        }

        .dash-card.red .dash-card-icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .dash-card.indigo .dash-card-icon {
            background: #eef2ff;
            color: #4f46e5;
        }

        .dash-card.purple .dash-card-icon {
            background: #faf5ff;
            color: #7c3aed;
        }

        .dash-card.orange .dash-card-icon {
            background: #fff7ed;
            color: #ea580c;
        }

        .dash-card.green .dash-card-icon {
            background: #f0fdf4;
            color: #16a34a;
        }

        @media (max-width: 1024px) {
            .dashboard-cards.grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .dashboard-cards.grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="dashboard-cards-container" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 0px;">
        <div class="dashboard-cards grid-3">
            <div class="dash-card blue">
                <div class="dash-card-icon">
                    <i class="ph ph-bank"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Belanja</span>
                    <h3 id="totalNominalPengeluaran">Rp 0</h3>
                    <small id="totalCountPengeluaran">0 Transaksi</small>
                </div>
            </div>

            <div class="dash-card red">
                <div class="dash-card-icon">
                    <i class="ph ph-receipt"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Pajak</span>
                    <h3 id="totalPajakPengeluaran">Rp 0</h3>
                    <p>Potongan pajak</p>
                </div>
            </div>

            <div class="dash-card indigo">
                <div class="dash-card-icon">
                    <i class="ph ph-check-circle"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Total Dibayarkan</span>
                    <h3 id="totalDibayarkanPengeluaran">Rp 0</h3>
                    <p>Bersih dibayarkan</p>
                </div>
            </div>
        </div>

        <div class="dashboard-cards grid-3">
            <div class="dash-card purple">
                <div class="dash-card-icon">
                    <i class="ph ph-wallet"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Uang Persediaan</span>
                    <h3 id="totalUP">Rp 0</h3>
                    <small id="countUP">0 Transaksi</small>
                </div>
            </div>

            <div class="dash-card orange">
                <div class="dash-card-icon">
                    <i class="ph ph-arrows-counter-clockwise"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Ganti Uang</span>
                    <h3 id="totalGU">Rp 0</h3>
                    <small id="countGU">0 Transaksi</small>
                </div>
            </div>

            <div class="dash-card green">
                <div class="dash-card-icon">
                    <i class="ph ph-lightning"></i>
                </div>
                <div class="dash-card-content">
                    <span class="label">Langsung</span>
                    <h3 id="totalLS">Rp 0</h3>
                    <small id="countLS">0 Transaksi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header">
            <div class="flex items-center gap-4" style="width: 100%;">

                <div class="search-wrapper flex-1" style="display: flex; flex-direction: column; gap: 4px;">
                    <label
                        style="font-size: 11px; font-weight: 600; color: #64748b; margin-left: 4px;">Pencarian</label>
                    <div class="input-group" style="position: relative;">
                        <i class="ph ph-magnifying-glass"
                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                        <input type="text" id="searchPengeluaran"
                            placeholder="Cari uraian, kode, atau nomor administrasi..."
                            onkeyup="handleSearchPengeluaran(event)"
                            style="width: 100%; height: 48px; padding-left: 48px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <style>
                #tablePengeluaran th,
                #tablePengeluaran td {
                    font-size: 11px !important;
                    white-space: nowrap !important;
                }

                .nominal-group {
                    display: flex;
                    flex-direction: column;
                    align-items: stretch;
                    gap: 4px;
                    width: 100%;
                    min-width: 220px;
                }

                .nom-row {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    width: 100%;
                }

                .nom-label {
                    font-size: 8px;
                    font-weight: 700;
                    padding: 2px 0;
                    border-radius: 3px;
                    text-transform: uppercase;
                    width: 45px;
                    text-align: center;
                    flex-shrink: 0;
                }

                .nom-val {
                    font-family: 'JetBrains Mono', monospace;
                    flex-grow: 1;
                }

                .label-bruto {
                    background: #f1f5f9;
                    color: #475569;
                }

                .label-pajak {
                    background: #fef2f2;
                    color: #dc2626;
                }

                .label-netto {
                    background: #ecfdf5;
                    color: #059669;
                }

                .val-bruto {
                    font-weight: 600;
                    color: #1e293b;
                }

                .val-pajak {
                    font-weight: 600;
                    color: #ef4444;
                }

                .val-netto {
                    font-weight: 800;
                    color: #059669;
                    font-size: 12px;
                }
            </style>
            <table id="tablePengeluaran">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="100" class="text-center">Tanggal</th>
                        <th width="280" class="text-center">Administrasi</th>
                        <th class="class=" text-center"">Uraian</th>
                        <th width="240" class="text-right">Rincian Nominal (Bruto/Pajak/Netto)</th>
                        <th width="80" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tablePengeluaranBody">
                    <tr>
                        <td colspan="6" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <p id="paginationInfoPengeluaran" class="text-slate-500" style="font-size: 13px;">Menampilkan 0–0 dari 0
                data</p>

            <div class="flex items-center gap-2">
                <button id="prevPagePengeluaran" class="btn-aksi" disabled><i class="ph ph-caret-left"></i></button>
                <span id="pageInfoPengeluaran" class="font-medium"
                    style="font-size: 14px; min-width: 100px; text-align: center;">1 / 1</span>
                <button id="nextPagePengeluaran" class="btn-aksi" disabled><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize when view is loaded
    if (typeof initPengeluaran === 'function') {
        initPengeluaran('{{ $param }}');
    }
</script>