$content = Get-Content 'd:\xampp\htdocs\rsjko\public\js\dashboard\laporan.js'
$newContent = New-Object System.Collections.Generic.List[string]
$inExportLaporan = $false
$inExportPdf = $false

foreach ($line in $content) {
    if ($line -match 'window\.exportLaporan = function') { $inExportLaporan = $true }
    if ($line -match 'window\.exportPdf = function') { $inExportPdf = $true }
    
    if ($inExportLaporan -and $line -match 'window\.location\.href = url;') {
        $newContent.Add('    if (reportType === "LO") {')
        $newContent.Add('        const p = document.getElementById("loPeriode")?.value || "Tahunan";')
        $newContent.Add('        const b = document.getElementById("loBulan")?.value || "";')
        $newContent.Add('        const tw = document.getElementById("loTriwulan")?.value || "";')
        $newContent.Add('        const sem = document.getElementById("loSemester")?.value || "";')
        $newContent.Add('        url = `/dashboard/laporan/export/${endpoint}?periode=${p}&bulan=${b}&triwulan=${tw}&semester=${sem}&start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;')
        $newContent.Add('    }')
        $inExportLaporan = $false
    }

    if ($inExportPdf -and $line -match 'window\.location\.href = url;') {
        $newContent.Add('    if (reportType === "LO") {')
        $newContent.Add('        const p = document.getElementById("loPeriode")?.value || "Tahunan";')
        $newContent.Add('        const b = document.getElementById("loBulan")?.value || "";')
        $newContent.Add('        const tw = document.getElementById("loTriwulan")?.value || "";')
        $newContent.Add('        const sem = document.getElementById("loSemester")?.value || "";')
        $newContent.Add('        url = `/dashboard/laporan/export/${endpoint}?periode=${p}&bulan=${b}&triwulan=${tw}&semester=${sem}&start=${start}&end=${end}&pt_id_kiri=${ptKiri}&pt_id_tengah=${ptTengah}&pt_id_kanan=${ptKanan}`;')
        $newContent.Add('    }')
        $inExportPdf = $false
    }
    
    $newContent.Add($line)
}

$newContent | Set-Content 'd:\xampp\htdocs\rsjko\public\js\dashboard\laporan.js'
