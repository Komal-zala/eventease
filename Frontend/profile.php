<?php
session_start();
include './header.php';
$page_title = "EventEase | My Profile";

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'EventEase User';
$user_email = $_SESSION['user_email'] ?? 'user@eventease.com';

$initials = '';

$name_parts = preg_split('/\s+/', trim($user_name));

foreach ($name_parts as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}

$initials = substr($initials, 0, 2);
?>

<style>
.profile-page,
.profile-page * {
    box-sizing: border-box;
}

.profile-page {
    min-height: calc(100vh - 75px);
    padding: 55px 25px 75px;
    background:
        radial-gradient(circle at 5% 10%, rgba(124, 58, 237, 0.07), transparent 28%),
        radial-gradient(circle at 95% 85%, rgba(192, 38, 211, 0.07), transparent 28%),
        #faf8fd;
    color: #24172f;
    font-family: Arial, sans-serif;
}

.profile-page .profile-container {
    max-width: 1120px;
    margin: 0 auto;
}

.profile-page .profile-heading {
    margin-bottom: 28px;
}

.profile-page .profile-heading .eyebrow {
    display: inline-block;
    color: #7c3aed;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.profile-page .profile-heading h1 {
    margin: 0;
    font-size: 38px;
    letter-spacing: -1px;
}

.profile-page .profile-heading p {
    margin: 10px 0 0;
    color: #83788d;
    font-size: 14px;
}

.profile-page .profile-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 25px;
}

.profile-page .profile-card {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #eee7f4;
    border-radius: 26px;
    box-shadow: 0 20px 60px rgba(59, 25, 88, 0.08);
    overflow: hidden;
}

.profile-page .profile-sidebar {
    padding: 34px 28px;
    position: relative;
}

.profile-page .profile-cover {
    height: 105px;
    margin: -34px -28px 0;
    background:
        radial-gradient(circle at 15% 30%, rgba(255,255,255,0.22), transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255,255,255,0.14), transparent 25%),
        linear-gradient(135deg, #2d0b4d, #7026b9, #c026d3);
}

.profile-page .profile-avatar {
    width: 104px;
    height: 104px;
    margin: -52px auto 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    border: 6px solid #fff;
    box-shadow: 0 12px 30px rgba(124, 58, 237, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 30px;
    font-weight: 900;
    position: relative;
    z-index: 2;
}

.profile-page .profile-name {
    text-align: center;
    margin: 0;
    font-size: 21px;
}

.profile-page .profile-email {
    text-align: center;
    color: #8b8292;
    font-size: 13px;
    margin: 7px 0 25px;
    word-break: break-word;
}

.profile-page .profile-status {
    width: fit-content;
    margin: 0 auto 28px;
    padding: 7px 13px;
    border-radius: 100px;
    background: #f1eaff;
    color: #7132c7;
    font-size: 11px;
    font-weight: 800;
}

.profile-page .profile-menu {
    border-top: 1px solid #eee8f3;
    padding-top: 20px;
}

.profile-page .profile-menu a {
    display: flex;
    align-items: center;
    gap: 13px;
    text-decoration: none;
    color: #655b6e;
    padding: 13px 14px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 5px;
    transition: 0.2s ease;
}

.profile-page .profile-menu a:hover,
.profile-page .profile-menu a.active {
    background: #f6efff;
    color: #7132c7;
}

.profile-page .menu-icon {
    width: 28px;
    height: 28px;
    border-radius: 9px;
    background: #f3edf9;
    position: relative;
    flex-shrink: 0;
}

.profile-page .menu-icon::before {
    content: "";
    position: absolute;
    width: 9px;
    height: 9px;
    border: 2px solid currentColor;
    border-radius: 50%;
    left: 7px;
    top: 4px;
}

.profile-page .menu-icon::after {
    content: "";
    position: absolute;
    width: 13px;
    height: 7px;
    border: 2px solid currentColor;
    border-bottom: 0;
    border-radius: 10px 10px 0 0;
    left: 5px;
    bottom: 4px;
}

.profile-page .profile-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.profile-page .info-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid #eee7f4;
    border-radius: 26px;
    padding: 32px;
    box-shadow: 0 20px 60px rgba(59, 25, 88, 0.07);
}

