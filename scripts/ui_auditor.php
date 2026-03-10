<?php
/**
 * UI Auto Auditor & Repair System
 * Standardizes Table UI across the application.
 * Usage: php ui_auditor.php [--fix] [--report=filename.md]
 */

$options = getopt("", ["fix", "report:"]);
$isFixMode = isset($options['fix']);
$artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/9700a2f7-d7df-442a-9f93-be53e637d4d9';
$reportFile = $options['report'] ?? ($artifactDir . '/table_audit_report.md');

$root = dirname(__DIR__); // Root project directory
$files = [];

// Define paths to scan
$scanPaths = [
    'resources/views' => ['blade.php'],
    'public/css' => ['css'],
    'public/js' => ['js']
];

echo "Scanning files in $root...\n";

foreach ($scanPaths as $path => $exts) {
    if (!is_dir($root . '/' . $path))
        continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $path));
    foreach ($it as $file) {
        if ($file->isDir())
            continue;
        $fileName = $file->getFilename();
        $fullPath = $file->getPathname();

        $shouldScan = false;
        foreach ($exts as $ext) {
            if (substr($fileName, -strlen($ext)) === $ext) {
                // Exclude exports from fixing to prevent breaking PDF/Excel layouts
                if (strpos($fullPath, 'dashboard' . DIRECTORY_SEPARATOR . 'exports') !== false) {
                    $shouldScan = false;
                } else {
                    $shouldScan = true;
                }
                break;
            }
        }

        if ($shouldScan) {
            $files[] = $fullPath;
        }
    }
}

