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

            // Check duplicate enrollment number
            $check = $conn->prepare(
                "SELECT id FROM students WHERE enrollment_number = ?"
            );

            $check->execute([$enrollment_number]);

            if ($check->fetch()) {

                $message = "This enrollment number is already registered.";
                $messageType = "error";

            } else {

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

               $stmt->execute([
    $name,
    $department,
    $program,
    $semester,
    $enrollment_number,
    $phone_number,
    $whatsapp_number
]);

// Get newly created student ID
$studentId = $conn->lastInsertId();

// Create unique registration code
$registrationCode = "EVT-" . date("Y") . "-" . strtoupper(
    substr(bin2hex(random_bytes(5)), 0, 8)
);

// Insert registration
$registrationSql = "
    INSERT INTO registrations
    (
        student_id,
        registration_code
    )
    VALUES (?, ?)
";

$registrationStmt = $conn->prepare($registrationSql);

$registrationStmt->execute([
    $studentId,
    $registrationCode
]);

// Redirect to thank-you page
header(
    "Location: thankyou.php?code=" .
    urlencode($registrationCode)
);

exit;

                // Clear fields
                $name = "";
                $department = "";
                $program = "";
                $semester = "";
                $enrollment_number = "";
                $phone_number = "";
                $whatsapp_number = "";
            }

        } catch (PDOException $e) {

            $message = "Something went wrong. Please try again.";
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


        <form method="POST" action="">


            <!-- Name -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    required
                >

            </div>


            <!-- Department -->

            <div class="form-group">

                <label for="department">
                    Department
                </label>

                <select
                    id="department"
                    name="department"
                    required
                    onchange="updatePrograms()"
                >

                    <option value="">
                        Choose your department
                    </option>

                    <?php foreach ($programs as $departmentName => $programList): ?>

                        <option
                            value="<?php echo htmlspecialchars($departmentName); ?>"
                            <?php
                            echo (($department ?? '') === $departmentName)
                                ? 'selected'
                                : '';
                            ?>
                        >

                            <?php echo htmlspecialchars($departmentName); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Program -->

            <div class="form-group">

                <label for="program">
                    Program
                </label>

                <select
                    id="program"
                    name="program"
                    required
                >

                    <option value="">
                        Choose your program
                    </option>

                </select>

            </div>


            <!-- Semester -->

            <div class="form-group">

                <label for="semester">
                    Semester
                </label>

                <select
                    id="semester"
                    name="semester"
                    required
                >

                    <option value="">
                        Choose semester
                    </option>

                    <option value="Semester 1">
                        Semester 1
                    </option>

                    <option value="Semester 2">
                        Semester 2
                    </option>

                    <option value="Semester 3">
                        Semester 3
                    </option>

                    <option value="Semester 4">
                        Semester 4
                    </option>

                    <option value="Semester 5">
                        Semester 5
                    </option>

                    <option value="Semester 6">
                        Semester 6
                    </option>

                    <option value="Semester 7">
                        Semester 7
                    </option>

                    <option value="Semester 8">
                        Semester 8
                    </option>

                </select>

            </div>


            <!-- Enrollment Number -->

            <div class="form-group">

                <label for="enrollment_number">
                    Enrollment Number
                </label>

                <input
                    type="text"
                    id="enrollment_number"
                    name="enrollment_number"
                    placeholder="Enter enrollment number"
                    value="<?php echo htmlspecialchars($enrollment_number ?? ''); ?>"
                    required
                >

            </div>


            <div class="row">

                <!-- Phone -->

                <div class="form-group">

                    <label for="phone_number">
                        Phone Number
                    </label>

                    <input
                        type="tel"
                        id="phone_number"
                        name="phone_number"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        placeholder="9876543210"
                        value="<?php echo htmlspecialchars($phone_number ?? ''); ?>"
                        required
                    >

                </div>


                <!-- WhatsApp -->

                <div class="form-group">

                    <label for="whatsapp_number">
                        WhatsApp Number
                    </label>

                    <input
                        type="tel"
                        id="whatsapp_number"
                        name="whatsapp_number"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        placeholder="9876543210"
                        value="<?php echo htmlspecialchars($whatsapp_number ?? ''); ?>"
                        required
                    >

                </div>

            </div>


            <!-- Submit -->

            <button
                type="submit"
                class="btn"
            >
                Register Now
            </button>

        </form>

    </div>

</div>


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