
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = "About Us | EventEase";

$is_logged_in = isset($_SESSION['user_logged_in']) &&
                $_SESSION['user_logged_in'] === true;

include './header.php';
?>

<style>
.about-page {
    --primary: #7c3aed;
    --secondary: #c026d3;
    --dark: #21152f;
    --text: #51465f;
    --light: #faf8ff;

    background: var(--light);
    color: var(--dark);
    overflow: hidden;
}

.about-page * {
    box-sizing: border-box;
}

.about-container {
    width: min(1150px, 90%);
    margin: auto;
}

.about-hero {
    position: relative;
    padding: 90px 20px;
    text-align: center;
    color: white;
    background: linear-gradient(135deg, #241035, #51228a, #8438c9);
    overflow: hidden;
}

.about-hero::before,
.about-hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(2px);
    pointer-events: none;
}

.about-hero::before {
    width: 260px;
    height: 260px;
    background: #c026d344;
    top: -100px;
    left: -60px;
}

.about-hero::after {
    width: 300px;
    height: 300px;
    background: #7c3aed55;
    bottom: -170px;
    right: -60px;
}

.about-hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: auto;
}

.about-label {
    display: inline-block;
    padding: 9px 18px;
    border: 1px solid #ffffff50;
    border-radius: 50px;
    background: #ffffff12;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 1px;
}

.about-hero h1 {
    margin: 24px 0 16px;
    font-size: clamp(36px, 5vw, 58px);
    line-height: 1.15;
    font-weight: 800;
}

