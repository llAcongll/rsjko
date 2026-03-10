<?php
/**
 * CSS Conflict Cleaner for Universal Table Integrity
 * Scans and repairs CSS rules that break the standardized table system.
 * Usage: php scripts/css_conflict_cleaner.php [--fix] [--report=path/to/report.md]
 */

$options = getopt("", ["fix", "report:"]);
$isFixMode = isset($options['fix']);
$artifactDir = 'C:/Users/ASUS/.gemini/antigravity/brain/9700a2f7-d7df-442a-9f93-be53e637d4d9';
$reportFile = $options['report'] ?? ($artifactDir . '/css_conflict_report.md');

$root = dirname(__DIR__);
$files = [];

// Define paths to scan
$cssPaths = [
    'public/css' => ['css'],
    'resources/views' => ['blade.php']
];

echo "Ã°Å¸Å¡â‚¬ Starting CSS Conflict Cleaner...\n";

foreach ($cssPaths as $path => $exts) {
    if (!is_dir($root . '/' . $path))
        continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $path));
    foreach ($it as $file) {
        if ($file->isDir())
            continue;
        // Skip base.css to protect core rules
        if ($file->getFilename() === 'base.css')
            continue;
        // Skip exports directory as PDFs/Excels often need specific styling
        if (strpos($file->getPathname(), 'exports') !== false)
            continue;

        $fileName = $file->getFilename();
        foreach ($exts as $ext) {
            if (substr($fileName, -strlen($ext)) === $ext) {
                $files[] = $file->getPathname();
                break;
            }
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
    $isCss = (substr($file, -4) === '.css');
    $isBlade = (strpos($file, '.blade.php') !== false);

    if ($isCss) {
        // 1. Detect !important in Table Styling
        // Matches properties inside selectors targeting table elements
        $content = preg_replace_callback('/((?:table|\.table|th|td|thead|tbody)[^{]*?){([^}]*?)}/is', function ($matches) use (&$issues) {
            $selector = $matches[1];
            $rules = $matches[2];

            if (preg_match('/!important/i', $rules)) {
                $issues[] = "!important detected in table styling ($selector)";
                return $selector . '{' . str_replace(' !important', '', $rules) . '}';
            }
            return $matches[0];
        }, $content);

        // 2. Detect white-space: nowrap in Tables
        $content = preg_replace_callback('/((?:table|\.table|th|td|thead|tbody)[^{]*?){([^}]*?)}/is', function ($matches) use (&$issues) {
            $selector = $matches[1];
            $rules = $matches[2];

            if (preg_match('/white-space\s*:\s*nowrap/i', $rules)) {
                $issues[] = "white-space: nowrap detected in table ($selector)";
                return $selector . '{' . preg_replace('/white-space\s*:\s*nowrap/i', 'white-space: normal', $rules) . '}';
            }
            return $matches[0];
        }, $content);

        // 3. Detect Module-Specific Table Overrides & Hardcoded Widths
        $content = preg_replace_callback('/([#\.]\w+-(?:table|list|container)\b[^,]*?(?:th|td|tr|thead))\b[^{]*?{([^}]*?)}/is', function ($matches) use (&$issues) {
            $selector = $matches[1];
            $rules = $matches[2];

            $changed = false;
            // Detect hardcoded widths (excluding small pixels which might be borders)
            if (preg_match('/width\s*:\s*\d{2,}px/i', $rules)) {
                $issues[] = "Hardcoded width detected in $selector";
                $rules = preg_replace('/width\s*:\s*\d+px/i', 'width: auto', $rules);
                $changed = true;
            }

            if ($changed) {
                return $selector . '{' . $rules . '}';
            }
            return $matches[0];
        }, $content);

        // 4. Scoping legacy selectors to .universal-table
        $legacySelectors = [
            '/\b\.table\s+th\b/' => '.universal-table thead th',
            '/\b\.table\s+td\b/' => '.universal-table tbody td',
            '/\bthead\s+th\b/' => '.universal-table thead th'
        ];

        foreach ($legacySelectors as $pattern => $replacement) {
            if (preg_match($pattern, $content)) {
                $issues[] = "Legacy generic selector refactored to universal-table scope";
                $content = preg_replace($pattern, $replacement, $content);
            }
        }

    } elseif ($isBlade) {
        // 5. Detect Inline Style Conflicts in Blade templates
        // Removes style attribute from table related tags
        $content = preg_replace_callback('/<(th|td|tr|thead|tbody|table)([^>]*?)\sstyle=["\']([^"\']*)["\']([^>]*?)>/i', function ($matches) use (&$issues) {
            $tag = $matches[1];
            $styleContent = $matches[3];

            // Allow some inline styles if they are NOT related to fonts, colors, or widths
            // But usually the system wants them gone
            if (!empty($styleContent)) {
                $issues[] = "Inline style removed from <$tag>";
                return "<$tag{$matches[2]}{$matches[4]}>";
            }
            return $matches[0];
        }, $content);
    }

    if (!empty($issues)) {
        $results[] = [
            'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file),
            'issues' => array_unique($issues),
            'status' => ($isFixMode && $content !== $original) ? 'Auto-fixed' : 'Detected'
        ];

        if ($isFixMode && $content !== $original) {
            file_put_contents($file, $content);
        }
    }
}

// Generate Report
$report = "# CSS Conflict Audit Report\n\n";
$report .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
$report .= "Mode: " . ($isFixMode ? "Fix & Protect" : "Audit Only") . "\n\n";

if (empty($results)) {
    $report .= "Ã°Å¸â€ºÂ¡Ã¯Â¸Â **Universal Table System is fully protected.** No conflicts detected.\n";
} else {
    $report .= "| File | Conflict Details | Status |\n";
    $report .= "| :--- | :--- | :--- |\n";
    foreach ($results as $res) {
        $issueList = "";
        foreach ($res['issues'] as $issue) {
            $issueList .= "- " . $issue . "<br>";
        }
        $report .= "| `{$res['file']}` | {$issueList} | **{$res['status']}** |\n";
    }
}

if (!is_dir(dirname($reportFile))) {
    mkdir(dirname($reportFile), 0777, true);
}

file_put_contents($reportFile, $report);
echo "Cleaning complete. Processed " . count($files) . " files.\nFound " . count($results) . " files with style conflicts.\nReport saved to: $reportFile\n";





