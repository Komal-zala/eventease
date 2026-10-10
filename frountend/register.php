
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$messageType = "";
$eventError = "";
$event = null;

$programs = [
    "Biotechnology" => ["B.Sc. Biotechnology", "M.Sc. Biotechnology"],
    "Chemistry" => ["B.Sc. Chemistry", "M.Sc. Chemistry"],
    "Computer Application" => ["BCA", "MCA"],
    "Computer Science" => ["B.Sc. Computer Science", "M.Sc. Computer Science"],
    "IT & CA" => ["B.Sc. IT & CA", "M.Sc. IT & CA"],
    "Industrial Chemistry" => ["B.Sc. Industrial Chemistry", "M.Sc. Industrial Chemistry"],
    "Information Technology" => ["B.Sc. Information Technology", "M.Sc. Information Technology"],
    "Mathematics" => ["B.Sc. Mathematics", "M.Sc. Mathematics"],
    "Medical Laboratory Technology" => ["B.Sc. Medical Laboratory Technology"],
    "Microbiology" => ["B.Sc. Microbiology", "M.Sc. Microbiology"],
    "Physics" => ["B.Sc. Physics", "M.Sc. Physics"]
];

$name = "";
$department = "";
$program = "";
$semester = "";
$enrollment_number = "";
$phone_number = "";
$whatsapp_number = "";

/* Safely escape HTML output. */
function e($value)
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

