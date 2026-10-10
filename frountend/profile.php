
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'EventEase User';
$user_email = $_SESSION['user_email'] ?? 'user@eventease.com';
$enroll_no = $_SESSION['enroll_no'] ?? '';

$parts = preg_split('/\s+/', trim($user_name));
$initials = '';
foreach ($parts as $part) {
    if ($part !== '') $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials, 0, 2);

$registered_events = [];
$event_error = '';

$db = new mysqli("localhost", "root", "", "eventease");

if ($db->connect_error) {
    $event_error = "Database connection failed.";
} elseif ($enroll_no === '') {
    $event_error = "Enrollment number is missing from your login session.";
} else {
    $db->set_charset("utf8mb4");

    $sql = "SELECT e.id AS event_id, e.title, e.description,
                   e.event_date, e.start_time, e.end_time,
                   e.venue, e.organizer, e.price,
                   r.registration_code, r.qr_code,
                   r.registered_at, r.payment_status
            FROM students s
            JOIN registrations r ON r.student_id = s.id
            JOIN events e ON e.id = r.event_id
            WHERE s.enrollment_number = ?
            ORDER BY r.registered_at DESC";

    $stmt = $db->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $enroll_no);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $registered_events[] = $row;
            }
        } else {
            $event_error = "Unable to load registered events.";
        }
        $stmt->close();
    } else {
        $event_error = "Unable to load registered events.";
    }
}

if (isset($db) && $db instanceof mysqli) {
    $db->close();
}

$page_title = "EventEase | My Profile";
include './header.php';
?>

