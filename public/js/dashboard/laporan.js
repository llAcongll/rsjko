window.initLaporan = function (type = 'PENDAPATAN') {
    const today = window.getTodayLocal();
    const firstDay = '2026-01-01';

    if (document.getElementById('laporanStart')) document.getElementById('laporanStart').value = firstDay;
    if (document.getElementById('laporanEnd')) document.getElementById('laporanEnd').value = today;

    loadLaporan(type);
};

window.handleTriwulanChange = function (tw) {
    const startInput = document.getElementById('laporanStart');
    const endInput = document.getElementById('laporanEnd');
    const year = window.tahunAnggaran || new Date().getFullYear();

    if (!tw) return;

    let start = '';
    let end = '';
    let label = '';

    if (tw.startsWith('m')) {
        const month = parseInt(tw.substring(1));
        const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        start = `${year}-${String(month).padStart(2, '0')}-01`;
        // Get last day of month
        const lastDay = new Date(year, month, 0).getDate();
        end = `${year}-${String(month).padStart(2, '0')}-${lastDay}`;
        label = `PERIODE BULAN ${monthNames[month].toUpperCase()} ${year}`;
    } else {
        const dates = {
            '1': { start: year + '-01-01', end: year + '-03-31', label: `PERIODE TRIWULAN I ${year}` },
            '2': { start: year + '-04-01', end: year + '-06-30', label: `PERIODE TRIWULAN II ${year}` },
            '3': { start: year + '-07-01', end: year + '-09-30', label: `PERIODE TRIWULAN III ${year}` },
            '4': { start: year + '-10-01', end: year + '-12-31', label: `PERIODE TRIWULAN IV ${year}` }
        };
        if (dates[tw]) {
            start = dates[tw].start;
            end = dates[tw].end;
            label = dates[tw].label;
        }
    }

    if (start && end) {
        if (startInput) startInput.value = start;
        if (endInput) endInput.value = end;

        const titlePeriod = document.getElementById('sptjbTitlePeriod');
        if (titlePeriod) titlePeriod.innerText = label;
    }
};

window.performManualLoad = async function () {
    const twSelect = document.getElementById('sptjbTriwulan');
    if (twSelect && !twSelect.value) {
        if (typeof toast === 'function') toast('Silakan pilih Triwulan terlebih dahulu!', 'warning');
        return;
    }

    if (typeof window.loadLaporan === 'function') {
        const btn = document.getElementById('btnSptjbLoad');
        const originalHtml = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Memuat...';
        }

        try {
            await window.loadLaporan('ANGGARAN');
        } catch (err) {
            console.error(err);
            if (typeof toast === 'function') toast('Gagal memuat data', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }
};

window.loadLaporan = async function (type) {
    const startEl = document.getElementById('laporanStart');
    const endEl = document.getElementById('laporanEnd');
    const tahunEl = document.getElementById('laporanTahun');
    const start = startEl ? startEl.value : '';
    const end = endEl ? endEl.value : '';
    const tahun = tahunEl ? tahunEl.value : '';

    window.lastLaporanType = type;

    // Only validate dates for reports that need start/end range
    if (!end && !['REKON', 'DPA', 'PIUTANG', 'BKU'].includes(type)) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    const container = document.querySelector('.laporan');
    if (container) container.classList.add('loading');

    try {
        let url = '';
        const params = `start=${start}&end=${end}&tahun=${tahun}`;
        switch (type) {
            case 'PENDAPATAN': url = `/dashboard/laporan/data?${params}`; break;
            case 'REKON': url = `/dashboard/laporan/rekon?${params}`; break;
            case 'PIUTANG': url = `/dashboard/laporan/piutang?${params}`; break;
            case 'MOU': url = `/dashboard/laporan/mou?${params}`; break;
            case 'ANGGARAN': {
                const cat = document.getElementById('lraCategory')?.value || 'SEMUA';
                const level = document.getElementById('lraLevel')?.value || '3'; // Default to Jenis (Level 3)
                url = `/dashboard/laporan/anggaran?${params}&category=${cat}&level=${level}`;
                break;
            }
            case 'PENGELUARAN': url = `/dashboard/laporan/pengeluaran?${params}`; break;
            case 'BKU': {
                const month = document.getElementById('ledgerMonth')?.value || '';
                const year = document.getElementById('ledgerYear')?.value || '';
                url = `/dashboard/laporan/bku?month=${month}&year=${year}`;
                break;
            }
            case 'DPA': url = `/dashboard/laporan/dpa`; break;
        }

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            throw new Error(`Error ${res.status}: ${res.statusText}`);
        }
        const data = await res.json();
        window.lastLaporanData = data;

        if (type === 'PENDAPATAN') renderPendapatan(data);
        else if (type === 'REKON') renderRekon(data);
        else if (type === 'PIUTANG') renderPiutang(data);
        else if (type === 'MOU') renderMou(data);
        else if (type === 'ANGGARAN') renderAnggaran(data);
        else if (type === 'BKU') {
            // No specific render function needed for BKU in the main view yet,
            // as it currently uses pagination in treasurer.js, 
            // but we store it for preview.
        }
        else if (type === 'PENGELUARAN') {
            if (data && data.summary) {
                renderPengeluaran(data);
            } else {
                console.error('Invalid data structure for PENGELUARAN', data);
                toast('Data laporan tidak valid', 'error');
            }
        }
        else if (type === 'DPA') renderDPA(data);

    } catch (err) {
        console.error(err);
        toast(`Gagal memuat laporan: ${err.message}`, 'error');
    } finally {
        if (container) container.classList.remove('loading');
    }
};

function renderPendapatan(data) {
    // 1. Render Cards (Umum, BPJS, Jaminan, Kerjasama, Lain-lain)
    const cardContainer = document.getElementById('laporanTypeCards');
    if (cardContainer) {
        cardContainer.innerHTML = '';
        const types = {
            'UMUM': { label: 'Umum', icon: 'ph-user', color: 'blue' },
            'BPJS': { label: 'BPJS', icon: 'ph-shield-check', color: 'green' },
            'JAMINAN': { label: 'Jaminan', icon: 'ph-buildings', color: 'orange' },
            'KERJASAMA': { label: 'Kerjasama', icon: 'ph-handshake', color: 'purple' },
            'LAIN': { label: 'Lain-lain', icon: 'ph-dots-three-circle', color: 'slate' }
        };

        let html = '';
        Object.keys(types).forEach(key => {
            const item = data.summary[key] || { total: 0, count: 0 };
            const conf = types[key];
            html += `
                <div class="laporan-card highlight-${conf.color}">
                    <div class="card-icon"><i class="ph ${conf.icon}"></i></div>
                    <div class="card-info">
                        <h3>${conf.label}</h3>
                        <span class="big">${formatRupiah(item.total)}</span>
                        <p>${item.count} Transaksi</p>
                    </div>
                </div>
            `;
        });
        cardContainer.innerHTML = html;
    }

    // List of keys in order
    const categoryKeys = [
        'BPJS_JAMINAN',
        'PASIEN_UMUM',
        'KERJASAMA',
        'PKL',
        'MAGANG',
        'LAIN_LAIN',
        'PENELITIAN',
        'PERMINTAAN_DATA',
        'STUDY_BANDING',
    ];

    // 1b. Render Jasa RS & Pelayanan Breakdown Table
    const jasaTableBody = document.getElementById('laporanJasaDetailedBody');
    if (jasaTableBody) {
        jasaTableBody.innerHTML = '';
        let html = '';
        let totalRs = 0;
        let totalPelayanan = 0;
        let totalJasaAll = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item || !item.jasa) return;

            const row = item.jasa;
            totalRs += row.RS;
            totalPelayanan += row.PELAYANAN;
            totalJasaAll += row.TOTAL;

            html += `
                <tr>
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.RS)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.PELAYANAN)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `;
        });

        html += `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL KESELURUHAN</td>
                <td style="text-align:right">${formatRupiahTable(totalRs)}</td>
                <td style="text-align:right">${formatRupiahTable(totalPelayanan)}</td>
                <td style="text-align:right">${formatRupiahTable(totalJasaAll)}</td>
            </tr>
        `;
        jasaTableBody.innerHTML = html;
    }

    // 2. Render Payment Method Table
    const payTableBody = document.getElementById('laporanPaymentDetailedBody');
    if (payTableBody) {
        payTableBody.innerHTML = '';
        let html = '';
        let totalTunai = 0;
        let totalNon = 0;
        let totalAll = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;

            const row = item.payments;
            totalTunai += row.TUNAI;
            totalNon += row.NON_TUNAI;
            totalAll += row.TOTAL;

            html += `
                <tr>
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.TUNAI)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.NON_TUNAI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `;
        });

        html += `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL KESELURUHAN</td>
                <td style="text-align:right">${formatRupiahTable(totalTunai)}</td>
                <td style="text-align:right">${formatRupiahTable(totalNon)}</td>
                <td style="text-align:right">${formatRupiahTable(totalAll)}</td>
            </tr>
        `;
        payTableBody.innerHTML = html;
    }

    // 3. Render Bank Reception Table
    const bankTableBody = document.getElementById('laporanBankDetailedBody');
    if (bankTableBody) {
        bankTableBody.innerHTML = '';
        let html = '';
        let totalBRK = 0;
        let totalBSI = 0;
        let totalAllBank = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;

            const row = item.banks;
            totalBRK += row.BRK;
            totalBSI += row.BSI;
            totalAllBank += row.TOTAL;

            html += `
                <tr>
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.BRK)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.BSI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `;
        });

        html += `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL PENERIMAAN BANK</td>
                <td style="text-align:right">${formatRupiahTable(totalBRK)}</td>
                <td style="text-align:right">${formatRupiahTable(totalBSI)}</td>
                <td style="text-align:right">${formatRupiahTable(totalAllBank)}</td>
            </tr>
        `;
        bankTableBody.innerHTML = html;
    }

    // 4. Room Stats (Top 10)
    const roomBody = document.getElementById('laporanRoomBody');
    if (roomBody) {
        roomBody.innerHTML = '';
        const roomEntries = Object.entries(data.rooms);
        if (roomEntries.length === 0) {
            roomBody.innerHTML = '<div class="text-center py-4 text-slate-400">Tidak ada data ruangan</div>';
        } else {
            const maxVal = Math.max(...roomEntries.map(e => e[1]), 1);
            let html = '';
            roomEntries.forEach(([room, total]) => {
                const percent = (total / maxVal) * 100;
                html += `
                    <div class="room-item">
                        <div class="room-info"><span>${room}</span><strong>${formatRupiah(total)}</strong></div>
                        <div class="room-bar-bg"><div class="room-bar-fill" style="width: ${percent}%"></div></div>
                    </div>
                `;
            });
            roomBody.innerHTML = html;
        }
    }

    // 5. Patient Stats (Per Room - Following Income per Room layout)
    const patientBody = document.getElementById('laporanPatientBody');
    if (patientBody) {
        patientBody.innerHTML = '';
        const patientEntries = Object.entries(data.room_patients);
        if (patientEntries.length === 0) {
            patientBody.innerHTML = '<div class="text-center py-4 text-slate-400">Tidak ada data pasien</div>';
        } else {
            const maxValP = Math.max(...patientEntries.map(e => e[1]), 1);
            let html = '';
            patientEntries.forEach(([room, count]) => {
                const percent = (count / maxValP) * 100;
                html += `
                    <div class="room-item">
                        <div class="room-info"><span>${room}</span><strong>${count} Pasien</strong></div>
                        <div class="room-bar-bg"><div class="room-bar-fill" style="width: ${percent}%; background: linear-gradient(90deg, #10b981, #34d399);"></div></div>
                    </div>
                `;
            });
            patientBody.innerHTML = html;
        }
    }
}

function renderRekon(data) {
    const body = document.getElementById('laporanRekonBody');
    if (!body) return;

    // Summary Els
    const sumBank = document.getElementById('rekonTotalBank');
    const sumPend = document.getElementById('rekonTotalPend');
    const sumDiff = document.getElementById('rekonTotalDiff');

    let totalBank = 0;
    let totalPend = 0;

    body.innerHTML = '';

    // Check if data is not empty before mapping 
    if (!data || data.length === 0) {
        body.innerHTML = '<tr><td colspan="6" style="text-align:center">📭 Tidak ada data transaksi</td></tr>';
        if (sumBank) sumBank.innerText = 'Rp 0';
        if (sumPend) sumPend.innerText = 'Rp 0';
        if (sumDiff) sumDiff.innerText = 'Rp 0';
        return;
    }
    let html = '';
    data.forEach(item => {
        totalBank += Number(item.bank);
        totalPend += Number(item.pendapatan);

        const isMatch = Math.abs(item.selisih) < 1;

        let statusClass = 'badge-danger';
        let statusText = '❌ SELISIH';

        if (isMatch) {
            statusClass = 'badge-success';
            statusText = '✅ MATCH';
        }

        const selisihColor = item.selisih === 0 ? '#64748b' : (item.selisih > 0 ? '#16a34a' : '#ef4444');

        html += `
            <tr>
                <td class="text-center" style="font-weight:600;">${item.tanggal}</td>
                <td style="text-align:right">${formatRupiahTable(item.bank)}</td>
                <td style="text-align:right">${formatRupiahTable(item.pendapatan)}</td>
                <td style="text-align:right; font-weight:600; color:${selisihColor}">${formatRupiahTable(item.selisih)}</td>
                <td style="text-align:left; color:#64748b; font-size:13px; white-space: normal; line-height: 1.6; min-width: 250px; padding: 10px;">${item.keterangan}</td>
                <td style="text-align:center"><span class="badge ${statusClass}">${statusText}</span></td>
            </tr>
        `;
    });
    body.innerHTML = html;

    if (sumBank) sumBank.innerText = formatRupiah(totalBank);
    if (sumPend) sumPend.innerText = formatRupiah(totalPend);
    if (sumDiff) {
        const net = totalBank - totalPend;
        sumDiff.innerText = formatRupiah(net);
        sumDiff.style.color = net === 0 ? '#16a34a' : '#ef4444';
    }
}

