<?php

require_once __DIR__ . '/../config/config.php';

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== DEVICE_API_KEY) {
    http_response_code(403);
    echo 'forbidden';
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
$messageFile = $replyDir . $deviceId . '.wav';

if (!file_exists($messageFile)) {
    http_response_code(404);
    echo 'no message';
    exit;
}

$fileSize = filesize($messageFile);

header('Content-Type: audio/wav');
header('Content-Length: ' . $fileSize);

readfile($messageFile);

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
}

$status['delivered'] = true;
$status['delivered_at'] = date('Y-m-d H:i:s');

file_put_contents(
    $statusFile,
    json_encode($status, JSON_PRETTY_PRINT)
);

unlink($messageFile);
