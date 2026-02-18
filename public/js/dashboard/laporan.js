window.initLaporan = function (type = 'PENDAPATAN') {
    const today = new Date().toISOString().split('T')[0];
    const firstDay = '2026-01-01';

    if (document.getElementById('laporanStart')) document.getElementById('laporanStart').value = firstDay;
    if (document.getElementById('laporanEnd')) document.getElementById('laporanEnd').value = today;

    loadLaporan(type);
};

window.loadLaporan = async function (type) {
    const startEl = document.getElementById('laporanStart');
    const endEl = document.getElementById('laporanEnd');
    const start = startEl ? startEl.value : '';
    const end = endEl ? endEl.value : '';

    if (!end) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    const container = document.querySelector('.laporan');
    if (container) container.classList.add('loading');

    try {
        let url = '';
        switch (type) {
            case 'PENDAPATAN': url = `/dashboard/laporan/data?start=${start}&end=${end}`; break;
            case 'REKON': url = `/dashboard/laporan/rekon?start=${start}&end=${end}`; break;
            case 'PIUTANG': url = `/dashboard/laporan/piutang?start=${start}&end=${end}`; break;
            case 'MOU': url = `/dashboard/laporan/mou?start=${start}&end=${end}`; break;
            case 'ANGGARAN': url = `/dashboard/laporan/anggaran?start=${start}&end=${end}`; break;
        }

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (type === 'PENDAPATAN') renderPendapatan(data);
        else if (type === 'REKON') renderRekon(data);
        else if (type === 'PIUTANG') renderPiutang(data);
        else if (type === 'MOU') renderMou(data);
        else if (type === 'ANGGARAN') renderAnggaran(data);

    } catch (err) {
        console.error(err);
        toast('Gagal memuat laporan', 'error');
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

        Object.keys(types).forEach(key => {
            const item = data.summary[key] || { total: 0, count: 0 };
            const conf = types[key];
            cardContainer.insertAdjacentHTML('beforeend', `
                <div class="laporan-card highlight-${conf.color}">
                    <div class="card-icon"><i class="ph ${conf.icon}"></i></div>
                    <div class="card-info">
                        <h3>${conf.label}</h3>
                        <span class="big">${formatRupiah(item.total)}</span>
                        <p>${item.count} Transaksi</p>
                    </div>
                </div>
            `);
        });
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

    // 2. Render Payment Method Table
    const payTableBody = document.getElementById('laporanPaymentDetailedBody');
    if (payTableBody) {
        payTableBody.innerHTML = '';
        let totalTunai = 0, totalNon = 0, totalAll = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;

            const row = item.payments;
            totalTunai += row.TUNAI;
            totalNon += row.NON_TUNAI;
            totalAll += row.TOTAL;

            payTableBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiah(row.TUNAI)}</td>
                    <td style="text-align:right">${formatRupiah(row.NON_TUNAI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiah(row.TOTAL)}</td>
                </tr>
            `);
        });

        payTableBody.insertAdjacentHTML('beforeend', `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:right">TOTAL KESELURUHAN</td>
                <td style="text-align:right">${formatRupiah(totalTunai)}</td>
                <td style="text-align:right">${formatRupiah(totalNon)}</td>
                <td style="text-align:right">${formatRupiah(totalAll)}</td>
            </tr>
        `);
    }

    // 3. Render Bank Reception Table
    const bankTableBody = document.getElementById('laporanBankDetailedBody');
    if (bankTableBody) {
        bankTableBody.innerHTML = '';
        let totalBRK = 0, totalBSI = 0, totalAllBank = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item) return;

            const row = item.banks;
            totalBRK += row.BRK;
            totalBSI += row.BSI;
            totalAllBank += row.TOTAL;

            bankTableBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiah(row.BRK)}</td>
                    <td style="text-align:right">${formatRupiah(row.BSI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiah(row.TOTAL)}</td>
                </tr>
            `);
        });

        bankTableBody.insertAdjacentHTML('beforeend', `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:right">TOTAL PENERIMAAN BANK</td>
                <td style="text-align:right">${formatRupiah(totalBRK)}</td>
                <td style="text-align:right">${formatRupiah(totalBSI)}</td>
                <td style="text-align:right">${formatRupiah(totalAllBank)}</td>
            </tr>
        `);
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
            roomEntries.forEach(([room, total]) => {
                const percent = (total / maxVal) * 100;
                roomBody.insertAdjacentHTML('beforeend', `
                    <div class="room-item">
                        <div class="room-info"><span>${room}</span><strong>${formatRupiah(total)}</strong></div>
                        <div class="room-bar-bg"><div class="room-bar-fill" style="width: ${percent}%"></div></div>
                    </div>
                `);
            });
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
            patientEntries.forEach(([room, count]) => {
                const percent = (count / maxValP) * 100;
                patientBody.insertAdjacentHTML('beforeend', `
                    <div class="room-item">
                        <div class="room-info"><span>${room}</span><strong>${count} Pasien</strong></div>
                        <div class="room-bar-bg"><div class="room-bar-fill" style="width: ${percent}%; background: linear-gradient(90deg, #10b981, #34d399);"></div></div>
                    </div>
                `);
            });
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

    if (data.length === 0) {
        body.innerHTML = '<tr><td colspan="6" style="text-align:center">📭 Tidak ada data transaksi</td></tr>';
        if (sumBank) sumBank.innerText = 'Rp 0';
        if (sumPend) sumPend.innerText = 'Rp 0';
        if (sumDiff) sumDiff.innerText = 'Rp 0';
        return;
    }

    let totalBank = 0;
    let totalPend = 0;

    body.innerHTML = '';
    data.forEach(item => {
        totalBank += Number(item.bank);
        totalPend += Number(item.pendapatan);

        const isMatch = Math.abs(item.selisih) < 1;
        const isKumulatifMatch = Math.abs(item.kumulatif) < 1;

        // Status Logic: 
        // 1. Match (Harian 0)
        // 2. Timing OK (Kumulatif 0, tapi harian ada selisih) -> Ini untuk kasus setor besoknya
        // 3. Selisih (Kumulatif != 0)

        let statusClass = 'badge-danger';
        let statusText = '❌ SELISIH';

        if (isMatch) {
            statusClass = 'badge-success';
            statusText = '✅ MATCH';
        } else if (isKumulatifMatch) {
            statusClass = 'badge-info';
            statusText = '⏳ TIMING OK';
        }

        const selisihColor = item.selisih === 0 ? '#64748b' : (item.selisih > 0 ? '#16a34a' : '#ef4444');
        const kumulatifColor = item.kumulatif === 0 ? '#64748b' : (item.kumulatif > 0 ? '#16a34a' : '#ef4444');

        body.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${formatTanggal(item.tanggal)}</td>
                <td style="text-align:right">${formatRupiah(item.bank)}</td>
                <td style="text-align:right">${formatRupiah(item.pendapatan)}</td>
                <td style="text-align:right; font-weight:600; color:${selisihColor}">${formatRupiah(item.selisih)}</td>
                <td style="text-align:right; font-weight:600; color:${kumulatifColor}">${formatRupiah(item.kumulatif)}</td>
                <td style="text-align:center"><span class="badge ${statusClass}">${statusText}</span></td>
            </tr>
        `);
    });

    if (sumBank) sumBank.innerText = formatRupiah(totalBank);
    if (sumPend) sumPend.innerText = formatRupiah(totalPend);
    if (sumDiff) {
        const net = totalBank - totalPend;
        sumDiff.innerText = formatRupiah(net);
        sumDiff.style.color = net === 0 ? '#16a34a' : '#ef4444';
    }
}

function renderPiutang(data) {
    if (document.getElementById('totalPiutangReport')) document.getElementById('totalPiutangReport').innerText = formatRupiah(data.totals.piutang);
    if (document.getElementById('totalPotonganPiutangReport')) document.getElementById('totalPotonganPiutangReport').innerText = formatRupiah(data.totals.potongan);
    if (document.getElementById('totalAdmBankPiutangReport')) document.getElementById('totalAdmBankPiutangReport').innerText = formatRupiah(data.totals.adm_bank);
    if (document.getElementById('totalDiterimaPiutangReport')) document.getElementById('totalDiterimaPiutangReport').innerText = formatRupiah(data.totals.dibayar);

    const body = document.getElementById('laporanPiutangBody');
    if (!body) return;
    body.innerHTML = '';
    data.data.forEach(item => {
        const sisa = item.total_piutang - item.total_dibayar - item.total_potongan - item.total_adm_bank;
        body.insertAdjacentHTML('beforeend', `
            <tr>
                <td><strong>${item.nama_perusahaan}</strong></td>
                <td style="text-align:right">${formatRupiah(item.total_piutang)}</td>
                <td style="text-align:right">${formatRupiah(item.total_potongan)}</td>
                <td style="text-align:right">${formatRupiah(item.total_adm_bank)}</td>
                <td style="text-align:right">${formatRupiah(item.total_dibayar)}</td>
                <td style="text-align:right; font-weight:700;">${formatRupiah(sisa)}</td>
            </tr>
        `);
    });
}

function renderMou(data) {
    const body = document.getElementById('laporanMouBody');
    if (!body) return;
    body.innerHTML = '';
    data.forEach((item, index) => {
        body.insertAdjacentHTML('beforeend', `
            <tr>
                <td style="text-align:center">${index + 1}</td>
                <td><strong>${item.nama_mou}</strong></td>
                <td style="text-align:center">${item.count}</td>
                <td style="text-align:right">${formatRupiah(item.rs)}</td>
                <td style="text-align:right">${formatRupiah(item.pelayanan)}</td>
                <td style="text-align:right">${formatRupiah(item.gross)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiah(item.potongan)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiah(item.adm_bank)}</td>
                <td style="text-align:right; font-weight:700; color:#16a34a">${formatRupiah(item.total)}</td>
            </tr>
        `);
    });
}

function renderAnggaran(data) {
    // Update Cards
    if (document.getElementById('totalTargetAnggaran')) document.getElementById('totalTargetAnggaran').innerText = formatRupiah(data.totals.target);
    if (document.getElementById('totalRealisasiAnggaran')) document.getElementById('totalRealisasiAnggaran').innerText = formatRupiah(data.totals.realisasi);

    // Dynamic Capaian Card
    const capaianEl = document.getElementById('totalPersentaseAnggaran');
    if (capaianEl) {
        capaianEl.innerText = data.totals.persen + '%';

        // Find parent card to update color
        const cardVal = parseFloat(data.totals.persen);
        const card = capaianEl.closest('.laporan-card');

        if (card) {
            // Remove old highlights
            card.classList.remove('highlight-orange', 'highlight-blue', 'highlight-green', 'highlight-purple');

            // Add new based on value
            if (cardVal >= 100) card.classList.add('highlight-purple'); // Excellent
            else if (cardVal >= 80) card.classList.add('highlight-green'); // Good
            else if (cardVal >= 50) card.classList.add('highlight-blue'); // Progressing
            else card.classList.add('highlight-orange'); // Warning
        }
    }

    const body = document.getElementById('laporanAnggaranBody');
    if (!body) return;
    body.innerHTML = '';

    data.data.forEach(item => {
        const isHeader = item.tipe === 'header';

        // Progress Bar Color Logic
        let progressColor = '#3b82f6'; // Default Blue
        if (item.persen >= 100) progressColor = '#9333ea'; // Purple
        else if (item.persen >= 80) progressColor = '#10b981'; // Green
        else if (item.persen < 50) progressColor = '#f59e0b'; // Orange

        body.insertAdjacentHTML('beforeend', `
            <tr class="${isHeader ? 'row-header' : 'row-detail'}">
                <td class="col-kode">${item.kode}</td>
                <td class="col-uraian">
                    <span>${item.nama}</span>
                </td>
                <td class="col-mono text-right">${formatRupiah(item.target)}</td>
                <td class="col-mono text-right">${formatRupiah(item.realisasi)}</td>
                <td class="col-mono text-right ${item.selisih < 0 ? 'text-red-500' : 'text-slate-500'}">
                    ${formatRupiah(item.selisih)}
                </td>
                <td class="text-center font-bold" style="color:${progressColor}">${item.persen}%</td>
                <td class="col-progress">
                    <div class="progress-track">
                        <div class="progress-fill" style="width: ${Math.min(item.persen, 100)}%; background:${progressColor};"></div>
                    </div>
                </td>
            </tr>
        `);
    });
}

window.exportLaporan = function () {
    const startEl = document.getElementById('laporanStart');
    const endEl = document.getElementById('laporanEnd');
    const start = startEl ? startEl.value : '';
    const end = endEl ? endEl.value : '';

    if (!end) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    // Redirect to backend export route
    window.location.href = `/dashboard/laporan/export/pendapatan?start=${start}&end=${end}`;
    toast('⏳ Menyiapkan Export Excel...', 'info');
};

window.exportPdf = function () {
    const startEl = document.getElementById('laporanStart');
    const endEl = document.getElementById('laporanEnd');
    const start = startEl ? startEl.value : '';
    const end = endEl ? endEl.value : '';

    if (!end) {
        toast('Pilih tanggal!', 'error');
        return;
    }

    window.location.href = `/dashboard/laporan/export/pendapatan-pdf?start=${start}&end=${end}`;
    toast('⏳ Menyiapkan Export PDF...', 'info');
};