.profile-page .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 26px;
}

.profile-page .card-header h2 {
    margin: 0;
    font-size: 21px;
}

.profile-page .card-header p {
    margin: 6px 0 0;
    color: #8a8191;
    font-size: 13px;
}

.profile-page .edit-button {
    text-decoration: none;
    padding: 10px 17px;
    border-radius: 11px;
    background: #f4edff;
    color: #7132c7;
    font-size: 12px;
    font-weight: 800;
    transition: 0.2s ease;
    white-space: nowrap;
}

.profile-page .edit-button:hover {
    background: #e9dcff;
}

.profile-page .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.profile-page .info-item {
    padding: 18px;
    border: 1px solid #eee8f3;
    border-radius: 15px;
    background: #fdfcff;
}

.profile-page .info-label {
    display: block;
    color: #958c9d;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.profile-page .info-value {
    display: block;
    color: #302039;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
}

.profile-page .quick-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.profile-page .quick-card {
    padding: 21px;
    border-radius: 17px;
    background: linear-gradient(145deg, #faf7ff, #fff);
    border: 1px solid #eee7f4;
}

.profile-page .quick-icon {
    width: 42px;
    height: 42px;
    border-radius: 13px;
    background: #f0e7ff;
    margin-bottom: 14px;
    position: relative;
}

.profile-page .quick-icon.ticket::before {
    content: "";
    position: absolute;
    width: 21px;
    height: 15px;
    border: 2px solid #7c3aed;
    border-radius: 4px;
    left: 10px;
    top: 12px;
}

.profile-page .quick-icon.ticket::after {
    content: "";
    position: absolute;
    width: 2px;
    height: 11px;
    background: #7c3aed;
    left: 20px;
    top: 14px;
}

.profile-page .quick-icon.calendar::before {
    content: "";
    position: absolute;
    width: 22px;
    height: 19px;
    border: 2px solid #7c3aed;
    border-radius: 4px;
    left: 9px;
    top: 12px;
}

.profile-page .quick-icon.calendar::after {
    content: "";
    position: absolute;
    width: 12px;
    height: 2px;
    background: #7c3aed;
    left: 14px;
    top: 18px;
}

.profile-page .quick-icon.security::before {
    content: "";
    position: absolute;
    width: 19px;
    height: 23px;
    border: 2px solid #7c3aed;
    border-radius: 11px 11px 14px 14px;
    left: 10px;
    top: 8px;
}

.profile-page .quick-icon.security::after {
    content: "";
    position: absolute;
    width: 7px;
    height: 4px;
    border-left: 2px solid #7c3aed;
    border-bottom: 2px solid #7c3aed;
    transform: rotate(-45deg);
    left: 16px;
    top: 18px;
}

.profile-page .quick-card h3 {
    margin: 0 0 5px;
    font-size: 14px;
}

.profile-page .quick-card p {
    margin: 0;
    color: #8a8191;
    font-size: 12px;
    line-height: 1.5;
}

.profile-page .logout-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 23px;
    border-radius: 18px;
    background: #fff8fa;
    border: 1px solid #f8dfe5;
}

.profile-page .logout-section h3 {
    margin: 0 0 5px;
    font-size: 14px;
}

.profile-page .logout-section p {
    margin: 0;
    color: #927f85;
    font-size: 12px;
}

.profile-page .logout-button {
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 11px;
    background: #fff;
    border: 1px solid #f1cbd4;
    color: #b4233c;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
    transition: 0.2s ease;
}

.profile-page .logout-button:hover {
    background: #fff0f3;
}

@media (max-width: 900px) {
    .profile-page .profile-layout {
        grid-template-columns: 1fr;
    }

    .profile-page .profile-sidebar {
        max-width: 100%;
    }
}

