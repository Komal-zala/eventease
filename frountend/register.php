<?php

require_once __DIR__ . '/../config/database.php';


$message = "";
$messageType = "";

// Department → Program mapping
$programs = [

    "Biotechnology" => [
        "B.Sc. Biotechnology",
        "M.Sc. Biotechnology"
    ],

    "Chemistry" => [
        "B.Sc. Chemistry",
        "M.Sc. Chemistry"
    ],

    "Computer Application" => [
        "BCA",
        "MCA"
    ],

    "Computer Science" => [
        "B.Sc. Computer Science",
        "M.Sc. Computer Science"
    ],

    "IT & CA" => [
        "B.Sc. IT & CA",
        "M.Sc. IT & CA"
    ],

    "Industrial Chemistry" => [
        "B.Sc. Industrial Chemistry",
        "M.Sc. Industrial Chemistry"
    ],

    "Information Technology" => [
        "B.Sc. Information Technology",
        "M.Sc. Information Technology"
    ],

    "Mathematics" => [
        "B.Sc. Mathematics",
        "M.Sc. Mathematics"
    ],

    "Medical Laboratory Technology" => [
        "B.Sc. Medical Laboratory Technology"
    ],

    "Microbiology" => [
        "B.Sc. Microbiology",
        "M.Sc. Microbiology"
    ],

    "Physics" => [
        "B.Sc. Physics",
        "M.Sc. Physics"
    ]
];


// Form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $semester = trim($_POST["semester"] ?? "");
    $enrollment_number = trim($_POST["enrollment_number"] ?? "");
    $phone_number = trim($_POST["phone_number"] ?? "");
    $whatsapp_number = trim($_POST["whatsapp_number"] ?? "");

    // Basic validation
    if (
        empty($name) ||
        empty($department) ||
        empty($program) ||
        empty($semester) ||
        empty($enrollment_number) ||
        empty($phone_number) ||
        empty($whatsapp_number)
    ) {

        $message = "Please fill all fields.";
        $messageType = "error";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone_number)) {

        $message = "Please enter a valid 10-digit phone number.";
        $messageType = "error";

    } elseif (!preg_match("/^[0-9]{10}$/", $whatsapp_number)) {

        $message = "Please enter a valid 10-digit WhatsApp number.";
        $messageType = "error";

    } else {

        try {

            // Start transaction so student + registration are saved together
            $conn->begin_transaction();

            // Check duplicate enrollment number
            $check = $conn->prepare(
                "SELECT id FROM students WHERE enrollment_number = ? LIMIT 1"
            );

            if (!$check) {
                throw new Exception("Unable to prepare duplicate check.");
            }

            $check->bind_param("s", $enrollment_number);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {

                $check->close();
                $conn->rollback();

                $message = "This enrollment number is already registered.";
                $messageType = "error";

            } else {

                $check->close();

                // Insert student
                $sql = "INSERT INTO students
                    (
                        name,
                        department,
                        program,
                        semester,
                        enrollment_number,
                        phone_number,
                        whatsapp_number
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Unable to prepare student registration.");
                }

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
                $stmt->close();

                // Get newly created student ID
                // MySQLi uses insert_id, NOT lastInsertId()
                $studentId = $conn->insert_id;

                // Create unique registration code
                $registrationCode = "EVT-" . date("Y") . "-" . strtoupper(
                    substr(bin2hex(random_bytes(5)), 0, 8)
                );

                // Insert registration
                $registrationSql = "
                    INSERT INTO registrations
                    (
                        student_id,
                        event_id,
                        registration_code
                    )
                    VALUES (?, ?,?)
                ";

                $registrationStmt = $conn->prepare($registrationSql);

                if (!$registrationStmt) {
                    throw new Exception("Unable to prepare event registration.");
                }

                $registrationStmt->bind_param(
                    "is",
                    $studentId,
                    $event_id,
                    $registrationCode
                );

                $registrationStmt->execute();
                $registrationStmt->close();

                // Save both records
                $conn->commit();

                // Redirect to thank-you page
                header(
                    "Location: thankyou.php?code=" .
                    urlencode($registrationCode)
                );

                exit;
            }

        } catch (Throwable $e) {

            // Roll back if anything failed
            try {
                $conn->rollback();
            } catch (Throwable $rollbackError) {
                // Ignore rollback errors
            }

            $message = "Something went wrong. Please try again.";
            $messageType = "error";

            // For development only:
            // Uncomment the next line if you need the exact database error.
            // $message = $e->getMessage();
        }
    }
}

?>

<?php
require_once __DIR__ . '/../config/database.php';

