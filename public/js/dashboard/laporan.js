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

    window.lastLaporanType = type;

    if (!end && type !== 'REKON' && type !== 'DPA') {
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
            case 'ANGGARAN': {
                const cat = document.getElementById('lraCategory')?.value || 'SEMUA';
                url = `/dashboard/laporan/anggaran?start=${start}&end=${end}&category=${cat}`;
                break;
            }
            case 'PENGELUARAN': url = `/dashboard/laporan/pengeluaran?start=${start}&end=${end}`; break;
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

    // 1b. Render Jasa RS & Pelayanan Breakdown Table
    const jasaTableBody = document.getElementById('laporanJasaDetailedBody');
    if (jasaTableBody) {
        jasaTableBody.innerHTML = '';
        let totalRs = 0, totalPelayanan = 0, totalJasaAll = 0;

        categoryKeys.forEach(key => {
            const item = data.breakdown[key];
            if (!item || !item.jasa) return;

            const row = item.jasa;
            totalRs += row.RS;
            totalPelayanan += row.PELAYANAN;
            totalJasaAll += row.TOTAL;

            jasaTableBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.RS)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.PELAYANAN)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `);
        });

        jasaTableBody.insertAdjacentHTML('beforeend', `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL KESELURUHAN</td>
                <td style="text-align:right">${formatRupiahTable(totalRs)}</td>
                <td style="text-align:right">${formatRupiahTable(totalPelayanan)}</td>
                <td style="text-align:right">${formatRupiahTable(totalJasaAll)}</td>
            </tr>
        `);
    }

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
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.TUNAI)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.NON_TUNAI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `);
        });

        payTableBody.insertAdjacentHTML('beforeend', `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL KESELURUHAN</td>
                <td style="text-align:right">${formatRupiahTable(totalTunai)}</td>
                <td style="text-align:right">${formatRupiahTable(totalNon)}</td>
                <td style="text-align:right">${formatRupiahTable(totalAll)}</td>
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
                    <td class="text-center"><code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:12px;">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td style="text-align:right">${formatRupiahTable(row.BRK)}</td>
                    <td style="text-align:right">${formatRupiahTable(row.BSI)}</td>
                    <td style="text-align:right; font-weight:700;">${formatRupiahTable(row.TOTAL)}</td>
                </tr>
            `);
        });

        bankTableBody.insertAdjacentHTML('beforeend', `
            <tr style="background:#f8fafc; font-weight:800;">
                <td colspan="2" style="text-align:center">TOTAL PENERIMAAN BANK</td>
                <td style="text-align:right">${formatRupiahTable(totalBRK)}</td>
                <td style="text-align:right">${formatRupiahTable(totalBSI)}</td>
                <td style="text-align:right">${formatRupiahTable(totalAllBank)}</td>
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
    data.forEach(item => {
        totalBank += Number(item.bank);
        totalPend += Number(item.pendapatan);

        const isMatch = Math.abs(item.selisih) < 1;
        const isKumulatifMatch = Math.abs(item.kumulatif) < 1;

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
                <td class="text-center" style="font-weight:600;">${item.tanggal}</td>
                <td style="text-align:right">${formatRupiahTable(item.bank)}</td>
                <td style="text-align:right">${formatRupiahTable(item.pendapatan)}</td>
                <td style="text-align:right; font-weight:600; color:${selisihColor}">${formatRupiahTable(item.selisih)}</td>
                <td style="text-align:right; font-weight:600; color:${kumulatifColor}">${formatRupiahTable(item.kumulatif)}</td>
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
                <td style="text-align:right">${formatRupiahTable(item.total_piutang)}</td>
                <td style="text-align:right">${formatRupiahTable(item.total_potongan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.total_adm_bank)}</td>
                <td style="text-align:right">${formatRupiahTable(item.total_dibayar)}</td>
                <td style="text-align:right; font-weight:700;">${formatRupiahTable(sisa)}</td>
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
                <td style="text-align:right">${formatRupiahTable(item.rs)}</td>
                <td style="text-align:right">${formatRupiahTable(item.pelayanan)}</td>
                <td style="text-align:right">${formatRupiahTable(item.gross)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiahTable(item.potongan)}</td>
                <td style="text-align:right; color:#ef4444">${formatRupiahTable(item.adm_bank)}</td>
                <td style="text-align:right; font-weight:700; color:#16a34a">${formatRupiahTable(item.total)}</td>
            </tr>
        `);
    });
}

