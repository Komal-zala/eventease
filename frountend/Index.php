<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "EventEase | Discover Amazing Events";

$is_logged_in = isset($_SESSION['user_logged_in']) &&
                $_SESSION['user_logged_in'] === true;

include './header.php';
?>

<style>
.ee-index {
    background: #faf8ff;
    color: #30283b;
    overflow: hidden;
}

.ee-index * {
    box-sizing: border-box;
}

.ee-card {
    width: 100%;
    min-height: 430px;
    padding: 55px 7%;
    background: linear-gradient(135deg, #241035, #51228a, #8438c9);
    color: #fff;
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    align-items: center;
    gap: 35px;
}

.ee-card-content {
    text-align: left;
}

.ee-badge {
    display: inline-block;
    padding: 9px 15px;
    margin-bottom: 22px;
    border: 1px solid #ffffff40;
    border-radius: 30px;
    background: #ffffff15;
    color: #f5e7ff;
    font-size: 13px;
}

.ee-title {
    margin: 0 0 20px;
    font-size: clamp(38px, 5vw, 64px);
    line-height: 1.08;
    font-weight: 800;
    letter-spacing: -1.5px;
}

.ee-title span {
    display: block;
    margin-top: 5px;
    color: #f1d9ff;
}

.ee-description {
    max-width: 550px;
    margin: 0 0 28px;
    color: #ffffffc7;
    font-size: 16px;
    line-height: 1.8;
}

.ee-button {
    display: inline-block;
    padding: 14px 28px;
    border-radius: 12px;
    background: #fff;
    color: #6325a8;
    font-weight: 700;
    text-decoration: none;
    transition: .3s;
}

.ee-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px #16082455;
}

.ee-orbit-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 370px;
    overflow: hidden;
    isolation: isolate;
}

.ee-orbit-glow {
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: #d946ef;
    filter: blur(70px);
    opacity: .45;
    animation: eeGlowPulse 3s ease-in-out infinite;
}

.ee-orbit-ring {
    position: absolute;
    width: 245px;
    height: 245px;
    border: 1px solid #e9d5ff48;
    border-radius: 50%;
}

.ee-ring-one {
    animation: eeRingPulse 4s ease-in-out infinite;
}

.ee-ring-two {
    width: 310px;
    height: 310px;
    border-color: #f0abfc30;
    animation: eeRingPulse 4s ease-in-out infinite 1s;
}

.ee-orbit-center {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 165px;
    height: 165px;
    border: 1px solid #ffffff45;
    border-radius: 50%;
    background: #ffffff0d;
    box-shadow: inset 0 0 35px #e9d5ff12, 0 0 45px #a855f72b;
    backdrop-filter: blur(8px);
}

.ee-orbit-center .ee-center-star {
    margin-bottom: 7px;
    color: #f5d0fe;
    font-size: 30px;
    text-shadow: 0 0 18px #e879f9;
    animation: eeStarTwinkle 2s ease-in-out infinite;
}

.ee-orbit-center strong {
    color: #fff;
    font-size: 22px;
    line-height: 1.25;
}

.ee-orbit-center small {
    margin-top: 10px;
    color: #e9d5ff;
    font-size: 9px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.ee-orbit-path {
    position: absolute;
    z-index: 3;
    width: 310px;
    height: 310px;
    border-radius: 50%;
    animation: eeOrbitSpin 7s linear infinite;
}

.ee-firefly {
    position: absolute;
    top: -5px;
    left: 50%;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 0 7px 3px #fff, 0 0 17px 7px #e879f9, 0 0 35px 12px #a855f7a6;
    transform: translateX(-50%);
    animation: eeFireflyBlink 1.5s ease-in-out infinite;
}

.ee-orbit-spark {
    position: absolute;
    color: #f5d0fe;
    text-shadow: 0 0 15px #d946ef;
    animation: eeSparkFloat 3s ease-in-out infinite;
}

.ee-spark-one {
    top: 15%;
    left: 17%;
    font-size: 22px;
}

.ee-spark-two {
    right: 15%;
    bottom: 18%;
    font-size: 25px;
    animation-delay: 1s;
}

.ee-spark-three {
    top: 23%;
    right: 19%;
    font-size: 30px;
    animation-delay: 1.5s;
}

@keyframes eeOrbitSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes eeFireflyBlink {
    0%, 100% { opacity: .7; scale: .85; }
    50% { opacity: 1; scale: 1.2; }
}

@keyframes eeGlowPulse {
    0%, 100% { opacity: .25; transform: scale(.85); }
    50% { opacity: .5; transform: scale(1.15); }
}

@keyframes eeRingPulse {
    0%, 100% { opacity: .55; transform: scale(.97); }
    50% { opacity: 1; transform: scale(1.035); }
}

@keyframes eeSparkFloat {
    0%, 100% { opacity: .45; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-9px); }
}

