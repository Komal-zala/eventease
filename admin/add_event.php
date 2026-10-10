
<?php
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";

    $start_time = !empty($_POST["start_time"])
        ? $_POST["start_time"] : NULL;

    $end_time = !empty($_POST["end_time"])
        ? $_POST["end_time"] : NULL;

    $venue = trim($_POST["venue"] ?? "");
    $organizer = trim($_POST["organizer"] ?? "");

    $capacity = filter_var(
        $_POST["capacity"] ?? "",
        FILTER_VALIDATE_INT
    );

    $priceInput = $_POST["price"] ?? "0";

    $registration_open = isset($_POST["registration_open"]) ? 1 : 0;

    $registration_deadline = !empty($_POST["registration_deadline"])
        ? $_POST["registration_deadline"] : NULL;

    $status = $_POST["status"] ?? "published";
    $imageName = NULL;
    $uploadDirectory = "../uploads/events/";

    // Validate required fields
    if (
        $title === "" ||
        $description === "" ||
        $event_date === "" ||
        $venue === "" ||
        $organizer === ""
    ) {
        $error = "Please fill in all required fields.";

    } elseif ($capacity === false || $capacity <= 0) {
        $error = "Capacity must be greater than zero.";

    } elseif (
        !is_numeric($priceInput) ||
        !is_finite((float)$priceInput) ||
        (float)$priceInput < 0
    ) {
        $error = "Please enter a valid event price.";

    } elseif (!in_array(
        $status,
        ["published", "draft", "cancelled", "completed"],
        true
    )) {
        $error = "Please select a valid event status.";
    }

    // Validate date
    if ($error === "" && $event_date < date("Y-m-d")) {
        $error = "Event date cannot be in the past.";
    }

    // Validate time
    if (
        $error === "" &&
        $start_time !== NULL &&
        $end_time !== NULL &&
        $end_time <= $start_time
    ) {
        $error = "End time must be later than start time.";
    }

    // Validate registration deadline
    if ($error === "" && $registration_deadline !== NULL) {
        $deadlineTimestamp = strtotime($registration_deadline);

        if ($deadlineTimestamp === false) {
            $error = "Please enter a valid registration deadline.";

        } elseif (
            $deadlineTimestamp < strtotime(date("Y-m-d H:i:s"))
        ) {
            $error = "Registration deadline cannot be in the past.";

        } elseif (
            $registration_deadline > $event_date . " 23:59"
        ) {
            $error = "Registration deadline cannot be after the event date.";
        }
    }

    // Validate and upload image
    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES["image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "There was a problem uploading the image.";

        } elseif ($file["size"] > 2 * 1024 * 1024) {
            $error = "Image size must not exceed 2 MB.";

        } else {
            $extension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );

            $allowedExtensions = ["jpg", "jpeg", "png"];

            $allowedMimeTypes = [
                "jpg" => "image/jpeg",
                "jpeg" => "image/jpeg",
                "png" => "image/png"
            ];

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file["tmp_name"]);
            finfo_close($fileInfo);

            if (
                !in_array($extension, $allowedExtensions, true) ||
                !isset($allowedMimeTypes[$extension]) ||
                $mimeType !== $allowedMimeTypes[$extension] ||
                getimagesize($file["tmp_name"]) === false
            ) {
                $error = "Only valid JPG, JPEG and PNG images are allowed.";

            } else {
                if (
                    !is_dir($uploadDirectory) &&
                    !mkdir($uploadDirectory, 0755, true)
                ) {
                    $error = "Unable to create the image upload folder.";

                } else {
                    $imageName = bin2hex(random_bytes(16))
                        . "." . $extension;

                    $imagePath = $uploadDirectory . $imageName;

                    if (!move_uploaded_file(
                        $file["tmp_name"],
                        $imagePath
                    )) {
                        $error = "Failed to save the event image.";
                        $imageName = NULL;
                    }
                }
            }
        }
    }

    // Insert event into database
    if ($error === "") {

        $sql = "INSERT INTO events (
            title,
            description,
            event_date,
            start_time,
            end_time,
            venue,
            organizer,
            capacity,
            price,
            image,
            registration_open,
            registration_deadline,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt === false) {
            $error = "Unable to prepare the database query.";

        } else {
            $price = (float)$priceInput;

            mysqli_stmt_bind_param(
                $stmt,
                "sssssssidssss",
                $title,
                $description,
                $event_date,
                $start_time,
                $end_time,
                $venue,
                $organizer,
                $capacity,
                $price,
                $imageName,
                $registration_open,
                $registration_deadline,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header("Location: events.php?message=added");
                exit;

            } else {
                if (
                    $imageName !== NULL &&
                    is_file($uploadDirectory . $imageName)
                ) {
                    unlink($uploadDirectory . $imageName);
                }

                $error = "Unable to save the event. Please try again.";
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Event | EventEase</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            color: #1e293b;
            background:
                radial-gradient(
                    circle at 8% 12%,
                    rgba(129, 140, 248, 0.22),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 92% 82%,
                    rgba(56, 189, 248, 0.20),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #eef2ff 0%,
                    #f8faff 48%,
                    #eaf7ff 100%
                );
            background-attachment: fixed;
        }

        /* Decorative background circles */
        .background-decoration {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            filter: blur(1px);
        }

        .circle-one {
            width: 240px;
            height: 240px;
            background: rgba(99, 102, 241, 0.09);
            top: 120px;
            left: -100px;
        }

        .circle-two {
            width: 320px;
            height: 320px;
            background: rgba(14, 165, 233, 0.08);
            bottom: -120px;
            right: -100px;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 18px rgba(30, 41, 59, 0.04);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 23px;
            font-weight: 750;
            color: #1d4ed8;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 39px;
            height: 39px;
            border-radius: 11px;
            background: linear-gradient(135deg, #2563eb, #6366f1);
            color: #ffffff;
            font-size: 21px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.22);
        }

        .navbar a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #ffffff;
            background: #2563eb;
            padding: 11px 17px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .navbar a:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* Main Container */
        .container {
            max-width: 1000px;
            margin: 42px auto;
            padding: 0 20px 35px;
        }

        /* Form Card */
        .form-box {
            background: rgba(255, 255, 255, 0.96);
            padding: 36px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow:
                0 20px 55px rgba(30, 64, 175, 0.09),
                0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .form-heading {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 29px;
            padding-bottom: 24px;
            border-bottom: 1px solid #edf0f7;
        }

        .heading-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: #2563eb;
            background: #eff6ff;
            font-size: 25px;
        }

        .form-box h1 {
            font-size: 28px;
            font-weight: 750;
            color: #172554;
            margin-bottom: 8px;
            letter-spacing: -0.6px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .required-note {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }

        .required-note span {
            color: #ef4444;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 23px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        label {
            font-size: 14px;
            font-weight: 650;
            color: #334155;
            margin-bottom: 9px;
        }

        label span {
            color: #ef4444;
        }

        /* Inputs */
        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            background: #ffffff;
            color: #1e293b;
            font-size: 14px;
            transition:
                border-color 0.2s,
                box-shadow 0.2s,
                background 0.2s;
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8;
        }

        input:hover,
        textarea:hover,
        select:hover {
            border-color: #a5b4fc;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.11);
        }

        textarea {
            min-height: 125px;
            resize: vertical;
            line-height: 1.65;
        }

        /* Image Upload */
        input[type="file"] {
            background: #f8faff;
            padding: 10px;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            border: none;
            background: #e8efff;
            color: #1d4ed8;
            padding: 9px 12px;
            border-radius: 6px;
            margin-right: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        input[type="file"]::file-selector-button:hover {
            background: #dbeafe;
        }

        .image-info {
            color: #64748b;
            font-size: 12px;
            margin-top: 8px;
        }

        .image-preview {
            display: none;
            margin-top: 14px;
            padding: 10px;
            width: fit-content;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .image-preview img {
            display: block;
            width: 230px;
            max-width: 100%;
            max-height: 165px;
            object-fit: cover;
            border-radius: 7px;
        }

        /* Registration Checkbox */
        .checkbox {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 11px;
            padding: 15px 16px;
            background: #f5f8ff;
            border: 1px solid #e0e7ff;
            border-radius: 9px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .checkbox:hover {
            background: #eef2ff;
        }

        .checkbox input {
            flex-shrink: 0;
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .checkbox label {
            margin: 0;
        }

        /* Error Message */
        .error {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Buttons */
        .buttons {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-top: 32px;
            padding-top: 25px;
            border-top: 1px solid #edf0f7;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border: none;
            padding: 13px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            transition:
                background 0.2s,
                transform 0.2s,
                box-shadow 0.2s;
        }

        .save {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff;
            box-shadow: 0 5px 12px rgba(79, 70, 229, 0.18);
        }

        .save:hover {
            background: linear-gradient(135deg, #1d4ed8, #4338ca);
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(79, 70, 229, 0.23);
        }

        .cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #dbe2ea;
        }

        .cancel:hover {
            background: #e2e8f0;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            color: #64748b;
            font-size: 12px;
            padding: 0 15px 25px;
        }

        .page-footer strong {
            color: #475569;
        }

        /* Responsive Design */
        @media (max-width: 700px) {
            .navbar {
                padding: 15px 18px;
                gap: 10px;
            }

            .brand {
                gap: 8px;
                font-size: 18px;
            }

            .brand-icon {
                width: 34px;
                height: 34px;
                font-size: 18px;
            }

            .navbar a {
                padding: 10px;
                font-size: 12px;
            }

            .container {
                margin: 23px auto;
                padding: 0 13px 25px;
            }

            .form-box {
                padding: 23px 18px;
                border-radius: 12px;
            }

            .form-heading {
                gap: 11px;
                margin-bottom: 24px;
                padding-bottom: 20px;
            }

            .heading-icon {
                width: 43px;
                height: 43px;
                font-size: 21px;
            }

            .form-box h1 {
                font-size: 23px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 19px;
            }

            .full {
                grid-column: 1;
            }

            .buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

            .circle-one {
                width: 150px;
                height: 150px;
            }

            .circle-two {
                width: 180px;
                height: 180px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition: none !important;
            }
        }
    </style>
</head>

<body>

<div class="background-decoration circle-one"></div>
<div class="background-decoration circle-two"></div>

<header class="navbar">
    <div class="brand">
        <span class="brand-icon" aria-hidden="true">✦</span>
        <span>EventEase Admin</span>
    </div>

    <a href="events.php">
        <span aria-hidden="true">&larr;</span>
        Back to Events
    </a>
</header>

<main class="container">
    <section class="form-box">

        <div class="form-heading">
            <div class="heading-icon" aria-hidden="true">＋</div>

            <div>
                <h1>Add New Event</h1>
                <p class="subtitle">
                    Create and manage your event details in one place.
                </p>
                <p class="required-note">
                    Fields marked with <span>*</span> are required.
                </p>
            </div>
        </div>

        <?php if ($error !== ""): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-grid">

                <div class="form-group full">
                    <label for="title">Event Title <span>*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Enter event title"
                        maxlength="255"
                        required
                        value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group full">
                    <label for="description">Event Description <span>*</span></label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Write a short description about your event..."
                        required
                    ><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group full">
                    <label for="image">Event Image</label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    >

                    <p class="image-info">
                        Accepted formats: JPG, JPEG and PNG. Maximum size: 2 MB.
                    </p>

                    <div class="image-preview" id="imagePreview">
                        <img
                            id="preview"
                            src=""
                            alt="Selected event image preview"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date <span>*</span></label>
                    <input
                        type="date"
                        id="event_date"
                        name="event_date"
                        min="<?= date('Y-m-d') ?>"
                        required
                        value="<?= htmlspecialchars($_POST['event_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input
                        type="time"
                        id="start_time"
                        name="start_time"
                        value="<?= htmlspecialchars($_POST['start_time'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input
                        type="time"
                        id="end_time"
                        name="end_time"
                        value="<?= htmlspecialchars($_POST['end_time'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="venue">Venue <span>*</span></label>
                    <input
                        type="text"
                        id="venue"
                        name="venue"
                        placeholder="Enter event venue"
                        required
                        value="<?= htmlspecialchars($_POST['venue'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="organizer">Organizer <span>*</span></label>
                    <input
                        type="text"
                        id="organizer"
                        name="organizer"
                        placeholder="Enter organizer name"
                        required
                        value="<?= htmlspecialchars($_POST['organizer'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="capacity">Maximum Capacity <span>*</span></label>
                    <input
                        type="number"
                        id="capacity"
                        name="capacity"
                        min="1"
                        step="1"
                        placeholder="e.g. 100"
                        required
                        value="<?= htmlspecialchars($_POST['capacity'] ?? '100', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="price">Event Price (₹)</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        placeholder="0 for free events"
                        value="<?= htmlspecialchars($_POST['price'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="registration_deadline">
                        Registration Deadline
                    </label>
                    <input
                        type="datetime-local"
                        id="registration_deadline"
                        name="registration_deadline"
                        value="<?= htmlspecialchars($_POST['registration_deadline'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="status">Event Status</label>

                    <?php $selectedStatus = $_POST['status'] ?? 'published'; ?>

                    <select id="status" name="status">
                        <option value="published" <?= $selectedStatus === 'published' ? 'selected' : '' ?>>
                            Published
                        </option>
                        <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>>
                            Draft
                        </option>
                        <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>
                            Cancelled
                        </option>
                        <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>
                            Completed
                        </option>
                    </select>
                </div>

                <div class="form-group full">
                    <label class="checkbox" for="registration_open">
                        <input
                            type="checkbox"
                            id="registration_open"
                            name="registration_open"
                            value="1"
                            <?= !isset($_POST['registration_open']) || $_POST['registration_open'] == '1' ? 'checked' : '' ?>
                        >
                        <span>Registration is Open</span>
                    </label>
                </div>

            </div>

            <div class="buttons">
                <button type="submit" class="btn save">
                    <span aria-hidden="true">＋</span>
                    Save Event
                </button>

                <a href="events.php" class="btn cancel">
                    Cancel
                </a>
            </div>

        </form>
    </section>
</main>

<footer class="page-footer">
    Powered by <strong>EventEase</strong> · College Event Management
</footer>

<script>
const imageInput = document.getElementById("image");
const imagePreview = document.getElementById("imagePreview");
const preview = document.getElementById("preview");

imageInput.addEventListener("change", function () {
    const file = this.files[0];

    imagePreview.style.display = "none";
    preview.removeAttribute("src");

    if (!file) {
        return;
    }

    const allowedTypes = ["image/jpeg", "image/png"];
    const maxSize = 2 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        alert("Only JPG, JPEG and PNG images are allowed.");
        this.value = "";
        return;
    }

    if (file.size > maxSize) {
        alert("Image size must not exceed 2 MB.");
        this.value = "";
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        preview.src = event.target.result;
        imagePreview.style.display = "block";
    };

    reader.readAsDataURL(file);
});
</script>

</body>
</html>