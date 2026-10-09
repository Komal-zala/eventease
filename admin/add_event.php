
<?php
require_once "../config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];
    $start_time = !empty($_POST["start_time"]) ? $_POST["start_time"] : NULL;
    $end_time = !empty($_POST["end_time"]) ? $_POST["end_time"] : NULL;
    $venue = trim($_POST["venue"]);
    $organizer = trim($_POST["organizer"]);
    $capacity = (int) $_POST["capacity"];
    $price = (float) $_POST["price"];

    $registration_open = isset($_POST["registration_open"]) ? 1 : 0;

    $registration_deadline = !empty($_POST["registration_deadline"])
        ? $_POST["registration_deadline"]
        : NULL;

    $status = $_POST["status"];

    $imageName = NULL;


    // -----------------------------
    // Validate normal fields
    // -----------------------------

    if (
        empty($title) ||
        empty($description) ||
        empty($event_date) ||
        empty($venue) ||
        empty($organizer)
    ) {

        $error = "Please fill all required fields.";

    } elseif ($capacity <= 0) {

        $error = "Capacity must be greater than 0.";

    } elseif ($price < 0) {

        $error = "Price cannot be negative.";

    }


    // -----------------------------
    // Image upload
    // -----------------------------

    if (empty($error) && isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {

        $file = $_FILES["image"];

        $allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/jpg"
        ];

        $maxSize = 2 * 1024 * 1024; // 2 MB


        // Check upload error

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        }

        // Check file type

        elseif (!in_array($file["type"], $allowedTypes)) {

            $error = "Only JPG, JPEG and PNG images are allowed.";

        }

        // Check file size

        elseif ($file["size"] > $maxSize) {

            $error = "Image size must be less than 2 MB.";

        }

        else {

            $extension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );

            $allowedExtensions = ["jpg", "jpeg", "png"];

            if (!in_array($extension, $allowedExtensions)) {

                $error = "Invalid image extension.";

            } else {

                $uploadDirectory = "../uploads/events/";


                // Create folder if it doesn't exist

                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true);
                }


                // Generate unique filename

                $imageName = uniqid("event_", true) . "." . $extension;

                $imagePath = $uploadDirectory . $imageName;


                if (!move_uploaded_file($file["tmp_name"], $imagePath)) {

                    $error = "Failed to save the image.";

                    $imageName = NULL;
                }
            }
        }
    }


    // -----------------------------
    // Insert event
    // -----------------------------

    if (empty($error)) {

        $sql = "INSERT INTO events
        (
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
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

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

            header("Location: events.php?message=added");
            exit;

        } else {

            // Delete uploaded image if database insert fails

            if ($imageName && file_exists("../uploads/events/" . $imageName)) {
                unlink("../uploads/events/" . $imageName);
            }

            $error = "Failed to add event: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Add Event - EventEase</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .navbar {
            background: #212529;
            color: white;
            padding: 18px 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: #495057;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }

        .form-box h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full {
            grid-column: 1 / 3;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 7px;
        }

        label span {
            color: red;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #0d6efd;
        }

        .checkbox {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .checkbox input {
            width: auto;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .image-preview {
            margin-top: 12px;
            display: none;
        }

        .image-preview img {
            width: 220px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .image-info {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }

        .buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            padding: 11px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
        }

        .save {
            background: #198754;
            color: white;
        }

        .cancel {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: 1;
            }

        }

    </style>

</head>

<body>

<div class="navbar">

    <h2>EventEase Admin</h2>

    <a href="events.php">
        ← Back to Events
    </a>

</div>


<div class="container">

    <div class="form-box">

        <h1>Add New Event</h1>

        <p class="subtitle">
            Create a new event for EventEase.
        </p>


        <?php if (!empty($error)): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST" enctype="multipart/form-data">

            <div class="form-grid">


                <!-- Event Title -->

                <div class="form-group full">

                    <label>
                        Event Title <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Enter event title"
                        required
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                    >

                </div>


                <!-- Description -->

                <div class="form-group full">

                    <label>
                        Description <span>*</span>
                    </label>

                    <textarea
                        name="description"
                        placeholder="Enter event description"
                        required
                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

                </div>


                <!-- Event Image -->

                <div class="form-group full">

                    <label>
                        Event Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    >

                    <div class="image-info">
                        Allowed: JPG, JPEG, PNG | Maximum size: 2 MB
                    </div>


                    <div
                        class="image-preview"
                        id="imagePreview"
                    >

                        <img
                            id="preview"
                            src=""
                            alt="Event Image Preview"
                        >

                    </div>

                </div>


                <!-- Event Date -->

                <div class="form-group">

                    <label>
                        Event Date <span>*</span>
                    </label>

                    <input
                        type="date"
                        name="event_date"
                        required
                        value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>"
                    >

                </div>


                <!-- Start Time -->

                <div class="form-group">

                    <label>
                        Start Time
                    </label>

                    <input
                        type="time"
                        name="start_time"
                        value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>"
                    >

                </div>


                <!-- End Time -->

                <div class="form-group">

                    <label>
                        End Time
                    </label>

                    <input
                        type="time"
                        name="end_time"
                        value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>"
                    >

                </div>


                <!-- Venue -->

                <div class="form-group">

                    <label>
                        Venue <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="venue"
                        placeholder="Example: Atmiya University"
                        required
                        value="<?= htmlspecialchars($_POST['venue'] ?? '') ?>"
                    >

                </div>


                <!-- Organizer -->

                <div class="form-group">

                    <label>
                        Organizer <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="organizer"
                        placeholder="Enter organizer name"
                        required
                        value="<?= htmlspecialchars($_POST['organizer'] ?? '') ?>"
                    >

                </div>


                <!-- Capacity -->

                <div class="form-group">

                    <label>
                        Maximum Capacity <span>*</span>
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        min="1"
                        required
                        value="<?= htmlspecialchars($_POST['capacity'] ?? '100') ?>"
                    >

                </div>


                <!-- Price -->

                <div class="form-group">

                    <label>
                        Event Price (₹)
                    </label>

                    <input
                        type="number"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($_POST['price'] ?? '0') ?>"
                    >

                </div>


                <!-- Registration Deadline -->

                <div class="form-group">

                    <label>
                        Registration Deadline
                    </label>

                    <input
                        type="datetime-local"
                        name="registration_deadline"
                        value="<?= htmlspecialchars($_POST['registration_deadline'] ?? '') ?>"
                    >

                </div>


                <!-- Status -->

                <div class="form-group">

                    <label>
                        Event Status
                    </label>

                    <select name="status">

                        <option value="published">
                            Published
                        </option>

                        <option value="draft">
                            Draft
                        </option>

                        <option value="cancelled">
                            Cancelled
                        </option>

                        <option value="completed">
                            Completed
                        </option>

                    </select>

                </div>


                <!-- Registration -->

                <div class="form-group full">

                    <label class="checkbox">

                        <input
                            type="checkbox"
                            name="registration_open"
                            value="1"
                            checked
                        >

                        Registration is Open

                    </label>

                </div>

            </div>


            <div class="buttons">

                <button
                    type="submit"
                    class="btn save"
                >
                    Save Event
                </button>

                <a
                    href="events.php"
                    class="btn cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>


<script>

const imageInput = document.getElementById("image");

const imagePreview = document.getElementById("imagePreview");

const preview = document.getElementById("preview");


imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {

        imagePreview.style.display = "none";

        return;
    }


    // Check size

    if (file.size > 2 * 1024 * 1024) {

        alert("Image size must be less than 2 MB.");

        this.value = "";

        imagePreview.style.display = "none";

        return;
    }


    // Check type

    const allowedTypes = [
        "image/jpeg",
        "image/png"
    ];

    if (!allowedTypes.includes(file.type)) {

        alert("Only JPG, JPEG and PNG images are allowed.");

        this.value = "";

        imagePreview.style.display = "none";

        return;
    }


    // Show preview

    const reader = new FileReader();

    reader.onload = function (e) {

        preview.src = e.target.result;

        imagePreview.style.display = "block";

    };

    reader.readAsDataURL(file);

});

</script>

</body>

</html>
