<?php
require_once "../config/database.php";

// Delete event
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $query = "DELETE FROM events WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: events.php?message=deleted");
        exit;
    }
}

// Get all events
$query = "SELECT * FROM events ORDER BY event_date ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Events - EventEase</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .navbar {
            background: #212529;
            color: white;
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: #495057;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            padding: 30px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .add-btn {
            background: #198754;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 5px;
        }

        .message {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #212529;
            color: white;
        }

        .btn {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
            color: white;
            font-size: 13px;
        }

        .view {
            background: #0d6efd;
        }

        .edit {
            background: #ffc107;
            color: #000;
        }

        .delete {
            background: #dc3545;
        }

        .status {
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
        }

        .published {
            background: #d1e7dd;
            color: #0f5132;
        }

        .draft {
            background: #fff3cd;
            color: #664d03;
        }

        .cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .completed {
            background: #cff4fc;
            color: #055160;
        }
    </style>
</head>

<body>

<div class="navbar">

    <h2>EventEase Admin</h2>

    <a href="dashboard.php">
        ← Dashboard
    </a>

</div>

<div class="container">

    <div class="top">

        <div>
            <h1>Manage Events</h1>
            <p>View and manage all events.</p>
        </div>

        <a href="add_event.php" class="add-btn">
            + Add Event
        </a>

    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] == 'deleted'): ?>

        <div class="message">
            Event deleted successfully.
        </div>

    <?php endif; ?>

    <div class="table-box">

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

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($event = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?= $event['id'] ?>
                        </td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($event['title']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= date("d M Y", strtotime($event['event_date'])) ?>
                        </td>

                        <td>
                            <?= $event['start_time'] ?: '-' ?>

                            <?php if ($event['end_time']): ?>
                                -
                                <?= $event['end_time'] ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event['venue']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event['organizer']) ?>
                        </td>

                        <td>
                            <?= $event['capacity'] ?>
                        </td>

                        <td>
                            ₹<?= number_format($event['price'], 2) ?>
                        </td>

                        <td>

                            <span class="status <?= $event['status'] ?>">

                                <?= ucfirst($event['status']) ?>

                            </span>

                        </td>

                        <td>

                            <a
                                href="view_event.php?id=<?= $event['id'] ?>"
                                class="btn view">
                                View
                            </a>

                            <a
                                href="edit_event.php?id=<?= $event['id'] ?>"
                                class="btn edit">
                                Edit
                            </a>

                            <a
                                href="events.php?delete=<?= $event['id'] ?>"
                                class="btn delete"
                                onclick="return confirm('Are you sure you want to delete this event?');">
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="10" style="text-align:center;">
                        No events found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>