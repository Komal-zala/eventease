<?php
require_once "../config/database.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header("Location: events.php");
    exit;
}

/* Get participant count */
$participantStmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?"
);

$participantStmt->bind_param("i", $id);
$participantStmt->execute();

$participantResult = $participantStmt->get_result();
$participantData = $participantResult->fetch_assoc();

$totalParticipants = $participantData['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Event - Admin</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h2 {
            margin: 0;
        }

        .btn {
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 5px;
            color: white;
            display: inline-block;
            margin-left: 5px;
        }

        .back {
            background: #6c757d;
        }

        .edit {
            background: #007bff;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .event-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .no-image {
            width: 100%;
            height: 250px;
            background: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #777;
        }

        .title {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }

        .label {
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .value {
            font-weight: bold;
            font-size: 16px;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
        }

        .published {
            background: #d4edda;
            color: #155724;
        }

        .draft {
            background: #fff3cd;
            color: #856404;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .participants {
            margin-top: 25px;
            padding: 20px;
            background: #eaf3ff;
            border-radius: 8px;
        }

        .participants strong {
            font-size: 25px;
        }

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .details {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="top-bar">

        <h2>📋 Event Details</h2>

        <div>

            <a href="events.php" class="btn back">
                ← Back
            </a>

            <a
                href="edit_event.php?id=<?= $event['id'] ?>"
                class="btn edit"
            >
                ✏️ Edit
            </a>

        </div>

    </div>


    <div class="card">

        <!-- Event Image -->

        <?php if (!empty($event['image'])): ?>

            <img
                src="../uploads/events/<?= htmlspecialchars($event['image']) ?>"
                alt="<?= htmlspecialchars($event['title']) ?>"
                class="event-image"
            >

        <?php else: ?>

            <div class="no-image">
                No Event Image
            </div>

        <?php endif; ?>


        <!-- Title -->

        <div class="title">

            <?= htmlspecialchars($event['title']) ?>

        </div>


        <!-- Description -->

        <div class="description">

            <?= nl2br(htmlspecialchars($event['description'])) ?>

        </div>


        <!-- Event Details -->

        <div class="details">

            <div class="detail-box">

                <div class="label">
                    📅 Event Date
                </div>

                <div class="value">

                    <?= date(
                        "d M Y",
                        strtotime($event['event_date'])
                    ) ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    ⏰ Time
                </div>

                <div class="value">

                    <?php if (!empty($event['start_time'])): ?>

                        <?= date(
                            "h:i A",
                            strtotime($event['start_time'])
                        ) ?>

                        <?php if (!empty($event['end_time'])): ?>

                            -
                            <?= date(
                                "h:i A",
                                strtotime($event['end_time'])
                            ) ?>

                        <?php endif; ?>

                    <?php else: ?>

                        Not specified

                    <?php endif; ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    📍 Venue
                </div>

                <div class="value">

                    <?= htmlspecialchars($event['venue']) ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    👤 Organizer
                </div>

                <div class="value">

                    <?= htmlspecialchars($event['organizer']) ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    👥 Capacity
                </div>

                <div class="value">

                    <?= htmlspecialchars($event['capacity']) ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    💰 Price
                </div>

                <div class="value">

                    <?php if ((float)$event['price'] == 0): ?>

                        Free

                    <?php else: ?>

                        ₹<?= number_format(
                            (float)$event['price'],
                            2
                        ) ?>

                    <?php endif; ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    📝 Registration
                </div>

                <div class="value">

                    <?php if ($event['registration_open']): ?>

                        Open

                    <?php else: ?>

                        Closed

                    <?php endif; ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    📊 Status
                </div>

                <div class="value">

                    <span
                        class="status <?= htmlspecialchars($event['status']) ?>"
                    >

                        <?= ucfirst(
                            htmlspecialchars($event['status'])
                        ) ?>

                    </span>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    ⏳ Registration Deadline
                </div>

                <div class="value">

                    <?php if (!empty($event['registration_deadline'])): ?>

                        <?= date(
                            "d M Y, h:i A",
                            strtotime($event['registration_deadline'])
                        ) ?>

                    <?php else: ?>

                        No deadline

                    <?php endif; ?>

                </div>

            </div>


            <div class="detail-box">

                <div class="label">
                    🆔 Event ID
                </div>

                <div class="value">

                    #<?= $event['id'] ?>

                </div>

            </div>

        </div>


        <!-- Participants -->

        <div class="participants">

            <div>
                👥 Registered Participants
            </div>

            <strong>
                <?= $totalParticipants ?>
            </strong>

            <div>
                out of <?= $event['capacity'] ?> seats
            </div>

        </div>

    </div>

</div>

</body>
</html>