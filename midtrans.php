<?php
// Include semua file SDK Midtrans manual
require_once 'midtrans-lib/Midtrans/Config.php';
require_once 'midtrans-lib/Midtrans/Snap.php';
require_once 'midtrans-lib/Midtrans/Transaction.php';
require_once 'midtrans-lib/Midtrans/CoreApi.php';
require_once 'midtrans-lib/Midtrans/ApiRequestor.php';
require_once 'midtrans-lib/Midtrans/Sanitizer.php';
require_once 'midtrans-lib/Midtrans/Notification.php';
require_once 'midtrans-lib/SnapBi/SnapBi.php';
require_once 'midtrans-lib/SnapBi/SnapBiApiRequestor.php';
require_once 'midtrans-lib/SnapBi/SnapBiConfig.php';

// Konfigurasi
\Midtrans\Config::$serverKey = 'Mid-server-NprZ0Ak8h0Mz9KDmxpgmZ9oL';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil data dari fetch() JS
$input = json_decode(file_get_contents('php://input'));

// Persiapkan Snap transaction
$transaction = array(
    'transaction_details' => array(
        'order_id' => 'ORD-' . rand(100000,999999),
        'gross_amount' => (int)$input->amount
    ),
    'customer_details' => array(
        'first_name' => $input->name,
        'email' => $input->email,
        'phone' => $input->phone
    )
);

// Buat Snap Token
try {
    $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
