
<?php
require_once __DIR__ . "/../config/database.php";

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

    <title>EventEase | Admin Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
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
            top: 100px;
            right: -100px;
            background: rgba(99, 102, 241, 0.10);
        }

        .circle-two {
            width: 230px;
            height: 230px;
            bottom: 10px;
            left: -90px;
            background: rgba(59, 130, 246, 0.10);
        }

        /* Navbar */
        .navbar {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 20px 5%;
            background: rgba(255, 255, 255, 0.88);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 20px rgba(30, 41, 59, 0.05);
            backdrop-filter: blur(12px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #818cf8);
            color: white;
            font-size: 22px;
            box-shadow: 0 5px 12px rgba(79, 70, 229, 0.2);
        }

        .navbar h2 {
            font-size: 22px;
            color: #1e293b;
            letter-spacing: -0.4px;
        }

        .brand small {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
        }

        .logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 17px;
            border-radius: 9px;
            background: #fff1f2;
            color: #be123c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .logout:hover {
            background: #ffe4e6;
            transform: translateY(-1px);
        }

        /* Main container */
        .container {
            width: 100%;
            max-width: 1350px;
            margin: auto;
            padding: 35px 25px 45px;
        }

        .welcome {
            margin-bottom: 28px;
        }

        .welcome h1 {
            margin-bottom: 10px;
            color: #1e293b;
            font-size: 30px;
            letter-spacing: -0.7px;
        }

        .welcome p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
        }

        .welcome-label {
            display: inline-block;
            margin-bottom: 12px;
            padding: 7px 12px;
            border-radius: 20px;
            background: #e0e7ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 700;
        }

        /* Statistics cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
        }

        .card {
            position: relative;
            overflow: hidden;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 8px 25px rgba(30, 41, 59, 0.06);
            backdrop-filter: blur(12px);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(30, 41, 59, 0.10);
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #6366f1;
        }

        .card.green::before {
            background: #16a34a;
        }

        .card.orange::before {
            background: #f97316;
        }

        .card.red::before {
            background: #ef4444;
        }

        .card.purple::before {
            background: #8b5cf6;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card h3 {
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }

        .card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 43px;
            height: 43px;
            border-radius: 12px;
            background: #eef2ff;
            font-size: 21px;
        }

        .green .card-icon {
            background: #dcfce7;
        }

        .orange .card-icon {
            background: #ffedd5;
        }

        .red .card-icon {
            background: #fee2e2;
        }

        .purple .card-icon {
            background: #ede9fe;
        }

        .number {
            color: #1e293b;
            font-size: 34px;
            font-weight: 750;
            line-height: 1.2;
        }

        .card-note {
            margin-top: 9px;
            color: #94a3b8;
            font-size: 12px;
        }

        /* Quick action section */
        .section-heading {
            margin: 38px 0 20px;
        }

        .section-heading h2 {
            margin-bottom: 7px;
            color: #1e293b;
            font-size: 22px;
        }

        .section-heading p {
            color: #64748b;
            font-size: 14px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            min-height: 105px;
            padding: 22px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.94);
            color: #1e293b;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(30, 41, 59, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .menu a:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.12);
            border-color: #c7d2fe;
        }

        .menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 23px;
        }

        .menu-text strong {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .menu-text small {
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 35px;
            padding: 18px 0 0;
            border-top: 1px solid rgba(203, 213, 225, 0.7);
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 1050px) {
            .cards,
            .menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px;
            }

            .navbar h2 {
                font-size: 19px;
            }

            .brand-icon {
                width: 40px;
                height: 40px;
            }

            .logout {
                padding: 10px 12px;
            }

            .container {
                padding: 25px 14px 30px;
            }

            .welcome h1 {
                font-size: 25px;
            }

            .cards,
            .menu {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .card {
                padding: 21px;
            }

            .number {
                font-size: 30px;
            }

            .menu a {
                min-height: 90px;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="background-decoration circle-one"></div>
<div class="background-decoration circle-two"></div>

<!-- Navbar -->
<nav class="navbar">

    <div class="brand">
        <div class="brand-icon">✦</div>

        <div>
            <h2>EventEase</h2>
            <small>Admin Panel</small>
        </div>
    </div>

    <a href="../Frontend/Index.php" class="logout">
        ↪ Logout
    </a>

</nav>

<!-- Dashboard -->
<main class="container">

    <div class="welcome">
        <span class="welcome-label">ADMIN OVERVIEW</span>

        <h1>Admin Dashboard</h1>

        <p>
            Welcome to EventEase. Manage your events, participants
            and attendance from one place.
        </p>
    </div>

    <!-- Statistics -->
    <div class="cards">

        <div class="card blue">
            <div class="card-top">
                <h3>Total Events</h3>
                <div class="card-icon">📅</div>
            </div>

            <div class="number">
                <?= (int) $totalEvents ?>
            </div>

            <p class="card-note">All events in the system</p>
        </div>

        <div class="card green">
            <div class="card-top">
                <h3>Upcoming Events</h3>
                <div class="card-icon">🗓️</div>
            </div>

            <div class="number">
                <?= (int) $upcomingEvents ?>
            </div>

            <p class="card-note">Published future events</p>
        </div>

        <div class="card orange">
            <div class="card-top">
                <h3>Current Events</h3>
                <div class="card-icon">⚡</div>
            </div>

            <div class="number">
                <?= (int) $currentEvents ?>
            </div>

            <p class="card-note">Published events scheduled today</p>
        </div>

        <div class="card red">
            <div class="card-top">
                <h3>Past Events</h3>
                <div class="card-icon">📋</div>
            </div>

            <div class="number">
                <?= (int) $pastEvents ?>
            </div>

            <p class="card-note">Events scheduled before today</p>
        </div>

        <div class="card purple">
            <div class="card-top">
                <h3>Total Participants</h3>
                <div class="card-icon">👥</div>
            </div>

            <div class="number">
                <?= (int) $totalParticipants ?>
            </div>

            <p class="card-note">Total event registrations</p>
        </div>

        <div class="card green">
            <div class="card-top">
                <h3>Present Participants</h3>
                <div class="card-icon">✅</div>
            </div>

            <div class="number">
                <?= (int) $presentParticipants ?>
            </div>

            <p class="card-note">Attendance marked present</p>
        </div>

        <div class="card red">
            <div class="card-top">
                <h3>Absent Participants</h3>
                <div class="card-icon">❌</div>
            </div>

            <div class="number">
                <?= (int) $absentParticipants ?>
            </div>

            <p class="card-note">Attendance marked absent</p>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="section-heading">
        <h2>Quick Actions</h2>
        <p>Choose a section to manage your event activities.</p>
    </div>

    <div class="menu">

        <a href="events.php">
            <div class="menu-icon">📅</div>

            <div class="menu-text">
                <strong>Manage Events</strong>
                <small>Create and manage college events</small>
            </div>
        </a>

        <a href="scan_attendance.php">
            <div class="menu-icon">📷</div>

            <div class="menu-text">
                <strong>Start Event Check-in</strong>
                <small>Scan participant QR codes</small>
            </div>
        </a>

        <a href="attendance.php">
            <div class="menu-icon">✅</div>

            <div class="menu-text">
                <strong>Attendance</strong>
                <small>View attendance and check-in status</small>
            </div>
        </a>

        <a href="attendance.php">
            <div class="menu-icon">👥</div>

            <div class="menu-text">
                <strong>Participants</strong>
                <small>View registered participants</small>
            </div>
        </a>

    </div>

    <div class="footer">
        EventEase Admin Dashboard &copy; <?= date('Y') ?>
    </div>

</main>

</body>
</html>