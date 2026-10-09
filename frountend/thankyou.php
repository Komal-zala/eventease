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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
        }

        .container {
            max-width: 650px;
            margin: 30px auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.10);
            text-align: center;
        }

        h1 {
            color: #198754;
            margin-bottom: 10px;
        }

        .success {
            font-size: 18px;
            margin-bottom: 25px;
        }

        .registration-code {
            background: #f1f3f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .registration-code strong {
            display: block;
            margin-bottom: 8px;
        }

        .code {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            letter-spacing: 1px;
        }

        .qr-box {
            margin: 25px 0;
        }

        .qr-image {
            width: 280px;
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }

        .details {
            text-align: left;
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .details p {
            margin: 8px 0;
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
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .ticket-btn {
            background: #198754;
            color: white;
        }

        .download-btn {
            background: #0d6efd;
            color: white;
        }

        .back-btn {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
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