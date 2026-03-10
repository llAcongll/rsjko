@php
    $tahun = session('tahun_anggaran', date('Y'));
@endphp

<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Catatan Atas Laporan Keuangan (CaLK)
            </h2>
            <p>Konsolidasi Narasi dan Penjelasan Seluruh Laporan Keuangan SAP Akrual</p>
        </div>

        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Periode</label>
                    <select id="calkPeriode" class="filter-date-input" onchange="toggleCalkPeriodInputs()">
                        <option value="Tahunan">Tahunan</option>
                        <option value="Semester">Semester</option>
                        <option value="Triwulan">Triwulan</option>
                        <option value="Bulanan">Bulanan</option>
                    </select>
                </div>

                <div class="filter-item" id="calkBulanContainer" style="display:none;">
                    <label>Bulan</label>
                    <select id="calkBulan" class="filter-date-input">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="filter-item" id="calkTriwulanContainer" style="display:none;">
                    <label>Triwulan</label>
                    <select id="calkTriwulan" class="filter-date-input">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                <div class="filter-item" id="calkSemesterContainer" style="display:none;">
                    <label>Semester</label>
                    <select id="calkSemester" class="filter-date-input">
                        <option value="1">Semester I</option>
                        <option value="2">Semester II</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Tahun</label>
                    <select id="calkTahun" class="filter-date-input">
                        @for ($i = $tahun - 1; $i <= $tahun + 1; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <button class="btn-filter" onclick="loadLaporan('CALK')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                <div class="filter-divider"></div>

                <button class="btn-preview" onclick="openPreviewModal('CALK')">
                    <i class="ph ph-file-search"></i>
                    <span>Preview & Unduh</span>
                </button>
            </div>
        </div>
    </div>

    <div id="calkContent" class="laporan-content-area" style="padding: 24px;">
        <div style="text-align: center; padding: 100px 0; color: #94a3b8;">
            <i class="ph ph-notebook" style="font-size: 48pt; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
            <p>Silakan pilih periode dan klik Tampilkan</p>
        </div>
    </div>
</div>

<style>
    .calk-bab-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .calk-bab-header {
        background: #f8fafc;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .calk-bab-header h4 {
        margin: 0;
        color: #1e293b;
        font-weight: 700;
    }

    .calk-bab-body {
        padding: 20px;
    }

    .calk-editor {
        width: 100%;
        min-height: 150px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.6;
        resize: vertical;
    }

    .calk-editor:focus {
        outline: none;
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }

    .btn-save-bab {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-save-bab:hover {
        background: #1e293b;
    }
</style>

<script>
    function toggleCalkPeriodInputs() {
        const periode = document.getElementById('calkPeriode').value;
        const bulanCont = document.getElementById('calkBulanContainer');
        const triCont = document.getElementById('calkTriwulanContainer');
        const semCont = document.getElementById('calkSemesterContainer');

        bulanCont.style.display = periode === 'Bulanan' ? 'block' : 'none';
        triCont.style.display = periode === 'Triwulan' ? 'block' : 'none';
        semCont.style.display = periode === 'Semester' ? 'block' : 'none';
    }

    async function saveCalkBab(bab) {
        const text = document.getElementById(`editor_${bab}`).value;
        const p = document.getElementById('calkPeriode').value;
        let b = 12;
        if (p === 'Bulanan') b = document.getElementById('calkBulan').value;
        else if (p === 'Triwulan') b = document.getElementById('calkTriwulan').value * 3;
        else if (p === 'Semester') b = document.getElementById('calkSemester').value * 6;

        const t = document.getElementById('calkTahun').value;

        try {
            const res = await fetch('/dashboard/laporan/calk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ bab, content: text, bulan: b, tahun: t })
            });
            const data = await res.json();
            if (data.success) toast(`Berhasil menyimpan ${bab.replace('_', ' ')}`, 'success');
        } catch (e) {
            toast('Gagal menyimpan data', 'error');
        }
    }
</script>







