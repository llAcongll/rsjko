const fs = require('fs');
const path = require('path');
const dir = 'c:/xampp/htdocs/rsjko/public/js/dashboard';
const jsFiles = fs.readdirSync(dir).filter(f => f.endsWith('.js'));

jsFiles.forEach(file => {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let old = content;

    // Find formatRupiah(...) inside <td> tags and change to formatRupiahTable(...)
    // Regex matches <td> content including potential tags but focuses on the formatRupiah part
    // Using a non-greedy match for everything between <td> and formatRupiah
    content = content.replace(/(<td[^>]*>[\s\S]*?)\$\{formatRupiah\(([^)]+)\)\}([\s\S]*?<\/td>)/g, (match, p1, p2, p3) => {
        return `${p1}\${formatRupiahTable(${p2})}${p3}`;
    });

    // Also handle report table in laporan.js (it has some special ones)
    if (file === 'laporan.js') {
        // Match specific segments if needed, but the general regex above might catch them
    }

    if (content !== old) {
        fs.writeFileSync(filePath, content);
        console.log('Updated to formatRupiahTable: ' + file);
    }
});
