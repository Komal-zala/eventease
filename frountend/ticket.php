<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

// Get registration code
$registrationCode = $_GET['code'] ?? '';

if (empty($registrationCode)) {
    die("Invalid ticket link.");
}

// Get registration details
$stmt = $conn->prepare("
    SELECT
        r.registration_code,
        r.qr_code,
        r.registered_at,
        s.name,
        s.department,
        s.program,
        s.semester,
        s.enrollment_number,
        s.phone_number,
        s.whatsapp_number
    FROM registrations r
    INNER JOIN students s
        ON r.student_id = s.id
    WHERE r.registration_code = ?
    LIMIT 1
");

$stmt->execute([$registrationCode]);

$registration = $stmt->fetch();

if (!$registration) {
    die("Ticket not found.");
}

// QR file
$qrFileName = $registrationCode . ".png";
$qrFilePath = __DIR__ . "/qr/" . $qrFileName;

if (!file_exists($qrFilePath)) {
    die("QR code not found.");
}

$qrImageUrl = "qr/" . rawurlencode($qrFileName);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>EventEase - Digital Event Ticket</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #eef2f7;
        }

        .ticket {
            width: 100%;
            max-width: 650px;
            margin: 20px auto;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .ticket-header {
            background: #0d6efd;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .ticket-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .ticket-header p {
            margin: 8px 0 0;
            opacity: 0.9;
        }

        .ticket-body {
            padding: 30px;
        }

        .qr-section {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 2px dashed #ddd;
        }

        .qr-image {
            width: 260px;
            max-width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 12px;
            background: white;
        }

        .scan-text {
            margin-top: 12px;
            color: #666;
            font-size: 14px;
        }

        .code-box {
            margin: 25px 0;
            padding: 18px;
            text-align: center;
            background: #f1f5ff;
            border-radius: 10px;
        }

        .code-label {
            display: block;
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .code {
            font-size: 22px;
            font-weight: bold;
            color: #0d6efd;
            letter-spacing: 1px;
        }

        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
        }

        .details h3 {
            margin-top: 0;
            margin-bottom: 18px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e5e5;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            text-align: right;
            color: #222;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .download-btn {
            background: #0d6efd;
            color: white;
        }

        .print-btn {
            background: #198754;
            color: white;
        }

        .back-btn {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .footer-note {
            text-align: center;
            color: #777;
            font-size: 13px;
            margin-top: 20px;
        }

        @media print {

            body {
                background: white;
                padding: 0;
            }

            .ticket {
                margin: 0 auto;
                box-shadow: none;
                max-width: 650px;
            }

            .no-print {
                display: none !important;
            }

        }

    </style>

</head>

<body>

<div class="ticket">

    <!-- HEADER -->

    <div class="ticket-header">

        <h1>🎟️ EventEase</h1>

        <p>Digital Event Registration Ticket</p>

    </div>


    <div class="ticket-body">

        <!-- QR -->

        <div class="qr-section">

            <img
                src="<?php echo htmlspecialchars($qrImageUrl); ?>"
                class="qr-image"
                alt="EventEase QR Code"
            >

            <p class="scan-text">
                Show this QR code at the event check-in.
            </p>

        </div>


        <!-- REGISTRATION CODE -->

        <div class="code-box">

            <span class="code-label">
                Registration ID
            </span>

            <div class="code">

                <?php echo htmlspecialchars($registrationCode); ?>

            </div>

        </div>


        <!-- STUDENT DETAILS -->

        <div class="details">

            <h3>Participant Details</h3>


            <div class="detail-row">

                <span class="label">
                    Name
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['name']); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Department
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['department']); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Program
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['program']); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Semester
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['semester']); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Enrollment No.
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['enrollment_number']); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Registration Date
                </span>

                <span class="value">
                    <?php echo htmlspecialchars($registration['registered_at']); ?>
                </span>

            </div>

        </div>


        <!-- BUTTONS -->

        <div class="buttons no-print">

            <!-- Download QR -->

            <a
                href="<?php echo htmlspecialchars($qrImageUrl); ?>"
                download="<?php echo htmlspecialchars($qrFileName); ?>"
                class="btn download-btn"
            >
                ⬇ Download QR
            </a>


            <!-- Print Ticket -->

            <button
                type="button"
                onclick="window.print()"
                class="btn print-btn"
            >
                🖨 Print Ticket
            </button>


            <!-- Back -->

            <a
                href="register.php"
                class="btn back-btn"
            >
                ← New Registration
            </a>

        </div>


        <p class="footer-note">

            Please keep this digital ticket ready for event check-in.

        </p>

    </div>

</div>

</body>

</html>