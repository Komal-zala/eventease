<footer class="footer" id="contact">

    <style>
        .footer {
            position: relative;
            overflow: hidden;
            margin-top: 90px;
            padding: 85px 28px 0;
            color: #fff;
            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(124,58,237,.25),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 20%,
                    rgba(192,38,211,.20),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #120a1d 0%,
                    #1d102b 45%,
                    #13091e 100%
                );
        }

        .footer::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(
                90deg,
                transparent,
                #7c3aed,
                #c026d3,
                transparent
            );
        }

        .footer::after {
            content: "";
            position: absolute;
            right: -180px;
            bottom: -300px;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: rgba(124,58,237,.13);
            filter: blur(90px);
            pointer-events: none;
        }

        .footer-container {
            position: relative;
            z-index: 2;
            width: min(1280px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            padding-bottom: 70px;
        }

        .footer-brand {
            max-width: 400px;
        }

        .footer-logo {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 30px;
            font-weight: 900;
            letter-spacing: -1.6px;
        }

        .footer-logo::before {
            content: "";
            width: 11px;
            height: 11px;
            margin-right: 11px;
            border-radius: 50%;
            background: linear-gradient(135deg,#7c3aed,#c026d3);
            box-shadow:
                0 0 0 6px rgba(124,58,237,.10),
                0 0 30px rgba(124,58,237,.55);
        }

        .footer-logo span {
            background: linear-gradient(
                135deg,
                #a78bfa,
                #f0abfc
            );
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .footer-brand p {
            margin: 0;
            max-width: 360px;
            color: rgba(255,255,255,.57);
            font-size: 13px;
            line-height: 1.9;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .footer-links h3 {
            position: relative;
            margin: 3px 0 25px;
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .footer-links h3::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 27px;
            height: 2px;
            border-radius: 10px;
            background: linear-gradient(
                90deg,
                #7c3aed,
                #c026d3
            );
        }

        .footer-links a {
            position: relative;
            width: fit-content;
            margin-bottom: 14px;
            color: rgba(255,255,255,.54);
            text-decoration: none;
            font-size: 13px;
            line-height: 1.5;
            transition: .3s ease;
        }

        .footer-links a::before {
            content: "";
            position: absolute;
            left: -13px;
            top: 50%;
            width: 0;
            height: 2px;
            border-radius: 10px;
            background: linear-gradient(
                90deg,
                #7c3aed,
                #c026d3
            );
            transform: translateY(-50%);
            transition: width .3s ease;
        }

        .footer-links a:hover {
            color: #fff;
            transform: translateX(8px);
        }

        .footer-links a:hover::before {
            width: 7px;
        }

        .footer-bottom {
            position: relative;
            z-index: 2;
            width: min(1280px, 100%);
            margin: 0 auto;
            padding: 22px 0;
            border-top: 1px solid rgba(255,255,255,.08);
            text-align: center;
        }

        .footer-bottom p {
            margin: 0;
            color: rgba(255,255,255,.38);
            font-size: 11px;
            letter-spacing: .3px;
        }

        .footer-links a:focus-visible {
            outline: 2px solid rgba(167,139,250,.7);
            outline-offset: 5px;
            border-radius: 3px;
        }

        @media (max-width: 900px) {
            .footer {
                padding: 65px 22px 0;
            }

            .footer-container {
                grid-template-columns: 1.5fr 1fr 1fr;
                gap: 45px 30px;
            }

            .footer-brand {
                grid-column: 1 / -1;
                max-width: 650px;
            }
        }

        @media (max-width: 620px) {
            .footer {
                margin-top: 65px;
                padding: 55px 20px 0;
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px 25px;
                padding-bottom: 45px;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }

            .footer-logo {
                font-size: 27px;
            }

            .footer-brand p {
                font-size: 12px;
            }

            .footer-links h3 {
                font-size: 12px;
            }

            .footer-links a {
                font-size: 12px;
            }
        }

        @media (max-width: 400px) {
            .footer {
                padding-left: 15px;
                padding-right: 15px;
            }

            .footer-container {
                gap: 35px 18px;
            }

            .footer-logo {
                font-size: 25px;
            }

            .footer-links a {
                font-size: 11px;
            }

            .footer-bottom {
                padding: 19px 0;
            }

            .footer-bottom p {
                font-size: 10px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .footer-links a {
                transition: none;
            }
        }
    </style>

    <div class="footer-container">

        <div class="footer-brand">
            <div class="footer-logo">
                Event<span>Ease</span>
            </div>

            <p>
                Discover, book and enjoy amazing events
                with a simple, seamless and memorable
                experience.
            </p>
        </div>

        <div class="footer-links">
            <h3>Explore</h3>
            <a href="index.php">Home</a>
            <a href="index.php#events">Events</a>
            <a href="index.php#categories">Categories</a>
        </div>

        <div class="footer-links">
            <h3>Account</h3>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>

        <div class="footer-links">
            <h3>Support</h3>
            <a href="index.php#contact">Contact</a>
            <a href="#">Privacy</a>
            <a href="#">Help Center</a>
        </div>

    </div>

    <div class="footer-bottom">
        <p>
            © <?= date('Y') ?> EventEase. All Rights Reserved.
        </p>
    </div>

</footer>

</body>
</html>