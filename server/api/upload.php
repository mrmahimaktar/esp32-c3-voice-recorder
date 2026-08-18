<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "POST required"
    ]);
    exit;
}

$apiKey = $_SERVER["HTTP_X_API_KEY"] ?? "";

if ($apiKey !== DEVICE_API_KEY) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

$deviceId = $_SERVER["HTTP_X_DEVICE_ID"]
    ?? DEFAULT_DEVICE_ID;

$deviceId = preg_replace(
    "/[^a-zA-Z0-9_-]/", "", $deviceId
);

if ($deviceId === "") {
    $deviceId = DEFAULT_DEVICE_ID;
}

$contentLength = intval(
    $_SERVER["CONTENT_LENGTH"] ?? 0
);

if ($contentLength <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Empty audio"
    ]);
    exit;
}

if ($contentLength > MAX_UPLOAD_SIZE) {
    echo json_encode([
        "success" => false,
        "message" => "File too large"
    ]);
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$deviceDir = rtrim(UPLOAD_DIR, "/") . "/" . $deviceId;

if (!is_dir($deviceDir)) {
    mkdir($deviceDir, 0755, true);
}

$filename = "REC_" . date("Ymd_His") . "_" .
    mt_rand(1000, 9999) . ".wav";

$tempFile = $deviceDir . "/" . $filename . ".tmp";
$finalFile = $deviceDir . "/" . $filename;

$input = fopen("php://input", "rb");
$output = fopen($tempFile, "wb");

if (!$input || !$output) {
    echo json_encode([
        "success" => false,
        "message" => "File open failed"
    ]);
    exit;
}

$bytes = stream_copy_to_stream($input, $output);

fclose($input);
fclose($output);

if ($bytes === false || $bytes <= 0) {
    @unlink($tempFile);
    echo json_encode([
        "success" => false,
        "message" => "Upload failed"
    ]);
    exit;
}

if (!rename($tempFile, $finalFile)) {
    @unlink($tempFile);
    echo json_encode([
        "success" => false,
        "message" => "Save failed"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Audio received",
    "device_id" => $deviceId,
    "filename" => $filename,
    "size" => $bytes
]);