function renderAnggaran(data) {
    const cardsContainer = document.getElementById('lraCardsContainer');
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

    const body = document.getElementById('laporanAnggaranBody');
    if (!body) return;
    body.innerHTML = '';

    data.data.forEach(item => {
        const isHeader = item.tipe === 'header';
        let progressColor = '#3b82f6';
        if (item.persen >= 100) progressColor = '#9333ea';
        else if (item.persen >= 80) progressColor = '#10b981';
        else if (item.persen < 50) progressColor = '#f59e0b';

        // Hide numeric values for Root Organization
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

        body.insertAdjacentHTML('beforeend', `
            <tr class="${isHeader ? 'row-header' : 'row-detail'}">
                <td class="col-kode">${item.kode}</td>
                <td class="col-uraian">
                    <span>${item.nama}</span>
                </td>
                <td class="col-mono ">${valTarget}</td>
                <td class="col-mono " style="color:#64748b; font-size:12px;">${valLalu}</td>
                <td class="col-mono font-medium text-slate-700">${valKini}</td>
                <td class="col-mono font-bold text-slate-900">${valTotal}</td>
                <td class="col-mono ${item.selisih < 0 ? 'text-red-500' : 'text-slate-500'}">
                    ${valSelisih}
                </td>
                <td class="text-center font-bold" style="color:${progressColor}">${valPersen}</td>
                <td class="col-progress">
                    ${valProgress}
                </td>
            </tr>
        `);
    });
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

            body.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-center"><code class="bg-slate-100 px-2 py-1 rounded">${item.kode}</code></td>
                    <td>${item.nama}</td>
                    <td class="">${formatRupiahTable(up)}</td>
                    <td class="">${formatRupiahTable(gu)}</td>
                    <td class="">${formatRupiahTable(ls)}</td>
                    <td class="font-bold">${formatRupiahTable(total)}</td>
                </tr>
            `);
        });

        body.insertAdjacentHTML('beforeend', `
            <tr class="bg-slate-50 font-extrabold">
                <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                <td class="">${formatRupiahTable(gUp)}</td>
                <td class="">${formatRupiahTable(gGu)}</td>
                <td class="">${formatRupiahTable(gLs)}</td>
                <td class="">${formatRupiahTable(gTotal)}</td>
            </tr>
        `);
    }
}

window.exportLaporan = function (type) {
    const reportType = type || window.lastLaporanType || 'PENDAPATAN';
    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    if (!end && reportType !== 'DPA' && reportType !== 'REKON') {
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
        'DPA': 'dpa'
    };

    const ptKiri = document.getElementById('ptSelectKiri')?.value || '';
    const ptTengah = document.getElementById('ptSelectTengah')?.value || '';
    const ptKanan = document.getElementById('ptSelectKanan')?.value || '';

    const endpoint = mapping[reportType] || 'pendapatan';
    let url = `/dashboard/laporan/export/${endpoint}?start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;
    if (reportType === 'ANGGARAN') {
        const cat = document.getElementById('lraCategory')?.value || 'PENDAPATAN';
        url += `&category=${cat}`;
    }
    window.location.href = url;
    toast(`⏳ Menyiapkan Unduh Excel ${reportType}...`, 'info');
};

