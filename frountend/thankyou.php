<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/database.php";

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;


/*
|--------------------------------------------------------------------------
| Get Registration Code
|--------------------------------------------------------------------------
*/

$registrationCode = $_GET['code'] ?? '';

if (empty($registrationCode)) {
    die("Invalid registration code.");
}


/*
|--------------------------------------------------------------------------
| Get Registration Details
|--------------------------------------------------------------------------
*/

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
    INNER JOIN students s ON r.student_id = s.id
    WHERE r.registration_code = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $registrationCode);

if (!$stmt->execute()) {
    die("Database execute failed: " . $stmt->error);
}


/*
|--------------------------------------------------------------------------
| Get Result
|--------------------------------------------------------------------------
*/

$result = $stmt->get_result();

if (!$result) {
    die("Unable to get registration details: " . $stmt->error);
}

$registration = $result->fetch_assoc();

$result->free();
$stmt->close();


/*
|--------------------------------------------------------------------------
| Check Registration
|--------------------------------------------------------------------------
*/

if (!$registration) {
    die("Registration not found.");
}


/*
|--------------------------------------------------------------------------
| Generate PNG QR
|--------------------------------------------------------------------------
*/

$qrFolder = __DIR__ . "/../qr/";

if (!is_dir($qrFolder)) {
    if (!mkdir($qrFolder, 0777, true)) {
        die("Unable to create QR folder.");
    }
}

$qrFileName = $registrationCode . ".png";
$qrFilePath = $qrFolder . $qrFileName;


/*
|--------------------------------------------------------------------------
| Generate QR only if it doesn't already exist
|--------------------------------------------------------------------------
*/

if (!file_exists($qrFilePath)) {

    $options = new QROptions();

    // PNG output
    $options->outputInterface = QRGdImagePNG::class;

    // QR size
    $options->scale = 8;

    // White background
    $options->imageTransparent = false;

    // Save as PNG file
    $options->outputBase64 = false;

    $qrCode = new QRCode($options);

    $qrCode->render(
        $registrationCode,
        $qrFilePath
    );
}


/*
|--------------------------------------------------------------------------
| Save QR filename in Database
|--------------------------------------------------------------------------
*/

