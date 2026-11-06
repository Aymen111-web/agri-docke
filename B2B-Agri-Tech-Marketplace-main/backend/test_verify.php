<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payment = \App\Models\Payment::latest()->first();
$chapaService = app(\App\Services\ChapaService::class);
$res = $chapaService->verifyTransaction($payment->chapa_tx_ref);

echo "Success: " . ($res['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "Status: " . ($res['status'] ?? 'N/A') . "\n";
echo "Chapa Status: " . ($res['data']['status'] ?? 'N/A') . "\n";