function renderPiutang(data) {
    if (document.getElementById('headerSisaTahun')) {
        document.getElementById('headerSisaTahun').innerText = `Sisa ${data.tahun - 1}`;
    }
    if (document.getElementById('totalPiutangReport')) document.getElementById('totalPiutangReport').innerText = formatRupiah(data.totals.sa_piutang + data.totals.berjalan_piutang);
    if (document.getElementById('totalPotonganPiutangReport')) document.getElementById('totalPotonganPiutangReport').innerText = formatRupiah(data.totals.total_potongan);
    if (document.getElementById('totalAdmBankPiutangReport')) document.getElementById('totalAdmBankPiutangReport').innerText = formatRupiah(data.totals.total_adm);
    if (document.getElementById('totalDiterimaPiutangReport')) document.getElementById('totalDiterimaPiutangReport').innerText = formatRupiah(data.totals.total_pelunasan);

    const body = document.getElementById('laporanPiutangBody');
    if (!body) return;
    body.innerHTML = '';
    let html = '';
    data.data.forEach(item => {
        html += `
            <tr>
                <td><strong>${item.nama_perusahaan}</strong></td>
                <td style="text-align:right">${formatRupiahTable(item.sa_piutang)}</td>
                <td style="text-align:right">${formatRupiahTable(item.sa_pelunasan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.sa_potongan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.sa_adm)}</td>
                <td style="text-align:right">${formatRupiahTable(item.berjalan_piutang)}</td>
                <td style="text-align:right">${formatRupiahTable(item.berjalan_pelunasan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.berjalan_potongan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.berjalan_adm)}</td>
                <td style="text-align:right; font-weight:700;">${formatRupiahTable(item.total_pelunasan)}</td>
                <td style="text-align:right; font-weight:700;">${formatRupiahTable(item.total_potongan)}</td>
                <td style="text-align:right; font-weight:700; color:#ef4444">${formatRupiahTable(item.sisa_sa)}</td>
                <td style="text-align:right; font-weight:700; background:#f1f5f9;">${formatRupiahTable(item.saldo_akhir)}</td>
            </tr>
        `;
    });
    body.innerHTML = html;
}

function renderMou(data) {
    const body = document.getElementById('laporanMouBody');
    if (!body) return;
    body.innerHTML = '';
    let html = '';
    data.forEach((item, index) => {
        html += `
            <tr>
                <td style="text-align:center">${index + 1}</td>
                <td><strong>${item.nama_mou}</strong></td>
                <td style="text-align:center">${item.count}</td>
                <td style="text-align:right">${formatRupiahTable(item.rs)}</td>
                <td style="text-align:right">${formatRupiahTable(item.pelayanan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.gross)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiahTable(item.potongan)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiahTable(item.adm_bank)}</td>
                <td style="text-align:right; font-weight:700; color:#16a34a">${formatRupiahTable(item.total)}</td>
            </tr>
        `;
    });
    body.innerHTML = html;
}

