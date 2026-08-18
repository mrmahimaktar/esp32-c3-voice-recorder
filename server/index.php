<?php

session_start();

require_once __DIR__ . '/config/config.php';

$FIXED_PASSWORD = '123456';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $FIXED_PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        $loginError = 'Wrong password!';
    }
}

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Voice Device - Login</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #1a1a2e; color: #eee;
            min-height: 100vh;
            display: flex; align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #16213e; padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            text-align: center; width: 350px;
        }
        .login-box h1 { color: #e94560; margin-bottom: 10px; font-size: 24px; }
        .login-box p { color: #888; margin-bottom: 25px; }
        .login-box input {
            width: 100%; padding: 15px;
            border: 2px solid #333; border-radius: 8px;
            background: #0f3460; color: #fff;
            font-size: 18px; text-align: center;
            letter-spacing: 8px; margin-bottom: 15px;
        }
        .login-box input:focus { outline: none; border-color: #e94560; }
        .login-box button {
            width: 100%; padding: 15px;
            background: #e94560; color: white;
            border: none; border-radius: 8px;
            font-size: 16px; cursor: pointer;
        }
        .login-box button:hover { background: #c73650; }
        .error { color: #ff6b6b; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🎙️ Voice Device</h1>
        <p>Enter password to access</p>
        <?php if (isset($loginError)): ?>
            <p class="error"><?php echo $loginError; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="password"
                   placeholder="••••••" maxlength="10"
                   required autofocus>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// ============================================
// DASHBOARD
// ============================================

$uploadDir = rtrim(UPLOAD_DIR, '/') . '/' . DEFAULT_DEVICE_ID . '/';
$replyDir = __DIR__ . '/api/replies/';
$statusDir = __DIR__ . '/api/status/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($replyDir)) mkdir($replyDir, 0755, true);
if (!is_dir($statusDir)) mkdir($statusDir, 0755, true);

// Device recordings

$recordings = [];

if (is_dir($uploadDir)) {
    $files = glob($uploadDir . '*.wav');
    foreach ($files as $file) {
        $recordings[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file)),
            'url' => 'uploads/' . DEFAULT_DEVICE_ID . '/' . basename($file)
        ];
    }
    usort($recordings, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Reply files

$replies = [];

if (is_dir($replyDir)) {
    $files = glob($replyDir . '*.wav');
    foreach ($files as $file) {
        $replies[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file)),
            'url' => 'api/replies/' . basename($file)
        ];
    }
    usort($replies, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Read status

$readStatus = [
    'read' => false,
    'delivered' => false,
    'last_read' => null,
    'delivered_at' => null,
    'sent_at' => null
];

$statusFile = $statusDir . DEFAULT_DEVICE_ID . '.json';

if (file_exists($statusFile)) {
    $readStatus = json_decode(
        file_get_contents($statusFile), true
    );
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Voice Device Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #1a1a2e; color: #eee;
            min-height: 100vh; padding: 15px;
        }
        .header {
            display: flex; justify-content: space-between;
            align-items: center; padding: 15px 20px;
            background: #16213e; border-radius: 10px;
            margin-bottom: 20px;
        }
        .header h1 { color: #e94560; font-size: 20px; }
        .logout-btn {
            background: #333; color: #aaa; border: none;
            padding: 8px 15px; border-radius: 5px;
            cursor: pointer; text-decoration: none;
        }
        .logout-btn:hover { background: #e94560; color: white; }
        .section {
            background: #16213e; border-radius: 10px;
            padding: 20px; margin-bottom: 20px;
        }
        .section h2 {
            color: #e94560; margin-bottom: 15px; font-size: 18px;
            border-bottom: 1px solid #333; padding-bottom: 10px;
        }
        .upload-area {
            border: 2px dashed #333; border-radius: 10px;
            padding: 30px; text-align: center;
        }
        .upload-area input[type="file"] { display: none; }
        .upload-label {
            display: inline-block; background: #0f3460;
            color: #fff; padding: 12px 25px;
            border-radius: 8px; cursor: pointer;
        }
        .upload-label:hover { background: #1a4a8a; }
        .upload-info { color: #666; font-size: 12px; margin-top: 10px; }
        .send-btn {
            background: #e94560; color: white; border: none;
            padding: 12px 30px; border-radius: 8px;
            font-size: 16px; cursor: pointer;
            margin-top: 15px; display: none;
        }
        .send-btn:hover { background: #c73650; }
        .status-msg {
            margin-top: 15px; padding: 10px;
            border-radius: 5px; display: none;
        }
        .status-success { background: #1a4a2e; color: #4ecdc4; display: block; }
        .status-error { background: #4a1a1a; color: #ff6b6b; display: block; }
        .audio-list { list-style: none; }
        .audio-item {
            background: #0f3460; border-radius: 8px;
            padding: 15px; margin-bottom: 10px;
            display: flex; align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 10px;
        }
        .audio-info { flex:1; min-width:150px; }
        .audio-info .name { color: #fff; font-weight: bold; font-size: 14px; }
        .audio-info .meta { color: #666; font-size: 12px; margin-top: 3px; }
        .audio-controls { display: flex; align-items: center; gap: 8px; }
        .audio-controls audio { height: 35px; max-width: 200px; }
        .delete-btn {
            background: #e94560; color: white; border: none;
            padding: 8px 12px; border-radius: 5px;
            cursor: pointer; font-size: 12px;
        }
        .delete-btn:hover { background: #c73650; }
        .empty-msg { color: #555; text-align: center; padding: 20px; }
        .badge {
            background: #e94560; color: white;
            padding: 2px 8px; border-radius: 10px;
            font-size: 12px; margin-left: 8px;
        }
        .pending-badge { background: #f39c12; }

        .read-status {
            padding: 15px; border-radius: 8px;
            margin-bottom: 15px;
        }
        .read-yes {
            background: #1a4a2e; border: 1px solid #4ecdc4;
        }
        .read-no {
            background: #4a3a1a; border: 1px solid #f39c12;
        }
        .read-none {
            background: #2a2a3e; border: 1px solid #555;
        }
        .tick { color: #4ecdc4; font-size: 18px; }
        .cross { color: #f39c12; font-size: 18px; }

        .refresh-btn {
            background: #0f3460; color: #aaa; border: none;
            padding: 6px 12px; border-radius: 5px;
            cursor: pointer; font-size: 12px;
            margin-left: 10px;
        }
        .refresh-btn:hover { background: #1a5a9a; color: white; }

        @media (max-width: 600px) {
            .audio-item { flex-direction: column; align-items: flex-start; }
            .audio-controls { width: 100%; }
            .audio-controls audio { flex: 1; max-width: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>🎙️ Voice Device</h1>
        <div>
            <button class="refresh-btn"
                    onclick="location.reload()">
                🔄 Refresh
            </button>
            <a href="?logout=1" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- READ STATUS -->

    <div class="section">
        <h2>📊 Message Status</h2>

        <?php if ($readStatus['sent_at'] === null && empty($replies)): ?>

            <div class="read-status read-none">
                <p>📭 No message sent yet</p>
                <p style="color:#666; font-size:12px; margin-top:5px;">
                    Send a voice reply below
                </p>
            </div>

        <?php elseif (isset($readStatus['read']) && $readStatus['read'] === true): ?>

            <div class="read-status read-yes">
                <p>
                    <span class="tick">✓✓</span>
                    Message <strong>HEARD</strong> by device
                </p>
                <p style="color:#888; font-size:12px; margin-top:5px;">
                    Heard at: <?php echo $readStatus['last_read'] ?? 'unknown'; ?>
                </p>
                <?php if (isset($readStatus['sent_at'])): ?>
                <p style="color:#666; font-size:12px;">
                    Sent at: <?php echo $readStatus['sent_at']; ?>
                </p>
                <?php endif; ?>
            </div>

        <?php elseif (isset($readStatus['delivered']) && $readStatus['delivered'] === true): ?>

            <div class="read-status read-no">
                <p>
                    <span class="tick">✓</span>
                    Message <strong>DELIVERED</strong> to device
                </p>
                <p style="color:#888; font-size:12px; margin-top:5px;">
                    Downloaded at: <?php echo $readStatus['delivered_at'] ?? 'unknown'; ?>
                </p>
                <p style="color:#f39c12; font-size:12px;">
                    ⏳ Not heard yet (waiting for B3 hold)
                </p>
            </div>

        <?php elseif (!empty($replies)): ?>

            <div class="read-status read-no">
                <p>
                    <span class="cross">○</span>
                    Message <strong>PENDING</strong>
                </p>
                <p style="color:#888; font-size:12px; margin-top:5px;">
                    Waiting for device to download
                </p>
                <?php if (isset($readStatus['sent_at'])): ?>
                <p style="color:#666; font-size:12px;">
                    Sent at: <?php echo $readStatus['sent_at']; ?>
                </p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="read-status read-yes">
                <p>
                    <span class="tick">✓✓</span>
                    Last message was heard
                </p>
                <?php if (isset($readStatus['last_read'])): ?>
                <p style="color:#888; font-size:12px; margin-top:5px;">
                    Heard at: <?php echo $readStatus['last_read']; ?>
                </p>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

    <!-- SEND REPLY -->

    <div class="section">
        <h2>📤 Send Voice to Device</h2>
        <div class="upload-area">
            <label class="upload-label" for="replyFile">
                📁 Choose WAV File
            </label>
            <input type="file" id="replyFile" accept=".wav">
            <p id="fileName" style="color:#aaa; margin-top:10px;">
                No file selected
            </p>
            <p class="upload-info">
                WAV only | Max 200KB | 8000Hz 16bit Mono recommended
            </p>
            <button class="send-btn" id="sendBtn" onclick="sendReply()">
                🚀 Send to Device
            </button>
        </div>
        <div class="status-msg" id="uploadStatus"></div>
    </div>

    <!-- DEVICE RECORDINGS -->

    <div class="section">
        <h2>
            📥 Device Recordings
            <span class="badge"><?php echo count($recordings); ?></span>
        </h2>
        <?php if (empty($recordings)): ?>
            <p class="empty-msg">No recordings from device yet</p>
        <?php else: ?>
            <ul class="audio-list">
            <?php foreach ($recordings as $rec): ?>
                <li class="audio-item" id="rec-<?php echo md5($rec['name']); ?>">
                    <div class="audio-info">
                        <div class="name">
                            🎤 <?php echo htmlspecialchars($rec['name']); ?>
                        </div>
                        <div class="meta">
                            <?php echo $rec['date']; ?> |
                            <?php echo round($rec['size']/1024, 1); ?> KB
                        </div>
                    </div>
                    <div class="audio-controls">
                        <audio controls preload="none">
                            <source src="<?php echo $rec['url']; ?>" type="audio/wav">
                        </audio>
                        <button class="delete-btn"
                                onclick="deleteAudio('device', '<?php echo $rec['name']; ?>', 'rec-<?php echo md5($rec['name']); ?>')">
                            🗑️
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- SENT REPLIES -->

    <div class="section">
        <h2>
            📨 Sent Replies
            <?php if (!empty($replies)): ?>
                <span class="badge pending-badge">
                    <?php echo count($replies); ?> pending
                </span>
            <?php endif; ?>
        </h2>
        <?php if (empty($replies)): ?>
            <p class="empty-msg">No pending replies</p>
        <?php else: ?>
            <ul class="audio-list">
            <?php foreach ($replies as $rep): ?>
                <li class="audio-item" id="rep-<?php echo md5($rep['name']); ?>">
                    <div class="audio-info">
                        <div class="name">
                            📨 <?php echo htmlspecialchars($rep['name']); ?>
                        </div>
                        <div class="meta">
                            <?php echo $rep['date']; ?> |
                            <?php echo round($rep['size']/1024, 1); ?> KB |
                            ⏳ Waiting for device
                        </div>
                    </div>
                    <div class="audio-controls">
                        <audio controls preload="none">
                            <source src="<?php echo $rep['url']; ?>" type="audio/wav">
                        </audio>
                        <button class="delete-btn"
                                onclick="deleteAudio('reply', '<?php echo $rep['name']; ?>', 'rep-<?php echo md5($rep['name']); ?>')">
                            🗑️
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('replyFile')
        .addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var sizeKB = Math.round(file.size / 1024);
            document.getElementById('fileName').innerText =
                file.name + ' (' + sizeKB + ' KB)';
            document.getElementById('sendBtn').style.display = 'inline-block';
            if (file.size > 200000) {
                showStatus('File too big! Max 200KB', 'error');
                document.getElementById('sendBtn').style.display = 'none';
            }
            if (!file.name.toLowerCase().endsWith('.wav')) {
                showStatus('Only .wav files allowed!', 'error');
                document.getElementById('sendBtn').style.display = 'none';
            }
        } else {
            document.getElementById('fileName').innerText = 'No file selected';
            document.getElementById('sendBtn').style.display = 'none';
        }
    });

    function sendReply() {
        var file = document.getElementById('replyFile').files[0];
        if (!file) { showStatus('Select a file!', 'error'); return; }
        if (file.size > 200000) { showStatus('Too big!', 'error'); return; }
        if (!file.name.toLowerCase().endsWith('.wav')) { showStatus('.wav only!', 'error'); return; }

        document.getElementById('sendBtn').innerText = '⏳ Sending...';
        document.getElementById('sendBtn').disabled = true;

        var fd = new FormData();
        fd.append('audio', file);
        fd.append('device_id', '<?php echo DEFAULT_DEVICE_ID; ?>');

        fetch('api/save_reply.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showStatus('Sent! Device will receive on B2 hold.', 'success');
                setTimeout(function() { location.reload(); }, 2000);
            } else { showStatus('Error: ' + d.error, 'error'); }
            document.getElementById('sendBtn').innerText = '🚀 Send to Device';
            document.getElementById('sendBtn').disabled = false;
        })
        .catch(function(e) {
            showStatus('Failed: ' + e, 'error');
            document.getElementById('sendBtn').innerText = '🚀 Send to Device';
            document.getElementById('sendBtn').disabled = false;
        });
    }

    function deleteAudio(type, filename, elementId) {
        if (!confirm('Delete?')) return;
        fetch('api/delete.php?type=' + type + '&file=' + encodeURIComponent(filename))
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var el = document.getElementById(elementId);
                if (el) el.style.display = 'none';
            } else { alert('Failed: ' + d.error); }
        });
    }

    function showStatus(msg, type) {
        var el = document.getElementById('uploadStatus');
        el.innerText = msg;
        el.className = 'status-msg';
        el.classList.add(type === 'success' ? 'status-success' : 'status-error');
    }

    // Auto refresh status every 30 seconds
    setInterval(function() {
        fetch('api/get_status.php?device_id=<?php echo DEFAULT_DEVICE_ID; ?>')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.status.read === true) {
                location.reload();
            }
        });
    }, 30000);
    </script>

</body>
</html>
