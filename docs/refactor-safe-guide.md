# Panduan Refactor & Replace Aman (Safe Replace)

Sistem ini dibuat untuk mencegah kerusakan pada file vendor sistem (seperti Composer, Laravel Framework, dll) saat melakukan proses *search and replace* massal.

## 1. Lokasi Script
Script pelindung berada di: `tools/safe_replace.php`

## 2. Fitur Proteksi
Script ini secara otomatis akan mengabaikan:
- **Folder Sistem**: `vendor/`, `node_modules/`, `storage/`, `bootstrap/cache/`, `public/build/`, `public/vendor/`.
- **File Konfigurasi Dependensi**: `composer.json`, `composer.lock`, `package.json`, `package-lock.json`.
- **Ekstensi Terbatas**: Hanya memproses `.php`, `.js`, dan `.blade.php`.

## 3. Cara Penggunaan

### Mode Simulasi (Dry Run)
Sangat disarankan untuk menjalankan simulasi terlebih dahulu guna melihat file apa saja yang akan terkena dampak.
```bash
php tools/safe_replace.php --find="STRING_LAMA" --replace="STRING_BARU" --dry-run
```

### Eksekusi Nyata
Jika simulasi sudah benar, jalankan tanpa flag `--dry-run`.
```bash
php tools/safe_replace.php --find="STRING_LAMA" --replace="STRING_BARU"
```

## 4. Backup Otomatis
Setiap kali script dijalankan dalam mode eksekusi (bukan dry run), sistem akan membuat folder backup baru:
`backup_replace_YYYYMMDD_HHMMSS/`

### Cara Restore Backup
Jika terjadi kesalahan, Anda dapat mengembalikan file secara manual dari folder backup tersebut atau menggunakan perintah git (jika file sudah di-*commit* sebelumnya):
```bash
git checkout -- .
```

## 5. Integrasi Git
Secara default, script menggunakan `git ls-files` untuk menentukan daftar file yang aman diubah (hanya file yang dilacak oleh Git).
Jika project tidak menggunakan Git, gunakan flag `--no-git`:
```bash
php tools/safe_replace.php --find="OLD" --replace="NEW" --no-git
```

---
**Peringatan**: Jangan pernah mencoba melakukan replace massal menggunakan tool eksternal (seperti VS Code Global Replace) pada folder root tanpa melakukan filter eksklusi pada folder `vendor/`. Selalu gunakan script ini untuk keamanan project.