function renderAnggaran(data) {
    const cardsContainer = document.getElementById('lraCardsContainer');
    const tableContainer = document.getElementById('lraTableContainer');
    const desc = document.getElementById('lraDescription');

    if (desc) {
        if (data.category === 'PENDAPATAN') desc.innerText = 'Perbandingan Pencapaian Pendapatan terhadap Target Anggaran Pendapatan';
        else if (data.category === 'PENGELUARAN') desc.innerText = 'Perbandingan Realisasi Belanja terhadap Target Anggaran Belanja (Expenditure)';
        else desc.innerText = 'Perbandingan Realisasi Pendapatan dan Belanja (Surplus/Defisit)';
    }

    if (cardsContainer) {
        cardsContainer.innerHTML = '';
        const createRow = (titlePrefix, target, real, percent) => {
            let progressClass = 'highlight-orange';
            if (percent >= 100) progressClass = 'highlight-purple';
            else if (percent >= 80) progressClass = 'highlight-green';
            else if (percent >= 50) progressClass = 'highlight-blue';

            return `
                <div class="laporan-main-cards anggaran-summary" style="margin-bottom: 20px; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="laporan-card highlight-blue">
                        <div class="card-icon"><i class="ph ph-target"></i></div>
                        <div class="card-info">
                            <h3>TARGET ${titlePrefix}</h3>
                            <span class="big">${formatRupiah(target)}</span>
                            <p>Estimasi anggaran</p>
                        </div>
                    </div>
                    <div class="laporan-card highlight-green">
                        <div class="card-icon"><i class="ph ph-trend-up"></i></div>
                        <div class="card-info">
                            <h3>REALISASI ${titlePrefix}</h3>
                            <span class="big">${formatRupiah(real)}</span>
                            <p>Realisasi terhimpun</p>
                        </div>
                    </div>
                    <div class="laporan-card ${progressClass}">
                        <div class="card-icon"><i class="ph ph-percent"></i></div>
                        <div class="card-info">
                            <h3>CAPAIAN</h3>
                            <span class="big">${percent}%</span>
                            <p>Prosentase target</p>
                        </div>
                    </div>
                </div>
            `;
        };

        if (data.category === 'SEMUA') {
            cardsContainer.insertAdjacentHTML('beforeend', createRow('PENDAPATAN', data.sub_totals.pendapatan.target, data.sub_totals.pendapatan.real, data.sub_totals.pendapatan.persen));
            cardsContainer.insertAdjacentHTML('beforeend', createRow('BELANJA', data.sub_totals.pengeluaran.target, data.sub_totals.pengeluaran.real, data.sub_totals.pengeluaran.persen));
        } else {
            const label = data.category === 'PENDAPATAN' ? 'PENDAPATAN' : 'BELANJA';
            cardsContainer.insertAdjacentHTML('beforeend', createRow(label, data.totals.target, data.totals.realisasi_total, data.totals.persen));
        }
    }

    if (tableContainer) {
        tableContainer.innerHTML = '';

        const generateTableHtml = (items, title, totals = null) => {
            let rowsHtml = '';
            items.forEach(item => {
                const isHeader = item.tipe === 'header';
                let progressColor = '#3b82f6';
                if (item.persen >= 100) progressColor = '#9333ea';
                else if (item.persen >= 80) progressColor = '#10b981';
                else if (item.persen < 50) progressColor = '#f59e0b';

                const isRoot = item.nama && item.nama.includes('Rumah Sakit Khusus Jiwa dan Ketergantungan Obat');

                const valTarget = isRoot ? '' : formatRupiah(item.target);
                const valLalu = isRoot ? '' : formatRupiah(item.realisasi_lalu);
                const valKini = isRoot ? '' : formatRupiah(item.realisasi_kini);
                const valTotal = isRoot ? '' : formatRupiah(item.realisasi_total);
                const valSelisih = isRoot ? '' : formatRupiah(item.selisih);
                const valPersen = isRoot ? '' : item.persen + '%';
                const valProgress = isRoot ? '' : `
                    <div class="progress-track">
                        <div class="progress-fill" style="width: ${Math.min(item.persen, 100)}%; background:${progressColor};"></div>
                    </div>`;

                const isSptjb = !!document.getElementById('sptjbTriwulan');

                if (isSptjb) {
                    // SPTJB Mode: Show Lalu, Kini, Total
                    rowsHtml += `
                        <tr class="${isHeader ? 'row-header' : 'row-detail'}">
                            <td class="col-kode">${item.kode}</td>
                            <td class="col-uraian"><span>${item.nama}</span></td>
                            <td class="col-mono ">${valTarget}</td>
                            <td class="col-mono " style="color:#64748b; font-size:12px;">${valLalu}</td>
                            <td class="col-mono font-medium text-slate-700">${valKini}</td>
                            <td class="col-mono font-bold text-slate-900">${valTotal}</td>
                            <td class="col-mono ${item.selisih < 0 ? 'text-red-500' : 'text-slate-500'}">${valSelisih}</td>
                            <td class="text-center font-bold" style="color:${progressColor}">${valPersen}</td>
                            <td class="col-progress">${valProgress}</td>
                        </tr>
                    `;
                } else {
                    // Standard LRA Mode: Simplified columns
                    rowsHtml += `
                        <tr class="${isHeader ? 'row-header' : 'row-detail'}">
                            <td class="col-kode">${item.kode}</td>
                            <td class="col-uraian"><span>${item.nama}</span></td>
                            <td class="col-mono ">${valTarget}</td>
                            <td class="col-mono font-bold text-slate-900">${valTotal}</td>
                            <td class="col-mono ${item.selisih < 0 ? 'text-red-500' : 'text-slate-500'}">${valSelisih}</td>
                            <td class="text-center font-bold" style="color:${progressColor}">${valPersen}</td>
                            <td class="col-progress">${valProgress}</td>
                        </tr>
                    `;
                }
            });

            let footerHtml = '';
            if (totals) {
                const isSptjb = !!document.getElementById('sptjbTriwulan');
                if (isSptjb) {
                    footerHtml = `
                        <tr style="background:#f1f5f9; font-weight:800; border-top:2px solid #cbd5e1;">
                            <td colspan="2" class="text-center" style="padding:15px; font-size:14px;">TOTAL ${title}</td>
                            <td class="text-right" style="padding:15px; font-family:'JetBrains Mono';">${formatRupiah(totals.target)}</td>
                            <td class="text-right" style="padding:15px; color:#64748b; font-size:12px;">${formatRupiah(totals.realisasi_lalu)}</td>
                            <td class="text-right" style="padding:15px;">${formatRupiah(totals.realisasi_kini)}</td>
                            <td class="text-right" style="padding:15px; font-weight:bold;">${formatRupiah(totals.realisasi_total)}</td>
                            <td class="text-right" style="padding:15px;">${formatRupiah(totals.target - totals.realisasi_total)}</td>
                            <td class="text-center" style="padding:15px;">${totals.persen}%</td>
                            <td></td>
                        </tr>
                    `;
                } else {
                    footerHtml = `
                        <tr style="background:#f1f5f9; font-weight:800; border-top:2px solid #cbd5e1;">
                            <td colspan="2" class="text-center" style="padding:15px; font-size:14px;">TOTAL ${title}</td>
                            <td class="text-right" style="padding:15px; font-family:'JetBrains Mono';">${formatRupiah(totals.target)}</td>
                            <td class="text-right" style="padding:15px; font-weight:bold;">${formatRupiah(totals.realisasi_total)}</td>
                            <td class="text-right" style="padding:15px;">${formatRupiah(totals.target - totals.realisasi_total)}</td>
                            <td class="text-center" style="padding:15px;">${totals.persen}%</td>
                            <td></td>
                        </tr>
                    `;
                }
            }

            const isSptjb = !!document.getElementById('sptjbTriwulan');
            const headerHtml = isSptjb ? `
                <tr>
                    <th class="text-center" style="width: 150px;">Kode Rekening</th>
                    <th class="text-center">Uraian</th>
                    <th class="text-center">Target</th>
                    <th class="text-center">Realisasi (L)</th>
                    <th class="text-center">Realisasi (K)</th>
                    <th class="text-center">Realisasi (T)</th>
                    <th class="text-center">Selisih</th>
                    <th class="text-center">%</th>
                    <th class="text-center" style="width: 120px;">Progres</th>
                </tr>
            ` : `
                <tr>
                    <th class="text-center" style="width: 150px;">Kode Rekening</th>
                    <th class="text-center">Uraian</th>
                    <th class="text-center">Target</th>
                    <th class="text-center">Realisasi</th>
                    <th class="text-center">Selisih</th>
                    <th class="text-center">%</th>
                    <th class="text-center" style="width: 120px;">Progres</th>
                </tr>
            `;

            return `
                <div class="laporan-section" style="margin-bottom:40px;">
                    <div class="section-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
                        <h3 style="margin:0; font-size:16px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;">
                            <i class="ph ph-table"></i> Rincian Realisasi ${title}
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                ${headerHtml}
                            </thead>
                            <tbody>
                                ${rowsHtml}
                                ${footerHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        };

        if (data.category === 'SEMUA') {
            tableContainer.innerHTML += generateTableHtml(data.data_pendapatan, 'PENDAPATAN', {
                target: data.sub_totals.pendapatan.target,
                realisasi_total: data.sub_totals.pendapatan.real,
                realisasi_lalu: data.data_pendapatan.reduce((a, b) => a + (b.level === 1 ? b.realisasi_lalu : 0), 0),
                realisasi_kini: data.data_pendapatan.reduce((a, b) => a + (b.level === 1 ? b.realisasi_kini : 0), 0),
                persen: data.sub_totals.pendapatan.persen
            });

            tableContainer.innerHTML += generateTableHtml(data.data_pengeluaran, 'BELANJA (PENGELUARAN)', {
                target: data.sub_totals.pengeluaran.target,
                realisasi_total: data.sub_totals.pengeluaran.real,
                realisasi_lalu: data.data_pengeluaran.reduce((a, b) => a + (b.level === 1 ? b.realisasi_lalu : 0), 0),
                realisasi_kini: data.data_pengeluaran.reduce((a, b) => a + (b.level === 1 ? b.realisasi_kini : 0), 0),
                persen: data.sub_totals.pengeluaran.persen
            });

            // Summary Table (Surplus/Defisit)
            const isSp3bp = !!document.getElementById('sp3bpTriwulan');
            let surplusRow = '';

            if (isSp3bp) {
                surplusRow = `
                    <tr style="background:#f1f5f9; font-weight:900; font-size:16px;">
                        <td colspan="2" class="text-center" style="padding:20px;">SURPLUS / (DEFISIT) ANGGARAN</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.target)}</td>
                        <td class="text-right" style="padding:20px; color:#64748b; font-size:12px;">${formatRupiah(data.totals.realisasi_lalu)}</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.realisasi_kini)}</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.realisasi_total)}</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.target - data.totals.realisasi_total)}</td>
                        <td class="text-center" style="padding:20px;">${data.totals.persen}%</td>
                        <td></td>
                    </tr>
                `;
            } else {
                surplusRow = `
                    <tr style="background:#f1f5f9; font-weight:900; font-size:16px;">
                        <td colspan="2" class="text-center" style="padding:20px;">SURPLUS / (DEFISIT) ANGGARAN</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.target)}</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.realisasi_total)}</td>
                        <td class="text-right" style="padding:20px;">${formatRupiah(data.totals.target - data.totals.realisasi_total)}</td>
                        <td class="text-center" style="padding:20px;">${data.totals.persen}%</td>
                        <td></td>
                    </tr>
                `;
            }

            const surplusHtml = `
                <div class="laporan-section" style="margin-top:20px; border:2px solid #e2e8f0;">
                    <table class="report-table">
                        ${surplusRow}
                    </table>
                </div>
            `;
            tableContainer.innerHTML += surplusHtml;
        } else {
            const title = data.category === 'PENDAPATAN' ? 'PENDAPATAN' : 'BELANJA (PENGELUARAN)';
            tableContainer.innerHTML += generateTableHtml(data.data, title, data.totals);
        }
    }
}


function renderPengeluaran(data) {
    // 1. Render Cards
    const cardContainer = document.getElementById('laporanPengeluaranCards');
    if (cardContainer) {
        cardContainer.innerHTML = '';
        const types = {
            'PEGAWAI': { label: 'Belanja Pegawai', icon: 'ph-user-gear', color: 'purple' },
            'BARANG_JASA': { label: 'Belanja Barang & Jasa', icon: 'ph-package', color: 'blue' },
            'MODAL': { label: 'Belanja Modal', icon: 'ph-office-building', color: 'green' }
        };

        Object.keys(types).forEach(key => {
            const item = data.summary[key] || { total: 0, count: 0 };
            const conf = types[key];
            cardContainer.insertAdjacentHTML('beforeend', `
                <div class="dash-card ${conf.color}">
                    <div class="dash-card-icon">
                        <i class="ph ${conf.icon}"></i>
                    </div>
                    <div class="dash-card-content">
                        <span class="label">${conf.label}</span>
                        <h3>${formatRupiahTable(item.total)}</h3>
                        <small>${item.count} Transaksi</small>
                    </div>
                </div>
            `);
        });
    }

    // 2. Render Table
    const body = document.getElementById('laporanPengeluaranBody');
    if (body) {
        body.innerHTML = '';
        if (data.data.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data pengeluaran.</td></tr>';
            return;
        }

        let html = '';
        let gTotal = 0;
        let gUp = 0;
        let gGu = 0;
        let gLs = 0;

        data.data.forEach(item => {
            const total = parseFloat(item.total);
            const up = parseFloat(item.up) || 0;
            const gu = parseFloat(item.gu) || 0;
            const ls = parseFloat(item.ls) || 0;

            gTotal += total;
            gUp += up;
            gGu += gu;
            gLs += ls;

            html += `
                <tr>
                    <td class="text-center"><code class="bg-slate-100 px-2 py-1 rounded">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td class="">${formatRupiahTable(up)}</td>
                    <td class="">${formatRupiahTable(gu)}</td>
                    <td class="">${formatRupiahTable(ls)}</td>
                    <td class="font-bold">${formatRupiahTable(total)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="bg-slate-50 font-extrabold">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="">${formatRupiahTable(gUp)}</td>
                <td class="">${formatRupiahTable(gGu)}</td>
                <td class="">${formatRupiahTable(gLs)}</td>
                <td class="">${formatRupiahTable(gTotal)}</td>
            </tr>
        `;
        body.innerHTML = html;
    }
}

window.exportLaporan = function (type) {
    const reportType = type || window.lastLaporanType || 'PENDAPATAN';
    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    if (!end && !['REKON', 'DPA', 'PIUTANG', 'BKU'].includes(reportType)) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    const mapping = {
        'PENDAPATAN': 'pendapatan',
        'REKON': 'rekon',
        'PIUTANG': 'piutang',
        'MOU': 'mou',
        'ANGGARAN': 'anggaran',
        'PENGELUARAN': 'pengeluaran',
        'DPA': 'dpa',
        'BKU': 'bku'
    };

    const ptKiri = document.getElementById('ptSelectKiri')?.value || '';
    const ptTengah = document.getElementById('ptSelectTengah')?.value || '';
    const ptKanan = document.getElementById('ptSelectKanan')?.value || '';

    const isSp3bp = !!document.getElementById('sp3bpTriwulan');
    const twSelect = document.getElementById('sp3bpTriwulan');
    if (isSp3bp && (!twSelect || !twSelect.value)) {
        toast('Silakan pilih Triwulan terlebih dahulu!', 'warning');
        return;
    }

    const endpoint = mapping[reportType] || 'pendapatan';
    let url = `/dashboard/laporan/export/${endpoint}?start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;

    if (reportType === 'ANGGARAN') {
        const cat = document.getElementById('lraCategory')?.value || 'PENDAPATAN';
        url += `&category=${cat}`;

        const tw = document.getElementById('sp3bpTriwulan')?.value;
        if (tw) {
            const romans = { '1': 'I', '2': 'II', '3': 'III', '4': 'IV' };
            const year = window.tahunAnggaran || new Date().getFullYear();
            url += `&report_title=LAPORAN REALISASI PENDAPATAN, BELANJA DAN PEMBIAYAAN`;
            url += `&report_period=PERIODE TRIWULAN ${romans[tw]} ${year}`;
        }
    }
    if (reportType === 'BKU') {
        const month = document.getElementById('ledgerMonth')?.value || '';
        const year = document.getElementById('ledgerYear')?.value || '';
        url = `/dashboard/laporan/export/${endpoint}?month=${month}&year=${year}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;
    }
    window.location.href = url;
    toast(`⏳ Menyiapkan Unduh Excel ${reportType}...`, 'info');
};

window.exportPdf = function (type) {
    const reportType = type || window.lastLaporanType || 'PENDAPATAN';

    // Special handling for Pengesahan modules
    if (reportType === 'LRKB' || reportType === 'SP3BP') {
        const id = window.lastLaporanData?.id || window.lastLaporanData?.periode_id;
        if (!id) {
            toast('ID Laporan tidak ditemukan', 'error');
            return;
        }
        const endpoint = reportType === 'LRKB' ? 'lrkb' : 'sp3bp';
        const url = `/dashboard/pengesahan/${endpoint}/${id}/print`;
        window.location.href = url;
        toast(`⏳ Menyiapkan Unduh PDF ${reportType}...`, 'info');
        return;
    }

    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    if (!end && !['REKON', 'DPA', 'PIUTANG', 'BKU'].includes(reportType)) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    const mapping = {
        'PENDAPATAN': 'pendapatan-pdf',
        'REKON': 'rekon-pdf',
        'PIUTANG': 'piutang-pdf',
        'MOU': 'mou-pdf',
        'ANGGARAN': 'anggaran-pdf',
        'PENGELUARAN': 'pengeluaran-pdf',
        'DPA': 'dpa-pdf',
        'BKU': 'bku-pdf'
    };

    const ptKiri = document.getElementById('ptSelectKiri')?.value || '';
    const ptTengah = document.getElementById('ptSelectTengah')?.value || '';
    const ptKanan = document.getElementById('ptSelectKanan')?.value || '';

    const isSp3bp = !!document.getElementById('sp3bpTriwulan');
    const twSelect = document.getElementById('sp3bpTriwulan');
    if (isSp3bp && (!twSelect || !twSelect.value)) {
        toast('Silakan pilih Triwulan terlebih dahulu!', 'warning');
        return;
    }

    const endpoint = mapping[reportType] || 'pendapatan-pdf';
    let url = `/dashboard/laporan/export/${endpoint}?start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;

    if (reportType === 'ANGGARAN') {
        const cat = document.getElementById('lraCategory')?.value || 'PENDAPATAN';
        url += `&category=${cat}`;

        const tw = document.getElementById('sp3bpTriwulan')?.value;
        if (tw) {
            const romans = { '1': 'I', '2': 'II', '3': 'III', '4': 'IV' };
            const year = window.tahunAnggaran || new Date().getFullYear();
            url += `&report_title=LAPORAN REALISASI PENDAPATAN, BELANJA DAN PEMBIAYAAN`;
            url += `&report_period=PERIODE TRIWULAN ${romans[tw]} ${year}`;
        }
    }
    if (reportType === 'BKU') {
        const month = document.getElementById('ledgerMonth')?.value || '';
        const year = document.getElementById('ledgerYear')?.value || '';
        url = `/dashboard/laporan/export/${endpoint}?month=${month}&year=${year}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;
    }
    window.location.href = url;
    toast(`⏳ Menyiapkan Export PDF ${reportType}...`, 'info');
};

window.numFr = (num) => {
    if (typeof num !== 'number') num = parseFloat(num) || 0;
    return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

window.fr = (num) => {
    const val = window.numFr(num);
    return `
        <div style="display: flex; justify-content: space-between; width: 100%; gap: 5px;">
            <span>Rp</span>
            <span style="text-align: right;">${val}</span>
        </div>
    `;
};

window.openPreviewModal = function (type) {
    const reportType = type || window.lastLaporanType;
    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    const titleMapping = {
        'PENDAPATAN': 'LAPORAN PENDAPATAN',
        'REKON': 'LAPORAN REKONSILIASI',
        'PIUTANG': 'LAPORAN PIUTANG',
        'MOU': 'LAPORAN KERJASAMA / MOU',
        'ANGGARAN': 'LAPORAN REALISASI ANGGARAN',
        'PENGELUARAN': 'LAPORAN REALISASI BELANJA',
        'DPA': 'LAPORAN DOKUMEN PELAKSANAAN ANGGARAN (DPA)',
        'BKU': 'BUKU KAS UMUM (BKU)',
        'LRKB': 'LAPORAN REKONSILIASI KAS BENDAHARA (LRKB)',
        'SP3BP': 'SURAT PERINTAH PENGESAHAN PENDAPATAN DAN BELANJA (SP3BP)'
    };

    // Special Title for SPTJB
    let customTitle = null;
    let customPeriod = null;
    const isSptjb = !!document.getElementById('sptjbTriwulan');
    const twSelect = document.getElementById('sptjbTriwulan');

    if (isSptjb && (!twSelect || !twSelect.value)) {
        toast('Silakan pilih Triwulan terlebih dahulu!', 'warning');
        return;
    }

    if (isSptjb && reportType === 'ANGGARAN') {
        customTitle = 'LAPORAN REALISASI PENDAPATAN, BELANJA DAN PEMBIAYAAN';
        const tw = twSelect ? twSelect.value : null;
        const year = window.tahunAnggaran || new Date().getFullYear();
        if (tw) {
            if (tw.startsWith('m')) {
                const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                const month = parseInt(tw.substring(1));
                customPeriod = `PERIODE BULAN ${monthNames[month].toUpperCase()} ${year}`;
            } else {
                const romans = { '1': 'I', '2': 'II', '3': 'III', '4': 'IV' };
                customPeriod = `PERIODE TRIWULAN ${romans[tw] || tw} ${year}`;
            }
        }
    }

    if (!window.lastLaporanData || window.lastLaporanType !== reportType || (reportType === 'BKU' && !window._bkuAlreadyReloaded)) {
        if (reportType === 'BKU') {
            // For BKU, ALWAYS reload to get the latest data (as it might have been updated on the same page)
            window._bkuAlreadyReloaded = true;
            toast('Memuat data BKU...', 'info');
            loadLaporan('BKU').then(() => {
                openPreviewModal('BKU');
                // Reset flag after a small delay to allow future reloads but prevent immediate recursion
                setTimeout(() => { window._bkuAlreadyReloaded = false; }, 100);
            });
            return;
        }
        // LRKB and SP3BP are manually populated before calling this, so they bypass this check if data exists
        if (!['LRKB', 'SP3BP'].includes(reportType)) {
            toast('Klik Tampilkan data terlebih dahulu!', 'info');
            return;
        }
    }

    // Hide Excel export for LRKB/SP3BP as they only have PDF
    const excelBtn = document.querySelector('button[onclick="exportLaporan()"]');
    if (excelBtn) {
        excelBtn.style.display = (['LRKB', 'SP3BP'].includes(reportType)) ? 'none' : 'flex';
    }

    // --- Load Penanda Tangan Dropdowns (Kiri, Tengah & Kanan) ---
    fetch('/dashboard/penanda-tangan-list')
        .then(res => res.json())
        .then(list => {
            ['Kiri', 'Tengah', 'Kanan'].forEach(side => {
                const select = document.getElementById(`ptSelect${side}`);
                if (select) {
                    const currentVal = select.value;
                    select.innerHTML = '<option value="">-- Kosong --</option>';
                    list.forEach(item => {
                        select.insertAdjacentHTML('beforeend', `<option value="${item.id}" data-jabatan="${item.jabatan}" data-pangkat="${item.pangkat}" data-nama="${item.nama}" data-nip="${item.nip}">${item.jabatan} - ${item.nama}</option>`);
                    });
                    select.value = currentVal;
                }
            });
        });

    const data = window.lastLaporanData;
    const periodeEl = document.getElementById('previewPeriode');
    const tahunEl = document.getElementById('previewTahun');
    const tahunContainer = document.getElementById('previewTahunContainer');

    if (periodeEl) {
        if (reportType === 'REKON') {
            periodeEl.innerText = `Laporan Tahunan (Tahun Anggaran Berjalan)`;
        } else if (reportType === 'DPA') {
            periodeEl.innerText = `Tahun Anggaran: ${data.tahun || window.tahunAnggaran}`;
        } else if (reportType === 'BKU') {
            periodeEl.innerText = `Periode: ${data.period || '-'}`;
        } else if (reportType === 'PIUTANG') {
            const tahunVal = document.getElementById('laporanTahun')?.value || new Date().getFullYear();
            periodeEl.innerText = `Tahun Anggaran: ${tahunVal}`;
        } else {
            periodeEl.innerText = customPeriod ? customPeriod : `Periode: ${formatTanggal(start)} s/d ${formatTanggal(end)}`;
        }
    }

    const modalMainTitle = document.getElementById('previewMainTitle');
    if (modalMainTitle) modalMainTitle.innerText = customTitle || titleMapping[reportType] || 'LAPORAN';

    const modalTitle = document.getElementById('modalReportTitle');
    if (modalTitle) modalTitle.innerText = `Preview ${customTitle || titleMapping[reportType] || 'Laporan'}`;

    const tablesContainer = document.getElementById('previewTables');
    if (!tablesContainer) return;
    tablesContainer.innerHTML = '';

    if (reportType === 'PENDAPATAN') {
        const categoryKeys = [
            'BPJS_JAMINAN', 'PASIEN_UMUM', 'KERJASAMA', 'PKL', 'MAGANG',
            'LAIN_LAIN', 'PENELITIAN', 'PERMINTAAN_DATA', 'STUDY_BANDING',
        ];

        // 1. RINGKASAN Table
        let summaryHtml = `
            <h6 style="margin:20px 0 10px; font-weight:bold; border-left:4px solid #6366f1; padding-left:10px; font-size:11pt;">1. RINGKASAN PENDAPATAN</h6>
                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 25%;">Kategori Pasien</th>
                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 10%;">Transaksi</th>
                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 22%;">Jasa RS</th>
                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 22%;">Jasa Pelayanan</th>
                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 21%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>`;

        let gCount = 0, gRs = 0, gPel = 0, gTotal = 0;
        Object.keys(data.summary).forEach(key => {
            const item = data.summary[key];
            const c = parseInt(item.count) || 0;
            const r = parseFloat(item.rs) || 0;
            const p = parseFloat(item.pelayanan) || 0;
            const t = parseFloat(item.total) || 0;
            gCount += c; gRs += r; gPel += p; gTotal += t;
            summaryHtml += `
                        <tr>
                            <td style="border:1px solid #000; padding:8px;">${key}</td>
                            <td style="border:1px solid #000; padding:8px; text-align:center;">${c}</td>
                            <td style="border:1px solid #000; padding:8px;">${fr(r)}</td>
                            <td style="border:1px solid #000; padding:8px;">${fr(p)}</td>
                            <td style="border:1px solid #000; padding:8px; font-weight:bold;">${fr(t)}</td>
                        </tr>`;
        });
        summaryHtml += `
                        <tr style="background:#f1f5f9; font-weight:bold;">
                            <td style="border:1px solid #000; padding:8px; text-align:center;">TOTAL</td>
                            <td style="border:1px solid #000; padding:8px; text-align:center;">${gCount}</td>
                            <td style="border:1px solid #000; padding:8px;">${fr(gRs)}</td>
                            <td style="border:1px solid #000; padding:8px;">${fr(gPel)}</td>
                            <td style="border:1px solid #000; padding:8px;">${fr(gTotal)}</td>
                        </tr></tbody></table>`;
        tablesContainer.innerHTML += summaryHtml;

        // 1b. JASA RS & PELAYANAN Table
        let jasaHtml = `
            <h6 style="margin:25px 0 10px; font-weight:bold; border-left:4px solid #f59e0b; padding-left:10px; font-size:11pt;">2. RINCIAN METODE JASA (RS & PELAYANAN)</h6>
                                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                                    <thead style="background:#f8fafc;">
                                        <tr>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 40%;">Uraian Akun</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Jasa Rumah Sakit</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Jasa Pelayanan</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

        let tjRs = 0, tjPel = 0, tjAll = 0;
        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item || !item.jasa) return;
            const jrs = parseFloat(item.jasa.RS) || 0;
            const jpel = parseFloat(item.jasa.PELAYANAN) || 0;
            const jtot = parseFloat(item.jasa.TOTAL) || 0;
            tjRs += jrs; tjPel += jpel; tjAll += jtot;
            jasaHtml += `
                                        <tr>
                                            <td style="border:1px solid #000; padding:8px;">${item.nama}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(jrs)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(jpel)}</td>
                                            <td style="border:1px solid #000; padding:8px; font-weight:bold;">${fr(jtot)}</td>
                                        </tr>`;
        });
        jasaHtml += `<tr style="background:#f1f5f9; font-weight:bold;">
                                            <td style="border:1px solid #000; padding:8px; text-align:center;">JUMLAH KESELURUHAN</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tjRs)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tjPel)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tjAll)}</td>
                                        </tr></tbody></table>`;
        tablesContainer.innerHTML += jasaHtml;

        // 2. METODE PEMBAYARAN Table
        let breakdownHtml = `
            <h6 style="margin:25px 0 10px; font-weight:bold; border-left:4px solid #10b981; padding-left:10px; font-size:11pt;">3. RINCIAN METODE PEMBAYARAN</h6>
                                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                                    <thead style="background:#f8fafc;">
                                        <tr>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 40%;">Uraian Akun</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Tunai</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Non-Tunai</th>
                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

        let tTunai = 0, tNon = 0, tPAll = 0;
        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;
            const tun = parseFloat(item.payments.TUNAI) || 0;
            const nonAt = parseFloat(item.payments.NON_TUNAI) || 0;
            const tot = parseFloat(item.payments.TOTAL) || 0;
            tTunai += tun; tNon += nonAt; tPAll += tot;
            breakdownHtml += `
                                        <tr>
                                            <td style="border:1px solid #000; padding:8px;">${item.nama}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tun)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(nonAt)}</td>
                                            <td style="border:1px solid #000; padding:8px; font-weight:bold;">${fr(tot)}</td>
                                        </tr>`;
        });
        breakdownHtml += `<tr style="background:#f1f5f9; font-weight:bold;">
                                            <td style="border:1px solid #000; padding:8px; text-align:center;">JUMLAH KESELURUHAN</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tTunai)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tNon)}</td>
                                            <td style="border:1px solid #000; padding:8px;">${fr(tPAll)}</td>
                                        </tr></tbody></table>`;
        tablesContainer.innerHTML += breakdownHtml;

        // 4. BANK RECEPTION Table
        let bankHtml = `
            <h6 style="margin:25px 0 10px; font-weight:bold; border-left:4px solid #3b82f6; padding-left:10px; font-size:11pt;">4. RINCIAN PENERIMAAN BANK</h6>
                                                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                                                    <thead style="background:#f8fafc;">
                                                        <tr>
                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 40%;">Uraian Akun</th>
                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">BRK</th>
                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">BSI</th>
                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>`;

        let tBRK = 0, tBSI = 0, tBAll = 0;
        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;
            const brk = parseFloat(item.banks.BRK) || 0;
            const bsi = parseFloat(item.banks.BSI) || 0;
            const tot = parseFloat(item.banks.TOTAL) || 0;
            tBRK += brk; tBSI += bsi; tBAll += tot;
            bankHtml += `
                                                        <tr>
                                                            <td style="border:1px solid #000; padding:8px;">${item.nama}</td>
                                                            <td style="border:1px solid #000; padding:8px;">${fr(brk)}</td>
                                                            <td style="border:1px solid #000; padding:8px;">${fr(bsi)}</td>
                                                            <td style="border:1px solid #000; padding:8px; font-weight:bold;">${fr(tot)}</td>
                                                        </tr>`;
        });
        bankHtml += `<tr style="background:#f1f5f9; font-weight:bold;">
                                                            <td style="border:1px solid #000; padding:8px; text-align:center;">JUMLAH PENERIMAAN BANK</td>
                                                            <td style="border:1px solid #000; padding:8px;">${fr(tBRK)}</td>
                                                            <td style="border:1px solid #000; padding:8px;">${fr(tBSI)}</td>
                                                            <td style="border:1px solid #000; padding:8px;">${fr(tBAll)}</td>
                                                        </tr></tbody></table>`;
        tablesContainer.innerHTML += bankHtml;

        // 5. ROOMS Section
        let roomHtml = `
            <h6 style="margin:25px 0 10px; font-weight:bold; border-left:4px solid #f43f5e; padding-left:10px; font-size:11pt;">5. PENDAPATAN PER RUANGAN</h6>
                                                                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                                                                    <thead style="background:#f8fafc;">
                                                                        <tr>
                                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 50%;">Nama Ruangan</th>
                                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Jumlah Pasien</th>
                                                                            <th style="border:1px solid #000; padding:8px; text-align:center; width: 35%;">Total Pendapatan</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;

        let tRCount = 0, tRTotal = 0;
        const roomEntries = Object.entries(data.rooms || {});
        roomEntries.forEach(([room, total]) => {
            const count = data.room_patients[room] || 0;
            tRCount += count; tRTotal += total;
            roomHtml += `
                                                                        <tr>
                                                                            <td style="border:1px solid #000; padding:8px;">${room}</td>
                                                                            <td style="border:1px solid #000; padding:8px; text-align:center;">${count}</td>
                                                                            <td style="border:1px solid #000; padding:8px;">${fr(total)}</td>
                                                                        </tr>`;
        });
        roomHtml += `
                                                                        <tr style="background:#f1f5f9; font-weight:bold;">
                                                                            <td style="border:1px solid #000; padding:8px; text-align:center;">GRAND TOTAL (SEMUA RUANGAN)</td>
                                                                            <td style="border:1px solid #000; padding:8px; text-align:center;">${tRCount}</td>
                                                                            <td style="border:1px solid #000; padding:8px;">${fr(tRTotal)}</td>
                                                                        </tr></tbody></table>`;
        tablesContainer.innerHTML += roomHtml;

    } else if (reportType === 'REKON') {
        let rekonHtml = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Bulan</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 22%;">Bank (Kredit)</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 22%;">Modul Netto</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 20%;">Selisih Harian</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 21%;">Selisih Kumulatif</th>
                    </tr>
                </thead>
                <tbody>`;
        data.forEach(item => {
            rekonHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${item.tanggal}</td>
                    <td style="border:1px solid #000; padding:8px;">${fr(item.bank)}</td>
                    <td style="border:1px solid #000; padding:8px;">${fr(item.pendapatan)}</td>
                    <td style="border:1px solid #000; padding:8px;">${fr(item.selisih)}</td>
                    <td style="border:1px solid #000; padding:8px; font-weight:bold;">${fr(item.kumulatif)}</td>
                </tr>`;
        });
        rekonHtml += `</tbody></table> `;
        tablesContainer.innerHTML = rekonHtml;

    } else if (reportType === 'PIUTANG') {
        let piutangHtml = `
            <div style="overflow-x: auto;">
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:7pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; width: 14%;">Perusahaan</th>
                        <th colspan="4" style="border:1px solid #000; padding:4px; text-align:center;">Saldo Awal (Tahun Lalu)</th>
                        <th colspan="4" style="border:1px solid #000; padding:4px; text-align:center;">Tahun Berjalan</th>
                        <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center;">Pel. Total</th>
                        <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center;">Pot. Total</th>
                        <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; color:#ef4444;">Sisa 2025</th>
                        <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center;">S. Akhir</th>
                    </tr>
                    <tr>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Piutang</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Lunas</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Pot</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Adm</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Piutang</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Lunas</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Pot</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center;">Adm</th>
                    </tr>
                </thead>
                <tbody>`;
        data.data.forEach(item => {
            piutangHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:4px; font-weight:bold;">${item.nama_perusahaan}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.sa_piutang)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.sa_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.sa_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.sa_adm)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.berjalan_piutang)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.berjalan_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.berjalan_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.berjalan_adm)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right; font-weight:bold;">${numFr(item.total_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.total_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right; color:#ef4444; font-weight:bold;">${numFr(item.sisa_2025)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right; font-weight:bold; background:#f8fafc;">${numFr(item.saldo_akhir)}</td>
                </tr>`;
        });
        piutangHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td style="border:1px solid #000; padding:4px; text-align:center;">GRAND TOTAL</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.sa_piutang)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.sa_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.sa_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.sa_adm)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.berjalan_piutang)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.berjalan_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.berjalan_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.berjalan_adm)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.total_pelunasan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.total_potongan)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.sisa_2025)}</td>
                    <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(data.totals.saldo_akhir)}</td>
                </tr></tbody></table></div>`;
        tablesContainer.innerHTML = piutangHtml;

    } else if (reportType === 'MOU') {
        let mouHtml = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:8pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 30%;">Nama MOU / Instansi</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 8%;">Trans</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 14%;">Jasa RS</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 14%;">Jasa Pel</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 14%;">Pot</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 10%;">Adm</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 18%;">Total Netto</th>
                    </tr>
                </thead>
                <tbody>`;
        let tT = 0, tR = 0, tP = 0, tPot = 0, tA = 0, tNet = 0;
        data.forEach(item => {
            tT += parseInt(item.count); tR += parseFloat(item.rs); tP += parseFloat(item.pelayanan);
            tPot += parseFloat(item.potongan); tA += parseFloat(item.adm_bank); tNet += parseFloat(item.total);
            mouHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:5px;">${item.nama_mou}</td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;">${item.count}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(item.rs)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(item.pelayanan)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(item.potongan)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(item.adm_bank)}</td>
                    <td style="border:1px solid #000; padding:5px; font-weight:bold;">${fr(item.total)}</td>
                </tr>`;
        });
        mouHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td style="border:1px solid #000; padding:5px; text-align:center;">TOTAL</td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;">${tT}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(tR)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(tP)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(tPot)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(tA)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(tNet)}</td>
                </tr></tbody></table>`;
        tablesContainer.innerHTML = mouHtml;

    } else if (reportType === 'ANGGARAN') {
        const isSptjb = !!document.getElementById('sptjbTriwulan');

        const getRowsHtml = (items) => {
            let html = '';
            items.forEach(item => {
                const isBold = item.level < 5;
                const isRoot = item.nama && item.nama.includes('Rumah Sakit Khusus Jiwa dan Ketergantungan Obat');

                if (isSptjb) {
                    html += `
                        <tr style="${isBold ? 'font-weight:bold; background-color:#f8fafc;' : ''}">
                            <td style="border:1px solid #000; padding:5px;">${item.kode}</td>
                            <td style="border:1px solid #000; padding:5px;">${item.nama}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.target)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.realisasi_lalu)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.realisasi_kini)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.realisasi_total)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.selisih)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:center;">${isRoot ? '' : item.persen + '%'}</td>
                        </tr>`;
                } else {
                    html += `
                        <tr style="${isBold ? 'font-weight:bold; background-color:#f8fafc;' : ''}">
                            <td style="border:1px solid #000; padding:5px;">${item.kode}</td>
                            <td style="border:1px solid #000; padding:5px;">${item.nama}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.target)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.realisasi_total)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:right;">${isRoot ? '' : numFr(item.selisih)}</td>
                            <td style="border:1px solid #000; padding:5px; text-align:center;">${isRoot ? '' : item.persen + '%'}</td>
                        </tr>`;
                }
            });
            return html;
        };

        const getTotalRowHtml = (title, totals) => {
            if (isSptjb) {
                return `
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="2" style="border:1px solid #000; padding:5px; text-align:center;">TOTAL ${title}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.target)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.real_lalu)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.real_kini)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.real)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.target - totals.real)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:center;">${totals.persen}%</td>
                    </tr>`;
            } else {
                return `
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="2" style="border:1px solid #000; padding:5px; text-align:center;">TOTAL ${title}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.target)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.real)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:right;">${numFr(totals.target - totals.real)}</td>
                        <td style="border:1px solid #000; padding:5px; text-align:center;">${totals.persen}%</td>
                    </tr>`;
            }
        };

        let tableBody = '';
        const cat = document.getElementById('lraCategory')?.value || 'SEMUA';

        if (cat === 'SEMUA') {
            tableBody += getRowsHtml(data.data_pendapatan);
            tableBody += getTotalRowHtml('PENDAPATAN', data.sub_totals.pendapatan);
            tableBody += getRowsHtml(data.data_pengeluaran);
            tableBody += getTotalRowHtml('BELANJA (PENGELUARAN)', data.sub_totals.pengeluaran);

            // Surplus row
            const diff = data.totals.target - data.totals.realisasi_total;
            if (isSptjb) {
                tableBody += `
                    <tr style="background:#e2e8f0; font-weight:bold; font-size:10pt;">
                        <td colspan="2" style="border:1px solid #000; padding:8px; text-align:center;">SURPLUS / (DEFISIT) ANGGARAN</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${numFr(data.totals.target)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${numFr(data.totals.realisasi_lalu)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${numFr(data.totals.realisasi_kini)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${numFr(data.totals.realisasi_total)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:right;">${numFr(diff)}</td>
                        <td style="border:1px solid #000; padding:8px; text-align:center;">${data.totals.persen}%</td>
                    </tr>`;
            } else {
                tableBody += `
                    <tr style="background:#e2e8f0; font-weight:bold; font-size:10pt;">
                        <td colspan="2" style="border:1px solid #000; padding:10px; text-align:center;">SURPLUS / (DEFISIT) ANGGARAN</td>
                        <td style="border:1px solid #000; padding:10px; text-align:right;">${numFr(data.totals.target)}</td>
                        <td style="border:1px solid #000; padding:10px; text-align:right;">${numFr(data.totals.realisasi_total)}</td>
                        <td style="border:1px solid #000; padding:10px; text-align:right;">${numFr(diff)}</td>
                        <td style="border:1px solid #000; padding:10px; text-align:center;">${data.totals.persen}%</td>
                    </tr>`;
            }
        } else {
            tableBody += getRowsHtml(data.data);
            tableBody += getTotalRowHtml(cat === 'PENGELUARAN' ? 'BELANJA' : 'PENDAPATAN', {
                target: data.totals.target,
                real: data.totals.realisasi_total,
                real_lalu: data.totals.realisasi_lalu,
                real_kini: data.totals.realisasi_kini,
                persen: data.totals.persen
            });
        }

        const headerHtml = isSptjb ? `
            <tr>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 10%;">Kode</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 25%;">Uraian</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 11%;">Target</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 11%;">Real. Lalu</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 11%;">Real. Kini</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 11%;">Real. Total</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 10%;">Selisih</th>
                <th style="border:1px solid #000; padding:5px; text-align:center; width: 5%;">%</th>
            </tr>
        ` : `
            <tr>
                <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Kode Rekening</th>
                <th style="border:1px solid #000; padding:8px; text-align:center;">Uraian</th>
                <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Target</th>
                <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Realisasi</th>
                <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Selisih</th>
                <th style="border:1px solid #000; padding:8px; text-align:center; width: 10%;">%</th>
            </tr>
        `;

        tablesContainer.innerHTML = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:8pt;">
                <thead style="background:#f8fafc;">
                    ${headerHtml}
                </thead>
                <tbody>
                    ${tableBody}
                </tbody>
            </table>
        `;
    } else if (reportType === 'PENGELUARAN') {
        let expHtml = `
            <h6 style="margin:20px 0 10px; font-weight:bold; border-left:4px solid #10b981; padding-left:10px; font-size:11pt;">1. RINGKASAN PER KATEGORI</h6>
            <table style="width:100%; border-collapse:collapse; margin-bottom:25px; font-size:9pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Kategori Belanja</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Jumlah Transaksi</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Total Nominal</th>
                    </tr>
                </thead>
                <tbody>`;

        let gC = 0, gT = 0;
        const labels = { 'PEGAWAI': 'Belanja Pegawai', 'BARANG_JASA': 'Belanja Barang & Jasa', 'MODAL': 'Belanja Modal' };
        Object.keys(labels).forEach(key => {
            const item = data.summary[key] || { count: 0, total: 0 };
            gC += parseInt(item.count); gT += parseFloat(item.total);
            expHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:8px;">${labels[key]}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${item.count}</td>
                    <td style="border:1px solid #000; padding:8px;">${fr(item.total)}</td>
                </tr>`;
        });
        expHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td style="border:1px solid #000; padding:8px; text-align:center;">TOTAL KESELURUHAN</td>
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${gC}</td>
                    <td style="border:1px solid #000; padding:8px;">${fr(gT)}</td>
                </tr></tbody></table>

            <h6 style="margin:20px 0 10px; font-weight:bold; border-left:4px solid #3b82f6; padding-left:10px; font-size:11pt;">2. RINCIAN PER KODE REKENING</h6>
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Kode</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 35%;">Nama Rekening</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 12.5%;">UP</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 12.5%;">GU</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 12.5%;">LS</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 12.5%;">Total</th>
                    </tr>
                </thead>
                <tbody>`;
        data.data.forEach(item => {
            expHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:8px; text-align:center;"><code>${item.kode}</code></td>
                    <td style="border:1px solid #000; padding:8px;">${item.nama}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(item.up || 0)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(item.gu || 0)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(item.ls || 0)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(item.total)}</td>
                </tr>`;
        });
        expHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td colspan="2" style="border:1px solid #000; padding:8px; text-align:center;">TOTAL KESELURUHAN</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(data.data.reduce((a, b) => a + (parseFloat(b.up) || 0), 0))}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(data.data.reduce((a, b) => a + (parseFloat(b.gu) || 0), 0))}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(data.data.reduce((a, b) => a + (parseFloat(b.ls) || 0), 0))}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${fr(data.data.reduce((a, b) => a + (parseFloat(b.total) || 0), 0))}</td>
                </tr>
            </tbody></table>`;
        tablesContainer.innerHTML = expHtml;
    } else if (reportType === 'DPA') {
        let html = `
            <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:9pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Kode Rekening</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center;">Uraian Rekening / Komponen</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 8%;">Vol</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 10%;">Satuan</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 18%;">Tarif Satuan</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 18%;">Total</th>
                    </tr>
                </thead>
                <tbody>`;

        let gTotal = 0;
        data.data.forEach(item => {
            const isHeader = item.tipe === 'header';
            const indent = (item.level - 1) * 15;

            if (isHeader && item.level === 1) {
                gTotal += parseFloat(item.subtotal) || 0;
            }

            const valVolume = isHeader ? '' : (item.volume ? parseFloat(item.volume) : '');
            const valSatuan = isHeader ? '' : (item.satuan || '');
            const valTarif = isHeader ? '' : fr(item.tarif);

            html += `
                <tr style="${isHeader ? 'background:#f8fafc; font-weight:bold;' : ''}">
                    <td style="border:1px solid #000; padding:8px; text-align:left; font-family:monospace;">
                        ${item.kode_rekening || ''}
                    </td>
                    <td style="border:1px solid #000; padding:8px; text-align:left;">
                        ${escapeHtml(item.uraian)}
                    </td>
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${valVolume}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${valSatuan}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">${valTarif}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right; font-weight:bold;">${fr(item.subtotal)}</td>
                </tr>`;
        });

        html += `
                <tr style="background:#f8fafc; font-weight:bold;">
                    <td colspan="5" style="border:1px solid #000; padding:10px; text-align:center; font-size:10pt;">TOTAL ANGGARAN DPA</td>
                    <td style="border:1px solid #000; padding:10px; text-align:right; font-size:10pt;">${fr(gTotal)}</td>
                </tr>
            </tbody>
        </table>`;
        tablesContainer.innerHTML = html;
    } else if (reportType === 'BKU') {
        let bkuHtml = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:7pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 25px;">No</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 60px;">Tanggal</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 90px;">No Bukti</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 150px;">Uraian</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 100px;">Kode Rek</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 85px;">Transfer Penerimaan</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 85px;">Pengajuan SP2D</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 85px;">Realisasi</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 85px;">Saldo Dana</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 85px;">Saldo Rekening Koran</th>
                        <th style="border:1px solid #000; padding:4px; text-align:center; width: 90px;">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>

                    ${data.data.map((item, i) => `
                    <tr>
                        <td style="border:1px solid #000; padding:4px; text-align:center;">${i + 1}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:center;">${formatTanggal(item.date)}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:center; font-size:7pt;">${item.no_bukti || '-'}</td>
                        <td style="border:1px solid #000; padding:4px; font-size:7pt;">${item.uraian || '-'}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:center; font-size:7pt;">${item.kode_rekening || '-'}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right;">${item.transfer_penerimaan > 0 ? numFr(item.transfer_penerimaan) : '-'}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right;">${item.sp2d_penerimaan > 0 ? numFr(item.sp2d_penerimaan) : '-'}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right;">${item.realisasi > 0 ? numFr(item.realisasi) : '-'}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.saldo_tunai)}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right;">${numFr(item.saldo_bank)}</td>
                        <td style="border:1px solid #000; padding:4px; text-align:right; font-weight:bold;">${numFr(item.saldo_akhir)}</td>
                    </tr>`).join('')}

                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="5" style="border:1px solid #000; padding:6px; text-align:center;">TOTAL MUTASI & SALDO AKHIR</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.total_debit_transfer)}</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.total_debit_sp2d)}</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.total_credit_realisasi)}</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.final_tunai)}</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.final_bank)}</td>
                        <td style="border:1px solid #000; padding:6px; text-align:right;">${numFr(data.summary.final_balance)}</td>
                    </tr>
                </tbody>
            </table>`;
        tablesContainer.innerHTML = bkuHtml;

        // Add special BKU footer info
        const bkuFooter = `
            <div style="margin-top:20px; font-size:9pt; font-family: 'Inter', sans-serif;">
                <div style="display:flex; margin-bottom:4px;">
                    <div style="width:250px;">Jumlah Penarikan Cek sampai periode ini</div>
                    <div style="width:20px;">:</div>
                    <div style="font-weight:bold;">Rp ${numFr(data.summary.ytd_receipts)}</div>
                </div>
                <div style="display:flex; margin-bottom:15px;">
                    <div style="width:250px;">Jumlah Pengeluaran sampai periode ini</div>
                    <div style="width:20px;">:</div>
                    <div style="font-weight:bold;">Rp ${numFr(data.summary.ytd_expenditures)}</div>
                </div>
                
                <div style="margin-top:15px;">
                    <div style="font-weight:bold; text-decoration:underline;">Catatan :</div>
                    <div style="display:flex; margin-top:4px;">
                        <div style="width:250px;">Saldo Rekening Per akhir bulan</div>
                        <div style="width:20px;">:</div>
                        <div style="font-weight:bold;">Rp ${numFr(data.summary.final_bank)}</div>
                    </div>
                    <div style="display:flex; margin-top:2px; font-style: italic; color: #4b5563; font-size: 8.5pt;">
                        <div style="width:250px; padding-left: 20px;">- Bank Riau Kepri Syariah</div>
                        <div style="width:20px;">:</div>
                        <div>Rp ${numFr(data.summary.final_bank_brk || 0)}</div>
                    </div>
                    <div style="display:flex; margin-top:2px; font-style: italic; color: #4b5563; font-size: 8.5pt;">
                        <div style="width:250px; padding-left: 20px;">- Bank Syariah Indonesia</div>
                        <div style="width:20px;">:</div>
                        <div>Rp ${numFr(data.summary.final_bank_bsi || 0)}</div>
                    </div>
                </div>
            </div>
        `;
        tablesContainer.insertAdjacentHTML('beforeend', bkuFooter);
    } else if (reportType === 'LRKB') {
        const triwulans = ["", "I (SATU)", "II (DUA)", "III (TIGA)", "IV (EMPAT)"];
        const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const data = window.lastLaporanData;

        const labelPeriode = data.triwulan ? `TRIWULAN ${triwulans[data.triwulan] || data.triwulan}` : (months[data.bulan] || '-').toUpperCase();
        const periodStr = `${labelPeriode} TAHUN ${data.tahun}`;

        const saldoBuku = data.saldo_akhir_buku;
        const saldoFisik = data.saldo_fisik;
        const selisihFinal = saldoBuku - saldoFisik;

        // Extract detailed flows
        const d_bank_in = (data.details || []).find(d => d.jenis === 'bank_masuk')?.jumlah || 0;
        const d_bank_out = (data.details || []).find(d => d.jenis === 'bank_keluar')?.jumlah || 0;
        const d_tunai_in = (data.details || []).find(d => d.jenis === 'tunai_masuk')?.jumlah || 0;
        const d_tunai_out = (data.details || []).find(d => d.jenis === 'tunai_keluar')?.jumlah || 0;

        let html = `
            <div style="padding: 20px; font-family: 'Inter', sans-serif;">

                <table style="width: 100%; border-collapse: collapse; border: 1.5px solid black; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; font-weight: bold; text-transform: uppercase;">
                            <th style="border: 1px solid black; padding: 12px; text-align: center;">URAIAN PEMBUKUAN DAN KAS</th>
                            <th style="border: 1px solid black; padding: 12px; width: 300px; text-align: center;">JUMLAH (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- BAGIAN SALDO AWAL -->
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; font-weight: bold; background: #fff;">SALDO AWAL KAS</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; padding-left: 30px;">Saldo Awal Triwulan Sebelumnya / 1 Januari</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;">${numFr(data.saldo_awal)}</td>
                        </tr>
                        <tr style="font-weight: bold; background: #fdfdfd;">
                            <td style="border: 1px solid black; padding: 10px;">TOTAL SALDO AWAL</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;">${numFr(data.saldo_awal)}</td>
                        </tr>

                        <!-- BAGIAN PENERIMAAN -->
                        <tr style="height: 10px;"><td colspan="2" style="border: 1px solid black;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; font-weight: bold;">PENERIMAAN KAS</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; padding-left: 30px;">Penerimaan Selama Periode Ini</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;">${numFr(data.pendapatan)}</td>
                        </tr>

                        <!-- BAGIAN PENGELUARAN -->
                        <tr style="height: 10px;"><td colspan="2" style="border: 1px solid black;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; font-weight: bold;">PENGELUARAN KAS</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 10px; padding-left: 30px;">Pengeluaran Selama Periode Ini</td>
                            <td style="border: 1px solid black; padding: 10px; text-align: right;">(${numFr(data.belanja)})</td>
                        </tr>

                        <!-- SALDO AKHIR BUKU -->
                        <tr style="background: #f1f5f9; font-weight: 800; font-size: 14px;">
                            <td style="border: 1px solid black; padding: 15px;">SALDO AKHIR MENURUT PEMBUKUAN (BKU)</td>
                            <td style="border: 1px solid black; padding: 15px; text-align: right; border-bottom: 4px double black;">${numFr(saldoBuku)}</td>
                        </tr>

                        <!-- BAGIAN FISIK -->
                        <tr style="height: 20px;"><td colspan="2" style="border: 1px solid black; background: #fafafa;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; font-weight: bold; text-transform: uppercase;">POSISI KAS NYATA / FISIK (REAL)</td>
                            <td style="border: 1px solid black; padding: 12px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px;">- Saldo Bank (Penerimaan)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; color: #10b981;">+ ${numFr(d_bank_in)}</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px;">- Saldo Bank (Pengeluaran)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; color: #ef4444;">- ${numFr(d_bank_out)}</td>
                        </tr>
                        <tr style="font-weight: 600;">
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px;">Sub-Total Saldo Bank (Rekening Koran)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; background: #fbfbfb;">${numFr(data.saldo_bank)}</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px; border-top: 1px dashed #ccc;">- Saldo Kas Tunai (Penerimaan)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; border-top: 1px dashed #ccc; color: #10b981;">+ ${numFr(d_tunai_in)}</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px;">- Saldo Kas Tunai (Pengeluaran)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; color: #ef4444;">- ${numFr(d_tunai_out)}</td>
                        </tr>
                        <tr style="font-weight: 600;">
                            <td style="border: 1px solid black; padding: 8px; padding-left: 30px;">Sub-Total Saldo Kas Tunai (Brankas)</td>
                            <td style="border: 1px solid black; padding: 8px; text-align: right; background: #fbfbfb;">${numFr(data.saldo_tunai)}</td>
                        </tr>
                        <tr style="font-weight: 800; background: #f4f4f4; font-size: 14px;">
                            <td style="border: 1px solid black; padding: 12px; text-transform: uppercase;">TOTAL SALDO AKHIR MENURUT KAS FISIK</td>
                            <td style="border: 1px solid black; padding: 12px; text-align: right; border-bottom: 4px double black;">${numFr(saldoFisik)}</td>
                        </tr>

                        <!-- SELISIH -->
                        <tr style="background: ${selisihFinal == 0 ? '#f0fdf4' : '#fef2f2'}; font-weight: 900; font-size: 15px;">
                            <td style="border: 1px solid black; padding: 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>SELISIH KAS</span>
                                    <span style="font-size: 12px; font-weight: 600; font-style: italic; color: ${selisihFinal == 0 ? '#15803d' : '#b91c1c'};">
                                        ${selisihFinal == 0 ? '* Kas dalam keadaan sinkron / balance' : '* Terdapat perbedaan antara saldo pembukuan dan fisik'}
                                    </span>
                                </div>
                            </td>
                            <td style="border: 1px solid black; padding: 20px; text-align: right; border-bottom: 4px double black;">${numFr(selisihFinal)}</td>
                        </tr>
                        ${data.catatan_selisih ? `
                        <tr>
                            <td colspan="2" style="border:1px solid black; padding:12px; background:#fff;">
                                <strong>Catatan Rekonsiliasi:</strong><br>
                                <div style="margin-top:5px; color:#475569; font-style:italic;">"${data.catatan_selisih}"</div>
                            </td>
                        </tr>` : ''}
                    </tbody>
                </table>

                <div style="margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr; gap: 100px; text-align: center; font-size: 13px;">
                    <div>
                        <p style="margin-bottom: 80px;">Mengetahui/Menyetujui,<br>Pejabat Pengelola Keuangan</p>
                        <p style="font-weight: bold; text-decoration: underline; margin: 0;">( ........................................ )</p>
                        <p style="margin: 0;">NIP. ........................................</p>
                    </div>
                    <div>
                        <p style="margin-bottom: 80px;">Semarang, ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}<br>Bendahara Pengeluaran</p>
                        <p style="font-weight: bold; text-decoration: underline; margin: 0;">( ........................................ )</p>
                        <p style="margin: 0;">NIP. ........................................</p>
                    </div>
                </div>
            </div>
        `;
        tablesContainer.innerHTML = html;
        if (periodeEl) {
            periodeEl.innerText = `PERIODE TRIWULAN ${triwulans[data.triwulan]} TAHUN ${data.tahun}`;
        }
    } else if (reportType === 'SP3BP') {
        const triwulans = ["", "Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV"];
        const data = window.lastLaporanData;
        const periodStr = `${triwulans[data.periode?.triwulan] || '-'} ${data.periode?.tahun || '-'}`;

        let html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div style="border: 1px solid black; padding: 10px;">
                    <h4 style="margin-top:0; border-bottom: 1px solid black; padding-bottom: 5px;">Ringkasan Kas</h4>
                    <table style="width: 100%; font-size: 10pt;">
                        <tr><td>Saldo Awal</td><td style="text-align: right;">${numFr(data.saldo_awal)}</td></tr>
                        <tr><td>Total Pendapatan</td><td style="text-align: right;">${numFr(data.pendapatan)}</td></tr>
                        <tr><td>Total Belanja</td><td style="text-align: right; color: red;">(${numFr(data.belanja)})</td></tr>
                        <tr style="font-weight: bold; border-top: 1px solid black;"><td>Saldo Akhir</td><td style="text-align: right;">${numFr(data.saldo_akhir)}</td></tr>
                    </table>
                </div>
                <div style="border: 1px solid black; padding: 10px;">
                    <h4 style="margin-top:0; border-bottom: 1px solid black; padding-bottom: 5px;">Rekonsiliasi BKU</h4>
                    <table style="width: 100%; font-size: 10pt;">
                        <tr><td>Saldo Bank</td><td style="text-align: right;">${numFr(data.rekonsiliasi?.saldo_bank || 0)}</td></tr>
                        <tr><td>Saldo Tunai</td><td style="text-align: right;">${numFr(data.rekonsiliasi?.saldo_tunai || 0)}</td></tr>
                        <tr style="font-weight: bold; border-top: 1px solid black;"><td>Total Buku</td><td style="text-align: right;">${numFr(data.rekonsiliasi?.saldo_buku || 0)}</td></tr>
                        <tr style="color: ${data.selisih == 0 ? 'green' : 'red'}"><td>Selisih</td><td style="text-align: right;">${numFr(data.selisih || 0)}</td></tr>
                    </table>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 5px;">Detail Pendapatan</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                    <thead><tr style="background: #f2f2f2;"><th style="border: 1px solid black; padding: 4px; width: 30px;">No</th><th style="border: 1px solid black; padding: 4px; width: 100px;">Kode</th><th style="border: 1px solid black; padding: 4px;">Uraian</th><th style="border: 1px solid black; padding: 4px; width: 100px;">Jumlah</th></tr></thead>
                    <tbody>
                        ${(data.detail_pendapatan || []).map((item, idx) => `
                            <tr><td style="border: 1px solid black; padding: 4px; text-align: center;">${idx + 1}</td><td style="border: 1px solid black; padding: 4px;">${item.kode_rekening}</td><td style="border: 1px solid black; padding: 4px;">${item.uraian}</td><td style="border: 1px solid black; padding: 4px; text-align: right;">${numFr(item.jumlah)}</td></tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <div>
                <h4 style="margin-bottom: 5px;">Detail Belanja</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                    <thead><tr style="background: #f2f2f2;"><th style="border: 1px solid black; padding: 4px; width: 30px;">No</th><th style="border: 1px solid black; padding: 4px; width: 100px;">Kode</th><th style="border: 1px solid black; padding: 4px;">Uraian</th><th style="border: 1px solid black; padding: 4px; width: 100px;">Jumlah</th></tr></thead>
                    <tbody>
                        ${(data.detail_belanja || []).map((item, idx) => `
                            <tr><td style="border: 1px solid black; padding: 4px; text-align: center;">${idx + 1}</td><td style="border: 1px solid black; padding: 4px;">${item.kode_rekening}</td><td style="border: 1px solid black; padding: 4px;">${item.uraian}</td><td style="border: 1px solid black; padding: 4px; text-align: right;">${numFr(item.jumlah)}</td></tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        tablesContainer.innerHTML = html;
        if (periodeEl) {
            periodeEl.innerText = `PERIODE ${periodStr.toUpperCase()}`;
        }
    }

    // Initial Signatory Setup (Trio)
    ['Kiri', 'Tengah', 'Kanan'].forEach(side => {
        const ptJabatan = document.getElementById(`previewPtJabatan${side}`);
        const ptNama = document.getElementById(`previewPtNama${side}`);
        const ptNip = document.getElementById(`previewPtNip${side}`);
        const ptPangkat = document.getElementById(`previewPtPangkat${side}`);
        const ptSelect = document.getElementById(`ptSelect${side}`);
        const ptArea = document.getElementById(`ptPreviewArea${side}`);

        if (ptJabatan) ptJabatan.innerText = '';
        if (ptNama) ptNama.innerText = '...................................';
        if (ptNip) ptNip.innerText = 'NIP. ...................................';
        if (ptSelect) ptSelect.value = '';
        if (ptArea) ptArea.style.visibility = (side === 'Kanan' ? 'visible' : 'hidden');
    });

    const modal = document.getElementById('previewLaporanModal');
    if (modal) modal.classList.add('show');
};
window.closePreviewModal = function () {
    const modal = document.getElementById('previewLaporanModal');
    if (modal) modal.classList.remove('show');
};

window.updateSignatory = function (side) {
    const select = document.getElementById(`ptSelect${side}`);
    const area = document.getElementById(`ptPreviewArea${side}`);
    const opt = select.options[select.selectedIndex];

    if (!opt.value) {
        document.getElementById(`previewPtJabatan${side}`).innerText = '';
        document.getElementById(`previewPtNama${side}`).innerText = '...................................';
        document.getElementById(`previewPtNip${side}`).innerText = 'NIP. ...................................';
        if (area && (side === 'Kiri' || side === 'Tengah')) area.style.visibility = 'hidden';
        return;
    }

    if (area) area.style.visibility = 'visible';

    const jabatan = opt.getAttribute('data-jabatan');
    const pangkat = opt.getAttribute('data-pangkat');
    const nama = opt.getAttribute('data-nama');
    const nip = opt.getAttribute('data-nip');

    document.getElementById(`previewPtJabatan${side}`).innerText = jabatan;
    document.getElementById(`previewPtNama${side}`).innerText = `${nama}`;
    document.getElementById(`previewPtNip${side}`).innerText = nip ? `NIP. ${nip}` : '';
};
function renderDPA(data) {
    const tbody = document.getElementById('laporanDPABody');
    if (!tbody) return;

    if (!data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data rincian anggaran untuk tahun ini.</td></tr>';
        return;
    }

    tbody.innerHTML = '';
    let grandTotal = 0;

    data.data.forEach(item => {
        const isHeader = item.tipe === 'header';
        const indent = (item.level - 1) * 20;

        // Only sum root level headers for grand total (level 1)
        if (isHeader && item.level === 1) {
            grandTotal += parseFloat(item.subtotal) || 0;
        }

        const valVolume = isHeader ? '' : (item.volume ? parseFloat(item.volume) : '');
        const valSatuan = isHeader ? '' : (item.satuan || '');
        const valTarif = isHeader ? '' : formatRupiahTable(item.tarif);
        const valSubtotal = formatRupiahTable(item.subtotal);

        tbody.insertAdjacentHTML('beforeend', `
            <tr style="${isHeader ? 'background:#f8fafc; font-weight:700;' : ''}">
                <td class="text-left">
                    <code style="background:${isHeader ? '#e2e8f0' : '#f1f5f9'}; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode_rekening || ''}</code>
                </td>
                <td style="text-align: left; padding: 15px 12px; ${!isHeader ? 'font-style: italic; color: #475569;' : ''}">
                    ${escapeHtml(item.uraian)}
                </td>
                <td class="text-center">${valVolume}</td>
                <td class="text-center">${valSatuan}</td>
                <td style="text-align:right">${valTarif}</td>
                <td style="text-align:right; font-weight:700;">${valSubtotal}</td>
            </tr>
        `);
    });

    tbody.insertAdjacentHTML('beforeend', `
        <tr style="background:#f1f5f9; font-weight:900; border-top: 2px solid #cbd5e1;">
            <td colspan="5" style="text-align:center; padding:15px; font-size:14px;">TOTAL KESELURUHAN (DPA)</td>
            <td style="text-align:right; padding:15px;">${formatRupiahTable(grandTotal)}</td>
        </tr>
    `);
}
/* =========================
   SP3BP MODULE LOGIC
========================= */
window.initSp3bp = function () {
    loadSp3bpList();
};

window.loadSp3bpList = async function () {
    const tbody = document.getElementById('sp3bpBody');
    if (!tbody) return;

    try {
        const res = await fetch('/dashboard/pengesahan/sp3bp');
        const data = await res.json();

        tbody.innerHTML = '';
        const triwulans = ["", "Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV"];
        const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Belum ada data. Klik + Tambah Periode.</td></tr>';
            return;
        }

        data.forEach((item, index) => {
            const labelPeriode = item.triwulan ? triwulans[item.triwulan] : months[item.bulan];
            const statusLabel = (item.status || 'DRAFT').toUpperCase();
            const statusClass = item.status === 'disahkan' ? 'badge-success' : 'badge-warning';

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">${labelPeriode || '-'}</td>
                    <td class="text-center">${item.tahun}</td>
                    <td class="text-center"><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td class="text-center">${item.tgl_pengesahan || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action-circle generate" title="Generate Data" 
                                onclick="openConfirm('Generate SP3BP', 'Kalkulasi data pengesahan?', () => generateSp3bp(${item.id}), 'Generate', 'ph-arrows-counter-clockwise', 'btn-primary')">
                                <i class="ph ph-arrows-counter-clockwise"></i>
                            </button>
                            <button class="btn-action-circle view" title="Lihat Detail" onclick="openSp3bpDetail(${item.id})">
                                <i class="ph ph-eye"></i>
                            </button>
                            <button class="btn-action-circle delete" title="Hapus" onclick="deleteSp3bp(${item.id})">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>';
    }
};

window.deleteSp3bp = function (id) {
    openConfirm('Hapus Periode', 'Apakah Anda yakin ingin menghapus periode pengesahan ini? Seluruh data yang sudah di-generate akan ikut terhapus.', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('Periode berhasil dihapus', 'success');
                loadSp3bpList();
            }
        } catch (e) {
            toast('Gagal menghapus periode', 'error');
        }
    });
};

window.showNewSp3bpModal = function () {
    const modal = document.getElementById('newSp3bpModal');
    if (modal) modal.classList.add('show');
};

window.closeSp3bpModal = function () {
    const modal = document.getElementById('newSp3bpModal');
    if (modal) modal.classList.remove('show');
};

window.createSp3bpPeriod = async function () {
    const rawVal = document.getElementById('newSp3bpTriwulan').value;
    const tahun = document.getElementById('newSp3bpYear').value;

    let triwulan = null;
    let bulan = null;

    if (rawVal.startsWith('T')) triwulan = rawVal.substring(1);
    else if (rawVal.startsWith('M')) bulan = rawVal.substring(1);

    try {
        const res = await fetch('/dashboard/pengesahan/sp3bp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ triwulan, bulan, tahun })
        });

        const data = await res.json();
        if (data.error) {
            toast(data.error, 'error');
        } else {
            toast('Periode SP3BP berhasil dibuat', 'success');
            closeSp3bpModal();
            loadSp3bpList();
        }
    } catch (e) {
        toast('Gagal membuat periode', 'error');
    }
};

window.openSp3bpDetail = async function (id) {
    // This will open a dedicated detail view
    // For now, let's just alert
    // I will implement a detail view next
    showSp3bpPreview(id);
};

window.showSp3bpPreview = async function (id) {
    const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}`);
    const data = await res.json();

    if (data.success === false) {
        toast('Data belum di-generate. Silakan klik tombol Generate (ikon putar) terlebih dahulu.', 'warning');
        return;
    }

    renderSp3bpDetail(data);
};