<style>
.profile-page,.profile-page *{box-sizing:border-box}
.profile-page{min-height:calc(100vh - 75px);padding:40px 20px 60px;background:radial-gradient(circle at 5% 10%,#7c3aed12,transparent 28%),radial-gradient(circle at 95% 85%,#c026d312,transparent 28%),#faf8fd;color:#24172f;font-family:Arial,sans-serif}
.profile-container{max-width:1050px;margin:auto}
.profile-heading{margin-bottom:25px}
.profile-heading .eyebrow{color:#7c3aed;font-size:11px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase}
.profile-heading h1{margin:10px 0;font-size:36px}
.profile-heading p,.card-header p,.logout-section p,.registered-events-heading p{color:#83788d;font-size:13px}
.profile-layout{display:grid;grid-template-columns:280px 1fr;gap:22px}
.profile-card,.info-card,.registered-event-card,.events-empty-message{background:#fff;border:1px solid #eee7f4;border-radius:20px;box-shadow:0 12px 35px #3b195810;overflow:hidden}
.profile-sidebar{padding:25px}
.profile-cover{height:90px;margin:-25px -25px 0;background:linear-gradient(135deg,#2d0b4d,#7026b9,#c026d3)}
.profile-avatar{width:85px;height:85px;margin:-42px auto 15px;border:5px solid white;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#c026d3);display:flex;align-items:center;justify-content:center;color:white;font-size:26px;font-weight:bold}
.profile-name,.profile-email{text-align:center;overflow-wrap:anywhere}
.profile-name{font-size:20px;margin:0}
.profile-email{font-size:13px;color:#8b8292}
.profile-content{display:flex;flex-direction:column;gap:20px}
.info-card{padding:25px}
.card-header{margin-bottom:20px}
.card-header h2,.registered-events-heading h2{margin:0 0 8px;font-size:22px}
.info-grid,.registered-event-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.info-item,.registered-event-detail{padding:15px;border:1px solid #eee8f3;border-radius:12px;background:#fdfcff;overflow-wrap:anywhere}
.info-label,.registered-event-detail strong{display:block;color:#958c9d;font-size:10px;font-weight:bold;text-transform:uppercase;margin-bottom:8px}
.info-value,.registered-event-detail span{font-size:13px;font-weight:bold;color:#302039}
.logout-section{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:20px;border-radius:16px;background:#fff8fa;border:1px solid #f8dfe5}
.logout-section h3{margin:0 0 6px;font-size:15px}
.logout-section p{margin:0}
.logout-button{display:inline-block;padding:11px 18px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#c026d3);color:white;text-decoration:none;font-size:13px;font-weight:bold;white-space:nowrap}
.registered-events{margin-top:30px}
.registered-events-heading{margin-bottom:18px}
.registered-event-card{padding:22px;margin-bottom:18px}
.registered-event-card h3{color:#7026b9;margin:0 0 10px;font-size:21px}
.registered-event-description{font-size:13px;color:#83788d;line-height:1.6}
.event-qr-section{display:flex;align-items:center;gap:20px;flex-wrap:wrap;margin-top:20px;padding-top:18px;border-top:1px solid #eee8f3}
.event-registration-code{color:#6d28d9;font-size:14px;font-weight:bold;overflow-wrap:anywhere}
.event-qr-image{width:140px;height:140px;object-fit:contain;padding:6px;border:1px solid #eee7f4;border-radius:12px}
.event-qr-message,.events-empty-message{color:#83788d;font-size:13px;line-height:1.6}
.events-empty-message{padding:25px;text-align:center}
.events-empty-message h3{color:#302039}
@media(max-width:800px){.profile-layout{grid-template-columns:1fr}}
@media(max-width:550px){.profile-page{padding:30px 14px}.profile-heading h1{font-size:30px}.info-card{padding:18px}.info-grid,.registered-event-details{grid-template-columns:1fr}.logout-section{align-items:flex-start;flex-direction:column}.registered-event-card{padding:17px}}
</style>

<main class="profile-page">
<div class="profile-container">

    <div class="profile-heading">
        <span class="eyebrow">Account Center</span>
        <h1>My Profile</h1>
        <p>View your personal information and registered events.</p>
    </div>

    <div class="profile-layout">
        <section class="profile-card profile-sidebar">
            <div class="profile-cover"></div>
            <div class="profile-avatar">
                <?= htmlspecialchars($initials ?: 'EU', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h2 class="profile-name">
                <?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="profile-email">
                <?= htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </section>

        <div class="profile-content">
            <section class="info-card">
                <div class="card-header">
                    <h2>Personal Information</h2>
                    <p>Your EventEase account details.</p>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Enrollment Number</span>
                        <span class="info-value"><?= htmlspecialchars($enroll_no ?: 'Not Available', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </section>

            <section class="logout-section">
                <div>
                    <h3>Sign out of EventEase</h3>
                    <p>Log out securely from your account.</p>
                </div>
                <a href="logout.php" class="logout-button">Logout</a>
            </section>
        </div>
    </div>

    <section class="registered-events">
        <div class="registered-events-heading">
            <h2>My Registered Events</h2>
            <p>Your event details, registration codes and QR tickets.</p>
        </div>

        <?php if ($event_error !== ''): ?>

            <div class="events-empty-message">
                <h3>Events Unavailable</h3>
                <p><?= htmlspecialchars($event_error, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

        <?php elseif (empty($registered_events)): ?>

            <div class="events-empty-message">
                <h3>No Events Registered Yet</h3>
                <p>Register for an event and your ticket will appear here.</p>
                <a href="events.php" class="logout-button">Explore Events</a>
            </div>

        <?php else: ?>

            <?php foreach ($registered_events as $event): ?>
                <article class="registered-event-card">
                    <h3><?= htmlspecialchars($event['title'] ?? 'Event', ENT_QUOTES, 'UTF-8') ?></h3>

                    <p class="registered-event-description">
                        <?= nl2br(htmlspecialchars($event['description'] ?? 'No description available.', ENT_QUOTES, 'UTF-8')) ?>
                    </p>

                    <div class="registered-event-details">
                        <div class="registered-event-detail">
                            <strong>Event Date</strong>
                            <span><?= !empty($event['event_date']) ? htmlspecialchars(date('d M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8') : 'N/A' ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Time</strong>
                            <span><?= htmlspecialchars($event['start_time'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($event['end_time'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Venue</strong>
                            <span><?= htmlspecialchars($event['venue'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Organizer</strong>
                            <span><?= htmlspecialchars($event['organizer'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Registered On</strong>
                            <span><?= !empty($event['registered_at']) ? htmlspecialchars(date('d M Y', strtotime($event['registered_at'])), ENT_QUOTES, 'UTF-8') : 'N/A' ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Payment Status</strong>
                            <span><?= htmlspecialchars(ucwords(str_replace('_', ' ', $event['payment_status'] ?? 'unknown')), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="registered-event-detail">
                            <strong>Price</strong>
                            <span>₹<?= number_format((float)($event['price'] ?? 0), 2) ?></span>
                        </div>
                    </div>

                    <div class="event-qr-section">
                        <div>
                            <strong>Registration Code</strong>
                            <p class="event-registration-code">
                                <?= htmlspecialchars($event['registration_code'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <?php
                        $qr = trim((string)($event['qr_code'] ?? ''));
                        $qr_path = '';

                        if (
                            $qr !== '' &&
                            !preg_match('/[\x00-\x1F\x7F]/', $qr) &&
                            !preg_match('~^(?:https?:)?//|^(?:data:|javascript:)~i', $qr) &&
                            !str_contains($qr, '..') &&
                            preg_match('~^[a-zA-Z0-9_./-]+$~', $qr)
                        ) {
                            $candidate = realpath(__DIR__ . '/' . $qr);
                            $qr_dir = realpath(__DIR__ . '/qrcodes');

                            if (
                                $candidate !== false &&
                                $qr_dir !== false &&
                                str_starts_with($candidate, $qr_dir . DIRECTORY_SEPARATOR) &&
                                is_file($candidate) &&
                                in_array(strtolower(pathinfo($candidate, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'webp'], true)
                            ) {
                                $qr_path = $qr;
                            }
                        }
                        ?>

                        <?php if ($qr_path !== ''): ?>
                            <img
                                class="event-qr-image"
                                src="<?= htmlspecialchars($qr_path, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Event QR code"
                            >
                        <?php else: ?>
                            <p class="event-qr-message">
                                QR image not found. Use your registration code for check-in.
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

</div>
</main>

<?php include './footer.php'; ?>
