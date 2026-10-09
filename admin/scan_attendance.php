
<?php
// admin/scan_attendance.php
session_start();
require_once __DIR__ . '/../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// QR scan requests return JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $registrationCode = trim($input['registration_code'] ?? '');

        if ($registrationCode === '' || strlen($registrationCode) > 255) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid QR code. Please scan again.'
            ]);
            exit;
        }

        // Find the registration and its student/event.
        $stmt = $conn->prepare(
            "SELECT r.id AS registration_id,
                    r.registration_code,
                    s.name AS student_name,
                    s.enrollment_number,
                    e.title AS event_title,
                    e.event_date
             FROM registrations r
             INNER JOIN students s ON s.id = r.student_id
             INNER JOIN events e ON e.id = r.event_id
             WHERE r.registration_code = ?
             LIMIT 1"
        );

        $stmt->bind_param('s', $registrationCode);
        $stmt->execute();
        $registration = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$registration) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid QR code. Registration not found.'
            ]);
            exit;
        }

        $registrationId = (int) $registration['registration_id'];

        // A transaction plus a unique index on registration_id
        // protects against duplicate check-ins.
        $conn->begin_transaction();

        $stmt = $conn->prepare(
            "SELECT id, status, check_in_time
             FROM attendance
             WHERE registration_id = ?
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $conn->commit();

            echo json_encode([
                'success' => false,
                'duplicate' => true,
                'message' => 'Already checked in!',
                'student' => $registration['student_name'],
                'enrollment' => $registration['enrollment_number'],
                'event' => $registration['event_title'],
                'check_in_time' => $existing['check_in_time']
            ]);
            exit;
        }

        $status = 'present';

        $stmt = $conn->prepare(
            "INSERT INTO attendance (registration_id, status, check_in_time)
             VALUES (?, ?, NOW())"
        );
        $stmt->bind_param('is', $registrationId, $status);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        // Return the saved check-in time.
        $stmt = $conn->prepare(
            "SELECT check_in_time
             FROM attendance
             WHERE registration_id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $saved = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Check-in successful!',
            'student' => $registration['student_name'],
            'enrollment' => $registration['enrollment_number'],
            'event' => $registration['event_title'],
            'check_in_time' => $saved['check_in_time'] ?? null
        ]);
    } catch (Throwable $e) {
        if ($conn->errno === 0) {
            // No action needed here; transaction handling below is best-effort.
        }

        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
            // There may be no active transaction.
        }

        error_log('EventEase QR attendance error: ' . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Could not record attendance. Please try again.'
        ]);
    }

    exit;
}

// Load events for the scanner's event selector.
$events = [];
$result = $conn->query(
    "SELECT id, title, event_date
     FROM events
     ORDER BY event_date DESC, title ASC"
);

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Attendance Scanner | EventEase</title>

    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #1f2937;
        }
        .container { max-width: 900px; margin: auto; }
        .card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.07);
            margin-bottom: 20px;
        }
        h1 { margin-top: 0; }
        label { display: block; margin: 12px 0 7px; font-weight: bold; }
        select, input, button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 15px;
        }
        button {
            cursor: pointer;
            background: #2563eb;
            color: white;
            border: 0;
            margin-top: 12px;
            font-weight: bold;
        }
        button.stop { background: #dc2626; }
        #reader { max-width: 500px; margin: 18px auto; }
        #result {
            padding: 14px;
            border-radius: 8px;
            margin-top: 16px;
            display: none;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }
        #result.success { display: block; background: #dcfce7; color: #166534; }
        #result.error { display: block; background: #fee2e2; color: #991b1b; }
        #result.duplicate { display: block; background: #fef3c7; color: #92400e; }
        .muted { color: #6b7280; }
        a { color: #2563eb; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>EventEase QR Attendance</h1>
        <p class="muted">Choose an event, start the camera, and scan a registered student's QR code.</p>

        <label for="eventFilter">Event</label>
        <select id="eventFilter">
            <option value="">All events</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= (int) $event['id'] ?>">
                    <?= htmlspecialchars($event['title']) ?>
                    (<?= htmlspecialchars($event['event_date']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <div id="reader"></div>

        <button id="startButton" type="button">Start Camera</button>
        <button id="stopButton" type="button" class="stop" disabled>Stop Camera</button>

        <div id="result" role="status" aria-live="polite"></div>

        <p style="margin-top:20px">
            <a href="attendance.php">View Attendance Dashboard →</a>
        </p>
    </div>
</div>

<script>
const readerElement = document.getElementById('reader');
const resultElement = document.getElementById('result');
const startButton = document.getElementById('startButton');
const stopButton = document.getElementById('stopButton');

let scanner = null;
let processing = false;
let cameraRunning = false;
let lastScannedCode = '';

function showResult(type, message) {
    resultElement.className = type;
    resultElement.innerHTML = message;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char];
    });
}

async function processQrCode(decodedText) {
    if (processing || decodedText === lastScannedCode) return;

    processing = true;
    lastScannedCode = decodedText;

    try {
        const response = await fetch('scan_attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registration_code: decodedText })
        });

        const data = await response.json();

        const details =
            '<br><strong>Student:</strong> ' + escapeHtml(data.student || '-') +
            '<br><strong>Enrollment:</strong> ' + escapeHtml(data.enrollment || '-') +
            '<br><strong>Event:</strong> ' + escapeHtml(data.event || '-') +
            (data.check_in_time
                ? '<br><strong>Check-in time:</strong> ' + escapeHtml(data.check_in_time)
                : '');

        if (data.success) {
            showResult('success', '<strong>✓ ' + escapeHtml(data.message) + '</strong>' + details);
        } else if (data.duplicate) {
            showResult('duplicate', '<strong>⚠ ' + escapeHtml(data.message) + '</strong>' + details);
        } else {
            showResult('error', '<strong>✗ ' + escapeHtml(data.message || 'Scan failed.') + '</strong>');
        }
    } catch (error) {
        showResult('error', '<strong>Could not contact the server. Check your connection and try again.</strong>');
    } finally {
        processing = false;

        // Allow the same QR to be scanned again after a short delay.
        setTimeout(() => {
            lastScannedCode = '';
        }, 2500);
    }
}

async function startCamera() {
    if (cameraRunning) return;

    try {
        scanner = new Html5Qrcode('reader');
        const cameras = await Html5Qrcode.getCameras();

        if (!cameras || cameras.length === 0) {
            showResult('error', 'No camera found. Connect a camera or check browser permissions.');
            scanner = null;
            return;
        }

        const camera = cameras[0];

        await scanner.start(
            camera.id,
            { fps: 10, qrbox: { width: 250, height: 250 } },
            processQrCode,
            () => {}
        );

        cameraRunning = true;
        startButton.disabled = true;
        stopButton.disabled = false;
        showResult('success', 'Camera started. Point it at a student QR code.');
    } catch (error) {
        scanner = null;
        showResult('error', 'Could not start camera. Allow camera access and use localhost or HTTPS.');
    }
}

async function stopCamera() {
    if (!scanner || !cameraRunning) return;

    try {
        await scanner.stop();
        await scanner.clear();
    } catch (error) {
        console.error(error);
    }

    scanner = null;
    cameraRunning = false;
    startButton.disabled = false;
    stopButton.disabled = true;
}

startButton.addEventListener('click', startCamera);
stopButton.addEventListener('click', stopCamera);
</script>
</body>
</html>