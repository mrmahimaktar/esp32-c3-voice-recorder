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

$replyDir = __DIR__ . '/replies/';

if (!is_dir($replyDir)) {
    mkdir($replyDir, 0755, true);
}

$messageFile = $replyDir . $deviceId . '.wav';

if (file_exists($messageFile)) {
    echo json_encode([
        'success' => true,
        'has_message' => true,
        'file_size' => filesize($messageFile)
    ]);
} else {
    echo json_encode([
        'success' => true,
        'has_message' => false
    ]);
}