$results = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false)
        continue;

    $original = $content;
    $issues = [];
    $isBlade = (strpos($file, '.blade.php') !== false);
    $isCss = (substr($file, -4) === '.css');
    $isJs = (substr($file, -3) === '.js');

    if ($isBlade) {
        // --- BLADE AUDITS ---

        // 1. Missing universal-table class on <table>
        $content = preg_replace_callback('/<table\b([^>]*?)>/i', function ($matches) use (&$issues) {
            $attrs = $matches[1];
            // Only fix if it's not already universal-table and we're not in a specifically excluded table
            if (strpos($attrs, 'universal-table') === false && strpos($attrs, 'no-audit') === false) {
                $issues[] = "Missing universal-table class";
                if (preg_match('/class=["\'](.*?)["\']/', $attrs, $classMatch)) {
                    $newClasses = trim($classMatch[1] . ' universal-table');
                    return '<table' . preg_replace('/class=["\'].*?["\']/', "class=\"$newClasses\"", $attrs) . '>';
                } else {
                    return '<table class="table universal-table"' . $attrs . '>';
                }
            }
            return $matches[0];
        }, $content);

        // 2. Remove inline styles from table header elements (<thead>, <tr>, <th>)
        $beforeStyles = $content;
        // Specifically targeting table elements to avoid breaking other components
        // Remove style attribute entirely from these tags
        $content = preg_replace('/<(thead|th|tr)([^>]*?)\sstyle=["\'][^"\']*["\']([^>]*?)>/i', '<$1$2$3>', $content);
        if ($content !== $beforeStyles) {
            $issues[] = "Inline styles removed from table elements";
        }

        // 3. Standard Column Classes (No -> checkbox-col, Aksi -> action-col)
        $content = preg_replace_callback('/<th([^>]*?)>\s*(No\.?|#)\s*<\/th>/i', function ($matches) use (&$issues) {
            if (strpos($matches[1], 'checkbox-col') === false) {
                $issues[] = "Missing .checkbox-col for No column";
                if (preg_match('/class=["\'](.*?)["\']/', $matches[1], $classMatch)) {
                    $newClasses = trim($classMatch[1] . ' checkbox-col');
                    return '<th' . preg_replace('/class=["\'].*?["\']/', "class=\"$newClasses\"", $matches[1]) . '>' . $matches[2] . '</th>';
                } else {
                    return '<th class="checkbox-col"' . $matches[1] . '>' . $matches[2] . '</th>';
                }
            }
            return $matches[0];
        }, $content);

        $content = preg_replace_callback('/<th([^>]*?)>\s*(Aksi|Action)\s*<\/th>/i', function ($matches) use (&$issues) {
            if (strpos($matches[1], 'action-col') === false) {
                $issues[] = "Missing .action-col for actions column";
                if (preg_match('/class=["\'](.*?)["\']/', $matches[1], $classMatch)) {
                    $newClasses = trim($classMatch[1] . ' action-col');
                    return '<th' . preg_replace('/class=["\'].*?["\']/', "class=\"$newClasses\"", $matches[1]) . '>' . $matches[2] . '</th>';
                } else {
                    return '<th class="action-col"' . $matches[1] . '>' . $matches[2] . '</th>';
                }
            }
            return $matches[0];
        }, $content);

        // 4. Wrap tables in table-container wrapper if missing
        if (preg_match_all('/<table[^>]*?class=["\'][^"\']*universal-table[^"\']*["\'][^>]*?>(.*?)<\/table>/is', $content, $matches, PREG_OFFSET_CAPTURE)) {
            // Loop backwards to not invalidate offsets
            for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
                $tableHtml = $matches[0][$i][0];
                $pos = $matches[0][$i][1];

                // Heuristic: Check if preceded by table-container within 250 characters
                $lookback = substr($content, max(0, $pos - 250), $pos - max(0, $pos - 250));
                if (strpos($lookback, 'table-container') === false) {
                    $issues[] = "Missing table-container wrapper";
                    if ($isFixMode) {
                        $content = substr_replace($content, '<div class="table-container">' . $tableHtml . '</div>', $pos, strlen($tableHtml));
                    }
                }
            }
        }

        // 5. Misplaced Toolbar / Search Input
        if (strpos($content, 'search-wrapper') !== false && strpos($content, 'table-toolbar') === false) {
            $issues[] = "Legacy search-wrapper detected outside table-toolbar";
        }

    } elseif ($isCss) {
        // --- CSS AUDITS ---
        $legacyPatterns = [
            '/\.table\s+th\b/' => 'Legacy .table th selector',
            '/\.table\s+thead\s+th\b/' => 'Legacy .table thead th selector',
            '/thead\s+th(?!\.universal-table)\b/' => 'Generic thead th selector',
            '/\.module-table\s+th\b/' => 'Legacy module-specific table selector'
        ];

        foreach ($legacyPatterns as $pattern => $msg) {
            if (preg_match($pattern, $content)) {
                $issues[] = $msg;
                if ($isFixMode) {
                    // Refactor to apply only to .universal-table if it was generic
                    $content = preg_replace($pattern, '.universal-table thead th', $content);
                }
            }
        }

    } elseif ($isJs) {
        // --- JS AUDITS ---
        // Flag js strings that create tables without the classes
        if (preg_match('/<table(?![^>]*?universal-table)/i', $content)) {
            $issues[] = "Hardcoded dynamic table in JS missing universal-table class";
        }
    }

    if (!empty($issues)) {
        $results[] = [
            'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file),
            'issues' => $issues,
            'status' => ($isFixMode && $content !== $original) ? 'Auto-fixed' : 'Detected'
        ];

        if ($isFixMode && $content !== $original) {
            file_put_contents($file, $content);
        }
    }
}

// Generate Report
$report = "# Table Audit Report\n\n";
$report .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
$report .= "Mode: " . ($isFixMode ? "Auto-Fix & Repair" : "Audit Only (No Changes Made)") . "\n\n";

if (empty($results)) {
    $report .= "Ã¢Å“â€¦ **All systems go!** No table inconsistencies detected across the application.\n";
} else {
    $report .= "| File | Issues Found / Resolved | Status |\n";
    $report .= "| :--- | :--- | :--- |\n";
    foreach ($results as $res) {
        $uniqueIssues = array_unique($res['issues']);
        $issueList = "";
        foreach ($uniqueIssues as $issue) {
            $issueList .= "- " . $issue . "<br>";
        }
        $report .= "| `{$res['file']}` | {$issueList} | **{$res['status']}** |\n";
    }
}

// Ensure artifact directory exists (it should, but just in case for a script)
if (!is_dir($artifactDir)) {
    mkdir($artifactDir, 0777, true);
}

file_put_contents($reportFile, $report);
echo "Audit complete. Processed " . count($files) . " files.\nFound " . count($results) . " files with issues.\nReport saved to: $reportFile\n";





