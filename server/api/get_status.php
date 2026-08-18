<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

$deviceId = $_GET['device_id'] ?? DEFAULT_DEVICE_ID;

$deviceId = preg_replace(
    '/[^a-zA-Z0-9_-]/', '', $deviceId
);

$statusDir = __DIR__ . '/status/';
$statusFile = $statusDir . $deviceId . '.json';

if (file_exists($statusFile)) {
    $status = json_decode(
        file_get_contents($statusFile), true
    );
    echo json_encode([
        'success' => true,
        'status' => $status
    ]);
} else {
    echo json_encode([
        'success' => true,
        'status' => [
            'read' => false,
            'last_read' => null
        ]
    ]);
}
