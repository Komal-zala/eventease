
<?php
// admin/attendance.php
session_start();
require_once __DIR__ . '/../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$selectedEventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$selectedEventId || $selectedEventId < 1) {
    $selectedEventId = 0;
}

$events = [];
$eventResult = $conn->query(
    "SELECT id, title, event_date
     FROM events
     ORDER BY event_date DESC, title ASC"
);

while ($row = $eventResult->fetch_assoc()) {
    $events[] = $row;
}

$attendanceRows = [];
$totalRegistrations = 0;
$totalPresent = 0;
$totalAbsent = 0;

if ($selectedEventId > 0) {
    $stmt = $conn->prepare(
        "SELECT r.id AS registration_id,
                r.registration_code,
                s.name AS student_name,
                s.enrollment_number,
                s.department,
                s.program,
                s.semester,
                e.title AS event_title,
                a.status AS attendance_status,
                a.check_in_time
         FROM registrations r
         INNER JOIN students s ON s.id = r.student_id
         INNER JOIN events e ON e.id = r.event_id
         LEFT JOIN attendance a ON a.registration_id = r.id
         WHERE r.event_id = ?
         ORDER BY s.name ASC"
    );

    $stmt->bind_param('i', $selectedEventId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $attendanceRows[] = $row;
        $totalRegistrations++;

        if (!empty($row['attendance_status'])) {
            $totalPresent++;
        } else {
            $totalAbsent++;
        }
    }

    $stmt->close();
}

function e($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventEase Attendance Dashboard</title>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #1f2937;
        }
        .container { max-width: 1200px; margin: auto; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px rgba(0,0,0,.06);
        }
        h1 { margin-top: 0; }
        label { display: block; font-weight: bold; margin-bottom: 8px; }
        select, input, button {
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
        }
        select { width: 100%; }
        button {
            background: #2563eb;
            color: white;
            border: 0;
            cursor: pointer;
            font-weight: bold;
        }
        .filter-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: end;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .stat {
            padding: 18px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }
        .stat strong { display: block; font-size: 27px; margin-top: 7px; }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }
        th { background: #f8fafc; }
        .badge {
            display: inline-block;
            border-radius: 20px;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
        }
        .present { background: #dcfce7; color: #166534; }
        .absent { background: #fee2e2; color: #991b1b; }
        .muted { color: #6b7280; }
        .links { display: flex; gap: 16px; flex-wrap: wrap; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .search { width: 100%; margin: 16px 0; }
        .empty { padding: 24px; text-align: center; color: #6b7280; }
        @media (max-width: 600px) {
            body { padding: 12px; }
            .stats { grid-template-columns: 1fr; }
            .filter-form { grid-template-columns: 1fr; }
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print, .filter-form, .search, .links { display: none !important; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="card">
        <h1>Attendance Dashboard</h1>
        <p class="muted">View event registrations and check-in status.</p>

        <div class="links no-print">
            <a href="scan_attendance.php">← Open QR Scanner</a>
            <a href="../frountend/event.php">View Events</a>
        </div>
    </div>

    <div class="card no-print">
        <form method="GET" action="attendance.php" class="filter-form">
            <div>
                <label for="event_id">Select Event</label>
                <select name="event_id" id="event_id" required>
                    <option value="">-- Choose an event --</option>
                    <?php foreach ($events as $event): ?>
                        <option
                            value="<?= (int) $event['id'] ?>"
                            <?= $selectedEventId === (int) $event['id'] ? 'selected' : '' ?>>
                            <?= e($event['title']) ?> (<?= e($event['event_date']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">View Attendance</button>
        </form>
    </div>

    <?php if ($selectedEventId > 0): ?>
        <div class="card">
            <h2><?= e($attendanceRows[0]['event_title'] ?? 'Selected Event') ?></h2>

            <div class="stats">
                <div class="stat">
                    Total Registrations
                    <strong><?= $totalRegistrations ?></strong>
                </div>
                <div class="stat">
                    Present
                    <strong><?= $totalPresent ?></strong>
                </div>
                <div class="stat">
                    Absent
                    <strong><?= $totalAbsent ?></strong>
                </div>
            </div>

            <div class="no-print">
                <input
                    type="search"
                    id="studentSearch"
                    class="search"
                    placeholder="Search by student name or enrollment number...">

                <button type="button" onclick="window.print()">Print Attendance</button>
            </div>

            <div class="table-wrap" style="margin-top:18px">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Enrollment No.</th>
                            <th>Department</th>
                            <th>Program</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Check-in Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($attendanceRows) > 0): ?>
                        <?php foreach ($attendanceRows as $index => $row): ?>
                            <?php $isPresent = !empty($row['attendance_status']); ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= e($row['student_name']) ?></td>
                                <td><?= e($row['enrollment_number']) ?></td>
                                <td><?= e($row['department']) ?></td>
                                <td><?= e($row['program']) ?></td>
                                <td><?= e($row['semester']) ?></td>
                                <td>
                                    <?php if ($isPresent): ?>
                                        <span class="badge present">Present</span>
                                    <?php else: ?>
                                        <span class="badge absent">Absent</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $isPresent && $row['check_in_time']
                                        ? e($row['check_in_time'])
                                        : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty">
                                No registrations found for this event.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <p class="empty">Choose an event above to view its attendance.</p>
        </div>
    <?php endif; ?>

</div>

<script>
const searchInput = document.getElementById('studentSearch');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#attendanceTable tbody tr');

        rows.forEach(function (row) {
            row.style.display = row.innerText.toLowerCase().includes(query)
                ? ''
                : 'none';
        });
    });
}
</script>
</body>
</html>