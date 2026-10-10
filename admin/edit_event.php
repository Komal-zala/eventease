
<?php
require_once "../config/database.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: events.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
    $venue = trim($_POST['venue'] ?? '');
    $organizer = trim($_POST['organizer'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $registration_open = isset($_POST['registration_open']) ? 1 : 0;

    $registration_deadline = !empty($_POST['registration_deadline'])
        ? str_replace('T', ' ', $_POST['registration_deadline'])
        : null;

    if ($registration_deadline !== null &&
        strlen($registration_deadline) === 16) {
        $registration_deadline .= ':00';
    }

    $status = $_POST['status'] ?? '';
    $imageName = $event['image'] ?? '';
    $newImagePath = '';

    if (
        $title === '' || $description === '' ||
        $event_date === '' || $venue === '' ||
        $organizer === ''
    ) {
        $error = "Please fill in all required fields.";
    } elseif ($capacity < 1 || $price < 0) {
        $error = "Please enter a valid capacity and price.";
    }

    /* Image upload */
    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Image upload failed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Image size must be less than 2 MB.";
        } else {
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png'
            ];

            $mimeType = mime_content_type($file['tmp_name']);

            if (!isset($allowedTypes[$mimeType])) {
                $error = "Only JPG, JPEG and PNG images are allowed.";
            } else {
                $uploadDir = "../uploads/events/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = $allowedTypes[$mimeType];
                $newImageName = uniqid("event_", true) . "." . $extension;
                $destination = $uploadDir . $newImageName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $imageName = $newImageName;
                    $newImagePath = $destination;
                } else {
                    $error = "Failed to save the uploaded image.";
                }
            }
        }
    }

    /* Update event */
    if ($error === '') {
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
            "sssssssidisssi",
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
            $stmt->close();

            $oldImage = $event['image'] ?? '';

            if (
                $newImagePath !== '' &&
                $oldImage !== '' &&
                $oldImage !== $imageName
            ) {
                $oldImagePath = "../uploads/events/" . basename($oldImage);

                if (is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            header("Location: events.php?message=updated");
            exit;
        } else {
            $error = "Unable to update event. Please try again.";
            $stmt->close();

            if ($newImagePath !== '' && is_file($newImagePath)) {
                unlink($newImagePath);
            }
        }
    }

    /* Keep form values when an error occurs */
    $event['title'] = $title;
    $event['description'] = $description;
    $event['event_date'] = $event_date;
    $event['start_time'] = $start_time;
    $event['end_time'] = $end_time;
    $event['venue'] = $venue;
    $event['organizer'] = $organizer;
    $event['capacity'] = $capacity;
    $event['price'] = $price;
    $event['registration_deadline'] = $registration_deadline;
    $event['status'] = $status;
    $event['registration_open'] = $registration_open;
    $event['image'] = $imageName;
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Event | EventEase</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html {
    min-height: 100%;
}

body {
    min-height: 100vh;
    padding: 35px 16px;
    font-family: "Segoe UI", Arial, sans-serif;
    color: #25324b;

    /* Main background */
    background-color: #eaf0ff;
    background-image: linear-gradient(
        135deg,
        #dbeafe 0%,
        #ede9fe 45%,
        #e0f2fe 100%
    );
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;

    position: relative;
    overflow-x: hidden;
}

/* Decorative background circles */
.bg-circle {
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
    filter: blur(1px);
}

.bg-circle-one {
    width: 330px;
    height: 330px;
    top: -100px;
    left: -100px;
    background: rgba(96, 165, 250, 0.35);
}

.bg-circle-two {
    width: 380px;
    height: 380px;
    right: -120px;
    bottom: -120px;
    background: rgba(167, 139, 250, 0.35);
}

.bg-circle-three {
    width: 180px;
    height: 180px;
    top: 42%;
    left: 3%;
    background: rgba(196, 181, 253, 0.24);
}

/* Main glass card */
.container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 32px;

    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(255, 255, 255, 0.95);
    border-top: 5px solid #6478e8;
    border-radius: 20px;

    box-shadow: 0 18px 50px rgba(71, 85, 150, 0.16);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
}

h2 {
    color: #3949ab;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
}

.subtitle {
    color: #71809a;
    font-size: 14px;
    margin-bottom: 28px;
}

.form-group {
    margin-bottom: 20px;
    min-width: 0;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #394661;
    font-size: 14px;
    font-weight: 600;
}

input,
textarea,
select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #d7e0f0;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.96);
    color: #26334b;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

input:focus,
textarea:focus,
select:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
}

textarea {
    min-height: 120px;
    resize: vertical;
}

input[type="file"] {
    padding: 10px;
    background: #f8f9ff;
}

.row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.current-image {
    margin-bottom: 12px;
}

.current-image img,
#imagePreview img {
    width: 220px;
    max-width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #dfe5f2;
    box-shadow: 0 5px 15px rgba(71, 85, 150, 0.10);
}

#imagePreview {
    display: none;
    margin-top: 15px;
}

#imagePreview p {
    margin-bottom: 8px;
    color: #394661;
    font-size: 14px;
}

