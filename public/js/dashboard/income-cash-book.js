/**
 * Logic for BKU Pendapatan (Income Cash Book)
 */

let incomeBkuKeyword = '';

window.initIncomeCashBook = function () {
    const now = new Date();
    const monthSelect = document.getElementById('incomeBkuMonth');
    if (monthSelect) monthSelect.value = now.getMonth() + 1;
    loadIncomeCashBook();
};

window.loadIncomeCashBook = function () {
    const month = document.getElementById('incomeBkuMonth')?.value || '';
    const year = document.getElementById('incomeBkuYear')?.value || new Date().getFullYear();
    const tbody = document.getElementById('tableIncomeBkuBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>';

    fetch(`/dashboard/bku-penerimaan?month=${month}&year=${year}`, {
        headers: { 'Accept': 'application/json' }
    })
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada data pada periode ini.</td></tr>';
                updateIncomeBkuSummary(res.summary, res.opening_balance);
                return;
            }

            let html = '';

            data.forEach((item, index) => {
                const pVal = item.penerimaan > 0 ? formatRupiahTable(item.penerimaan) : '-';
                const kVal = item.pengeluaran > 0 ? formatRupiahTable(item.pengeluaran) : '-';
                const noBukti = item.reference_id > 0 ? `TRX-${item.reference_id}` : '-';

                html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${formatTanggal(item.tanggal)}</td>
                        <td class="text-center" style="font-family: monospace; font-size: 0.85rem;">${noBukti}</td>
                        <td>${item.uraian || '-'}</td>
                        <td class="text-center"><span class="badge-mini">${item.sumber || '-'}</span></td>
                        <td class="text-right" style="color:#059669">${pVal}</td>
                        <td class="text-right" style="color:#dc2626">${kVal}</td>
                        <td class="text-right font-bold">${formatRupiahTable(item.saldo)}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            updateIncomeBkuSummary(res.summary, res.opening_balance);
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-red-500">Gagal memuat data</td></tr>';
        });
};

function updateIncomeBkuSummary(summary, opening) {
    const s = summary || { total_penerimaan: 0, total_pengeluaran: 0, final_saldo: opening };
    const elP = document.getElementById('incomeBkuTotalPenerimaan');
    const elK = document.getElementById('incomeBkuTotalPengeluaran');
    const elS = document.getElementById('incomeBkuFinalSaldo');

    if (elP) elP.innerHTML = formatRupiahTable(s.total_penerimaan);
    if (elK) elK.innerHTML = formatRupiahTable(s.total_pengeluaran);
    if (elS) elS.innerHTML = formatRupiahTable(s.final_saldo);

    const formatNum = (v) => Number(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Footer summaries
    const fP = document.getElementById('footerTotalPenerimaan');
    const fK = document.getElementById('footerTotalPengeluaran');
    const fS = document.getElementById('footerFinalSaldo');
    const fBRK = document.getElementById('footerBankBRK');
    const fBSI = document.getElementById('footerBankBSI');

    if (fP) fP.innerText = 'Rp ' + formatNum(s.cumulative_penerimaan || s.total_penerimaan);
    if (fK) fK.innerText = 'Rp ' + formatNum(s.cumulative_pengeluaran || s.total_pengeluaran);
    if (fS) fS.innerText = 'Rp ' + formatNum(s.final_saldo);
    if (fBRK) fBRK.innerText = 'Rp ' + formatNum(s.bank_brk);
    if (fBSI) fBSI.innerText = 'Rp ' + formatNum(s.bank_bsi);

    // Update Label if month is null (full year)
    const month = document.getElementById('incomeBkuMonth')?.value || '';
    const labelS = document.getElementById('labelFinalSaldo');
    const footerLabelS = document.getElementById('footerLabelSaldoAkhir');

    if (labelS) {
        labelS.innerText = month ? 'Saldo Akhir (Tunai)' : 'Saldo Akhir Tahun (Tunai)';
    }
    if (footerLabelS) {
        footerLabelS.innerText = month ? 'Saldo Kas Bendahara Akhir Bulan' : 'Saldo Kas Bendahara Akhir Tahun';
    }
}

window.syncIncomeCashBook = function () {
    const btn = document.getElementById('btnSyncIncomeBku');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap animate-spin"></i> <span>Mensinkronkan...</span>';
    }

    const year = document.getElementById('incomeBkuYear')?.value || new Date().getFullYear();

    fetch('/dashboard/bku-penerimaan/sync', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ year: year })
    })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.message || 'Gagal sinkronisasi');
            }
            toast('BKU Pendapatan berhasil disinkronkan', 'success');
            loadIncomeCashBook();
        })
        .catch(err => toast(err.message, 'error'))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-arrows-clockwise"></i> <span>Sinkronisasi</span>';
            }
        });
};

window.filterIncomeBkuTable = function () {
    const kw = document.getElementById('searchIncomeBku').value.toLowerCase();
    const rows = document.querySelectorAll('#tableIncomeBkuBody tr');
    rows.forEach(row => {
        // Skip Saldo Awal row if desired or keep it
        if (row.innerText.includes('Saldo Awal') && row.style.fontStyle === 'italic') return;

        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(kw) ? '' : 'none';
    });
};




