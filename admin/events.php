
<?php
require_once "../config/database.php";

// Delete event
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: events.php?message=deleted");
    exit;
}

// Get all events
$query = "SELECT * FROM events ORDER BY event_date ASC";
$result = mysqli_query($conn, $query);

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Events | EventEase</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            color: #26324b;
            background-color: #eaf0ff;
            background-image: linear-gradient(
                135deg,
                #dbeafe 0%,
                #ede9fe 45%,
                #e0f2fe 100%
            );
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
        }

        /* Decorative background circles */
        .bg-circle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(2px);
        }

        .circle-one {
            width: 280px;
            height: 280px;
            background: rgba(99, 102, 241, 0.13);
            top: 100px;
            right: -80px;
        }

        .circle-two {
            width: 230px;
            height: 230px;
            background: rgba(59, 130, 246, 0.12);
            bottom: 20px;
            left: -80px;
        }

        .circle-three {
            width: 140px;
            height: 140px;
            background: rgba(168, 85, 247, 0.10);
            top: 48%;
            left: 45%;
        }

        /* Navigation */
        .navbar {
            position: relative;
            z-index: 2;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            color: white;
            background: linear-gradient(110deg, #4f46e5, #6366f1, #7c3aed);
            box-shadow: 0 5px 22px rgba(79, 70, 229, 0.20);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 43px;
            height: 43px;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.20);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 23px;
        }

        .brand h2 {
            font-size: 23px;
            letter-spacing: 0.3px;
        }

        .brand p {
            margin-top: 4px;
            font-size: 12px;
            color: #e0e7ff;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 11px 17px;
            border-radius: 11px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.25s;
        }

        .nav-link:hover {
            background: white;
            color: #4f46e5;
            transform: translateY(-2px);
        }

        /* Main page */
        .container {
            position: relative;
            z-index: 1;
            width: 92%;
            max-width: 1500px;
            margin: 35px auto;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .heading-text h1 {
            font-size: 30px;
            color: #26356b;
            margin-bottom: 8px;
        }

        .heading-text p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
        }

        .add-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 13px 20px;
            border-radius: 12px;
            color: white;
            background: linear-gradient(110deg, #4f46e5, #7c3aed);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.22);
            text-decoration: none;
            font-weight: bold;
            transition: 0.25s;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 9px 20px rgba(79, 70, 229, 0.3);
        }

        /* Success message */
        .message {
            padding: 15px 18px;
            margin-bottom: 20px;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            background: rgba(236, 253, 245, 0.94);
            color: #047857;
            font-size: 14px;
            font-weight: bold;
        }

        /* Events table card */
        .table-box {
            padding: 24px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-top: 4px solid #6366f1;
            box-shadow: 0 12px 35px rgba(51, 65, 120, 0.10);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .table-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .table-heading h2 {
            font-size: 20px;
            color: #29366c;
        }

        .table-heading p {
            color: #7b849b;
            font-size: 13px;
            margin-top: 5px;
        }

        .table-label {
            padding: 8px 13px;
            border-radius: 20px;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 12px;
            font-weight: bold;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e6eaf2;
        }

        table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
        }

        thead {
            background: linear-gradient(110deg, #eef2ff, #f3e8ff);
        }

        th {
            padding: 16px 14px;
            text-align: left;
            color: #414b80;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            border-bottom: 1px solid #e0e7ff;
        }

        td {
            padding: 15px 14px;
            color: #475569;
            font-size: 13px;
            border-bottom: 1px solid #edf0f7;
            vertical-align: middle;
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f5f7ff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .event-id {
            color: #6366f1;
            font-weight: bold;
        }

        .event-title {
            color: #26356b;
            font-size: 14px;
            font-weight: bold;
            max-width: 190px;
            overflow-wrap: anywhere;
        }

        .date-text {
            color: #475569;
            white-space: nowrap;
        }

        .price {
            color: #4338ca;
            font-weight: bold;
            white-space: nowrap;
        }

        /* Status badges */
        .status {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status.published {
            background: #d1fae5;
            color: #047857;
        }

        .status.draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status.cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status.completed {
            background: #cffafe;
            color: #0e7490;
        }

        .status-default {
            background: #e2e8f0;
            color: #475569;
        }

        /* Action buttons */
        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .btn {
            display: inline-block;
            padding: 8px 11px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .view {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .view:hover {
            background: #bfdbfe;
        }

        .edit {
            color: #92400e;
            background: #fef3c7;
        }

        .edit:hover {
            background: #fde68a;
        }

        .delete {
            color: #b91c1c;
            background: #fee2e2;
        }

        .delete:hover {
            background: #fecaca;
        }

        .empty-state {
            text-align: center;
            padding: 45px 20px !important;
            color: #64748b;
            font-size: 15px;
        }

        .empty-icon {
            display: block;
            font-size: 35px;
            margin-bottom: 12px;
        }

        .footer-text {
            text-align: center;
            margin: 22px 0;
            font-size: 12px;
            color: #7b849b;
        }

        /* Responsive layout */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 4%;
            }

            .brand h2 {
                font-size: 20px;
            }

            .container {
                width: 94%;
                margin: 25px auto;
            }

            .heading-text h1 {
                font-size: 25px;
            }

            .page-heading {
                align-items: flex-start;
            }

            .add-btn {
                width: 100%;
            }

            .table-box {
                padding: 16px;
                border-radius: 16px;
            }

            .table-heading h2 {
                font-size: 18px;
            }
        }

        @media (max-width: 420px) {
            .nav-link {
                padding: 9px 11px;
                font-size: 12px;
            }

            .brand-icon {
                width: 38px;
                height: 38px;
            }

            .heading-text h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<div class="bg-circle circle-one"></div>
<div class="bg-circle circle-two"></div>
<div class="bg-circle circle-three"></div>

<header class="navbar">
    <div class="brand">
        <div class="brand-icon">🎓</div>
        <div>
            <h2>EventEase</h2>
            <p>Admin Management Panel</p>
        </div>
    </div>

    <a href="dashboard.php" class="nav-link">
        &#8592; Dashboard
    </a>
</header>

<main class="container">

    <section class="page-heading">
        <div class="heading-text">
            <h1>Manage Events</h1>
            <p>View, organize and manage all your college events in one place.</p>
        </div>

        <a href="add_event.php" class="add-btn">
            <span>+</span> Add New Event
        </a>
    </section>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'deleted'): ?>
        <div class="message">
            &#10003; Event deleted successfully.
        </div>
    <?php endif; ?>

    <section class="table-box">

        <div class="table-heading">
            <div>
                <h2>All Events</h2>
                <p>Event details and available actions</p>
            </div>

            <span class="table-label">EVENT MANAGEMENT</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Organizer</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>

                    <?php while ($event = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $status = strtolower((string)($event['status'] ?? ''));
                            $allowedStatuses = ['published', 'draft', 'cancelled', 'completed'];
                            $statusClass = in_array($status, $allowedStatuses, true)
                                ? $status
                                : 'status-default';

                            $startTime = !empty($event['start_time'])
                                ? date('h:i A', strtotime($event['start_time']))
                                : '';

                            $endTime = !empty($event['end_time'])
                                ? date('h:i A', strtotime($event['end_time']))
                                : '';
                        ?>

                        <tr>
                            <td class="event-id">
                                #<?= (int)$event['id'] ?>
                            </td>

                            <td>
                                <div class="event-title">
                                    <?= e($event['title']) ?>
                                </div>
                            </td>

                            <td class="date-text">
                                <?= !empty($event['event_date'])
                                    ? e(date('d M Y', strtotime($event['event_date'])))
                                    : '-' ?>
                            </td>

                            <td>
                                <?php if ($startTime !== ''): ?>
                                    <?= e($startTime) ?>
                                    <?php if ($endTime !== ''): ?>
                                        - <?= e($endTime) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td><?= e($event['venue']) ?></td>

                            <td><?= e($event['organizer']) ?></td>

                            <td><?= (int)$event['capacity'] ?></td>

                            <td class="price">
                                ₹<?= number_format((float)$event['price'], 2) ?>
                            </td>

                            <td>
                                <span class="status <?= e($statusClass) ?>">
                                    <?= e(ucfirst($status !== '' ? $status : 'Unknown')) ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="view_event.php?id=<?= (int)$event['id'] ?>"
                                        class="btn view">
                                        View
                                    </a>

                                    <a
                                        href="edit_event.php?id=<?= (int)$event['id'] ?>"
                                        class="btn edit">
                                        Edit
                                    </a>

                                    <a
                                        href="events.php?delete=<?= (int)$event['id'] ?>"
                                        class="btn delete"
                                        onclick="return confirm('Are you sure you want to delete this event?');">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" class="empty-state">
                            <span class="empty-icon">📅</span>
                            <strong>No events found</strong>
                            <p style="margin-top: 8px;">
                                Add your first event to see it listed here.
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

    <p class="footer-text">
        EventEase &copy; <?= date('Y') ?> · College Event Management System
    </p>

</main>

</body>
</html>