@media (max-width: 650px) {
    .profile-page {
        padding: 35px 15px 55px;
    }

    .profile-page .profile-heading h1 {
        font-size: 30px;
    }

    .profile-page .info-card {
        padding: 23px;
    }

    .profile-page .info-grid,
    .profile-page .quick-grid {
        grid-template-columns: 1fr;
    }

    .profile-page .card-header {
        align-items: flex-start;
    }

    .profile-page .logout-section {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="profile-page">

    <div class="profile-container">

        <div class="profile-heading">
            <span class="eyebrow">Account Center</span>

            <h1>My Profile</h1>

            <p>
                Manage your EventEase account and personal information.
            </p>
        </div>

        <div class="profile-layout">

            <div class="profile-card profile-sidebar">

                <div class="profile-cover"></div>

                <div class="profile-avatar">
                    <?= htmlspecialchars($initials) ?>
                </div>

                <h2 class="profile-name">
                    <?= htmlspecialchars($user_name) ?>
                </h2>

                <p class="profile-email">
                    <?= htmlspecialchars($user_email) ?>
                </p>

                <div class="profile-status">
                    ACCOUNT ACTIVE
                </div>

                <div class="profile-menu">

                    <a href="profile.php" class="active">
                        <span class="menu-icon"></span>
                        Profile
                    </a>

                    <a href="edit-profile.php">
                        <span class="menu-icon"></span>
                        Edit Profile
                    </a>

                    <a href="my-bookings.php">
                        <span class="menu-icon"></span>
                        My Bookings
                    </a>

                    <a href="change-password.php">
                        <span class="menu-icon"></span>
                        Change Password
                    </a>

                    <a href="notifications.php">
                        <span class="menu-icon"></span>
                        Notifications
                    </a>

                </div>

            </div>

            <div class="profile-content">

                <div class="info-card">

                    <div class="card-header">

                        <div>
                            <h2>Personal Information</h2>

                            <p>
                                Your basic EventEase account information.
                            </p>
                        </div>

                        <a href="edit-profile.php" class="edit-button">
                            Edit Profile
                        </a>

                    </div>

                    <div class="info-grid">

                        <div class="info-item">

                            <span class="info-label">
                                Full Name
                            </span>

                            <span class="info-value">
                                <?= htmlspecialchars($user_name) ?>
                            </span>

                        </div>

                        <div class="info-item">

                            <span class="info-label">
                                Email Address
                            </span>

                            <span class="info-value">
                                <?= htmlspecialchars($user_email) ?>
                            </span>

                        </div>

                        <div class="info-item">

                            <span class="info-label">
                                Account Type
                            </span>

                            <span class="info-value">
                                Client Account
                            </span>

                        </div>

                        <div class="info-item">

                            <span class="info-label">
                                Account Status
                            </span>

                            <span class="info-value">
                                Active
                            </span>

                        </div>

                    </div>

                </div>

                <div class="info-card">

                    <div class="card-header">

                        <div>
                            <h2>EventEase Features</h2>

                            <p>
                                Everything you can manage from your account.
                            </p>
                        </div>

                    </div>

                    <div class="quick-grid">

                        <div class="quick-card">

                            <div class="quick-icon ticket"></div>

                            <h3>
                                Digital Tickets
                            </h3>

                            <p>
                                Access your event tickets and booking details
                                from one place.
                            </p>

                        </div>

                        <div class="quick-card">

                            <div class="quick-icon calendar"></div>

                            <h3>
                                Event Bookings
                            </h3>

                            <p>
                                View and manage all your upcoming event
                                registrations.
                            </p>

                        </div>

                        <div class="quick-card">

                            <div class="quick-icon security"></div>

                            <h3>
                                Account Security
                            </h3>

                            <p>
                                Manage your password and keep your account
                                protected.
                            </p>

                        </div>

                    </div>

                </div>

                <div class="logout-section">

                    <div>

                        <h3>
                            Sign out of EventEase
                        </h3>

                        <p>
                            You can log back in anytime using your account.
                        </p>

                    </div>

                    <a href="logout.php" class="logout-button">
                        Logout
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>
<?php include './footer.php';?>