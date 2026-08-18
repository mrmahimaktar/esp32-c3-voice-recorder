<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== DEVICE_API_KEY) {
    echo json_encode([
        'success' => false,
        'error' => 'invalid api key'
    ]);
    exit;
}

$deviceId = $_SERVER['HTTP_X_DEVICE_ID']
    ?? DEFAULT_DEVICE_ID;

$deviceId = preg_replace(
    '/[^a-zA-Z0-9_-]/', '', $deviceId
);

if ($deviceId === '') {
    $deviceId = DEFAULT_DEVICE_ID;
}

$statusDir = __DIR__ . '/status/';

if (!is_dir($statusDir)) {
    mkdir($statusDir, 0755, true);
}

$statusFile = $statusDir . $deviceId . '.json';

$status = [];

if (file_exists($statusFile)) {
    $status = json_decode(
        file_get_contents($statusFile), true
    );
    if (!is_array($status)) {
        $status = [];
    }
}

$status['last_read'] = date('Y-m-d H:i:s');
$status['read'] = true;
$status['device_id'] = $deviceId;

file_put_contents(
    $statusFile,
    json_encode($status, JSON_PRETTY_PRINT)
);

echo json_encode([
    'success' => true,
    'message' => 'marked as read'
]);
