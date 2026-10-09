<?php
require_once "../config/database.php";

// Total Events
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events");
$totalEvents = mysqli_fetch_assoc($result)['total'];

// Upcoming Events
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM events 
     WHERE event_date > CURDATE()
     AND status = 'published'"
);
$upcomingEvents = mysqli_fetch_assoc($result)['total'];

// Current Events
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM events 
     WHERE event_date = CURDATE()
     AND status = 'published'"
);
$currentEvents = mysqli_fetch_assoc($result)['total'];

// Past Events
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM events 
     WHERE event_date < CURDATE()"
);
$pastEvents = mysqli_fetch_assoc($result)['total'];

// Total Participants
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM registrations"
);
$totalParticipants = mysqli_fetch_assoc($result)['total'];

// Present Participants
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM attendance 
     WHERE status = 'present'"
);
$presentParticipants = mysqli_fetch_assoc($result)['total'];

// Absent Participants
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM attendance 
     WHERE status = 'absent'"
);
$absentParticipants = mysqli_fetch_assoc($result)['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - EventEase</title>

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

        .navbar h2 {
            font-size: 22px;
        }

        .logout {
            color: white;
            text-decoration: none;
            background: #dc3545;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            padding: 30px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin-bottom: 8px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            font-size: 16px;
            color: #666;
            margin-bottom: 12px;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
        }

        .blue {
            border-left: 5px solid #007bff;
        }

        .green {
            border-left: 5px solid #28a745;
        }

        .orange {
            border-left: 5px solid #fd7e14;
        }

        .red {
            border-left: 5px solid #dc3545;
        }

        .purple {
            border-left: 5px solid #6f42c1;
        }

        .menu {
            margin-top: 35px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .menu a {
            background: white;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: #212529;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-weight: bold;
        }

        .menu a:hover {
            background: #212529;
            color: white;
        }

        @media (max-width: 900px) {
            .cards,
            .menu {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .cards,
            .menu {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="navbar">
        <h2>EventEase Admin</h2>

        <a href="../Frontend/Index.php" class="logout">
            Logout
        </a>
    </div>

    <div class="container">

        <div class="welcome">
            <h1>Admin Dashboard</h1>
            <p>Manage events, participants and attendance.</p>
        </div>

        <div class="cards">

            <div class="card blue">
                <h3>Total Events</h3>
                <div class="number">
                    <?= $totalEvents ?>
                </div>
            </div>

            <div class="card green">
                <h3>Upcoming Events</h3>
                <div class="number">
                    <?= $upcomingEvents ?>
                </div>
            </div>

            <div class="card orange">
                <h3>Current Events</h3>
                <div class="number">
                    <?= $currentEvents ?>
                </div>
            </div>

            <div class="card red">
                <h3>Past Events</h3>
                <div class="number">
                    <?= $pastEvents ?>
                </div>
            </div>

            <div class="card purple">
                <h3>Total Participants</h3>
                <div class="number">
                    <?= $totalParticipants ?>
                </div>
            </div>

            <div class="card green">
                <h3>Present</h3>
                <div class="number">
                    <?= $presentParticipants ?>
                </div>
            </div>

            <div class="card red">
                <h3>Absent</h3>
                <div class="number">
                    <?= $absentParticipants ?>
                </div>
            </div>

        </div>

        <div class="menu">

            <a href="events.php">
                📅 Manage Events
            </a>

            <a href="scan_attendance.php">
                   Start Event 
            </a>

            <a href="attendance.php">
                ✅ Attendance
            </a>

            <a href="attendance.php">
                ✅ participents
            </a>

        </div>

    </div>

</body>
</html>