if ($registration['qr_code'] !== $qrFileName) {

    $update = $conn->prepare("
        UPDATE registrations
        SET qr_code = ?
        WHERE registration_code = ?
    ");

    if (!$update) {
        die("Failed to prepare QR update: " . $conn->error);
    }

    $update->bind_param(
        "ss",
        $qrFileName,
        $registrationCode
    );

    if (!$update->execute()) {
        die("Failed to save QR filename: " . $update->error);
    }

    $update->close();
}


/*
|--------------------------------------------------------------------------
| QR Image URL for Browser
|--------------------------------------------------------------------------
|
| thankyou.php is inside:
| frountend/
|
| QR is inside:
| qr/
|
| Therefore:
| ../qr/filename.png
|
|--------------------------------------------------------------------------
*/

$qrImageUrl = "../qr/" . rawurlencode($qrFileName);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EventEase - Registration Successful</title>

    <style>

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary: #c026d3;
            --dark: #21152f;
            --text: #30283b;
            --muted: #81788e;
            --background: #faf8ff;
            --white: #ffffff;
            --border: #eee6f8;
            --gradient: linear-gradient(
                135deg,
                #7c3aed 0%,
                #c026d3 100%
            );
            --shadow: 0 20px 60px rgba(69, 35, 110, 0.12);
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
            padding: 35px 18px;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(
                    circle at 8% 5%,
                    rgba(124, 58, 237, 0.12),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 92% 90%,
                    rgba(192, 38, 211, 0.10),
                    transparent 28%
                ),
                var(--background);
        }

        .container {
            width: 100%;
            max-width: 720px;
            margin: 20px auto;
            padding: 42px;
            position: relative;
            overflow: hidden;
            background: var(--white);
            border: 1px solid rgba(124, 58, 237, 0.10);
            border-radius: 26px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 7px;
            background: var(--gradient);
        }

        h1 {
            margin-bottom: 12px;
            color: var(--primary);
            font-size: clamp(27px, 5vw, 36px);
            font-weight: 900;
            line-height: 1.3;
            letter-spacing: -1px;
        }

        .success {
            margin-bottom: 30px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .registration-code {
            margin: 25px 0;
            padding: 23px 18px;
            background: linear-gradient(
                135deg,
                #f5efff 0%,
                #fff0fc 100%
            );
            border: 1px dashed rgba(124, 58, 237, 0.40);
            border-radius: 16px;
        }

        .registration-code strong {
            display: block;
            margin-bottom: 12px;
            color: var(--dark);
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .code {
            overflow-wrap: anywhere;
            color: var(--primary);
            font-size: clamp(18px, 4vw, 25px);
            font-weight: 900;
            letter-spacing: 1.5px;
        }

        .qr-box {
            margin: 30px 0;
            padding: 28px 20px;
            background: #fdfbff;
            border: 1px solid var(--border);
            border-radius: 18px;
        }

        .qr-box h3 {
            margin-bottom: 20px;
            color: var(--dark);
            font-size: 22px;
            font-weight: 800;
        }

        .qr-image {
            display: block;
            width: 280px;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            padding: 12px;
            background: var(--white);
            border: 1px solid #e7ddf2;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(69, 35, 110, 0.08);
        }

        .qr-box p {
            margin-top: 18px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .details {
            margin: 30px 0;
            padding: 25px;
            background: #faf8ff;
            border: 1px solid var(--border);
            border-radius: 16px;
            text-align: left;
        }

        .details p {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin: 0;
            padding: 13px 0;
            border-bottom: 1px solid #eee6f8;
            color: var(--text);
            font-size: 14px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .details p:first-child {
            padding-top: 0;
        }

        .details p:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .details strong {
            flex: 0 0 42%;
            color: var(--dark);
            font-weight: 800;
        }

        .buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 13px 20px;
            border: 1px solid transparent;
            border-radius: 11px;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.4;
            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;
        }

        .ticket-btn {
            color: var(--white);
            background: var(--gradient);
            box-shadow: 0 7px 18px rgba(124, 58, 237, 0.20);
        }

        .ticket-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(124, 58, 237, 0.30);
        }

        .download-btn {
            color: var(--primary);
            background: #f3edff;
            border-color: #e6d9ff;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            background: #e9ddff;
            box-shadow: 0 8px 18px rgba(124, 58, 237, 0.12);
        }

        .back-btn {
            color: var(--text);
            background: #f4f1f8;
            border-color: #e8e0ef;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            color: var(--primary);
            background: #eee7f8;
        }

        .btn:focus-visible {
            outline: 3px solid rgba(124, 58, 237, 0.35);
            outline-offset: 3px;
        }

        @media (max-width: 600px) {

            body {
                padding: 15px 10px;
            }

            .container {
                margin: 12px auto;
                padding: 30px 18px;
                border-radius: 20px;
            }

            h1 {
                font-size: 27px;
            }

            .success {
                font-size: 14px;
            }

            .registration-code {
                padding: 20px 12px;
            }

            .qr-box {
                padding: 22px 14px;
            }

            .qr-box h3 {
                font-size: 20px;
            }

            .qr-image {
                width: 250px;
            }

            .details {
                padding: 18px;
            }

            .details p {
                flex-direction: column;
                gap: 3px;
                padding: 12px 0;
            }

            .details strong {
                flex-basis: auto;
            }

            .buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
                padding: 14px 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <h1>🎉 Registration Successful!</h1>

    <p class="success">
        Thank you for registering for EventEase.
    </p>


    <!-- REGISTRATION CODE -->

    <div class="registration-code">

        <strong>Your Registration Code</strong>

        <div class="code">
            <?php echo htmlspecialchars($registrationCode); ?>
        </div>

    </div>


    <!-- QR CODE -->

    <div class="qr-box">

        <h3>Your QR Code</h3>

        <img
            src="<?php echo htmlspecialchars($qrImageUrl); ?>"
            class="qr-image"
            id="qrImage"
            alt="EventEase QR Code"
        >

        <p>
            Show this QR code at the event check-in.
        </p>

    </div>


    <!-- STUDENT DETAILS -->

    <div class="details">

        <p>
            <strong>Name:</strong>
            <?php echo htmlspecialchars($registration['name']); ?>
        </p>

        <p>
            <strong>Department:</strong>
            <?php echo htmlspecialchars($registration['department']); ?>
        </p>

        <p>
            <strong>Program:</strong>
            <?php echo htmlspecialchars($registration['program']); ?>
        </p>

        <p>
            <strong>Semester:</strong>
            <?php echo htmlspecialchars($registration['semester']); ?>
        </p>

        <p>
            <strong>Enrollment Number:</strong>
            <?php echo htmlspecialchars($registration['enrollment_number']); ?>
        </p>

        <p>
            <strong>WhatsApp:</strong>
            <?php echo htmlspecialchars($registration['whatsapp_number']); ?>
        </p>

    </div>


    <!-- BUTTONS -->

    <div class="buttons">

        <!-- View Digital Ticket -->

        <a
            href="ticket.php?code=<?php echo urlencode($registrationCode); ?>"
            class="btn ticket-btn"
        >
            🎟️ View Digital Ticket
        </a>


        <!-- Download QR -->

        <a
            href="<?php echo htmlspecialchars($qrImageUrl); ?>"
            download="<?php echo htmlspecialchars($qrFileName); ?>"
            class="btn download-btn"
        >
            ⬇ Download QR
        </a>


        <!-- New Registration -->

        <a
            href="register.php"
            class="btn back-btn"
        >
            ← New Registration
        </a>

    </div>

</div>

</body>

</html>