<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "eventease");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$error = "";
$name = "";
$enroll_no = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $enroll_no = trim($_POST["enroll_no"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $enroll_no === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, enroll_no, email, password, status
             FROM users
             WHERE email = ? OR enroll_no = ?
             LIMIT 1"
        );

        $stmt->bind_param("ss", $email, $enroll_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (strcasecmp($user["email"], $email) !== 0) {
                $error = "This enrollment number is already registered with another email.";
            } elseif (strcasecmp($user["enroll_no"], $enroll_no) !== 0) {
                $error = "This email is already registered with another enrollment number.";
            } else {
                $passwordMatches = password_verify($password, $user["password"]);

                if (
                    !$passwordMatches &&
                    password_get_info($user["password"])["algo"] === null
                ) {
                    $passwordMatches = hash_equals($user["password"], $password);
                }

                if (!$passwordMatches) {
                    $error = "Incorrect password. Please try again.";
                } elseif (strtolower($user["status"] ?? "active") !== "active") {
                    $error = "Your account is inactive. Please contact the administrator.";
                } else {
                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["enroll_no"] = $user["enroll_no"];
                    $_SESSION["user_logged_in"] = true;

                    header("Location: index.php");
                    exit;
                }
            }
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $status = "active";

            $insert = $conn->prepare(
                "INSERT INTO users (name, enroll_no, email, password, status)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "sssss",
                $name,
                $enroll_no,
                $email,
                $hashedPassword,
                $status
            );

            if ($insert->execute()) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $insert->insert_id;
                $_SESSION["user_name"] = $name;
                $_SESSION["user_email"] = $email;
                $_SESSION["enroll_no"] = $enroll_no;
                $_SESSION["user_logged_in"] = true;

                header("Location: index.php");
                exit;
            } else {
                if ($insert->errno === 1062) {
                    $error = "Email or enrollment number is already registered. Please log in using your existing details.";
                } else {
                    $error = "Unable to create your account. Please try again.";
                }
            }

            $insert->close();
        }

        $stmt->close();
    }
}

$page_title = "Login | EventEase";
include "header.php";
?>

<style>
.login-page {
    min-height: 78vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 45px 20px;
    background: linear-gradient(135deg, #faf5ff, #fdf2f8);
}

.login-card {
    width: 100%;
    max-width: 460px;
    padding: 38px;
    border-radius: 24px;
    background: #ffffff;
    box-shadow: 0 18px 55px rgba(88, 28, 135, 0.13);
    border: 1px solid #f0e5ff;
}

.login-card h1 {
    margin: 0 0 10px;
    color: #32134d;
    text-align: center;
    font-size: 30px;
}

.login-subtitle {
    margin-bottom: 28px;
    color: #796b86;
    text-align: center;
    line-height: 1.6;
}

.login-form label {
    display: block;
    margin: 17px 0 8px;
    color: #39234d;
    font-weight: 600;
}

.login-form input {
    width: 100%;
    padding: 13px 15px;
    border: 1px solid #e4d7ef;
    border-radius: 12px;
    outline: none;
    font-size: 15px;
    background: #fdfbff;
    box-sizing: border-box;
}

.login-form input:focus {
    border-color: #9333ea;
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
}

.login-button {
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(124, 58, 237, 0.25);
}

.login-error {
    padding: 12px 14px;
    margin-bottom: 15px;
    border-radius: 10px;
    color: #b42318;git status
    background: #fff0f0;
    font-size: 14px;
}

.login-note {
    margin-top: 22px;
    color: #796b86;
    text-align: center;
    font-size: 14px;
}

.login-note a {
    color: #7c3aed;
    font-weight: 700;
    text-decoration: none;
}
</style>

<main class="login-page">
    <section class="login-card">
        <h1>Welcome to EventEase</h1>

        <p class="login-subtitle">
            Sign in to your account or enter your details to create one automatically.
        </p>

        <?php if ($error !== ""): ?>
            <div class="login-error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter your full name"
                value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>"
                required
            >

            <label for="enroll_no">Enrollment Number</label>
            <input
                type="text"
                id="enroll_no"
                name="enroll_no"
                placeholder="Enter your enrollment number"
                value="<?= htmlspecialchars($enroll_no, ENT_QUOTES, "UTF-8") ?>"
                required
            >

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit" class="login-button">
                Continue to EventEase
            </button>
        </form>

        <p class="login-note">
            New user? Your account will be created automatically.
        </p>
    </section>
</main>

<?php include "footer.php"; ?>