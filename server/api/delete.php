<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

$type = $_GET['type'] ?? '';
$file = $_GET['file'] ?? '';

$file = basename($file);

if (empty($file)) {
    echo json_encode([
        'success' => false,
        'error' => 'no file specified'
    ]);
    exit;
}

if ($type === 'device') {
    $filepath = rtrim(UPLOAD_DIR, '/') . '/' .
        DEFAULT_DEVICE_ID . '/' . $file;
} elseif ($type === 'reply') {
    $filepath = __DIR__ . '/replies/' . $file;
} else {
    echo json_encode([
        'success' => false,
        'error' => 'invalid type'
    ]);
    exit;
}

if (!file_exists($filepath)) {
    echo json_encode([
        'success' => false,
        'error' => 'file not found'
    ]);
    exit;
}

if (unlink($filepath)) {
    echo json_encode([
        'success' => true,
        'message' => 'deleted'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'delete failed'
    ]);
}
