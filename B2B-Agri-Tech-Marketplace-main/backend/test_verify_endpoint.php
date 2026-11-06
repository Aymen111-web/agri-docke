<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = \App\Models\Order::find(4);
$paymentService = app(\App\Services\PaymentService::class);
$chapaService = app(\App\Services\ChapaService::class);

$controller = app(\App\Http\Controllers\PaymentController::class);
$res = $controller->verifyOrderPayments($order->id, $chapaService, $paymentService);

echo "Controller Response Status: " . $res->status() . "\n";
echo "Controller Response Body: " . $res->getContent() . "\n";