.about-hero h1 span {
    background: linear-gradient(90deg, #f0abfc, #ffffff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.about-hero p {
    max-width: 690px;
    margin: auto;
    color: #eee4ff;
    font-size: 17px;
    line-height: 1.8;
}

.about-section {
    padding: 75px 0;
}

.about-heading {
    margin-bottom: 38px;
    text-align: center;
}

.about-heading .small-title,
.eventease-content .small-title {
    color: var(--primary);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.about-heading h2 {
    margin: 12px 0;
    font-size: clamp(27px, 4vw, 38px);
    font-weight: 800;
}

.about-heading p {
    max-width: 700px;
    margin: 0 auto;
    color: var(--text);
    line-height: 1.8;
}

.college-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 35px;
    align-items: center;
    padding: 35px;
    border: 1px solid #eee5ff;
    border-radius: 24px;
    background: #ffffff;
    box-shadow: 0 12px 40px #4520800d;
}

.college-icon {
    min-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    background: linear-gradient(135deg, #ede9fe, #fae8ff);
}

.college-icon .icon-circle {
    width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 35px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    box-shadow: 0 18px 35px #7c3aed40;
    font-size: 75px;
}

.college-content h3 {
    margin: 0 0 15px;
    font-size: 27px;
    line-height: 1.4;
}

.college-content p {
    color: var(--text);
    line-height: 1.9;
    font-size: 15px;
}

.college-details {
    display: grid;
    gap: 13px;
    margin-top: 22px;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: var(--text);
    font-size: 14px;
    line-height: 1.7;
}

.detail-icon {
    flex-shrink: 0;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #f3e8ff;
    font-size: 17px;
}

.eventease-section {
    background: #f2ecff;
}

.eventease-card {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 45px;
    align-items: center;
}

.eventease-content h2 {
    margin: 12px 0 18px;
    font-size: clamp(30px, 4vw, 42px);
    line-height: 1.25;
}

.eventease-content h2 span {
    color: var(--primary);
}

.eventease-content p {
    color: var(--text);
    line-height: 1.9;
    font-size: 15px;
}

.eventease-visual {
    padding: 35px;
    border-radius: 25px;
    background: linear-gradient(135deg, #241035, #51228a, #8438c9);
    color: white;
    box-shadow: 0 20px 45px #51228a25;
}

.eventease-logo {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 28px;
}

.eventease-logo-icon {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: #ffffff20;
    font-size: 27px;
}

.eventease-logo h3 {
    margin: 0;
    font-size: 25px;
}

.eventease-logo p {
    margin: 5px 0 0;
    color: #eadbff;
    font-size: 12px;
}

.eventease-visual h4 {
    margin: 0 0 12px;
    font-size: 23px;
}

.eventease-visual > p {
    color: #eadbff;
    font-size: 14px;
    line-height: 1.8;
}

.visual-feature {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #ffffff25;
    font-size: 14px;
}

.visual-feature:last-child {
    border-bottom: none;
}

.visual-feature span {
    font-size: 19px;
}

.about-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
    padding: 14px 25px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 8px 20px #7c3aed30;
    transition: transform 0.2s;
}

.about-button:hover {
    transform: translateY(-3px);
}

@media (max-width: 800px) {
    .college-card,
    .eventease-card {
        grid-template-columns: 1fr;
    }

    .college-card {
        padding: 22px;
    }
}

@media (max-width: 520px) {
    .about-hero {
        padding: 70px 18px;
    }

    .about-section {
        padding: 55px 0;
    }

    .college-icon {
        min-height: 210px;
    }

    .college-icon .icon-circle {
        width: 120px;
        height: 120px;
        font-size: 60px;
    }

    .eventease-visual {
        padding: 25px;
    }
}
</style>

<main class="about-page">

    <section class="about-hero">
        <div class="about-hero-content">
            <span class="about-label">ABOUT US</span>

            <h1>
                Where Education Meets <span>Innovation</span>
            </h1>

            <p>
                Discover the institution behind our journey and learn how
                EventEase is making college events easier to organize,
                discover, and experience.
            </p>
        </div>
    </section>

    <section class="about-section">
        <div class="about-container">

            <div class="about-heading">
                <span class="small-title">Our Institution</span>
                <h2>About Our College</h2>

                <p>
                    Our college is a place for learning, creativity,
                    teamwork, and personal growth. We encourage students
                    to develop practical skills and turn their ideas
                    into meaningful projects.
                </p>
            </div>

            <div class="college-card">

                <div class="college-icon">
                    <div class="icon-circle">🎓</div>
                </div>

                <div class="college-content">
                    <h3>Welcome to Our College</h3>

                    <p>
                        Our institution focuses on academic excellence,
                        practical knowledge, innovation, and the overall
                        development of students. Through educational
                        activities, workshops, seminars, and cultural
                        programs, students get opportunities to learn,
                        collaborate, and showcase their talents.
                    </p>

                    <p>
                        We believe that education goes beyond classrooms.
                        College events and extracurricular activities help
                        students build confidence, leadership, creativity,
                        and communication skills.
                    </p>

                    <div class="college-details">

                        <div class="detail-item">
                            <div class="detail-icon">🏫</div>
                            <div>
                                <strong>College Name</strong><br>
                                ATMIYA UNIVERSITY
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">📍</div>
                            <div>
                                <strong>Location</strong><br>
                                Rajkot, Gujarat, India
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">💻</div>
                            <div>
                                <strong>Academic Focus</strong><br>
                                Technology, innovation, practical learning,
                                and student development
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon">🌟</div>
                            <div>
                                <strong>Our Values</strong><br>
                                Knowledge, creativity, teamwork, and excellence
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="about-section eventease-section">
        <div class="about-container">

            <div class="eventease-card">

                <div class="eventease-content">
                    <span class="small-title">Our Project</span>

                    <h2>
                        Introducing <span>EventEase</span>
                    </h2>

                    <p>
                        EventEase is a college event registration and
                        check-in management platform designed to simplify
                        the way events are organized and attended.
                    </p>

                    <p>
                        Managing events through separate forms,
                        spreadsheets, and manual attendance records can
                        be time-consuming and confusing. EventEase brings
                        these activities together in one convenient system.
                    </p>

                    <p>
                        Our goal is to provide students with an easy way
                        to explore events and register, while helping
                        organizers manage participants, monitor capacity,
                        and record attendance efficiently.
                    </p>

                    <?php if ($is_logged_in): ?>
                        <a href="events.php" class="about-button">
                            Explore Events 
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="about-button">
                            Login to Explore 
                        </a>
                    <?php endif; ?>

                </div>

                <div class="eventease-visual">

                    <div class="eventease-logo">
                        <div class="eventease-logo-icon">🎟️</div>

                        <div>
                            <h3>EventEase</h3>
                            <p>Discover Events. Create Memories.</p>
                        </div>
                    </div>

                    <h4>Everything in One Place</h4>

                    <p>
                        A smarter way to connect students, organizers,
                        and exciting college experiences.
                    </p>

                    <div class="visual-feature">
                        <span>📝</span>
                        <div>Simple event registration</div>
                    </div>

                    <div class="visual-feature">
                        <span>🎫</span>
                        <div>Unique QR code or entry code</div>
                    </div>

                    <div class="visual-feature">
                        <span>✅</span>
                        <div>Quick and reliable check-in</div>
                    </div>

                    <div class="visual-feature">
                        <span>📊</span>
                        <div>Registration and attendance tracking</div>
                    </div>

                </div>

            </div>
        </div>
    </section>

</main>

<?php include './footer.php'; ?>