window.exportPdf = function (type) {
    const reportType = type || window.lastLaporanType || 'PENDAPATAN';
    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    if (!end && reportType !== 'DPA' && reportType !== 'REKON') {
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
        'DPA': 'dpa-pdf'
    };

    const ptKiri = document.getElementById('ptSelectKiri')?.value || '';
    const ptTengah = document.getElementById('ptSelectTengah')?.value || '';
    const ptKanan = document.getElementById('ptSelectKanan')?.value || '';

    const endpoint = mapping[reportType] || 'pendapatan-pdf';
    let url = `/dashboard/laporan/export/${endpoint}?start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;
    if (reportType === 'ANGGARAN') {
        const cat = document.getElementById('lraCategory')?.value || 'PENDAPATAN';
        url += `&category=${cat}`;
    }
    window.location.href = url;
    toast(`⏳ Menyiapkan Export PDF ${reportType}...`, 'info');
};

window.openPreviewModal = function (type) {
    const reportType = type || window.lastLaporanType;
    const start = document.getElementById('laporanStart')?.value;
    const end = document.getElementById('laporanEnd')?.value;

    if (!window.lastLaporanData || window.lastLaporanType !== reportType) {
        toast('Klik Tampilkan data terlebih dahulu!', 'info');
        return;
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
        } else {
            periodeEl.innerText = `Periode: ${formatTanggal(start)} s/d ${formatTanggal(end)}`;
        }
    }

    const titleMapping = {
        'PENDAPATAN': 'LAPORAN PENDAPATAN',
        'REKON': 'LAPORAN REKONSILIASI',
        'PIUTANG': 'LAPORAN PIUTANG',
        'MOU': 'LAPORAN KERJASAMA / MOU',
        'ANGGARAN': 'LAPORAN REALISASI ANGGARAN',
        'PENGELUARAN': 'LAPORAN REALISASI BELANJA',
        'DPA': 'LAPORAN DOKUMEN PELAKSANAAN ANGGARAN (DPA)'
    };
    const modalMainTitle = document.getElementById('previewMainTitle');
    if (modalMainTitle) modalMainTitle.innerText = titleMapping[reportType] || 'LAPORAN';

    const modalTitle = document.getElementById('modalReportTitle');
    if (modalTitle) modalTitle.innerText = `Preview ${titleMapping[reportType] || 'Laporan'}`;

    const tablesContainer = document.getElementById('previewTables');
    if (!tablesContainer) return;
    tablesContainer.innerHTML = '';

    const fr = (num) => {
        if (typeof num !== 'number') num = parseFloat(num) || 0;
        const val = num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return `
            <div style="display: flex; justify-content: space-between; width: 100%; gap: 5px;">
                <span>Rp</span>
                <span style="text-align: right;">${val}</span>
            </div>
        `;
    };

    const numFr = (num) => {
        if (typeof num !== 'number') num = parseFloat(num) || 0;
        return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

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
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 15%;">Tanggal</th>
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
                    <td style="border:1px solid #000; padding:8px; text-align:center;">${formatTanggal(item.tanggal)}</td>
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
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 30%;">Perusahaan Penjamin</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 17%;">Piutang</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 17%;">Potongan</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 17%;">Adm Bank</th>
                        <th style="border:1px solid #000; padding:8px; text-align:center; width: 19%;">Total Dibayar</th>
                    </tr>
                </thead>
                <tbody>`;
        data.data.forEach(item => {
            piutangHtml += `
                <tr>
                    <td style="border:1px solid #000; padding:8px; vertical-align: middle;">${item.nama_perusahaan}</td>
                    <td style="border:1px solid #000; padding:8px; vertical-align: middle;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Rp</span>
                            <span>${numFr(item.total_piutang)}</span>
                        </div>
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align: middle;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Rp</span>
                            <span>${numFr(item.total_potongan)}</span>
                        </div>
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align: middle;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Rp</span>
                            <span>${numFr(item.total_adm_bank)}</span>
                        </div>
                    </td>
                    <td style="border:1px solid #000; padding:8px; vertical-align: middle; font-weight:bold;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Rp</span>
                            <span>${numFr(item.total_dibayar)}</span>
                        </div>
                    </td>
                </tr>`;
        });
        piutangHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td style="border:1px solid #000; padding:8px; text-align:center;">GRAND TOTAL</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right; white-space:nowrap;">${fr(data.totals.piutang)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right; white-space:nowrap;">${fr(data.totals.potongan)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right; white-space:nowrap;">${fr(data.totals.adm_bank)}</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right; white-space:nowrap;">${fr(data.totals.dibayar)}</td>
                </tr></tbody></table>`;
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
        let aggHtml = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:8pt;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 8%;">Kode</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 22%;">Uraian</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 12%;">Target</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 12%;">Real. Lalu</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 12%;">Real. Kini</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 12%;">Real. Total</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 12%;">Selisih</th>
                        <th style="border:1px solid #000; padding:5px; text-align:center; width: 6%;">%</th>
                    </tr>
                </thead>
                <tbody>`;
        data.data.forEach(item => {
            const isBold = item.level < 5;
            const isRoot = item.nama && item.nama.includes('Rumah Sakit Khusus Jiwa dan Ketergantungan Obat');

            aggHtml += `
                <tr style="${isBold ? 'font-weight:bold; background-color:#f8fafc;' : ''}">
                    <td style="border:1px solid #000; padding:5px;">${item.kode}</td>
                    <td style="border:1px solid #000; padding:5px;">${item.nama}</td>
                    <td style="border:1px solid #000; padding:5px;">${isRoot ? '' : fr(item.target)}</td>
                    <td style="border:1px solid #000; padding:5px;">${isRoot ? '' : fr(item.realisasi_lalu)}</td>
                    <td style="border:1px solid #000; padding:5px;">${isRoot ? '' : fr(item.realisasi_kini)}</td>
                    <td style="border:1px solid #000; padding:5px;">${isRoot ? '' : fr(item.realisasi_total)}</td>
                    <td style="border:1px solid #000; padding:5px;">${isRoot ? '' : fr(item.selisih)}</td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;">${isRoot ? '' : item.persen + '%'}</td>
                </tr>`;
        });
        aggHtml += `
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <td colspan="2" style="border:1px solid #000; padding:5px; text-align:center;">TOTAL</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(data.totals.target)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(data.totals.realisasi_lalu)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(data.totals.realisasi_kini)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(data.totals.realisasi_total)}</td>
                    <td style="border:1px solid #000; padding:5px;">${fr(data.totals.target - data.totals.realisasi_total)}</td>
                    <td style="border:1px solid #000; padding:5px; text-align:center;">${data.totals.persen}%</td>
                </tr></tbody></table>`;
        tablesContainer.innerHTML = aggHtml;
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
