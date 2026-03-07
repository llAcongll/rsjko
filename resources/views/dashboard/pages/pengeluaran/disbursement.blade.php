@php
    $title = 'Pencairan Dana (SP2D)';
@endphp

<div id="disbursementMainList" class="page-container">
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-wallet"></i> {{ $title }}</h2>
            <p>Kelola pencairan dana (UP/GU/LS) &mdash; Alur: SPP &rarr; SPM &rarr; SP2D (Cair)</p>
        </div>

        <div class="page-header-right">
            @if(auth()->user()->hasPermission('SPP_CRUD') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="openDisbursementForm()">
                    <i class="ph-bold ph-plus"></i>
                    <span>Buat Pengajuan SPP</span>
                </button>
            @endif
        </div>
    </div>

    <div class="dashboard-box">

        <style>
            /* Styles for Report View */
            #disbursementMainList.is-report-view #tableDisbursementHeadDefault {
                display: none;
            }

            #disbursementMainList:not(.is-report-view) #tableDisbursementHeadReport {
                display: none;
            }
        </style>
        <div class="table-container">
            <table id="tableDisbursement" class="table universal-table table-mobile-cards">
                <thead id="tableDisbursementHeadDefault">
                    <tr>
                        <th width="60" class="text-center checkbox-col">No</th>
                        <th width="80" class="text-center sortable" onclick="sortDisbursement('paket_number')"
                            data-sort="paket_number">Paket <i class="ph ph-caret-up-down"></i></th>
                        <th width="100" class="text-center sortable" onclick="sortDisbursement('type')"
                            data-sort="type">Tipe <i class="ph ph-caret-up-down"></i></th>
                        <th width="220" class="text-left">No. Dokumen</th>
                        <th width="100" class="text-center sortable" onclick="sortDisbursement('siklus_number')"
                            data-sort="siklus_number">Siklus <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortDisbursement('sp2d_date')"
                            data-sort="sp2d_date">Tanggal <i class="ph ph-caret-up-down"></i></th>
                        <th class="sortable" onclick="sortDisbursement('uraian')" data-sort="uraian">Kegiatan <i
                                class="ph ph-caret-up-down"></i></th>
                        <th class="text-right sortable" onclick="sortDisbursement('value')" data-sort="value">Nilai (Rp)
                            <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortDisbursement('status')"
                            data-sort="status">Status <i class="ph ph-caret-up-down"></i></th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <thead id="tableDisbursementHeadReport">
                    <tr>
                        <th width="60" class="text-center checkbox-col">No</th>
                        <th width="120" class="text-center sortable" onclick="sortDisbursement('sp2d_date')"
                            data-sort="sp2d_date">Tanggal <i class="ph ph-caret-up-down"></i></th>
                        <th width="250" class="text-left">No. Dokumen</th>
                        <th class="sortable" onclick="sortDisbursement('uraian')" data-sort="uraian">Kegiatan <i
                                class="ph ph-caret-up-down"></i></th>
                        <th width="200" class="text-right sortable" onclick="sortDisbursement('value')"
                            data-sort="value">Nilai (Rp) <i class="ph ph-caret-up-down"></i></th>
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
    <div class="page-header">
        <div class="page-header-left">
            <button onclick="closeBelanjaItemsModal()"
                style="display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 10px; cursor: pointer; color: #64748b; font-weight: 600; margin-bottom: 12px; transition: all 0.2s;">
                <i class="ph ph-arrow-left"></i> Kembali ke Daftar
            </button>
            <h2 id="belanjaItemsTitle"><i class="ph ph-shopping-cart"></i> Detail Belanja / Kegiatan</h2>
            <p id="belanjaItemsSubtitle" style="font-size: 14px; color: #64748b;">Kelola rincian kegiatan untuk
                dokumen: <span id="belanjaRefNo" style="font-weight: 700; color: #1e293b;">-</span></p>
        </div>
        <div class="page-header-right">
            @if(auth()->user()->hasPermission('PENCAIRAN_CRUD') || auth()->user()->isAdmin())
                <button class="btn-tambah-data" onclick="addNewBelanjaItem()" style="background:#059669; height: 44px;">
                    <i class="ph-bold ph-plus"></i>
                    <span>Tambah Rincian Kegiatan</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid-responsive grid-3 mb-4">
        <div class="dash-card">
            <div class="label"
                style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                Total Dana Cair (SP2D)</div>
            <div style="font-size: 24px; font-weight: 800; color: #1e40af;">
                <small style="font-size: 14px; font-weight: 600; opacity: 0.6;">Rp</small> <span
                    id="belanjaTotalValue">0,00</span>
            </div>
        </div>
        <div class="dash-card">
            <div class="label"
                style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                Total Realisasi (Belanja)</div>
            <div style="font-size: 24px; font-weight: 800; color: #dc2626;">
                <small style="font-size: 14px; font-weight: 600; opacity: 0.6;">Rp</small> <span
                    id="belanjaUsedValue">0,00</span>
            </div>
        </div>
        <div class="dash-card">
            <div class="label"
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
            <table class="table universal-table">
                <thead>
                    <tr>
                        <th class="text-center checkbox-col" width="60">No</th>
                        <th class="text-center sortable" width="140" onclick="sortBelanjaItems('spending_date')"
                            data-sort="spending_date">Tanggal <i class="ph ph-caret-up-down"></i></th>
                        <th class="text-center sortable" width="260" onclick="sortBelanjaItems('no_bukti')"
                            data-sort="no_bukti">No. Bukti <i class="ph ph-caret-up-down"></i></th>
                        <th class="sortable" onclick="sortBelanjaItems('description')" data-sort="description">Uraian
                            Kegiatan <i class="ph ph-caret-up-down"></i></th>
                        <th class="text-right sortable" width="180" onclick="sortBelanjaItems('gross_value')"
                            data-sort="gross_value">Nilai (Rp) <i class="ph ph-caret-up-down"></i></th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="belanjaItemsTableBody">
                    <tr>
                        <td colspan="6" class="text-center">
                            <i class="ph ph-mask-sad" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                            Belum ada rincian kegiatan untuk pencairan ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    window.hasSppCrud = @json(auth()->user()->hasPermission('SPP_CRUD') || auth()->user()->isAdmin());
    window.hasSpmCrud = @json(auth()->user()->hasPermission('SPM_CRUD') || auth()->user()->isAdmin());
    window.hasSp2dCrud = @json(auth()->user()->hasPermission('SP2D_CRUD') || auth()->user()->isAdmin());
    window.hasPencairanCrud = @json(auth()->user()->hasPermission('PENCAIRAN_CRUD') || auth()->user()->isAdmin());
    window.hasSaldoDanaCrud = @json(auth()->user()->hasPermission('SALDO_DANA_CRUD') || auth()->user()->isAdmin());
    if (typeof initDisbursement === 'function') {
        initDisbursement();
    }
</script>