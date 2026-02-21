<div id="previewLaporanModal" class="confirm-overlay">
    <div class="confirm-box"
        style="max-width: 1150px; width: 98%; height: 95vh; display: flex; flex-direction: column; overflow: hidden; padding: 25px;">
        <div
            style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
            <h3 style="margin:0; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-file-search" style="color: #6366f1;"></i>
                <span id="modalReportTitle">Preview Laporan</span>
            </h3>
        </div>

        <div id="previewContent"
            style="flex: 1; overflow-y: auto; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px;">
            <div id="previewFormalView"
                style="background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; min-height: 100%; margin: 0 auto; padding: 40px; box-sizing: border-box; font-family: 'Inter', sans-serif;">

                {{-- KOP SURAT --}}
                <div style="text-align: center; margin-bottom: 5px;">
                    <h1 style="margin: 0; padding: 0; font-size: 14pt; font-weight: normal; color: #000;">
                        PEMERINTAH PROVINSI KEPULAUAN RIAU</h1>
                    <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000;">RUMAH SAKIT JIWA
                        DAN KETERGANTUNGAN OBAT</h2>
                    <h2 style="margin: 0; padding: 0; font-size: 13pt; font-weight: bold; color: #000;">ENGKU HAJI DAUD
                    </h2>
                    <div class="address"
                        style="line-height: 1.4; margin-top: 5px; font-size: 8pt; font-weight: normal; color: #000;">
                        Jalan Indun Suri – Simpang Busung Nomor 1 Tanjung Uban Kode Pos 29152<br>
                        Telepon (0771) 482655, 482796 • Faksimile (0771) 482795<br>
                        Pos-el: rskjoehd@kepriprov.go.id<br>
                        Laman: www.rsuehd.kepriprov.go.id
                    </div>
                </div>

                <div style="height: 2px; background: #000; margin: 15px 0 20px;"></div>

                <div
                    style="text-align: center; margin-top: 20px; margin-bottom: 30px; width: 100%; position: relative;">
                    <h3 id="previewMainTitle"
                        style="margin: 0 auto; font-size: 14pt; font-weight: bold; text-decoration: underline; color: #000; text-align: center; display: block;">
                        LAPORAN
                    </h3>
                    <div style="text-align: center; margin-top: 8px;">
                        <p id="previewPeriode" style="margin: 0; font-size: 10pt; color: #000; display: block;">Periode:
                            -</p>
                    </div>
                </div>

                <div id="previewTables" style="font-size: 9pt; color: #000;">
                    {{-- DYNAMIC TABLES HERE --}}
                </div>

                {{-- SIGNATURE AREA --}}
                <div
                    style="margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
                    {{-- LEFT SIGNATORY SLOT --}}
                    <div id="ptPreviewAreaKiri" style="width: 32%; text-align: center; visibility: hidden;">
                        <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                        <p id="previewPtJabatanKiri" style="margin: 0; font-weight: bold; min-height: 1.2em;"></p>
                        <div style="height: 60px;"></div>
                        <p id="previewPtNamaKiri" style="margin: 0; font-weight: bold;">
                            ...................................</p>
                        <p id="previewPtNipKiri" style="margin: 0;">NIP. ...................................</p>
                    </div>

                    {{-- MIDDLE SIGNATORY SLOT --}}
                    <div id="ptPreviewAreaTengah" style="width: 32%; text-align: center; visibility: hidden;">
                        <p style="margin: 0; min-height: 1.25em;">&nbsp;</p>
                        <p id="previewPtJabatanTengah" style="margin: 0; font-weight: bold; min-height: 1.2em;"></p>
                        <div style="height: 60px;"></div>
                        <p id="previewPtNamaTengah" style="margin: 0; font-weight: bold;">
                            ...................................</p>
                        <p id="previewPtNipTengah" style="margin: 0;">NIP. ...................................</p>
                    </div>

                    {{-- RIGHT SIGNATORY SLOT --}}
                    <div id="ptPreviewAreaKanan" style="width: 32%; text-align: center;">
                        <p style="margin: 0;">Tanjung Uban, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p id="previewPtJabatanKanan" style="margin: 0; font-weight: bold; min-height: 1.2em;"></p>
                        <div style="height: 60px;"></div>
                        <p id="previewPtNamaKanan" style="margin: 0; font-weight: bold;">
                            ...................................</p>
                        <p id="previewPtNipKanan" style="margin: 0;">NIP. ...................................</p>
                    </div>
                </div>
            </div>
        </div>

        <div
            style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="btn-preview" style="background: #64748b; border-color: #64748b; color: white;"
                    onclick="closePreviewModal()">
                    <i class="ph ph-x-circle"></i> Tutup
                </button>

                <div
                    style="display: flex; gap: 10px; background: #f8fafc; padding: 6px 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KIRI:</label>
                        <select id="ptSelectKiri" onchange="updateSignatory('Kiri')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                    <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. TENGAH:</label>
                        <select id="ptSelectTengah" onchange="updateSignatory('Tengah')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                    <div class="divider" style="width: 1px; height: 25px; background: #e2e8f0;"></div>
                    <div class="filter-item" style="display: flex; align-items: center; gap: 5px;">
                        <label style="font-size: 10px; font-weight: 700; color: #475569;">PT. KANAN:</label>
                        <select id="ptSelectKanan" onchange="updateSignatory('Kanan')"
                            style="height: 30px; padding: 0 6px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 10px; min-width: 140px; background: #fff;">
                            <option value="">-- Kosong --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                @if(auth()->user()->hasPermission('LAPORAN_EXPORT'))
                    <button class="btn-filter" style="background: #10b981; border-color: #10b981; color: white;"
                        onclick="exportLaporan()">
                        <i class="ph ph-file-xls"></i> Unduh Excel
                    </button>
                @endif
                @if(auth()->user()->hasPermission('LAPORAN_EXPORT_PDF'))
                    <button class="btn-filter" style="background: #ef4444; border-color: #ef4444; color: white;"
                        onclick="exportPdf()">
                        <i class="ph ph-file-pdf"></i> Unduh PDF
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>