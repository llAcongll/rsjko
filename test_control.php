<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Mock request
    $request = Illuminate\Http\Request::create('/dashboard/pengeluaran', 'GET', [
        'kategori' => 'PEGAWAI',
        'page' => 1,
        'limit' => 10
    ]);

    // Instantiate controller
    $controller = new App\Http\Controllers\PengeluaranController();

    // Call index
    $response = $controller->index($request);

    echo "Status: " . $response->status() . "\n";
    echo "Content: " . substr($response->content(), 0, 500) . "...\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