window.generateSp3bp = async function (id) {
    const btn = document.querySelector(`.btn-table-view`);
    if (btn) btn.disabled = true;

    try {
        const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}/generate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.error) toast(data.error, 'error');
        else {
            toast('Data SP3BP berhasil dikalkulasi', 'success');
            showSp3bpPreview(id);
        }
    } catch (e) {
        toast('Gagal generate data', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
};

window.renderSp3bpDetail = function (data) {
    const container = document.querySelector('.laporan');
    if (!container) return;

    const triwulans = ["", "Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV"];
    const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const bulan = data.periode?.bulan || 0;
    const triwulan = data.periode?.triwulan || 0;
    const tahun = data.periode?.tahun || '-';

    let labelPeriode = triwulan ? triwulans[triwulan] : months[bulan];
    const periodStr = `${labelPeriode || '-'} ${tahun}`;

    let html = `
        <div class="sp3bp-detail-container" style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
                <div>
                    <h2 style="font-size: 20px; color: #1e293b; margin: 0;">SP3BP Unit SKPD</h2>
                    <p style="color: #64748b; margin: 5px 0;">Periode: ${periodStr}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button class="btn-filter" style="background: transparent; border: 1px solid #e2e8f0; color: #64748b;" onclick="openPengesahan('SP3BP')">
                        <i class="ph ph-arrow-left"></i> Kembali
                    </button>
                    ${data.status === 'draft' ? `<button class="btn-preview" style="background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe;" onclick="generateSp3bp(${data.periode_id})"><i class="ph ph-arrows-counter-clockwise"></i> Re-Generate</button>` : ''}
                    ${data.status === 'draft' ? `<button class="btn-filter" style="background: #10b981;" onclick="sahkanSp3bp(${data.periode_id})"><i class="ph ph-check-circle"></i> Sahkan SP3BP</button>` : ''}
                    ${data.status === 'final' ? `<button class="btn-filter" style="background: #f59e0b;" onclick="batalSahkanSp3bp(${data.periode_id})"><i class="ph ph-lock-key-open"></i> Buka Pengesahan</button>` : ''}
                    <button class="btn-preview" onclick="printSp3bp(${data.periode_id})">
                        <i class="ph ph-file-pdf"></i> Review & Unduh
                    </button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="dashboard-box" style="padding: 15px;">
                    <h4 style="margin-bottom:10px;">Ringkasan Kas</h4>
                    <table style="width: 100%; font-size: 14px;">
                        <tr><td>Saldo Awal</td><td class="text-right">${formatRupiahTable(data.saldo_awal)}</td></tr>
                        <tr><td>Total Pendapatan</td><td class="text-right">${formatRupiahTable(data.pendapatan)}</td></tr>
                        <tr><td>Total Belanja</td><td class="text-right text-danger">(${formatRupiahTable(data.belanja)})</td></tr>
                        <tr style="font-weight: bold; border-top: 1px solid #e2e8f0;"><td>Saldo Akhir</td><td class="text-right">${formatRupiahTable(data.saldo_akhir)}</td></tr>
                    </table>
                </div>
                <div class="dashboard-box" style="padding: 15px;">
                    <h4 style="margin-bottom:10px;">Rekonsiliasi Kas (Fisik)</h4>
                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                        <tr>
                            <td style="color: #64748b;">Bank (Penerimaan)</td>
                            <td class="text-right" style="color: #10b981;">+ ${formatRupiahTable(data.rekonsiliasi?.bank_masuk || 0)}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Bank (Pengeluaran)</td>
                            <td class="text-right" style="color: #ef4444;">- ${formatRupiahTable(data.rekonsiliasi?.bank_keluar || 0)}</td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="font-weight: 600;">Sub-Total Bank</td>
                            <td class="text-right" style="font-weight: 600; background: #fafafa;">${formatRupiahTable(data.rekonsiliasi?.saldo_bank || 0)}</td>
                        </tr>
                        <tr style="height: 5px;"><td></td><td></td></tr>
                        <tr>
                            <td style="color: #64748b;">Tunai (Penerimaan)</td>
                            <td class="text-right" style="color: #10b981;">+ ${formatRupiahTable(data.rekonsiliasi?.tunai_masuk || 0)}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b;">Tunai (Pengeluaran)</td>
                            <td class="text-right" style="color: #ef4444;">- ${formatRupiahTable(data.rekonsiliasi?.tunai_keluar || 0)}</td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="font-weight: 600;">Sub-Total Tunai</td>
                            <td class="text-right" style="font-weight: 600; background: #fafafa;">${formatRupiahTable(data.rekonsiliasi?.saldo_tunai || 0)}</td>
                        </tr>
                        <tr style="font-weight: bold; border-top: 1px solid #1e293b; background: #f8fafc;">
                            <td>Total Kas Fisik</td>
                            <td class="text-right">${formatRupiahTable(data.rekonsiliasi?.saldo_buku || 0)}</td>
                        </tr>
                        <tr style="color: ${data.selisih == 0 ? '#10b981' : '#ef4444'}; font-weight: bold;">
                            <td>Selisih Kas</td>
                            <td class="text-right">${formatRupiahTable(data.selisih || 0)}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                     <h4 style="margin-bottom:10px;">Detail Pendapatan</h4>
                     <table class="laporan-table" style="font-size: 12px;">
                        <thead><tr><th style="width: 40px;">No</th><th>Kode</th><th>Uraian</th><th>Jumlah</th></tr></thead>
                        <tbody>
                            ${(data.detail_pendapatan || []).map((item, idx) => `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>${item.kode_rekening}</td>
                                    <td>${item.uraian}</td>
                                    <td class="text-right">${formatRupiahTable(item.jumlah)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                     </table>
                </div>
                <div>
                     <h4 style="margin-bottom:10px;">Detail Belanja</h4>
                     <table class="laporan-table" style="font-size: 12px;">
                        <thead><tr><th style="width: 40px;">No</th><th>Kode</th><th>Uraian</th><th>Jumlah</th></tr></thead>
                        <tbody>
                            ${(data.detail_belanja || []).map((item, idx) => `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>${item.kode_rekening}</td>
                                    <td>${item.uraian}</td>
                                    <td class="text-right">${formatRupiahTable(item.jumlah)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = html;
};

window.sahkanSp3bp = function (id) {
    openConfirm('Sahkan SP3BP', 'Setelah disahkan, data ini tidak dapat diubah lagi. Lanjutkan?', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}/sahkan`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('SP3BP Berhasil Disahkan', 'success');
                showSp3bpPreview(id);
            }
        } catch (e) {
            toast('Gagal mengesahkan', 'error');
        }
    }, 'Sahkan', 'ph-check-circle', 'btn-primary');
};

