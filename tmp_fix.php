<?php
$f = 'app/Services/ReportService.php';
$content = file_get_contents($f);

// Replace string literals: DB::table('pendapatan_umum')
$content = preg_replace("/DB::table\('pendapatan_([a-z]+)'\)/", "\$this->getActiveRevenueQuery('pendapatan_$1')", $content);

// Replace variable: DB::table($table) 
$content = preg_replace('/DB::table\(\$table\)/', '$this->getActiveRevenueQuery($table)', $content);

// Add the method to ReportService before the last brace
$method = "
    public function getActiveRevenueQuery(\$table)
    {
        return DB::table(\$table)->whereExists(function (\$query) use (\$table) {
            \$query->select(DB::raw(1))
                ->from('revenue_masters')
                ->whereColumn('revenue_masters.id', \"{\$table}.revenue_master_id\")
                ->where('revenue_masters.is_posted', true);
        });
    }
}
";
$content = preg_replace('/}\s*$/', $method, $content);

file_put_contents($f, $content);
echo "Replaced properly\n";





