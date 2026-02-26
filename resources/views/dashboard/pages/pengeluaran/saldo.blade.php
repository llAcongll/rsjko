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
            <table id="tableSaldo">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="80" class="text-center">Tipe</th>
                        <th width="80" class="text-center">Siklus</th>
                        <th width="120" class="text-center">Tanggal</th>
                        <th>Uraian</th>
                        <th class="text-right">Nilai (Rp)</th>
                        <th width="100" class="text-center">Status</th>
                        <th width="80" class="text-center">Aksi</th>
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