window.printSp3bp = async function (id) {
    try {
        const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}`);
        const data = await res.json();
        if (data.success === false) {
            toast('Data belum di-generate', 'warning');
            return;
        }
        window.lastLaporanData = data;
        window.lastLaporanType = 'SP3BP';
        openPreviewModal('SP3BP');
    } catch (e) {
        toast('Gagal memuat preview', 'error');
    }
};

window.deleteSp3bp = function (id) {
    openConfirm('Hapus SP3BP', 'Hapus periode ini beserta data di dalamnya?', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('Data berhasil dihapus', 'success');
                loadSp3bpList();
            }
        } catch (e) {
            toast('Gagal menghapus', 'error');
        }
    });
};

/* =========================
   LRKB MODULE (REKONSILIASI KAS)
   ========================= */

window.initLrkb = function () {
    loadLrkbList();
};

window.saveLrkbCatatan = async function (id) {
    const catatan = document.getElementById('catatan_selisih').value;
    try {
        const res = await fetch(`/dashboard/pengesahan/lrkb/${id}/catatan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ catatan })
        });
        const data = await res.json();
        if (data.error) {
            toast(data.error, 'error');
        } else {
            toast('Catatan berhasil disimpan', 'success');
        }
    } catch (e) {
        toast('Gagal menyimpan catatan', 'error');
    }
};

