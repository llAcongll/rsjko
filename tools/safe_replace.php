<?php

/**
 * Safe Replace Utility for Laravel Projects
 * 
 * This tool allows for bulk find-and-replace across project source files
 * while strictly protecting system directories (vendor, node_modules, etc.)
 * 
 * Usage:
 * php tools/safe_replace.php --find="OLD_STRING" --replace="NEW_STRING" [--dry-run] [--no-git]
 */

// --- CONFIGURATION ---
$protectedFolders = [
    'vendor/',
    'node_modules/',
    'storage/',
    'bootstrap/cache/',
    'public/build/',
    'public/vendor/',
    '.git/',
];

$allowedFolders = [
    'app/',
    'resources/',
    'routes/',
    'config/',
    'public/js/',
];

$allowedExtensions = ['php', 'js', 'blade.php'];

$protectedFiles = [
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
];

// --- OPTIONS ---
$options = getopt("", ["find:", "replace:", "dry-run", "no-git"]);
$find = $options['find'] ?? null;
$replace = $options['replace'] ?? null;
$isDryRun = isset($options['dry-run']);
$useGit = !isset($options['no-git']);

if (!$find || $replace === null) {
    echo "Usage: php tools/safe_replace.php --find=\"STRING\" --replace=\"STRING\" [--dry-run] [--no-git]\n";
    exit(1);
}

echo "[SAFE REPLACE] Target: '$find' -> '$replace'\n";
if ($isDryRun)
    echo "[MODE] DRY RUN (No files will be modified)\n";

// --- FILE DISCOVERY ---
$targetFiles = [];

if ($useGit && is_dir('.git')) {
    echo "[SCAN] Using 'git ls-files' for discovery...\n";
    $output = [];
    exec("git ls-files", $output);
    foreach ($output as $file) {
        if (isFileSafe($file)) {
            $targetFiles[] = $file;
        }
    }
} else {
    echo "[SCAN] Using manual recursive scan (No Git mode)...\n";
    foreach ($allowedFolders as $folder) {
        if (is_dir($folder)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $relative = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $fileInfo->getRealPath());
                    if (isFileSafe($relative)) {
                        $targetFiles[] = $relative;
                    }
                }
            }
        }
    }
}

// --- BACKUP SETUP ---
$backupDir = null;
if (!$isDryRun) {
    $backupDir = 'backup_replace_' . date('Ymd_His');
    if (!mkdir($backupDir, 0777, true)) {
        die("Error: Could not create backup directory $backupDir\n");
    }
    echo "[BACKUP] Created: $backupDir/\n";
}

// --- PROCESSING ---
$matchCount = 0;
$fileCount = 0;

foreach ($targetFiles as $file) {
    if (!file_exists($file))
        continue;

    $content = file_get_contents($file);
    if (strpos($content, $find) !== false) {
        $matchCount++;
        echo "[MATCH] $file\n";

        if (!$isDryRun) {
            // Backup
            $dest = $backupDir . '/' . str_replace(['/', '\\'], '_', $file);
            copy($file, $dest);

            // Replace
            $newContent = str_replace($find, $replace, $content);
            file_put_contents($file, $newContent);
        }
    }
}

echo "\n------------------------------------------\n";
echo "Summary:\n";
echo "Files scanned: " . count($targetFiles) . "\n";
echo "Files matched: $matchCount\n";
if (!$isDryRun) {
    echo "Files updated: $matchCount\n";
    echo "Backup stored in: $backupDir/\n";
}
echo "------------------------------------------\n";

// --- HELPERS ---

function isFileSafe($path)
{
    global $protectedFolders, $allowedExtensions, $protectedFiles, $allowedFolders;

    // Normalize path
    $path = str_replace('\\', '/', $path);

    // 1. Check Protected Folders
    foreach ($protectedFolders as $p) {
        if (strpos($path, $p) === 0 || strpos($path, '/' . $p) !== false) {
            return false;
        }
    }

    // 2. Check Protected Files
    if (in_array(basename($path), $protectedFiles)) {
        return false;
    }

    // 3. Check Extensions
    $extFound = false;
    foreach ($allowedExtensions as $ext) {
        if (substr($path, -strlen($ext)) === $ext) {
            $extFound = true;
            break;
        }
    }
    if (!$extFound)
        return false;

    // 4. (Non-Git only) Check Allowed Folders if we started from root
    // But since we control discovery in Non-Git mode, we are already safe.

    return true;
}
