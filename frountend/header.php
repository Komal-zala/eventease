<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#160d24">
    <title><?= isset($page_title) ? $page_title : 'EventEase' ?></title>

    <style>
        :root {
            --primary: #7c3aed;
            --primary-light: #a78bfa;
            --secondary: #c026d3;
            --accent: #f0abfc;
            --dark: #18111f;
            --text: #4b4455;
            --muted: #7d7487;
            --white: #ffffff;
            --border: rgba(124, 58, 237, .10);
            --gradient: linear-gradient(135deg, #7c3aed 0%, #c026d3 100%);
            --gradient-soft: linear-gradient(135deg, rgba(124,58,237,.10), rgba(192,38,211,.08));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: var(--dark);
            background: #fff;
        }

        .header {
            position: sticky;
            top: 0;
            z-index: 9999;
            width: 100%;
            padding: 12px 28px;
            background: rgba(255,255,255,.76);
            border-bottom: 1px solid rgba(124,58,237,.08);
            backdrop-filter: blur(26px) saturate(160%);
            -webkit-backdrop-filter: blur(26px) saturate(160%);
        }

        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--gradient);
        }

        .header::after {
            content: "";
            position: absolute;
            top: -100px;
            left: 15%;
            width: 250px;
            height: 180px;
            background: rgba(124,58,237,.08);
            filter: blur(70px);
            border-radius: 50%;
            pointer-events: none;
        }

        .navbar {
            position: relative;
            z-index: 2;
            width: min(1320px, 100%);
            min-height: 64px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .logo {
            position: relative;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--dark);
            font-size: 29px;
            font-weight: 900;
            letter-spacing: -1.8px;
            white-space: nowrap;
        }

        .logo::before {
            content: "";
            width: 11px;
            height: 11px;
            margin-right: 11px;
            border-radius: 50%;
            background: var(--gradient);
            box-shadow:
                0 0 0 6px rgba(124,58,237,.08),
                0 0 25px rgba(124,58,237,.35);
        }

        .logo span {
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 5px;
            border: 1px solid rgba(124,58,237,.08);
            border-radius: 17px;
            background: rgba(255,255,255,.58);
            box-shadow:
                0 10px 35px rgba(48,25,75,.05),
                inset 0 1px 0 rgba(255,255,255,.9);
        }

        .nav-links a {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 17px;
            color: #6d6577;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border-radius: 12px;
            transition: .3s ease;
            isolation: isolate;
        }

        .nav-links a::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--gradient-soft);
            opacity: 0;
            transform: scale(.85);
            transition: .3s ease;
            z-index: -1;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 0;
            height: 2px;
            border-radius: 10px;
            background: var(--gradient);
            transform: translateX(-50%);
            transition: .3s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
            transform: translateY(-1px);
        }

        .nav-links a:hover::before {
            opacity: 1;
            transform: scale(1);
        }

        .nav-links a:hover::after {
            width: 20px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .login-link,
        .profile-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 43px;
            padding: 0 16px;
            color: var(--dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 750;
            border-radius: 12px;
            transition: .3s ease;
        }

        .login-link:hover,
        .profile-link:hover {
            color: var(--primary);
            background: rgba(124,58,237,.06);
            transform: translateY(-2px);
        }

        .profile-link {
            position: relative;
            gap: 9px;
        }

        .profile-link::before {
            content: "";
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: var(--gradient);
            box-shadow: 0 4px 12px rgba(124,58,237,.20);
        }

        .profile-link::after {
            content: "";
            position: absolute;
            left: 25px;
            top: 10px;
            width: 7px;
            height: 7px;
            border: 1.5px solid #fff;
            border-radius: 50%;
            box-shadow: 0 5px 0 1px #fff;
        }

        .register-link,
        .logout-link {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 45px;
            padding: 0 21px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            border-radius: 13px;
            background: var(--gradient);
            box-shadow:
                0 10px 25px rgba(124,58,237,.25),
                inset 0 1px 0 rgba(255,255,255,.3);
            transition: .3s ease;
        }

        .logout-link {
            background: linear-gradient(135deg, #4b1d68, #7c2d91);
        }

        .register-link::before,
        .logout-link::before {
            content: "";
            position: absolute;
            top: 0;
            left: -130%;
            width: 70%;
            height: 100%;
            background: linear-gradient(
                100deg,
                transparent,
                rgba(255,255,255,.45),
                transparent
            );
            transform: skewX(-20deg);
            transition: left .7s ease;
        }

        .register-link:hover::before,
        .logout-link:hover::before {
            left: 140%;
        }

        .register-link:hover,
        .logout-link:hover {
            transform: translateY(-3px);
            box-shadow:
                0 16px 35px rgba(124,58,237,.34),
                inset 0 1px 0 rgba(255,255,255,.35);
        }

        .register-link:active,
        .logout-link:active {
            transform: translateY(-1px);
        }

        .logo:focus-visible,
        .nav-links a:focus-visible,
        .login-link:focus-visible,
        .profile-link:focus-visible,
        .register-link:focus-visible,
        .logout-link:focus-visible {
            outline: 3px solid rgba(124,58,237,.25);
            outline-offset: 4px;
        }

        @media (max-width: 950px) {

            .header {
                padding: 11px 20px;
            }

            .navbar {
                gap: 16px;
            }

            .nav-links a {
                padding: 9px 12px;
            }

            .profile-link,
            .login-link {
                padding: 0 12px;
            }

            .register-link,
            .logout-link {
                padding: 0 15px;
            }
        }

        @media (max-width: 760px) {

            .header {
                padding: 11px 15px;
            }

            .navbar {
                flex-wrap: wrap;
                justify-content: center;
                gap: 9px;
            }

            .logo {
                width: 100%;
                justify-content: center;
                font-size: 26px;
            }

            .nav-links {
                order: 3;
                width: 100%;
                overflow-x: auto;
                justify-content: flex-start;
                scrollbar-width: none;
            }

            .nav-links::-webkit-scrollbar {
                display: none;
            }

            .nav-links a {
                flex: 0 0 auto;
                padding: 9px 14px;
                font-size: 12px;
            }

            .nav-actions {
                position: absolute;
                right: 0;
                top: 0;
            }

            .login-link {
                display: none;
            }

            .profile-link {
                min-height: 36px;
                padding: 0 10px;
                font-size: 11px;
                border-radius: 10px;
                gap: 5px;
            }

            .profile-link::before {
                width: 21px;
                height: 21px;
            }

            .profile-link::after {
                left: 19px;
                top: 8px;
                width: 6px;
                height: 6px;
            }

            .register-link,
            .logout-link {
                min-height: 36px;
                padding: 0 13px;
                font-size: 11px;
                border-radius: 10px;
            }
        }

        @media (max-width: 430px) {

            .header {
                padding: 9px 11px;
            }

            .logo {
                font-size: 23px;
            }

            .logo::before {
                width: 8px;
                height: 8px;
                margin-right: 8px;
            }

            .nav-actions {
                right: 0;
            }

            .profile-link {
                min-height: 33px;
                padding: 0 8px;
                font-size: 10px;
            }

            .profile-link::before {
                width: 18px;
                height: 18px;
            }

            .profile-link::after {
                left: 16px;
                top: 7px;
                width: 5px;
                height: 5px;
            }

            .register-link,
            .logout-link {
                min-height: 33px;
                padding: 0 10px;
                font-size: 10px;
            }

            .nav-links {
                padding: 4px;
            }

            .nav-links a {
                padding: 8px 10px;
                font-size: 11px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

<header class="header">

    <nav class="navbar">

        <a href="index.php" class="logo">
            Event<span>Ease</span>
        </a>

       <div class="nav-links">

    <a href="index.php">
        Home
    </a>

    <a href="upcoming-events.php">
        Upcoming Events
    </a>

    <a href="past-events.php">
        Past Events
    </a>

    <a href="about.php">
        About Us
    </a>

</div>

        <div class="nav-actions">

            <?php if ($is_logged_in): ?>

                <a href="profile.php" class="profile-link">
                    Profile
                </a>

                <a href="logout.php" class="logout-link">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php" class="login-link">
                    Login
                </a>

                <a href="register.php" class="register-link">
                    Account
                    
                </a>

            <?php endif; ?>

        </div>

    </nav>

</header>