window.loadLrkbList = async function () {
    const tbody = document.getElementById('lrkbBody');
    if (!tbody) return;

    try {
        const res = await fetch('/dashboard/pengesahan/lrkb');
        if (!res.ok) throw new Error('Network response was not ok');
        const data = await res.json();

        if (Array.isArray(data) && data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada data LRKB. Klik "Tambah Periode" untuk memulai.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        const triwulans = ["", "Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV"];
        const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        data.forEach((item, index) => {
            const statusLabel = (item.status || 'DRAFT').toUpperCase();
            const statusClass = item.status === 'valid' ? 'badge-success' : 'badge-warning';
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">${item.triwulan ? triwulans[item.triwulan] : '-'}</td>
                    <td class="text-center">${item.bulan ? months[item.bulan] : '-'}</td>
                    <td class="text-center">${item.tahun}</td>
                    <td class="text-center"><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td class="text-center">${item.tgl_rekonsiliasi || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action-circle generate" title="Generate Data" 
                                onclick="openConfirm('Generate LRKB', 'Kalkulasi data rekonsiliasi?', () => generateLrkb(${item.id}), 'Generate', 'ph-arrows-counter-clockwise', 'btn-primary')">
                                <i class="ph ph-arrows-counter-clockwise"></i>
                            </button>
                            <button class="btn-action-circle view" title="Lihat Detail" onclick="openLrkbDetail(${item.id})">
                                <i class="ph ph-eye"></i>
                            </button>
                            <button class="btn-action-circle delete" title="Hapus" onclick="deleteLrkb(${item.id})">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    } catch (e) {
        console.error("LRKB LOAD ERROR:", e);
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>';
    }
};

window.showNewLrkbModal = function () {
    const modal = document.getElementById('newLrkbModal');
    if (modal) modal.classList.add('show');
};

window.closeLrkbModal = function () {
    const modal = document.getElementById('newLrkbModal');
    if (modal) modal.classList.remove('show');
};

window.createLrkbPeriod = async function () {
    const rawVal = document.getElementById('newLrkbTriwulan').value;
    const tahun = document.getElementById('newLrkbYear').value;

    let triwulan = null;
    let bulan = null;

    if (rawVal.startsWith('T')) triwulan = rawVal.substring(1);
    else if (rawVal.startsWith('M')) bulan = rawVal.substring(1);

    try {
        const res = await fetch('/dashboard/pengesahan/lrkb', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ triwulan, bulan, tahun })
        });

        const data = await res.json();
        if (data.error) toast(data.error, 'error');
        else {
            toast('Periode LRKB berhasil dibuat', 'success');
            closeLrkbModal();
            loadLrkbList();
        }
    } catch (e) {
        toast('Gagal membuat periode', 'error');
    }
};

window.generateLrkb = async function (id) {
    try {
        const res = await fetch(`/dashboard/pengesahan/lrkb/${id}/generate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.error) toast(data.error, 'error');
        else {
            toast('Data LRKB berhasil dikalkulasi', 'success');
            loadLrkbList();
        }
    } catch (e) {
        toast('Gagal generate LRKB', 'error');
    }
};

window.openLrkbDetail = async function (id) {
    const res = await fetch(`/dashboard/pengesahan/lrkb/${id}`);
    const data = await res.json();

    if (!data.tgl_rekonsiliasi && !data.saldo_akhir_buku) {
        toast('Data belum di-generate. Silakan klik tombol Generate terlebih dahulu.', 'warning');
        return;
    }

    renderLrkbDetail(data);
};

window.renderLrkbDetail = function (data) {
    const container = document.querySelector('.laporan');
    if (!container) return;

    const triwulans = ["", "Triwulan I", "Triwulan II", "Triwulan III", "Triwulan IV"];
    const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const labelPeriode = data.triwulan ? triwulans[data.triwulan] : months[data.bulan];
    const periodStr = `${labelPeriode || '-'} ${data.tahun}`;

    let html = `
        <div class="sp3bp-detail-container" style="background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
                <div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button class="btn-filter" style="background: transparent; border: 1px solid #e2e8f0; color: #64748b;" onclick="openPengesahan('LRKB')">
                        <i class="ph ph-arrow-left"></i> Kembali
                    </button>
                    ${data.status === 'draft' ? `<button class="btn-preview" style="background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe;" onclick="generateLrkb(${data.id})"><i class="ph ph-arrows-counter-clockwise"></i> Re-Generate</button>` : ''}
                    ${data.status === 'draft' ? `<button class="btn-filter" style="background: #10b981;" onclick="validateLrkb(${data.id})"><i class="ph ph-check-circle"></i> Validasi LRKB</button>` : ''}
                    ${data.status === 'valid' ? `<button class="btn-filter" style="background: #f59e0b;" onclick="unvalidateLrkb(${data.id})"><i class="ph ph-lock-key-open"></i> Buka Validasi</button>` : ''}
                    <button class="btn-preview" onclick="printLrkb(${data.id})">
                        <i class="ph ph-file-pdf"></i> Review & Unduh
                    </button>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 12px; border: 1.5px solid #e2e8f0; margin-top: 20px; font-family: 'Inter', sans-serif;">
                <div style="text-align: center; margin-bottom: 40px; border-bottom: 2px solid black; padding-bottom: 15px;">
                    <h2 style="margin: 0; font-size: 22px; font-weight: 800; text-transform: uppercase;">LAPORAN REKONSILIASI KAS BENDAHARA (LRKB)</h2>
                    <p style="margin: 8px 0 0; font-size: 16px; color: #475569; letter-spacing: 1px;">PERIODE ${periodStr.toUpperCase()}</p>
                </div>

                <table style="width: 100%; border-collapse: collapse; border: 1.5px solid black; font-size: 15px;">
                    <thead>
                        <tr style="background: #f8fafc; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="border: 1.5px solid black; padding: 18px; text-align: center;">URAIAN ADMINISTRASI KEUANGAN</th>
                            <th style="border: 1.5px solid black; padding: 18px; width: 350px; text-align: center;">JUMLAH (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- SALDO AWAL -->
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; font-weight: 800; background: #fafafa;">SALDO AWAL KAS</td>
                            <td style="border: 1px solid black; padding: 12px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">Saldo Kas Per 1 Januari / Libur Lalu</td>
                            <td style="border: 1px solid black; padding: 12px;">${formatRupiahTable(data.saldo_awal)}</td>
                        </tr>
                        <tr style="font-weight: 800; background: #fff;">
                            <td style="border: 1px solid black; padding: 12px; padding-left: 20px;">JUMLAH SALDO AWAL</td>
                            <td style="border: 1px solid black; padding: 12px; border-top: 2px solid black;">${formatRupiahTable(data.saldo_awal)}</td>
                        </tr>

                        <!-- PENERIMAAN -->
                        <tr style="height: 15px;"><td colspan="2" style="border: 1px solid black;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; font-weight: 800; background: #fafafa;">PENERIMAAN KAS BENDAHARA</td>
                            <td style="border: 1px solid black; padding: 12px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">Penerimaan Selama Periode Triwulan Ini</td>
                            <td style="border: 1px solid black; padding: 12px; color: #10b981;">+ ${formatRupiahTable(data.pendapatan)}</td>
                        </tr>

                        <!-- PENGELUARAN -->
                        <tr style="height: 15px;"><td colspan="2" style="border: 1px solid black;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; font-weight: 800; background: #fafafa;">PENGELUARAN KAS BENDAHARA</td>
                            <td style="border: 1px solid black; padding: 12px; text-align: right;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">Pengeluaran Selama Periode Triwulan Ini</td>
                            <td style="border: 1px solid black; padding: 12px; color: #ef4444;">- ${formatRupiahTable(data.belanja)}</td>
                        </tr>

                        <!-- SALDO BUKU -->
                        <tr style="background: #f1f5f9; font-weight: 900; font-size: 16px;">
                            <td style="border: 1px solid black; padding: 18px;">SALDO AKHIR MENURUT PEMBUKUAN (BKU)</td>
                            <td style="border: 1px solid black; padding: 18px; border-bottom: 5px double black;">${formatRupiahTable(data.saldo_akhir_buku)}</td>
                        </tr>

                        <!-- POSISI KAS FISIK -->
                        <tr style="height: 30px;"><td colspan="2" style="border: 1px solid black; background: #fafafa;"></td></tr>
                        <tr>
                            <td style="border: 1px solid black; padding: 15px; font-weight: 900; text-transform: uppercase; background: #f8fafc;">POSISI KAS NYATA / FISIK (REAL)</td>
                            <td style="border: 1px solid black; padding: 15px; text-align: right;"></td>
                        </tr>
                        ${(() => {
            const d_bank_in = (data.details || []).find(d => d.jenis === 'bank_masuk')?.jumlah || 0;
            const d_bank_out = (data.details || []).find(d => d.jenis === 'bank_keluar')?.jumlah || 0;
            const d_tunai_in = (data.details || []).find(d => d.jenis === 'tunai_masuk')?.jumlah || 0;
            const d_tunai_out = (data.details || []).find(d => d.jenis === 'tunai_keluar')?.jumlah || 0;

            return `
                                <tr>
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">- Arus Bank (Penerimaan)</td>
                                    <td style="border: 1px solid black; padding: 12px; color: #10b981;">+ ${formatRupiahTable(d_bank_in)}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">- Arus Bank (Pengeluaran)</td>
                                    <td style="border: 1px solid black; padding: 12px; color: #ef4444;">- ${formatRupiahTable(d_bank_out)}</td>
                                </tr>
                                <tr style="background: #fafafa;">
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px; font-weight: 700;">Sub-Total Saldo Bank (Rekening Koran)</td>
                                    <td style="border: 1px solid black; padding: 12px; font-weight: 700;">${formatRupiahTable(data.saldo_bank)}</td>
                                </tr>
                                <tr style="height: 10px;"><td colspan="2" style="border-left: 1px solid black; border-right: 1px solid black;"></td></tr>
                                <tr>
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">- Arus Tunai (Penerimaan)</td>
                                    <td style="border: 1px solid black; padding: 12px; color: #10b981;">+ ${formatRupiahTable(d_tunai_in)}</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px;">- Arus Tunai (Pengeluaran)</td>
                                    <td style="border: 1px solid black; padding: 12px; color: #ef4444;">- ${formatRupiahTable(d_tunai_out)}</td>
                                </tr>
                                <tr style="background: #fafafa;">
                                    <td style="border: 1px solid black; padding: 12px; padding-left: 40px; font-weight: 700;">Sub-Total Saldo Kas Tunai (Fisik di Brankas)</td>
                                    <td style="border: 1px solid black; padding: 12px; font-weight: 700;">${formatRupiahTable(data.saldo_tunai)}</td>
                                </tr>
                            `;
        })()}
                        
                        <tr style="font-weight: 900; background: #eff6ff; font-size: 16px;">
                            <td style="border: 1px solid black; padding: 15px; padding-left: 20px;">TOTAL SALDO AKHIR MENURUT KAS FISIK</td>
                            <td style="border: 1px solid black; padding: 15px; border-top: 2px solid black; border-bottom: 5px double black;">${formatRupiahTable(data.saldo_fisik)}</td>
                        </tr>

                        <!-- SELISIH -->
                        <tr style="height: 30px;"><td colspan="2" style="border: 1px solid black; background: #fafafa;"></td></tr>
                        <tr style="background: ${data.selisih == 0 ? '#ecfdf5' : '#fef2f2'}; font-weight: 900; font-size: 18px;">
                            <td style="border: 1px solid black; padding: 25px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>HASIL REKONSILIASI (SELISIH)</span>
                                    <span style="font-size: 13px; font-weight: 600; padding: 4px 12px; border: 1.5px solid currentColor; border-radius: 6px; letter-spacing: 0.5px;">
                                        ${data.selisih == 0 ? 'STATUS: SINKRON (MATCH)' : 'STATUS: SELISIH (UNMATCH)'}
                                    </span>
                                </div>
                            </td>
                            <td style="border: 1px solid black; padding: 25px; border-bottom: 6px double black;">${formatRupiahTable(data.selisih)}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: 1px solid black; padding: 15px; background: #fff;">
                                <div style="margin-bottom: 8px; font-weight: 700; color: #475569;">PENJELASAN SELISIH / CATATAN REKONSILIASI:</div>
                                <div style="display: flex; gap: 10px;">
                                    <textarea id="catatan_selisih" style="flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 14px; min-height: 80px;" placeholder="Contoh: Saldo di bank belum tercatat karena setoran di tanggal libur, atau terdapat biaya administrasi bank yang belum dijurnalkan...">${data.catatan_selisih || ''}</textarea>
                                    <button onclick="saveLrkbCatatan(${data.id})" class="btn-filter" style="height: fit-content; align-self: flex-end; background: #3b82f6;">Simpan Catatan</button>
                                </div>
                            </td>
                        </tr>
                    </tbody >
                </table >

            </div >
        </div >
        </div >
        `;

    container.innerHTML = html;
};

window.validateLrkb = function (id) {
    openConfirm('Validasi LRKB', 'Pastikan selisih kas adalah 0. Setelah divalidasi, data ini akan menjadi acuan SP3BP.', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/lrkb/${id}/validate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('LRKB Berhasil Divalidasi', 'success');
                openLrkbDetail(id);
            }
        } catch (e) {
            toast('Gagal validasi', 'error');
        }
    }, 'Validasi', 'ph-shield-check', 'btn-primary');
};

window.deleteLrkb = function (id) {
    openConfirm('Hapus LRKB', 'Hapus periode rekonsiliasi ini?', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/lrkb/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('Data berhasil dihapus', 'success');
                loadLrkbList();
            }
        } catch (e) {
            toast('Gagal menghapus', 'error');
        }
    });
};

window.printLrkb = async function (id) {
    try {
        const res = await fetch(`/dashboard/pengesahan/lrkb/${id}`);
        const data = await res.json();
        if (!data.tgl_rekonsiliasi && !data.saldo_akhir_buku) {
            toast('Data belum di-generate', 'warning');
            return;
        }
        window.lastLaporanData = data;
        window.lastLaporanType = 'LRKB';
        openPreviewModal('LRKB');
    } catch (e) {
        toast('Gagal memuat preview', 'error');
    }
};

window.unvalidateLrkb = function (id) {
    openConfirm('Buka Validasi', 'Data akan kembali ke status DRAFT dan dapat diedit kembali. Lanjutkan?', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/lrkb/${id}/unvalidate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('Validasi berhasil dibuka', 'success');
                openLrkbDetail(id);
            }
        } catch (e) {
            toast('Gagal membuka validasi', 'error');
        }
    }, 'Buka Validasi', 'ph-lock-key-open', 'btn-warning');
};

window.batalSahkanSp3bp = function (id) {
    openConfirm('Buka Pengesahan', 'Data akan kembali ke status DRAFT dan dapat diedit/hapus kembali. Lanjutkan?', async () => {
        try {
            const res = await fetch(`/dashboard/pengesahan/sp3bp/${id}/batal-sah`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (data.error) toast(data.error, 'error');
            else {
                toast('Pengesahan berhasil dibatalkan', 'success');
                showSp3bpPreview(id);
            }
        } catch (e) {
            toast('Gagal membatalkan pengesahan', 'error');
        }
    }, 'Buka Pengesahan', 'ph-lock-key-open', 'btn-warning');
};