$message = "";
$messageType = "";

// Read event ID from the URL or submitted form.
$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
}

$event = null;
$eventError = "";

// Verify the selected event.
if (!$eventId || $eventId < 1) {
    $eventError = "Please select a valid event first.";
} else {
    $eventStmt = $conn->prepare(
        "SELECT id, title, event_date, price, registration_open,
                registration_deadline, status
         FROM events
         WHERE id = ?
         LIMIT 1"
    );

    if (!$eventStmt) {
        $eventError = "Unable to load event details.";
    } else {
        $eventStmt->bind_param("i", $eventId);
        $eventStmt->execute();
        $eventResult = $eventStmt->get_result();
        $event = $eventResult->fetch_assoc();
        $eventStmt->close();

        if (!$event) {
            $eventError = "Event not found.";
        } elseif (
            (string)$event['registration_open'] !== '1' ||
            $event['status'] !== 'published' ||
            strtotime($event['event_date']) < strtotime(date('Y-m-d')) ||
            (
                !empty($event['registration_deadline']) &&
                strtotime($event['registration_deadline']) < time()
            )
        ) {
            $eventError = "Registration for this event is closed.";
        }
    }
}

// Keep these variables available for the form.
$name = "";
$department = "";
$program = "";
$semester = "";
$enrollment_number = "";
$phone_number = "";
$whatsapp_number = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $eventError === "") {
    $name = trim($_POST["name"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $semester = trim($_POST["semester"] ?? "");
    $enrollment_number = trim($_POST["enrollment_number"] ?? "");
    $phone_number = trim($_POST["phone_number"] ?? "");
    $whatsapp_number = trim($_POST["whatsapp_number"] ?? "");

    if (
        $name === "" || $department === "" || $program === "" ||
        $semester === "" || $enrollment_number === "" ||
        $phone_number === "" || $whatsapp_number === ""
    ) {
        $message = "Please fill all fields.";
        $messageType = "error";
    } elseif (
        !isset($programs[$department]) ||
        !in_array($program, $programs[$department], true)
    ) {
        $message = "Please select a valid department and program.";
        $messageType = "error";
    } elseif (
        !preg_match('/^[0-9]{10}$/', $phone_number) ||
        !preg_match('/^[0-9]{10}$/', $whatsapp_number)
    ) {
        $message = "Enter valid 10-digit phone and WhatsApp numbers.";
        $messageType = "error";
    } else {
        try {
            $conn->begin_transaction();

            // Reuse an existing student record when the enrollment
            // number is already registered.
            $studentStmt = $conn->prepare(
                "SELECT id FROM students
                 WHERE enrollment_number = ?
                 LIMIT 1"
            );
            if (!$studentStmt) {
                throw new Exception("Unable to check student.");
            }

            $studentStmt->bind_param("s", $enrollment_number);
            $studentStmt->execute();
            $studentResult = $studentStmt->get_result();
            $existingStudent = $studentResult->fetch_assoc();
            $studentStmt->close();

            if ($existingStudent) {
                $studentId = (int)$existingStudent['id'];

                // Update the existing student's details.
                $updateStmt = $conn->prepare(
                    "UPDATE students
                     SET name = ?, department = ?, program = ?,
                         semester = ?, phone_number = ?,
                         whatsapp_number = ?
                     WHERE id = ?"
                );
                if (!$updateStmt) {
                    throw new Exception("Unable to update student.");
                }

                $updateStmt->bind_param(
                    "ssssssi",
                    $name, $department, $program, $semester,
                    $phone_number, $whatsapp_number, $studentId
                );
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                $insertStudent = $conn->prepare(
                    "INSERT INTO students
                     (name, department, program, semester,
                      enrollment_number, phone_number, whatsapp_number)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                if (!$insertStudent) {
                    throw new Exception("Unable to create student.");
                }

                $insertStudent->bind_param(
                    "sssssss",
                    $name, $department, $program, $semester,
                    $enrollment_number, $phone_number, $whatsapp_number
                );
                $insertStudent->execute();
                $studentId = (int)$conn->insert_id;
                $insertStudent->close();
            }

            // Prevent registering the same student twice for one event.
            $duplicateStmt = $conn->prepare(
                "SELECT id FROM registrations
                 WHERE student_id = ? AND event_id = ?
                 LIMIT 1"
            );
            if (!$duplicateStmt) {
                throw new Exception("Unable to check existing registration.");
            }

            $duplicateStmt->bind_param("ii", $studentId, $eventId);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();
            $alreadyRegistered = $duplicateResult->fetch_assoc();
            $duplicateStmt->close();

            if ($alreadyRegistered) {
                $conn->rollback();
                $message = "You have already registered for this event.";
                $messageType = "error";
            } else {
                $registrationCode = "EVT-" . date("Y") . "-" .
                    strtoupper(bin2hex(random_bytes(4)));

                $price = (float)$event['price'];
                $isPaidEvent = $price > 0;
                $paymentStatus = $isPaidEvent ? 'pending' : 'not_required';

                $registrationStmt = $conn->prepare(
                    "INSERT INTO registrations
                     (student_id, event_id, registration_code, payment_status)
                     VALUES (?, ?, ?, ?)"
                );
                if (!$registrationStmt) {
                    throw new Exception("Unable to create registration.");
                }

                $registrationStmt->bind_param(
                    "iiss",
                    $studentId, $eventId, $registrationCode, $paymentStatus
                );
                $registrationStmt->execute();
                $registrationStmt->close();

                $conn->commit();

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
            }
        } catch (Throwable $e) {
            try {
                $conn->rollback();
            } catch (Throwable $rollbackError) {
                // Ignore rollback errors.
            }

            // Show the actual error during local development.
            error_log($e->getMessage());
            $message = "Registration failed. Please check the database setup and try again.";
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

    <title>EventEase - Student Registration</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #222;
        }

        .container {
            width: 90%;
            max-width: 700px;
            margin: 50px auto;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
        }

        .title h1 {
            margin: 0;
            color: #2563eb;
        }

        .title p {
            color: #666;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 15px;
            background: white;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn {
            width: 100%;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 600px) {

            .container {
                width: 95%;
                margin: 20px auto;
            }

            .card {
                padding: 22px;
            }

            .row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="title">

            <h1>EventEase</h1>

            <p>Student Registration</p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


    
<?php if ($eventError !== ""): ?>

    <div class="message error">
        <?= htmlspecialchars($eventError, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <p><a href="event.php">Back to events</a></p>

<?php else: ?>

    <h2>
        Register for:
        <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
    </h2>

    <p>
        Event fee:
        <?= (float)$event['price'] <= 0
            ? 'FREE'
            : '₹' . number_format((float)$event['price'], 2) ?>
    </p>

    <form method="POST" action="">
        <input type="hidden" name="event_id"
               value="<?= (int)$eventId ?>">

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <select id="department" name="department"
                    required onchange="updatePrograms()">
                <option value="">Choose your department</option>

                <?php foreach ($programs as $departmentName => $programList): ?>
                    <option value="<?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?>"
                        <?= (($department ?? '') === $departmentName) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="program">Program</label>
            <select id="program" name="program" required>
                <option value="">Choose your program</option>
            </select>
        </div>

        <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester" name="semester" required>
                <option value="">Choose semester</option>

                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="Semester <?= $i ?>"
                        <?= (($semester ?? '') === "Semester $i") ? 'selected' : '' ?>>
                        Semester <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="enrollment_number">Enrollment Number</label>
            <input type="text" id="enrollment_number"
                   name="enrollment_number"
                   value="<?= htmlspecialchars($enrollment_number ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required>
        </div>

        <div class="row">
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number"
                       maxlength="10" pattern="[0-9]{10}"
                       value="<?= htmlspecialchars($phone_number ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="whatsapp_number">WhatsApp Number</label>
                <input type="tel" id="whatsapp_number"
                       name="whatsapp_number"
                       maxlength="10" pattern="[0-9]{10}"
                       value="<?= htmlspecialchars($whatsapp_number ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>
        </div>

        <button type="submit" class="btn">Register Now</button>
    </form>

<?php endif; ?>
<script>

    // PHP department → program data
    const programs = <?php echo json_encode($programs); ?>;

    const selectedDepartment =
        <?php echo json_encode($department ?? ""); ?>;

    const selectedProgram =
        <?php echo json_encode($program ?? ""); ?>;


    function updatePrograms() {

        const department =
            document.getElementById("department").value;

        const programSelect =
            document.getElementById("program");


        // Clear existing programs

        programSelect.innerHTML =
            '<option value="">Choose your program</option>';


        // If department selected

        if (department && programs[department]) {

            programs[department].forEach(function(program) {

                const option =
                    document.createElement("option");

                option.value = program;

                option.textContent = program;


                if (program === selectedProgram) {
                    option.selected = true;
                }

                programSelect.appendChild(option);

            });

        }

    }


    // Load programs if department already selected
    document.addEventListener("DOMContentLoaded", function() {

        if (selectedDepartment) {
            updatePrograms();
        }

    });

</script>

</body>

</html>