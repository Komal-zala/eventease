
<?php
require_once __DIR__ . '/../config/database.php';


// Get event ID from URL
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Invalid event ID.');
}

// Fetch event from database
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

$stmt->close();

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

// Event image
$imageName = $event['image'] ?? '';
$imageFile = basename($imageName);
$imagePhysicalPath = __DIR__ . '/../uploads/events/' . $imageFile;

if (
    !empty($imageName) &&
    $imageName === $imageFile &&
    is_file($imagePhysicalPath)
) {
    $imageUrl = '../uploads/events/' . rawurlencode($imageFile);
} else {
    $imageUrl = 'assets/images/event-placeholder.jpg';
}

// Safely display database values
function escapeValue($value) {
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Format date and time
$eventDate = !empty($event['event_date'])
    ? date('d F Y', strtotime($event['event_date']))
    : 'Not provided';

$startTime = !empty($event['start_time'])
    ? date('h:i A', strtotime($event['start_time']))
    : 'Not provided';

$endTime = !empty($event['end_time'])
    ? date('h:i A', strtotime($event['end_time']))
    : 'Not provided';

$price = isset($event['price'])
    ? ((float)$event['price'] == 0
        ? 'FREE'
        : '₹' . number_format((float)$event['price'], 2))
    : 'Not provided';

// Determine registration status
$registrationOpen = $event['registration_open'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escapeValue($event['title'] ?? 'Event Details') ?> | EventEase</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f4fb;
            color: #242136;
        }

        .navbar {
            background: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px #0000000d;
        }

        .logo {
            color: #8b2be2;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link {
            color: #7024bf;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            max-width: 1050px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .event-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 35px #2c16440c;
        }

        .event-image {
            display: block;
            width: 100%;
            height: 380px;
            object-fit: cover;
            background: #eee5fa;
        }

        .event-content {
            padding: 32px;
        }

        .event-title {
            margin: 0 0 14px;
            font-size: 32px;
            overflow-wrap: anywhere;
        }

        .price {
            display: inline-block;
            background: #e9ffed;
            color: #16833a;
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 22px;
        }

        .description {
            color: #666174;
            line-height: 1.8;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .section-title {
            margin-top: 32px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee9f5;
            font-size: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .detail-item {
            padding: 17px;
            background: #faf8fe;
            border: 1px solid #f0e9fa;
            border-radius: 10px;
            min-width: 0;
        }

        .detail-label {
            color: #81788e;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .detail-value {
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .status {
            color: #16833a;
        }

        .closed {
            color: #c0392b;
        }

        .all-fields {
            display: grid;
            gap: 12px;
        }

        .all-fields .detail-item {
            display: grid;
            grid-template-columns: minmax(120px, 0.7fr) minmax(0, 1.3fr);
            gap: 12px;
        }

        .all-fields .detail-label {
            margin: 0;
        }

        .footer {
            text-align: center;
            color: #81788e;
            padding: 25px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 22px auto;
                padding: 0 12px;
            }

            .event-image {
                height: 230px;
            }

            .event-content {
                padding: 20px;
            }

            .event-title {
                font-size: 25px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .all-fields .detail-item {
                grid-template-columns: 1fr;
                gap: 7px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="event.php">EventEase</a>
    <a class="back-link" href="event.php">← Back to Events</a>
</header>

<main class="container">

    <article class="event-card">

        <img
            class="event-image"
            src="<?= escapeValue($imageUrl) ?>"
            alt="<?= escapeValue($event['title'] ?? 'Event image') ?>"
        >

        <div class="event-content">

            <h1 class="event-title">
                <?= escapeValue($event['title'] ?? 'Untitled Event') ?>
            </h1>

            <div class="price"><?= escapeValue($price) ?></div>

            
<?php if (
    (string)($event['registration_open'] ?? '0') === '1'
    && strtotime($event['event_date']) >= strtotime(date('Y-m-d'))
): ?>

    <a
        class="register-btn"
        href="register.php?event_id=<?= (int)$event['id'] ?>"
    >
        Register Now →
    </a>

<?php else: ?>

    <p class="registration-closed">
        Registration is currently closed.
    </p>

<?php endif; ?>



            <h2 class="section-title">About This Event</h2>

            <p class="description"><?= escapeValue($event['description'] ?? '') ?></p>

            <h2 class="section-title">Event Information</h2>

            <div class="details-grid">

                <div class="detail-item">
                    <div class="detail-label">Event Date</div>
                    <div class="detail-value"><?= escapeValue($eventDate) ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Time</div>
                    <div class="detail-value">
                        <?= escapeValue($startTime) ?> – <?= escapeValue($endTime) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Venue</div>
                    <div class="detail-value"><?= escapeValue($event['venue'] ?? '') ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Organizer</div>
                    <div class="detail-value"><?= escapeValue($event['organizer'] ?? '') ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Capacity</div>
                    <div class="detail-value"><?= escapeValue($event['capacity'] ?? '') ?> people</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Registration</div>
                    <div class="detail-value <?= (string)$registrationOpen === '1' ? 'status' : 'closed' ?>">
                        <?= (string)$registrationOpen === '1' ? 'Open' : 'Closed' ?>
                    </div>
                </div>

            </div>

            <h2 class="section-title">All Event Details</h2>

            <div class="all-fields">
           
<?php foreach ($event as $column => $value): ?>

    <?php
    // Skip fields that should not be displayed
    if (in_array($column, [
        'image',
        'created_at',
        'updated_at'
    ], true)) {
        continue;
    }

    if ($column === 'event_date') {
        $displayValue = $eventDate;
    } elseif ($column === 'start_time') {
        $displayValue = $startTime;
    } elseif ($column === 'end_time') {
        $displayValue = $endTime;
    } elseif ($column === 'price') {
        $displayValue = $price;
    } elseif ($column === 'registration_open') {
        $displayValue = (string)$value === '1'
            ? 'Open'
            : 'Closed';
    } else {
        $displayValue = ($value === null || $value === '')
            ? 'Not provided'
            : (string)$value;
    }

    $label = ucwords(str_replace('_', ' ', $column));
    ?>

    <div class="detail-item">
        <div class="detail-label">
            <?= escapeValue($label) ?>
        </div>

        <div class="detail-value">
            <?= escapeValue($displayValue) ?>
        </div>
    </div>

<?php endforeach; ?>

            </div>

        </div>
    </article>

</main>

<footer class="footer">
    © <?= date('Y') ?> EventEase. All rights reserved.
</footer>

</body>
</html>