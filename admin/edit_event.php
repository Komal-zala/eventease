<?php
require_once "../config/database.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit;
}

$id = (int) $_GET['id'];

/* Get existing event */
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header("Location: events.php");
    exit;
}

$error = "";

/* Update Event */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
    $venue = trim($_POST['venue']);
    $organizer = trim($_POST['organizer']);
    $capacity = (int) $_POST['capacity'];
    $price = (float) $_POST['price'];
    $registration_deadline = !empty($_POST['registration_deadline'])
        ? $_POST['registration_deadline']
        : null;
    $status = $_POST['status'];
    $registration_open = isset($_POST['registration_open']) ? 1 : 0;

    /* Keep old image */
    $imageName = $event['image'];

    /* Image replacement */
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "Image upload failed.";
        } else {

            $file = $_FILES['image'];

            /* Maximum 2 MB */
            if ($file['size'] > 2 * 1024 * 1024) {
                $error = "Image size must be less than 2 MB.";
            } else {

                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png'
                ];

                $allowedExtensions = [
                    'jpg',
                    'jpeg',
                    'png'
                ];

                $mimeType = mime_content_type($file['tmp_name']);
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (
                    !in_array($mimeType, $allowedMimeTypes) ||
                    !in_array($extension, $allowedExtensions)
                ) {
                    $error = "Only JPG, JPEG and PNG images are allowed.";
                } else {

                    /* Upload folder */
                    $uploadDir = "../uploads/events/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    /* New unique image name */
                    $newImageName = uniqid("event_", true) . "." . $extension;

                    $destination = $uploadDir . $newImageName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {

                        /* Delete old image */
                        if (!empty($event['image'])) {

                            $oldImagePath = $uploadDir . $event['image'];

                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }

                        $imageName = $newImageName;

                    } else {
                        $error = "Failed to save the uploaded image.";
                    }
                }
            }
        }
    }

    /* Update database */
    if (empty($error)) {

        $stmt = $conn->prepare("
            UPDATE events SET
                title = ?,
                description = ?,
                event_date = ?,
                start_time = ?,
                end_time = ?,
                venue = ?,
                organizer = ?,
                capacity = ?,
                price = ?,
                image = ?,
                registration_open = ?,
                registration_deadline = ?,
                status = ?
            WHERE id = ?
        ");

       $stmt->bind_param(
    "sssssssidsissi",
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
    $status,
    $id
);

        if ($stmt->execute()) {

            header("Location: events.php?message=updated");
            exit;

        } else {
            $error = "Failed to update event: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Event - Admin</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .current-image {
            margin-bottom: 12px;
        }

        .current-image img {
            width: 220px;
            height: 130px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        #imagePreview {
            display: none;
            margin-top: 15px;
        }

        #imagePreview img {
            width: 220px;
            height: 130px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox input {
            width: auto;
        }

        .error {
            background: #ffe5e5;
            color: #b30000;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        button,
        .cancel {
            padding: 11px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        button {
            background: #007bff;
            color: white;
        }

        .cancel {
            background: #6c757d;
            color: white;
        }

        button:hover {
            background: #0056b3;
        }

        .cancel:hover {
            background: #545b62;
        }

        .help {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>✏️ Edit Event</h2>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- Event Title -->
        <div class="form-group">
            <label>Event Title</label>

            <input
                type="text"
                name="title"
                value="<?= htmlspecialchars($event['title']) ?>"
                required
            >
        </div>

        <!-- Description -->
        <div class="form-group">
            <label>Description</label>

            <textarea
                name="description"
                required
            ><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <!-- Date -->
        <div class="row">

            <div class="form-group">
                <label>Event Date</label>

                <input
                    type="date"
                    name="event_date"
                    value="<?= htmlspecialchars($event['event_date']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Venue</label>

                <input
                    type="text"
                    name="venue"
                    value="<?= htmlspecialchars($event['venue']) ?>"
                    required
                >
            </div>

        </div>

        <!-- Time -->
        <div class="row">

            <div class="form-group">
                <label>Start Time</label>

                <input
                    type="time"
                    name="start_time"
                    value="<?= htmlspecialchars($event['start_time'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label>End Time</label>

                <input
                    type="time"
                    name="end_time"
                    value="<?= htmlspecialchars($event['end_time'] ?? '') ?>"
                >
            </div>

        </div>

        <!-- Organizer -->
        <div class="form-group">
            <label>Organizer</label>

            <input
                type="text"
                name="organizer"
                value="<?= htmlspecialchars($event['organizer']) ?>"
                required
            >
        </div>

        <!-- Capacity / Price -->
        <div class="row">

            <div class="form-group">
                <label>Capacity</label>

                <input
                    type="number"
                    name="capacity"
                    min="1"
                    value="<?= htmlspecialchars($event['capacity']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Price</label>

                <input
                    type="number"
                    name="price"
                    min="0"
                    step="0.01"
                    value="<?= htmlspecialchars($event['price']) ?>"
                    required
                >
            </div>

        </div>

        <!-- Existing Image -->
        <div class="form-group">

            <label>Current Event Image</label>

            <?php if (!empty($event['image'])): ?>

                <div class="current-image">
                    <img
                        src="../uploads/events/<?= htmlspecialchars($event['image']) ?>"
                        alt="Current Event Image"
                    >
                </div>

            <?php else: ?>

                <p class="help">No image uploaded.</p>

            <?php endif; ?>

        </div>

        <!-- Replace Image -->
        <div class="form-group">

            <label>Replace Event Image</label>

            <input
                type="file"
                name="image"
                id="image"
                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
            >

            <div class="help">
                JPG, JPEG or PNG only. Maximum size: 2 MB.
            </div>

            <div id="imagePreview">
                <p><strong>New Image Preview:</strong></p>

                <img id="previewImage" src="" alt="Preview">
            </div>

        </div>

        <!-- Registration Deadline -->
        <div class="form-group">

            <label>Registration Deadline</label>

            <input
                type="datetime-local"
                name="registration_deadline"
                value="<?php
                    if (!empty($event['registration_deadline'])) {
                        echo date(
                            'Y-m-d\TH:i',
                            strtotime($event['registration_deadline'])
                        );
                    }
                ?>"
            >

        </div>

        <!-- Status -->
        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option
                    value="draft"
                    <?= $event['status'] === 'draft' ? 'selected' : '' ?>
                >
                    Draft
                </option>

                <option
                    value="published"
                    <?= $event['status'] === 'published' ? 'selected' : '' ?>
                >
                    Published
                </option>

                <option
                    value="cancelled"
                    <?= $event['status'] === 'cancelled' ? 'selected' : '' ?>
                >
                    Cancelled
                </option>

                <option
                    value="completed"
                    <?= $event['status'] === 'completed' ? 'selected' : '' ?>
                >
                    Completed
                </option>

            </select>

        </div>

        <!-- Registration Open -->
        <div class="form-group">

            <label class="checkbox">

                <input
                    type="checkbox"
                    name="registration_open"
                    value="1"
                    <?= $event['registration_open'] ? 'checked' : '' ?>
                >

                Registration Open

            </label>

        </div>

        <!-- Buttons -->
        <div class="buttons">

            <button type="submit">
                💾 Update Event
            </button>

            <a href="events.php" class="cancel">
                Cancel
            </a>

        </div>

    </form>

</div>

<script>

document.getElementById("image").addEventListener("change", function(event) {

    const file = event.target.files[0];

    if (!file) {
        document.getElementById("imagePreview").style.display = "none";
        return;
    }

    const allowedTypes = [
        "image/jpeg",
        "image/png"
    ];

    if (!allowedTypes.includes(file.type)) {
        alert("Only JPG, JPEG and PNG images are allowed.");
        event.target.value = "";
        document.getElementById("imagePreview").style.display = "none";
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert("Image size must be less than 2 MB.");
        event.target.value = "";
        document.getElementById("imagePreview").style.display = "none";
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {

        document.getElementById("previewImage").src = e.target.result;

        document.getElementById("imagePreview").style.display = "block";
    };

    reader.readAsDataURL(file);

});

</script>

</body>
</html>