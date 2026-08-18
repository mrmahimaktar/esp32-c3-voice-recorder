<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'POST only'
    ]);
    exit;
}

$deviceId = $_POST['device_id'] ?? '';

if (empty($deviceId)) {
    $deviceId = DEFAULT_DEVICE_ID;
}

$deviceId = preg_replace(
    '/[^a-zA-Z0-9_-]/', '', $deviceId
);

if (!isset($_FILES['audio'])) {
    echo json_encode([
        'success' => false,
        'error' => 'no audio file'
    ]);
    exit;
}

$file = $_FILES['audio'];

if ($file['size'] > MAX_UPLOAD_SIZE) {
    echo json_encode([
        'success' => false,
        'error' => 'file too big'
    ]);
    exit;
}

$ext = strtolower(
    pathinfo($file['name'], PATHINFO_EXTENSION)
);

if ($ext !== 'wav') {
    echo json_encode([
        'success' => false,
        'error' => 'only .wav allowed'
    ]);
    exit;
}

$replyDir = __DIR__ . '/replies/';

if (!is_dir($replyDir)) {
    mkdir($replyDir, 0755, true);
}

$target = $replyDir . $deviceId . '.wav';

if (move_uploaded_file($file['tmp_name'], $target)) {

    $statusDir = __DIR__ . '/status/';

    if (!is_dir($statusDir)) {
        mkdir($statusDir, 0755, true);
    }

    $statusFile = $statusDir . $deviceId . '.json';

    $status = [
        'read' => false,
        'last_read' => null,
        'delivered' => false,
        'delivered_at' => null,
        'sent_at' => date('Y-m-d H:i:s'),
        'device_id' => $deviceId,
        'file_size' => filesize($target)
    ];

    file_put_contents(
        $statusFile,
        json_encode($status, JSON_PRETTY_PRINT)
    );

    echo json_encode([
        'success' => true,
        'message' => 'reply saved',
        'size' => filesize($target)
    ]);

} else {
    echo json_encode([
        'success' => false,
        'error' => 'save failed'
    ]);
}
