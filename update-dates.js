const fs = require('fs');
const path = require('path');
const dir = 'c:/xampp/htdocs/rsjko/public/js/dashboard';
const files = fs.readdirSync(dir);
let updated = [];
files.forEach(file => {
    if (file.endsWith('.js')) {
        const filePath = path.join(dir, file);
        let content = fs.readFileSync(filePath, 'utf8');
        let oldContent = content;

        // Target specifically the pattern: <td>${formatTanggal(item.tanggal)}</td>
        // Covers formatDate, formatDateIndo, formatTanggal, and row.tanggal, item.tanggal, data.tanggal
        content = content.replace(/<td>(\s*\$\{(?:formatTanggal|formatDate|formatDateIndo)\([a-zA-Z]+\.tanggal\)\}\s*)<\/td>/g, '<td class="text-center">$1</td>');

        if (content !== oldContent) {
            fs.writeFileSync(filePath, content);
            updated.push(file);
        }
    }
});
console.log('Updated files: ' + updated.join(', '));
