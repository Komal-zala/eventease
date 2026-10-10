
<?php
require_once __DIR__ . '/../config/database.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Invalid event ID.');
}

$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to load event details.');
}

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

function escapeValue($value)
{
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* Event Image */
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

/* Date and Time */
$eventDate = !empty($event['event_date'])
    && strtotime($event['event_date']) !== false
    ? date('d F Y', strtotime($event['event_date']))
    : 'Not provided';

$startTime = !empty($event['start_time'])
    && strtotime($event['start_time']) !== false
    ? date('h:i A', strtotime($event['start_time']))
    : 'Not provided';

$endTime = !empty($event['end_time'])
    && strtotime($event['end_time']) !== false
    ? date('h:i A', strtotime($event['end_time']))
    : 'Not provided';

/* Price */
$price = isset($event['price']) && $event['price'] !== ''
    ? (
        (float)$event['price'] == 0
            ? 'FREE'
            : '₹' . number_format((float)$event['price'], 2)
    )
    : 'Not provided';

/* Registration Status */
$registrationOpen =
    (string)($event['registration_open'] ?? '0') === '1';

$validEventDate = !empty($event['event_date'])
    && strtotime($event['event_date']) !== false;

$eventNotPassed = $validEventDate
    && strtotime($event['event_date']) >= strtotime(date('Y-m-d'));

$deadlineValid = empty($event['registration_deadline'])
    || strtotime($event['registration_deadline']) === false
    || strtotime($event['registration_deadline']) >= time();

$eventPublished = ($event['status'] ?? '') === 'published';

$canRegister = $registrationOpen
    && $eventNotPassed
    && $deadlineValid
    && $eventPublished;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#7c3aed">

    <title>
        <?= escapeValue($event['title'] ?? 'Event Details') ?> | EventEase
    </title>

    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary: #c026d3;
            --accent: #f0abfc;
            --dark: #18111f;
            --text: #332746;
            --muted: #80758e;
            --background: #faf8ff;
            --white: #ffffff;
            --border: #e8e0f1;
            --success: #16833a;
            --danger: #c0392b;
            --gradient: linear-gradient(135deg, #7c3aed 0%, #c026d3 100%);
            --shadow: 0 22px 65px rgba(69, 35, 110, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(
                    circle at top left,
                    rgba(124, 58, 237, 0.08),
                    transparent 32%
                ),
                var(--background);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        /* NAVBAR */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            min-height: 72px;
            padding: 15px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 4px 18px rgba(33, 21, 47, 0.04);
        }

        /* SAME LOGO AS header.php */

        .logo {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            font-size: 29px;
            font-weight: 900;
            letter-spacing: -1.8px;
            text-decoration: none;
            color: #7c3aed;
        }

        .logo span {
            background: linear-gradient(135deg, #7c3aed, #c026d3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo::before {
            content: "";
            width: 10px;
            height: 10px;
            margin-right: 9px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #c026d3);
            flex-shrink: 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border: 1px solid #e7dafa;
            border-radius: 10px;
            color: var(--primary-dark);
            background: white;
            font-size: 14px;
            font-weight: 700;
            transition: 0.25s ease;
            white-space: nowrap;
        }

        .back-link:hover {
            color: white;
            background: var(--gradient);
            border-color: transparent;
            transform: translateY(-2px);
        }

        /* PAGE CONTAINER */

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 20px;
            flex: 1;
        }

        /* EVENT CARD */

        .event-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid rgba(124, 58, 237, 0.09);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        /* EVENT IMAGE */

        .image-wrapper {
            position: relative;
            width: 100%;
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 10px;
            background: linear-gradient(
                135deg,
                #f0e7ff,
                #fff7ff,
                #eee5ff
            );
            border-bottom: 1px solid #eee6f8;
        }

        .event-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            border-radius: 8px;
            transition: transform 0.35s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.015);
        }

        /* EVENT CONTENT */

        .event-content {
            padding: 30px 35px 35px;
        }

        .event-label {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 13px;
            border: 1px solid #eadcff;
            border-radius: 30px;
            color: var(--primary-dark);
            background: #f7f0ff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .event-title {
            margin-bottom: 16px;
            color: var(--dark);
            font-size: clamp(26px, 3.5vw, 36px);
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.6px;
            overflow-wrap: anywhere;
        }

        /* PRICE */

        .price {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            margin-bottom: 18px;
            padding: 9px 17px;
            border: 1px solid #c9f0d2;
            border-radius: 10px;
            color: var(--success);
            background: #edfff1;
            font-size: 16px;
            font-weight: 800;
        }

        /* REGISTRATION BUTTON */

        .register-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 44px;
            margin: 0 0 18px 10px;
            padding: 11px 21px;
            border: none;
            border-radius: 10px;
            color: white;
            background: var(--gradient);
            box-shadow: 0 6px 18px rgba(124, 58, 237, 0.22);
            font-size: 14px;
            font-weight: 800;
            transition: 0.25s ease;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 23px rgba(124, 58, 237, 0.30);
        }

        .registration-closed {
            display: inline-block;
            margin: 0 0 18px 10px;
            padding: 10px 14px;
            border: 1px solid #f5d4d1;
            border-radius: 10px;
            color: var(--danger);
            background: #fff3f2;
            font-size: 14px;
            font-weight: 700;
        }

        /* SECTION HEADINGS */

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 28px 0 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--dark);
            font-size: 21px;
            font-weight: 800;
        }

        .section-title::before {
            content: "";
            width: 5px;
            height: 23px;
            flex-shrink: 0;
            border-radius: 5px;
            background: var(--gradient);
        }

        /* DESCRIPTION */

        .description {
            color: #655d73;
            font-size: 15px;
            line-height: 1.9;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* EVENT INFORMATION GRID */

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .detail-item {
            min-width: 0;
            padding: 17px 18px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fcfaff;
            transition: 0.25s ease;
        }

        .detail-item:hover {
            border-color: #d7c1fa;
            background: #faf6ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(124, 58, 237, 0.06);
        }

        .detail-label {
            margin-bottom: 7px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .detail-value {
            color: var(--dark);
            font-size: 15px;
            font-weight: 600;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .status {
            color: var(--success);
        }

        .closed {
            color: var(--danger);
        }

        /* ALL EVENT DETAILS */

        .all-fields {
            display: grid;
            grid-template-columns: 1fr;
            gap: 11px;
        }

        .all-fields .detail-item {
            display: grid;
            grid-template-columns: minmax(130px, 0.7fr) minmax(0, 1.3fr);
            align-items: start;
            gap: 15px;
        }

        .all-fields .detail-label {
            margin: 0;
        }

        /* FOOTER */

        .footer {
            margin-top: auto;
            padding: 22px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            background: white;
            text-align: center;
            font-size: 13px;
        }

        /* TABLET */

        @media (max-width: 768px) {
            .navbar {
                padding: 14px 5%;
            }

            .container {
                margin: 25px auto;
                padding: 0 15px;
            }

            .image-wrapper {
                height: 240px;
            }

            .event-content {
                padding: 25px;
            }
        }

        /* MOBILE */

        @media (max-width: 520px) {
            .navbar {
                padding: 12px 4%;
                gap: 8px;
            }

            .logo {
                font-size: 23px;
                letter-spacing: -1.8px;
            }

            .logo::before {
                width: 8px;
                height: 8px;
                margin-right: 7px;
            }

            .back-link {
                padding: 9px 10px;
                font-size: 12px;
            }

            .container {
                margin: 18px auto;
                padding: 0 11px;
            }

            .event-card {
                border-radius: 15px;
            }

            .image-wrapper {
                height: 190px;
                padding: 7px;
            }

            .event-content {
                padding: 20px 16px 24px;
            }

            .event-title {
                font-size: 25px;
            }

            .price {
                font-size: 15px;
            }

            .register-btn,
            .registration-closed {
                margin-left: 0;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .detail-item {
                padding: 15px;
            }

            .all-fields .detail-item {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .section-title {
                font-size: 19px;
            }

            .description {
                font-size: 14px;
            }
        }

        @media (max-width: 360px) {
            .logo {
                font-size: 20px;
            }

            .back-link {
                padding: 8px;
                font-size: 11px;
            }
        }

        /* ACCESSIBILITY */

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }

            .event-card:hover .event-image {
                transform: none;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="../index.php">
        Event<span>Ease</span>
    </a>

    <a class="back-link" href="event.php">
        &larr; Back to Events
    </a>
</header>

<main class="container">

    <article class="event-card">

        <div class="image-wrapper">
            <img
                class="event-image"
                src="<?= escapeValue($imageUrl) ?>"
                alt="<?= escapeValue($event['title'] ?? 'Event image') ?>"
                onerror="this.onerror=null;this.src='assets/images/event-placeholder.jpg';"
            >
        </div>

        <div class="event-content">

            <div class="event-label">
                ✨ Event Details
            </div>

            <h1 class="event-title">
                <?= escapeValue($event['title'] ?? 'Untitled Event') ?>
            </h1>

            <div class="price">
                <?= escapeValue($price) ?>
            </div>

            <?php if ($canRegister): ?>

                <a
                    class="register-btn"
                    href="register.php?event_id=<?= (int)$event['id'] ?>"
                >
                    Register Now &rarr;
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
                    <div class="detail-label">📅 Event Date</div>
                    <div class="detail-value">
                        <?= escapeValue($eventDate) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">🕒 Time</div>
                    <div class="detail-value">
                        <?= escapeValue($startTime) ?>

                        <?php if ($endTime !== 'Not provided'): ?>
                            – <?= escapeValue($endTime) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">📍 Venue</div>
                    <div class="detail-value">
                        <?= escapeValue($event['venue'] ?? '') ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">👤 Organizer</div>
                    <div class="detail-value">
                        <?= escapeValue($event['organizer'] ?? '') ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">👥 Capacity</div>
                    <div class="detail-value">
                        <?= escapeValue($event['capacity'] ?? '') ?> people
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">🎟️ Registration</div>
                    <div class="detail-value <?= $canRegister ? 'status' : 'closed' ?>">
                        <?= $canRegister ? 'Open' : 'Closed' ?>
                    </div>
                </div>

            </div>

            <h2 class="section-title">All Event Details</h2>

            <div class="all-fields">

                <?php foreach ($event as $column => $value): ?>

                    <?php
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
                        $displayValue = $registrationOpen ? 'Open' : 'Closed';
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
    &copy; <?= date('Y') ?> EventEase. All rights reserved.
</footer>

</body>
</html>
