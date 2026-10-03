<?php
$page_title = "EventEase | Login";
?>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #f8f1ff, #fff5fc);
    color: #21152f;
}

.login-page {
    min-height: calc(100vh - 75px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
}

.login-box {
    width: 100%;
    max-width: 950px;
    min-height: 560px;
    background: white;
    border-radius: 28px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    box-shadow: 0 25px 70px rgba(87,42,130,0.16);
}

.login-left {
    background: linear-gradient(145deg,#30104d,#742ac5,#c026d3);
    padding: 55px;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-left h1 {
    font-size: 42px;
    margin-bottom: 18px;
}

.login-left p {
    color: #f1dfff;
    line-height: 1.7;
}

.login-art {
    font-size: 90px;
    margin-top: 35px;
}

.login-right {
    padding: 55px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-right h2 {
    font-size: 30px;
    margin-bottom: 8px;
}

.subtitle {
    color: #81758c;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #e4dcec;
    border-radius: 12px;
    outline: none;
    font-size: 14px;
}

.form-group input:focus {
    border-color: #8b4de8;
    box-shadow: 0 0 0 4px #8b4de815;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    font-size: 13px;
}

.remember {
    display: flex;
    gap: 7px;
    align-items: center;
}

.forgot {
    color: #7c3aed;
    font-weight: 700;
}

.login-submit {
    width: 100%;
    border: none;
    padding: 15px;
    border-radius: 12px;
    background: linear-gradient(135deg,#7c3aed,#c026d3);
    color: white;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
}

.register-text {
    text-align: center;
    margin-top: 25px;
    color: #777084;
    font-size: 14px;
}

.register-text a {
    color: #7c3aed;
    font-weight: 800;
}

@media(max-width: 750px) {
    .login-box {
        grid-template-columns: 1fr;
    }

    .login-left {
        padding: 40px;
    }

    .login-art {
        display: none;
    }

    .login-right {
        padding: 40px;
    }
}
</style>

<div class="login-page">
    <div class="login-box">

        <div class="login-left">
            <h1>Welcome Back!</h1>
            <p>
                Login to EventEase and continue discovering
                amazing events, managing your bookings and
                creating unforgettable memories.
            </p>
            <div class="login-art">🎟️✨</div>
        </div>

        <div class="login-right">
            <h2>Login to EventEase</h2>
            <p class="subtitle">Enter your details to continue</p>

            <form action="index.php" method="POST">

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>

                    <a href="#" class="forgot">Forgot Password?</a>
                </div>

                <button type="submit" class="login-submit">
                    Login
                </button>
            </form>

            <div class="register-text">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </div>
        </div>

    </div>
</div>