.help {
    color: #78849a;
    font-size: 12px;
    margin-top: 7px;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 14px;
    background: rgba(238, 242, 255, 0.8);
    border: 1px solid #dfe4f7;
    border-radius: 10px;
}

.checkbox input {
    width: 17px;
    height: 17px;
    margin: 0;
    accent-color: #6478e8;
}

.checkbox label {
    margin: 0;
    cursor: pointer;
}

.error {
    background: #fff0f1;
    color: #a12535;
    border: 1px solid #ffd4d9;
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 22px;
    font-size: 14px;
}

.buttons {
    display: flex;
    gap: 12px;
    margin-top: 28px;
    flex-wrap: wrap;
}

button,
.cancel {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 12px 23px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    transition: transform .2s, box-shadow .2s;
}

button {
    color: white;
    background: linear-gradient(135deg, #667eea, #7658d6);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.28);
}

.cancel {
    color: #44516b;
    background: #edf1fa;
    border: 1px solid #dfe5f2;
}

button:hover,
.cancel:hover {
    transform: translateY(-2px);
}

button:hover {
    box-shadow: 0 9px 20px rgba(102, 126, 234, 0.35);
}

@media (max-width: 600px) {
    body {
        padding: 18px 10px;
    }

    .container {
        padding: 22px 17px;
    }

    h2 {
        font-size: 24px;
    }

    .row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .buttons {
        flex-direction: column;
    }

    button,
    .cancel {
        width: 100%;
    }
}
</style>
</head>

<body>

<!-- Background circles -->
<div class="bg-circle bg-circle-one"></div>
<div class="bg-circle bg-circle-two"></div>
<div class="bg-circle bg-circle-three"></div>

<!-- Main content -->
<div class="container">

    <h2>Edit Event</h2>
    <p class="subtitle">Update your event details and registration settings.</p>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label for="title">Event Title *</label>
            <input type="text" id="title" name="title"
                   value="<?= e($event['title']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description"
                      required><?= e($event['description']) ?></textarea>
        </div>

        <div class="row">
            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" id="event_date" name="event_date"
                       value="<?= e($event['event_date']) ?>" required>
            </div>

            <div class="form-group">
                <label for="venue">Venue *</label>
                <input type="text" id="venue" name="venue"
                       value="<?= e($event['venue']) ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time"
                       value="<?= e($event['start_time']) ?>">
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time"
                       value="<?= e($event['end_time']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="organizer">Organizer *</label>
            <input type="text" id="organizer" name="organizer"
                   value="<?= e($event['organizer']) ?>" required>
        </div>

        <div class="row">
            <div class="form-group">
                <label for="capacity">Capacity *</label>
                <input type="number" id="capacity" name="capacity"
                       min="1" value="<?= e($event['capacity']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price"
                       min="0" step="0.01"
                       value="<?= e($event['price']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Current Event Image</label>

            <?php if (!empty($event['image'])): ?>
                <div class="current-image">
                    <img
                        src="../uploads/events/<?= e(basename($event['image'])) ?>"
                        alt="Current Event Image">
                </div>
            <?php else: ?>
                <p class="help">No image uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">Replace Event Image</label>
            <input type="file" name="image" id="image"
                   accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <div class="help">JPG, JPEG or PNG. Maximum size: 2 MB.</div>

            <div id="imagePreview">
                <p><strong>New Image Preview</strong></p>
                <img id="previewImage" src="" alt="Image preview">
            </div>
        </div>

        <div class="form-group">
            <label for="registration_deadline">Registration Deadline</label>
            <input type="datetime-local" id="registration_deadline"
                   name="registration_deadline"
                   value="<?php
                       if (!empty($event['registration_deadline'])) {
                           echo e(date(
                               'Y-m-d\TH:i',
                               strtotime($event['registration_deadline'])
                           ));
                       }
                   ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php
                $statuses = ['draft', 'published', 'cancelled', 'completed'];
                foreach ($statuses as $option):
                ?>
                    <option value="<?= e($option) ?>"
                        <?= (($event['status'] ?? '') === $option) ? 'selected' : '' ?>>
                        <?= e(ucfirst($option)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" id="registration_open"
                       name="registration_open" value="1"
                       <?= !empty($event['registration_open']) ? 'checked' : '' ?>>
                <label for="registration_open">Registration Open</label>
            </div>
        </div>

        <div class="buttons">
            <button type="submit">Update Event</button>
            <a href="events.php" class="cancel">Cancel</a>
        </div>

    </form>
</div>

<script>
document.getElementById("image").addEventListener("change", function () {
    const file = this.files[0];
    const preview = document.getElementById("imagePreview");
    const previewImage = document.getElementById("previewImage");

    if (!file) {
        preview.style.display = "none";
        previewImage.src = "";
        return;
    }

    if (!["image/jpeg", "image/png"].includes(file.type)) {
        alert("Only JPG, JPEG and PNG images are allowed.");
        this.value = "";
        preview.style.display = "none";
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert("Image size must be less than 2 MB.");
        this.value = "";
        preview.style.display = "none";
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        previewImage.src = event.target.result;
        preview.style.display = "block";
    };

    reader.readAsDataURL(file);
});
</script>

</body>
</html>
