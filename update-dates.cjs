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

        // Match <td>${formatXXX(item/row/data.tanggal)}</td>
        content = content.replace(/<td>(\s*\$\{(?:formatTanggal|formatDate|formatDateIndo)\([a-zA-Z]+\.tanggal\)\}\s*)<\/td>/g, '<td class="text-center">$1</td>');

        if (content !== oldContent) {
            fs.writeFileSync(filePath, content);
            updated.push(file);
        }
    }
});
console.log('Updated JS files: ' + updated.join(', '));
