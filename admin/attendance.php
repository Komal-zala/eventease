
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
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EventEase | Attendance Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 30px 20px;
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;

            background:
                radial-gradient(
                    circle at top left,
                    #dbeafe 0,
                    transparent 36%
                ),
                radial-gradient(
                    circle at bottom right,
                    #ede9fe 0,
                    transparent 36%
                ),
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eff6ff,
                    #f5f3ff
                );
        }

        .background-decoration {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            filter: blur(2px);
        }

        .circle-one {
            width: 280px;
            height: 280px;
            top: 80px;
            right: -100px;
            background: rgba(99, 102, 241, 0.10);
        }

        .circle-two {
            width: 220px;
            height: 220px;
            bottom: 20px;
            left: -80px;
            background: rgba(59, 130, 246, 0.10);
        }

        .container {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            position: relative;
        }

        .card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 18px;
            padding: 26px;
            margin-bottom: 22px;

            box-shadow: 0 8px 30px rgba(30, 41, 59, 0.07);
            backdrop-filter: blur(12px);
        }

        .header-card {
            border-top: 4px solid #6366f1;
        }

        h1 {
            margin: 0 0 10px;
            color: #1e293b;
            font-size: 30px;
            font-weight: 750;
            letter-spacing: -0.5px;
        }

        h2 {
            margin: 0 0 22px;
            color: #334155;
            font-size: 23px;
        }

        p {
            line-height: 1.6;
        }

        .muted {
            color: #64748b;
        }

        .header-description {
            margin: 0 0 20px;
            font-size: 15px;
        }

        .links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .links a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 15px;
            border-radius: 9px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .links a:hover {
            background: #e0e7ff;
            transform: translateY(-1px);
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        select,
        input,
        button {
            font-family: inherit;
            font-size: 14px;
        }

        select,
        input[type="search"] {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            color: #1e293b;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        select:focus,
        input[type="search"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.13);
        }

        button {
            min-height: 46px;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.18);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
        }

        .filter-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: end;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat {
            position: relative;
            overflow: hidden;
            padding: 22px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .stat::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #6366f1;
        }

        .stat:nth-child(2)::before {
            background: #16a34a;
        }

        .stat:nth-child(3)::before {
            background: #ef4444;
        }

        .stat-label {
            display: block;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        .stat strong {
            display: block;
            margin-top: 10px;
            color: #1e293b;
            font-size: 32px;
            line-height: 1.2;
        }

        .search {
            margin: 0 0 14px;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
        }

        table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 14px;
            border-bottom: 1px solid #e8edf4;
            text-align: left;
            font-size: 13px;
            white-space: nowrap;
        }

        th {
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        tbody tr {
            transition: background 0.15s;
        }

        tbody tr:hover {
            background: #f8faff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .present {
            background: #dcfce7;
            color: #166534;
        }

        .absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            padding: 28px;
            color: #64748b;
            text-align: center;
        }

        .table-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .table-actions .search {
            flex: 1;
            min-width: 220px;
            margin: 0;
        }

        .print-button {
            background: linear-gradient(135deg, #334155, #475569);
            box-shadow: 0 4px 12px rgba(51, 65, 85, 0.15);
        }

        .print-button:hover {
            box-shadow: 0 6px 16px rgba(51, 65, 85, 0.22);
        }

        @media (max-width: 700px) {
            body {
                padding: 16px 10px;
            }

            .card {
                padding: 19px;
                border-radius: 14px;
            }

            h1 {
                font-size: 25px;
            }

            h2 {
                font-size: 20px;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .filter-form button {
                width: 100%;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .stat {
                padding: 18px;
            }

            .stat strong {
                font-size: 28px;
            }

            .table-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .table-actions .search {
                min-width: 0;
            }

            .print-button {
                width: 100%;
            }
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                color: #000;
            }

            .background-decoration,
            .no-print,
            .filter-form,
            .search,
            .links,
            button {
                display: none !important;
            }

            .container {
                max-width: 100%;
            }

            .card {
                padding: 12px;
                margin-bottom: 12px;
                border: 1px solid #ddd;
                border-radius: 0;
                box-shadow: none;
                background: #fff;
            }

            h1 {
                font-size: 23px;
            }

            h2 {
                font-size: 18px;
            }

            .stats {
                grid-template-columns: repeat(3, 1fr);
            }

            .stat {
                padding: 12px;
                background: #fff;
            }

            .table-wrap {
                overflow: visible;
                border: none;
            }

            table {
                min-width: 0;
                width: 100%;
            }

            th,
            td {
                padding: 7px 5px;
                font-size: 10px;
                white-space: normal;
            }

            th {
                background: #eee !important;
                print-color-adjust: exact;
            }

            .badge {
                padding: 3px 5px;
            }
        }
    </style>
</head>

<body>

<div class="background-decoration circle-one"></div>
<div class="background-decoration circle-two"></div>

<div class="container">

    <!-- Header -->
    <div class="card header-card">
        <h1>Attendance Dashboard</h1>

        <p class="muted header-description">
            View event registrations, monitor attendance, and check student check-in status.
        </p>

        <div class="links no-print">
            <a href="scan_attendance.php">← Open QR Scanner</a>
            <a href="events.php">View Events</a>
        </div>
    </div>

    <!-- Event Selection -->
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

                            <?= e($event['title']) ?>
                            (<?= e($event['event_date']) ?>)

                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <button type="submit">View Attendance</button>
        </form>
    </div>

    <?php if ($selectedEventId > 0): ?>

        <!-- Attendance Summary -->
        <div class="card">

            <h2>
                <?= e($attendanceRows[0]['event_title'] ?? 'Selected Event') ?>
            </h2>

            <div class="stats">

                <div class="stat">
                    <span class="stat-label">Total Registrations</span>
                    <strong><?= $totalRegistrations ?></strong>
                </div>

                <div class="stat">
                    <span class="stat-label">Present</span>
                    <strong><?= $totalPresent ?></strong>
                </div>

                <div class="stat">
                    <span class="stat-label">Absent</span>
                    <strong><?= $totalAbsent ?></strong>
                </div>

            </div>

            <!-- Search and Print -->
            <div class="table-actions no-print">

                <input
                    type="search"
                    id="studentSearch"
                    class="search"
                    placeholder="Search student name or enrollment number...">

                <button
                    type="button"
                    class="print-button"
                    onclick="window.print()">

                    Print Attendance
                </button>

            </div>

            <!-- Attendance Table -->
            <div class="table-wrap">

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

                            <?php
                            $isPresent = !empty($row['attendance_status']);
                            ?>

                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td><?= e($row['student_name']) ?></td>

                                <td><?= e($row['enrollment_number']) ?></td>

                                <td><?= e($row['department']) ?></td>

                                <td><?= e($row['program']) ?></td>

                                <td><?= e($row['semester']) ?></td>

                                <td>
                                    <?php if ($isPresent): ?>

                                        <span class="badge present">
                                            Present
                                        </span>

                                    <?php else: ?>

                                        <span class="badge absent">
                                            Absent
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                    if ($isPresent && !empty($row['check_in_time'])) {
                                        echo e($row['check_in_time']);
                                    } else {
                                        echo '—';
                                    }
                                    ?>
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
            <p class="empty">
                Choose an event above to view its attendance.
            </p>
        </div>

    <?php endif; ?>

</div>

<script>
const searchInput = document.getElementById('studentSearch');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();

        const rows = document.querySelectorAll(
            '#attendanceTable tbody tr'
        );

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