/* Check whether an event is currently accepting registrations. */
function eventIsOpen(array $event): bool
{
    if ((int)$event["registration_open"] !== 1) {
        return false;
    }

    if ($event["status"] !== "published") {
        return false;
    }

    if ($event["event_date"] < date("Y-m-d")) {
        return false;
    }

    if (
        !empty($event["registration_deadline"]) &&
        strtotime($event["registration_deadline"]) < time()
    ) {
        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| Get event ID from GET or POST
|--------------------------------------------------------------------------
*/

$eventId = filter_var(
    $_GET["event_id"] ?? null,
    FILTER_VALIDATE_INT
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedEventId = filter_var(
        $_POST["event_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($postedEventId !== false && $postedEventId !== null && $postedEventId > 0) {
        $eventId = $postedEventId;
    }

    $name = trim($_POST["name"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $semester = trim($_POST["semester"] ?? "");
    $enrollment_number = trim($_POST["enrollment_number"] ?? "");
    $phone_number = trim($_POST["phone_number"] ?? "");
    $whatsapp_number = trim($_POST["whatsapp_number"] ?? "");
}

/*
|--------------------------------------------------------------------------
| Load event
|--------------------------------------------------------------------------
*/

if (!$eventId || $eventId < 1) {
    $eventError = "Invalid event selected. Please open an event and register again.";
} else {
    try {
        $stmt = $conn->prepare(
            "SELECT id, title, event_date, price, capacity,
                    registration_open, registration_deadline, status
             FROM events
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->bind_param("i", $eventId);
        $stmt->execute();

        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            $eventError = "The selected event could not be found.";
        } elseif (!eventIsOpen($event)) {
            $eventError = "Registration for this event is closed.";
        }
    } catch (Throwable $ex) {
        error_log("EventEase event loading error: " . $ex->getMessage());
        $eventError = "Unable to load event details. Check the PHP error log.";
    }
}

/*
|--------------------------------------------------------------------------
| Process registration
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && $eventError === "") {

    if (
        $name === "" ||
        $department === "" ||
        $program === "" ||
        $semester === "" ||
        $enrollment_number === "" ||
        $phone_number === "" ||
        $whatsapp_number === ""
    ) {
        $message = "Please fill in all required fields.";
        $messageType = "error";

    } elseif (
        !isset($programs[$department]) ||
        !in_array($program, $programs[$department], true)
    ) {
        $message = "Please select a valid department and program.";
        $messageType = "error";

    } elseif (!preg_match('/^Semester [1-8]$/', $semester)) {
        $message = "Please select a valid semester.";
        $messageType = "error";

    } elseif (
        !preg_match('/^[0-9]{10}$/', $phone_number) ||
        !preg_match('/^[0-9]{10}$/', $whatsapp_number)
    ) {
        $message = "Enter valid 10-digit phone and WhatsApp numbers.";
        $messageType = "error";

    } elseif (strlen($enrollment_number) > 50) {
        $message = "Enrollment number cannot exceed 50 characters.";
        $messageType = "error";

    } else {
        $transactionStarted = false;

        try {
            $conn->begin_transaction();
            $transactionStarted = true;

            /*
             * Lock the event row. This also helps prevent multiple
             * simultaneous registrations exceeding event capacity.
             */
            $stmt = $conn->prepare(
                "SELECT id, title, event_date, price, capacity,
                        registration_open, registration_deadline, status
                 FROM events
                 WHERE id = ?
                 LIMIT 1
                 FOR UPDATE"
            );

            $stmt->bind_param("i", $eventId);
            $stmt->execute();

            $currentEvent = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$currentEvent) {
                throw new DomainException("The selected event no longer exists.");
            }

            if (!eventIsOpen($currentEvent)) {
                throw new DomainException("Registration for this event is closed.");
            }

            /*
             * Find an existing student using the unique enrollment number.
             */
            $stmt = $conn->prepare(
                "SELECT id
                 FROM students
                 WHERE enrollment_number = ?
                 LIMIT 1"
            );

            $stmt->bind_param("s", $enrollment_number);
            $stmt->execute();

            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $studentId = $student ? (int)$student["id"] : 0;

            /*
             * Reject a duplicate registration before changing student details.
             */
            if ($studentId > 0) {
                $stmt = $conn->prepare(
                    "SELECT id
                     FROM registrations
                     WHERE student_id = ? AND event_id = ?
                     LIMIT 1"
                );

                $stmt->bind_param("ii", $studentId, $eventId);
                $stmt->execute();

                $duplicate = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($duplicate) {
                    throw new DomainException(
                        "You have already registered for this event."
                    );
                }
            }

            /*
             * Check event capacity.
             */
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS total
                 FROM registrations
                 WHERE event_id = ?"
            );

            $stmt->bind_param("i", $eventId);
            $stmt->execute();

            $countRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $registeredCount = (int)$countRow["total"];
            $capacity = (int)$currentEvent["capacity"];

            if ($registeredCount >= $capacity) {
                throw new DomainException(
                    "Sorry, this event has reached its registration capacity."
                );
            }

            /*
             * Update an existing student or insert a new student.
             */
            if ($studentId > 0) {
                $stmt = $conn->prepare(
                    "UPDATE students
                     SET name = ?,
                         department = ?,
                         program = ?,
                         semester = ?,
                         phone_number = ?,
                         whatsapp_number = ?
                     WHERE id = ?"
                );

                $stmt->bind_param(
                    "ssssssi",
                    $name,
                    $department,
                    $program,
                    $semester,
                    $phone_number,
                    $whatsapp_number,
                    $studentId
                );

                $stmt->execute();
                $stmt->close();

            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO students
                    (
                        name,
                        department,
                        program,
                        semester,
                        enrollment_number,
                        phone_number,
                        whatsapp_number
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "sssssss",
                    $name,
                    $department,
                    $program,
                    $semester,
                    $enrollment_number,
                    $phone_number,
                    $whatsapp_number
                );

                $stmt->execute();
                $studentId = (int)$conn->insert_id;
                $stmt->close();
            }

            /*
             * Create a unique registration code.
             */
            $registrationCode = "EVT-" . date("Y") . "-" .
                strtoupper(bin2hex(random_bytes(5)));

            $isPaidEvent = (float)$currentEvent["price"] > 0;

            $paymentStatus = $isPaidEvent
                ? "pending"
                : "not_required";

            /*
             * qr_code is nullable in your schema and existing rows
             * use it for image filenames. Leave it NULL until the
             * actual QR image has been generated.
             */
            $qrCodeValue = null;

            $stmt = $conn->prepare(
                "INSERT INTO registrations
                (
                    student_id,
                    event_id,
                    registration_code,
                    qr_code,
                    payment_status
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iisss",
                $studentId,
                $eventId,
                $registrationCode,
                $qrCodeValue,
                $paymentStatus
            );

            $stmt->execute();
            $stmt->close();

            /*
             * Save all changes before redirecting.
             */
            $conn->commit();
            $transactionStarted = false;

            if ($isPaidEvent) {
                header(
                    "Location: payment.php?code=" .
                    urlencode($registrationCode)
                );
            } else {
                header(
                    "Location: thankyou.php?code=" .
                    urlencode($registrationCode)
                );
            }

            exit;

        } catch (Throwable $ex) {
            if ($transactionStarted) {
                try {
                    $conn->rollback();
                } catch (Throwable $rollbackError) {
                    error_log(
                        "EventEase rollback error: " .
                        $rollbackError->getMessage()
                    );
                }
            }

            error_log(
                "EventEase registration error: " .
                $ex->getMessage()
            );

            if ($ex instanceof DomainException) {
                $message = $ex->getMessage();

            } elseif (
                $ex instanceof mysqli_sql_exception &&
                (int)$ex->getCode() === 1062
            ) {
                $message = "You may already be registered for this event. Please check your enrollment number.";

            } else {
                $message = "Registration failed because of a server or database error. Please check the PHP error log.";
            }

            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#7c3aed">
    <title>EventEase | Student Registration</title>

    <style>
        :root {
            --primary: #7c3aed;
            --secondary: #c026d3;
            --dark: #18111f;
            --text: #332746;
            --muted: #80758e;
            --background: #faf8ff;
            --border: #e8e0f1;
            --gradient: linear-gradient(135deg, #7c3aed, #c026d3);
            --shadow: 0 22px 65px rgba(69, 35, 110, .12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 8% 10%, rgba(192,38,211,.09), transparent 28%),
                radial-gradient(circle at 92% 85%, rgba(124,58,237,.10), transparent 30%),
                var(--background);
        }

        a {
            text-decoration: none;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            min-height: 72px;
            padding: 14px 7%;
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 4px 18px rgba(33,21,47,.04);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            font-size: 29px;
            font-weight: 900;
            letter-spacing: -1.8px;
        }

        .logo span {
            background: var(--gradient);
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
            background: var(--gradient);
        }

        .back-link {
            padding: 10px 15px;
            border: 1px solid #e7dafa;
            border-radius: 10px;
            color: var(--primary);
            background: white;
            font-size: 13px;
            font-weight: 700;
        }

        .back-link:hover {
            color: white;
            background: var(--gradient);
        }

        .container {
            width: 100%;
            max-width: 850px;
            margin: 38px auto;
            padding: 0 20px;
            flex: 1;
        }

        .card {
            overflow: hidden;
            border: 1px solid rgba(124,58,237,.09);
            border-radius: 22px;
            background: white;
            box-shadow: var(--shadow);
        }

        .card-header {
            position: relative;
            overflow: hidden;
            padding: 36px 38px;
            color: white;
            background: linear-gradient(125deg, #241035, #51228a 52%, #8438c9);
        }

        .card-header::after {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            top: -120px;
            right: -40px;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 50%;
        }

        .eyebrow {
            position: relative;
            z-index: 1;
            display: inline-block;
            padding: 7px 12px;
            margin-bottom: 15px;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 30px;
            background: rgba(255,255,255,.10);
            color: #f4dfff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .card-header h1 {
            position: relative;
            z-index: 1;
            margin-bottom: 10px;
            font-size: clamp(27px, 5vw, 35px);
            font-weight: 800;
            letter-spacing: -1px;
        }

        .card-header p {
            position: relative;
            z-index: 1;
            color: #e6d9f4;
            font-size: 14px;
            line-height: 1.8;
        }

        .card-body {
            padding: 32px 38px 38px;
        }

        .event-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 19px;
            margin-bottom: 30px;
            border: 1px solid #e9dcff;
            border-radius: 15px;
            background: linear-gradient(120deg, #faf5ff, #fff8ff);
        }

        .event-label {
            margin-bottom: 6px;
            color: var(--primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .event-summary h2 {
            margin-bottom: 5px;
            color: var(--dark);
            font-size: 19px;
            overflow-wrap: anywhere;
        }

        .event-date {
            color: var(--muted);
            font-size: 12px;
        }

        .event-price {
            flex-shrink: 0;
            padding: 11px 15px;
            border: 1px solid #e7d6ff;
            border-radius: 12px;
            background: white;
            text-align: center;
        }

        .event-price span {
            display: block;
            margin-bottom: 3px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .event-price strong {
            color: var(--primary);
            font-size: 19px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 23px;
        }

        .section-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 13px;
            background: #f1e8ff;
            color: var(--primary);
            font-size: 19px;
        }

        .section-heading h3 {
            margin-bottom: 3px;
            color: var(--dark);
            font-size: 17px;
            font-weight: 800;
        }

        .section-heading p {
            color: var(--muted);
            font-size: 12px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 18px;
        }

        .form-group {
            min-width: 0;
            margin-bottom: 20px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #40334f;
            font-size: 12px;
            font-weight: 700;
        }

        .required {
            color: var(--secondary);
        }

        input, select {
            width: 100%;
            min-height: 49px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            background: #fdfcff;
            color: var(--text);
            font-family: inherit;
            font-size: 13px;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(124,58,237,.09);
        }

        .form-hint {
            margin-top: 6px;
            color: #94869f;
            font-size: 10px;
        }

        .divider {
            height: 1px;
            margin: 6px 0 25px;
            border: 0;
            background: #eee7f5;
        }

        .message {
            padding: 13px 15px;
            margin-bottom: 22px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: 13px;
            line-height: 1.6;
        }

        .message.error {
            border-color: #fecaca;
            background: #fff1f2;
            color: #b4233b;
        }

        .event-error {
            padding: 28px 20px;
            border: 1px solid #fecaca;
            border-radius: 14px;
            background: #fff7f7;
            text-align: center;
        }

        .event-error h2 {
            margin-bottom: 10px;
            color: #991b1b;
            font-size: 20px;
        }

        .event-error p {
            margin-bottom: 18px;
            color: #7f1d1d;
            font-size: 13px;
        }

        .event-error a {
            display: inline-flex;
            padding: 11px 18px;
            border-radius: 10px;
            background: var(--gradient);
            color: white;
            font-size: 12px;
            font-weight: 700;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 52px;
            padding: 14px 20px;
            margin-top: 5px;
            border: none;
            border-radius: 11px;
            background: var(--gradient);
            box-shadow: 0 10px 24px rgba(124,58,237,.22);
            color: white;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(124,58,237,.30);
        }

        .btn:disabled {
            opacity: .7;
            cursor: wait;
            transform: none;
        }

        .secure-note {
            margin-top: 16px;
            color: #9689a1;
            font-size: 11px;
            text-align: center;
        }

        .secure-note span {
            color: #16a34a;
        }

        .footer {
            margin-top: auto;
            padding: 22px;
            border-top: 1px solid var(--border);
            background: white;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 13px 5%;
            }

            .logo {
                font-size: 23px;
            }

            .container {
                margin: 22px auto;
                padding: 0 12px;
            }

            .card {
                border-radius: 18px;
            }

            .card-header {
                padding: 28px 22px;
            }

            .card-body {
                padding: 24px 21px 28px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .full-width {
                grid-column: auto;
            }

            .event-summary {
                align-items: flex-start;
                padding: 15px;
                gap: 10px;
            }

            .event-summary h2 {
                font-size: 16px;
            }

            .event-price {
                padding: 9px 11px;
            }

            .event-price strong {
                font-size: 16px;
            }
        }

        @media (max-width: 380px) {
            .navbar {
                gap: 8px;
            }

            .logo {
                font-size: 20px;
            }

            .logo::before {
                width: 8px;
                height: 8px;
                margin-right: 6px;
            }

            .back-link {
                padding: 8px;
                font-size: 11px;
            }

            .event-summary {
                flex-direction: column;
            }

            .event-price {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .event-price span {
                margin: 0;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a href="../index.php" class="logo">Event<span>Ease</span></a>
    <a href="event.php" class="back-link">&larr; Back to Events</a>
</header>

<main class="container">
    <section class="card">

        <div class="card-header">
            <div class="eyebrow">✦ Your next experience awaits</div>
            <h1>Event Registration</h1>
            <p>
                Complete your details below to reserve your place
                and create memorable experiences with EventEase.
            </p>
        </div>

        <div class="card-body">

            <?php if ($message !== ""): ?>
                <div class="message <?= e($messageType) ?>" role="alert">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($eventError !== ""): ?>

                <div class="event-error">
                    <h2>Registration Unavailable</h2>
                    <p><?= e($eventError) ?></p>
                    <a href="event.php">Explore Events</a>
                </div>

            <?php else: ?>

                <div class="event-summary">
                    <div>
                        <div class="event-label">You're registering for</div>
                        <h2><?= e($event["title"]) ?></h2>
                        <div class="event-date">
                            📅 <?= date("d F Y", strtotime($event["event_date"])) ?>
                        </div>
                    </div>

                    <div class="event-price">
                        <span>Entry Fee</span>
                        <strong>
                            <?= (float)$event["price"] <= 0
                                ? "FREE"
                                : "₹" . number_format((float)$event["price"], 2) ?>
                        </strong>
                    </div>
                </div>

                <div class="section-heading">
                    <div class="section-icon">♙</div>
                    <div>
                        <h3>Student Information</h3>
                        <p>Enter your academic details carefully.</p>
                    </div>
                </div>

                <form method="POST" action="" id="registrationForm">
                    <input
                        type="hidden"
                        name="event_id"
                        value="<?= (int)$eventId ?>"
                    >

                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Enter your full name"
                                maxlength="100"
                                autocomplete="name"
                                value="<?= e($name) ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="department">Department <span class="required">*</span></label>
                            <select id="department" name="department" required>
                                <option value="">Choose department</option>
                                <?php foreach ($programs as $departmentName => $programList): ?>
                                    <option
                                        value="<?= e($departmentName) ?>"
                                        <?= $department === $departmentName ? "selected" : "" ?>
                                    >
                                        <?= e($departmentName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="program">Program <span class="required">*</span></label>
                            <select id="program" name="program" required>
                                <option value="">Choose program</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="semester">Semester <span class="required">*</span></label>
                            <select id="semester" name="semester" required>
                                <option value="">Choose semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option
                                        value="Semester <?= $i ?>"
                                        <?= $semester === "Semester $i" ? "selected" : "" ?>
                                    >
                                        Semester <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="enrollment_number">Enrollment Number <span class="required">*</span></label>
                            <input
                                type="text"
                                id="enrollment_number"
                                name="enrollment_number"
                                placeholder="Enter enrollment number"
                                maxlength="50"
                                value="<?= e($enrollment_number) ?>"
                                required
                            >
                        </div>

                    </div>

                    <hr class="divider">

                    <div class="section-heading">
                        <div class="section-icon">☎</div>
                        <div>
                            <h3>Contact Information</h3>
                            <p>Provide valid numbers for event updates.</p>
                        </div>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="phone_number">Phone Number <span class="required">*</span></label>
                            <input
                                type="tel"
                                id="phone_number"
                                name="phone_number"
                                placeholder="10-digit phone number"
                                inputmode="numeric"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                autocomplete="tel"
                                value="<?= e($phone_number) ?>"
                                required
                            >
                            <div class="form-hint">Enter exactly 10 digits.</div>
                        </div>

                        <div class="form-group">
                            <label for="whatsapp_number">WhatsApp Number <span class="required">*</span></label>
                            <input
                                type="tel"
                                id="whatsapp_number"
                                name="whatsapp_number"
                                placeholder="10-digit WhatsApp number"
                                inputmode="numeric"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                value="<?= e($whatsapp_number) ?>"
                                required
                            >
                            <div class="form-hint">Enter exactly 10 digits.</div>
                        </div>

                    </div>

                    <button type="submit" class="btn" id="submitButton">
                        Register Now &rarr;
                    </button>

                    <div class="secure-note">
                        <span>✓</span>
                        Your registration details will be handled securely.
                    </div>
                </form>

            <?php endif; ?>

        </div>
    </section>
</main>

<footer class="footer">
    &copy; <?= date("Y") ?> EventEase. Discover events. Create memories.
</footer>

<script>
    const programs = <?= json_encode(
        $programs,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const selectedDepartment = <?= json_encode(
        $department,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const selectedProgram = <?= json_encode(
        $program,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const departmentSelect = document.getElementById("department");
    const programSelect = document.getElementById("program");

    function updatePrograms(keepSelected = false) {
        if (!departmentSelect || !programSelect) return;

        const department = departmentSelect.value;
        const oldProgram = keepSelected ? programSelect.value : "";

        programSelect.replaceChildren();

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Choose program";
        programSelect.appendChild(defaultOption);

        if (!department || !programs[department]) return;

        programs[department].forEach(function(programName) {
            const option = document.createElement("option");
            option.value = programName;
            option.textContent = programName;

            if (
                programName === oldProgram ||
                (
                    department === selectedDepartment &&
                    programName === selectedProgram
                )
            ) {
                option.selected = true;
            }

            programSelect.appendChild(option);
        });
    }

    if (departmentSelect && programSelect) {
        departmentSelect.addEventListener("change", function() {
            updatePrograms(false);
        });

        updatePrograms(true);
    }

    const registrationForm = document.getElementById("registrationForm");

    if (registrationForm) {
        registrationForm.addEventListener("submit", function(event) {
            const phone = document.getElementById("phone_number").value.trim();
            const whatsapp = document.getElementById("whatsapp_number").value.trim();

            if (!/^[0-9]{10}$/.test(phone)) {
                event.preventDefault();
                alert("Please enter a valid 10-digit phone number.");
                return;
            }

            if (!/^[0-9]{10}$/.test(whatsapp)) {
                event.preventDefault();
                alert("Please enter a valid 10-digit WhatsApp number.");
                return;
            }

            if (
                !departmentSelect.value ||
                !programSelect.value ||
                !programs[departmentSelect.value] ||
                !programs[departmentSelect.value].includes(programSelect.value)
            ) {
                event.preventDefault();
                alert("Please select a valid department and program.");
                return;
            }

            const button = document.getElementById("submitButton");

            if (button) {
                button.disabled = true;
                button.textContent = "Processing Registration...";
            }
        });
    }
</script>

</body>
</html>
