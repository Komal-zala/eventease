<?php
session_start();

$page_title = "EventEase | Create Account";

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$register_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {

        $register_error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $register_error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $register_error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $register_error = "Passwords do not match.";

    } else {

        /*
         * Temporary demo account storage.
         * Database will replace this later.
         */

        if (!isset($_SESSION['registered_users'])) {
            $_SESSION['registered_users'] = [];
        }

        $email_exists = false;

        foreach ($_SESSION['registered_users'] as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                $email_exists = true;
                break;
            }
        }

        if ($email_exists) {

            $register_error = "An account with this email already exists. Please login.";

        } else {

            $_SESSION['registered_users'][] = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            session_regenerate_id(true);

            header("Location: index.php");
            exit;
        }
    }
}
?>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background:
        radial-gradient(circle at 10% 20%, rgba(192, 38, 211, 0.08), transparent 30%),
        radial-gradient(circle at 90% 80%, rgba(124, 58, 237, 0.10), transparent 30%),
        linear-gradient(135deg, #faf7ff, #fff7fd);
    color: #21152f;
}

.register-page {
    min-height: calc(100vh - 75px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 55px 20px;
}

.register-box {
    width: 100%;
    max-width: 1050px;
    min-height: 620px;
    background: rgba(255, 255, 255, 0.96);
    border-radius: 32px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    box-shadow:
        0 35px 100px rgba(65, 28, 105, 0.18),
        0 10px 30px rgba(65, 28, 105, 0.08);
    border: 1px solid rgba(124, 58, 237, 0.08);
}

.register-left {
    position: relative;
    overflow: hidden;
    padding: 65px;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background:
        radial-gradient(circle at 20% 15%, rgba(255,255,255,0.20), transparent 24%),
        radial-gradient(circle at 85% 85%, rgba(255,255,255,0.12), transparent 30%),
        linear-gradient(145deg, #260b40, #5917a5 55%, #b01bc1);
}

.register-left::before {
    content: "";
    position: absolute;
    width: 260px;
    height: 260px;
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 50%;
    top: -90px;
    right: -80px;
}

.register-left::after {
    content: "";
    position: absolute;
    width: 170px;
    height: 170px;
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 50%;
    bottom: -65px;
    left: -50px;
}

.register-content {
    position: relative;
    z-index: 2;
}

.register-badge {
    width: fit-content;
    padding: 8px 14px;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.22);
    background: rgba(255,255,255,0.08);
    color: #f7eaff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    margin-bottom: 28px;
    backdrop-filter: blur(10px);
}

.register-left h1 {
    font-size: 46px;
    line-height: 1.05;
    margin: 0 0 20px;
    letter-spacing: -1.5px;
}

.register-left h1 span {
    color: #f1b7ff;
}

.register-left p {
    color: #eadcff;
    line-height: 1.8;
    font-size: 15px;
    max-width: 410px;
    margin: 0;
}

.register-art {
    position: relative;
    width: 190px;
    height: 145px;
    margin-top: 42px;
}

.register-card {
    position: absolute;
    width: 125px;
    height: 78px;
    left: 20px;
    top: 25px;
    border-radius: 17px;
    border: 1px solid rgba(255,255,255,0.32);
    background: linear-gradient(
        135deg,
        rgba(255,255,255,0.22),
        rgba(255,255,255,0.05)
    );
    transform: rotate(-12deg);
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
    backdrop-filter: blur(10px);
}

.register-card::before {
    content: "";
    position: absolute;
    left: 18px;
    top: 17px;
    width: 50px;
    height: 7px;
    border-radius: 10px;
    background: rgba(255,255,255,0.8);
}

.register-card::after {
    content: "";
    position: absolute;
    left: 18px;
    bottom: 15px;
    width: 75px;
    height: 5px;
    border-radius: 10px;
    background: rgba(255,255,255,0.28);
}

.register-orbit {
    position: absolute;
    width: 120px;
    height: 120px;
    right: 0;
    top: 0;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 50%;
}

.register-orbit::before {
    content: "";
    position: absolute;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #ffffff;
    top: 8px;
    left: 78px;
    box-shadow: 0 0 22px rgba(255,255,255,0.8);
}

.register-spark {
    position: absolute;
    width: 20px;
    height: 20px;
    right: 25px;
    bottom: 15px;
}

.register-spark::before,
.register-spark::after {
    content: "";
    position: absolute;
    background: #ffffff;
    border-radius: 10px;
}

.register-spark::before {
    width: 3px;
    height: 24px;
    left: 8px;
    top: -2px;
}

.register-spark::after {
    width: 24px;
    height: 3px;
    left: -2px;
    top: 8px;
}

.register-right {
    padding: 55px 65px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.register-right h2 {
    font-size: 32px;
    line-height: 1.1;
    margin: 0 0 9px;
    letter-spacing: -0.8px;
}

.subtitle {
    color: #81758c;
    margin: 0 0 30px;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 800;
    margin-bottom: 8px;
    color: #382844;
}

.form-group input {
    width: 100%;
    padding: 15px 16px;
    border: 1px solid #e5dceb;
    border-radius: 13px;
    outline: none;
    font-size: 14px;
    color: #2b1938;
    background: #fff;
    transition: 0.25s ease;
}

.form-group input::placeholder {
    color: #aaa1b2;
}

.form-group input:hover {
    border-color: #d3c2df;
}

.form-group input:focus {
    border-color: #8b4de8;
    box-shadow: 0 0 0 4px rgba(139, 77, 232, 0.09);
}

.register-error {
    margin-bottom: 20px;
    padding: 13px 15px;
    border-radius: 12px;
    background: #fff1f3;
    border: 1px solid #ffd2d8;
    color: #b42338;
    font-size: 13px;
    font-weight: 700;
}

.register-submit {
    width: 100%;
    border: none;
    padding: 16px;
    border-radius: 13px;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 12px 28px rgba(124, 58, 237, 0.22);
    transition: 0.25s ease;
}

.register-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 17px 34px rgba(124, 58, 237, 0.30);
}

.register-submit:active {
    transform: translateY(0);
}

.login-text {
    text-align: center;
    margin-top: 24px;
    color: #777084;
    font-size: 14px;
}

.login-text a {
    color: #7c3aed;
    font-weight: 800;
    text-decoration: none;
}

.login-text a:hover {
    color: #c026d3;
}

.terms {
    margin-top: 18px;
    text-align: center;
    color: #a19aa8;
    font-size: 11px;
    line-height: 1.6;
}

@media (max-width: 850px) {

    .register-box {
        grid-template-columns: 1fr;
        max-width: 600px;
    }

    .register-left {
        padding: 45px;
        min-height: 350px;
    }

    .register-left h1 {
        font-size: 38px;
    }

    .register-art {
        margin-top: 25px;
    }

    .register-right {
        padding: 45px;
    }
}

@media (max-width: 500px) {

    .register-page {
        padding: 25px 15px;
    }

    .register-left,
    .register-right {
        padding: 32px 25px;
    }

    .register-left h1 {
        font-size: 32px;
    }

    .register-right h2 {
        font-size: 27px;
    }

    .register-art {
        display: none;
    }
}
</style>

<div class="register-page">

    <div class="register-box">

        <div class="register-left">

            <div class="register-content">

                <div class="register-badge">
                    Create Your Experience
                </div>

                <h1>
                    Join <span>EventEase</span>
                </h1>

                <p>
                    Create your account and unlock a seamless
                    way to discover events, manage bookings
                    and experience memorable moments.
                </p>

                <div class="register-art">

                    <div class="register-card"></div>

                    <div class="register-orbit"></div>

                    <div class="register-spark"></div>

                </div>

            </div>

        </div>

        <div class="register-right">

            <h2>Create Account</h2>

            <p class="subtitle">
                Enter your details to get started
            </p>

            <?php if ($register_error !== ""): ?>

                <div class="register-error">
                    <?= htmlspecialchars($register_error) ?>
                </div>

            <?php endif; ?>

            <form action="register.php" method="POST">

                <div class="form-group">
                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="register-submit"
                >
                    Create Account
                </button>

            </form>

            <div class="login-text">
                Already have an account?
                <a href="login.php">Login</a>
            </div>

            <div class="terms">
                By creating an account, you agree to the
                EventEase terms and conditions.
            </div>

        </div>

    </div>

</div>