@keyframes eeStarTwinkle {
    0%, 100% { opacity: .75; transform: scale(.92); }
    50% { opacity: 1; transform: scale(1.12); }
}

.ee-cta {
    padding: 90px 0;
}

.ee-cta-box {
    width: min(900px, calc(100% - 30px));
    margin: auto;
    padding: 60px 25px;
    border-radius: 24px;
    background: linear-gradient(135deg, #8142b9, #51228a, #8438c9);
    color: #fff;
    text-align: center;
}

.ee-cta-box h2 {
    margin: 0 0 15px;
    color: #fff;
    font-size: clamp(32px, 5vw, 48px);
    line-height: 1.2;
}

.ee-cta-box p {
    max-width: 600px;
    margin: 0 auto 25px;
    color: #ffffffc7;
    line-height: 1.7;
}

.ee-cta-button {
    display: inline-block;
    padding: 14px 24px;
    border-radius: 12px;
    background: #fff;
    color: #6d28d9;
    font-weight: 700;
    text-decoration: none;
    transition: .3s;
}

.ee-cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px #16082444;
}

@media(max-width: 800px) {
    .ee-card {
        grid-template-columns: 1fr;
        padding: 45px 25px 30px;
        gap: 5px;
    }

    .ee-card-content {
        text-align: center;
    }

    .ee-description {
        margin-right: auto;
        margin-left: auto;
    }

    .ee-orbit-visual {
        min-height: 340px;
    }

    .ee-orbit-path,
    .ee-ring-two {
        width: 270px;
        height: 270px;
    }

    .ee-ring-one {
        width: 215px;
        height: 215px;
    }
}

@media(max-width: 600px) {
    .ee-card {
        padding: 38px 20px 20px;
    }

    .ee-title {
        font-size: 42px;
    }

    .ee-description {
        font-size: 15px;
    }

    .ee-orbit-visual {
        min-height: 310px;
    }

    .ee-orbit-path,
    .ee-ring-two {
        width: 245px;
        height: 245px;
    }

    .ee-ring-one {
        width: 195px;
        height: 195px;
    }

    .ee-orbit-center {
        width: 140px;
        height: 140px;
    }

    .ee-orbit-center strong {
        font-size: 19px;
    }

    .ee-cta {
        padding: 65px 0;
    }

    .ee-cta-box {
        padding: 45px 20px;
    }
}

@media(prefers-reduced-motion: reduce) {
    .ee-orbit-path,
    .ee-orbit-glow,
    .ee-orbit-ring,
    .ee-firefly,
    .ee-orbit-spark,
    .ee-orbit-center .ee-center-star {
        animation: none;
    }
}
</style>

<main class="ee-index">

    <section class="ee-card">

        <div class="ee-card-content">

            <div class="ee-badge">
                ✨ Your Event Journey Starts Here
            </div>

            <h1 class="ee-title">
                Discover Events.
                <span>Create Memories.</span>
            </h1>

            <p class="ee-description">
                Find amazing college events, connect with people,
                and create unforgettable experiences with EventEase.
            </p>

            <?php if ($is_logged_in): ?>
                <a href="event.php" class="ee-button">
                    Event
                </a>
            <?php else: ?>
                <a href="login.php" class="ee-button">
                    Login 
                </a>
            <?php endif; ?>

        </div>

        <div class="ee-orbit-visual">

            <div class="ee-orbit-glow"></div>

            <div class="ee-orbit-ring ee-ring-one"></div>
            <div class="ee-orbit-ring ee-ring-two"></div>

            <div class="ee-orbit-center">
                <span class="ee-center-star">✦</span>
                <strong>EXPERIENCES</strong>
                <strong>REIMAGINED</strong>
                <small>Make Memories</small>
            </div>

            <div class="ee-orbit-path">
                <span class="ee-firefly"></span>
            </div>

            <div class="ee-orbit-spark ee-spark-one">✦</div>
            <div class="ee-orbit-spark ee-spark-two">✧</div>
            <div class="ee-orbit-spark ee-spark-three">·</div>

        </div>

    </section>

    <section class="ee-cta">

        <div class="ee-cta-box">

            <h2>Ready to Experience Something Amazing?</h2>

            <p>
                Join EventEase today and discover exciting events,
                meet new people and create unforgettable memories.
            </p>

            <?php if ($is_logged_in): ?>
                <a href="Upcoming_event.php" class="ee-cta-button">
                    Join Event 
                </a>
            <?php else: ?>
                <a href="login.php" class="ee-cta-button">
                    Login to Join Event 
                </a>
            <?php endif; ?>

        </div>

    </section>

</main>

<?php include './footer.php'; ?>