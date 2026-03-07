@php
    $title = 'Saldo Dana';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-piggy-bank"></i> {{ $title }}</h2>
            <p>Kelola saldo kas — UP dan GU</p>
        </div>

        <div class="dashboard-header-right">
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div id="saldoSummaryCards"
        style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-bottom:20px;">
        {{-- dynamically filled by JS --}}
    </div>

    {{-- TABLE --}}
    <div class="dashboard-box">
        <div class="table-container">
            <table class="table universal-table" id="tableSaldo">
                <thead>
                    <tr>
                        <th width="40" class="text-center checkbox-col">No</th>
                        <th width="80" class="text-center sortable" onclick="sortSaldoDana('type')" data-sort="type">
                            Tipe <i class="ph ph-caret-up-down"></i></th>
                        <th width="80" class="text-center sortable" onclick="sortSaldoDana('siklus_up')"
                            data-sort="siklus_up">Siklus <i class="ph ph-caret-up-down"></i></th>
                        <th width="120" class="text-center sortable" onclick="sortSaldoDana('sp2d_date')"
                            data-sort="sp2d_date">Tanggal <i class="ph ph-caret-up-down"></i></th>
                        <th class="sortable" onclick="sortSaldoDana('description')" data-sort="description">Uraian <i
                                class="ph ph-caret-up-down"></i></th>
                        <th class="text-right sortable" onclick="sortSaldoDana('value')" data-sort="value">Nilai (Rp) <i
                                class="ph ph-caret-up-down"></i></th>
                        <th width="100" class="text-center sortable" onclick="sortSaldoDana('status')"
                            data-sort="status">Status <i class="ph ph-caret-up-down"></i></th>
                        <th width="80" class="text-center action-col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableSaldoBody">
                    <tr>
                        <td colspan="8" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    if (typeof initSaldoDana === 'function') {
        initSaldoDana();
    }
</script>