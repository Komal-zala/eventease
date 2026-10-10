<?php
$page_title = "EventEase | All Events";

require_once __DIR__ . '/../config/database.php';

$today = date("Y-m-d");

$upcoming_sql = "
    SELECT *
    FROM events
    WHERE event_date >= ?
    ORDER BY event_date ASC, start_time ASC
";

$upcoming_stmt = $conn->prepare($upcoming_sql);
$upcoming_stmt->bind_param("s", $today);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();
$upcoming_events = $upcoming_result->fetch_all(MYSQLI_ASSOC);

$past_sql = "
    SELECT *
    FROM events
    WHERE event_date < ?
    ORDER BY event_date DESC, start_time DESC
";

$past_stmt = $conn->prepare($past_sql);
$past_stmt->bind_param("s", $today);
$past_stmt->execute();
$past_result = $past_stmt->get_result();
$past_events = $past_result->fetch_all(MYSQLI_ASSOC);

function formatEventDate($date)
{
    if (!$date) {
        return "Date not available";
    }

    return date("d M Y", strtotime($date));
}

function formatEventTime($time)
{
    if (!$time) {
        return "";
    }

    return date("h:i A", strtotime($time));
}

function eventImage($image)
{
    if (!$image) {
        return "assets/images/event-placeholder.jpg";
    }

    if (
        strpos($image, "http://") === 0 ||
        strpos($image, "https://") === 0
    ) {
        return $image;
    }

    return "assets/images/" . ltrim($image, "/");
}

require_once "header.php";
?>

<style>
.events-page {
    min-height: 100vh;
    overflow: hidden;
    background:
        radial-gradient(circle at 5% 5%, rgba(124, 58, 237, 0.08), transparent 28%),
        radial-gradient(circle at 95% 35%, rgba(192, 38, 211, 0.05), transparent 25%),
        linear-gradient(180deg, #ffffff 0%, #faf8ff 50%, #ffffff 100%);
    color: #302438;
}

.events-page *,
.events-page *::before,
.events-page *::after {
    box-sizing: border-box;
}

.events-container {
    width: min(1350px, 92%);
    margin: 0 auto;
}

.event-section {
    padding: 65px 0;
}

.event-section + .event-section {
    padding-top: 35px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
}

.section-title-wrapper h2 {
    margin: 0;
    color: #21152f;
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 900;
    line-height: 1.2;
    letter-spacing: -1px;
}

.section-title-wrapper p {
    margin: 9px 0 0;
    color: #817789;
    font-size: 14px;
    line-height: 1.6;
}

.section-title-line {
    width: 55px;
    height: 4px;
    margin-top: 14px;
    border-radius: 20px;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
}

.carousel-buttons {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.carousel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 1px solid rgba(124, 58, 237, 0.15);
    border-radius: 50%;
    background: #ffffff;
    color: #7c3aed;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    box-shadow: 0 6px 22px rgba(48, 25, 75, 0.07);
    transition: background 0.25s ease, color 0.25s ease,
                transform 0.25s ease, box-shadow 0.25s ease;
}

.carousel-btn:hover {
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(124, 58, 237, 0.25);
}

.carousel-btn:focus-visible {
    outline: 3px solid rgba(124, 58, 237, 0.35);
    outline-offset: 3px;
}

.event-carousel {
    display: flex;
    align-items: stretch;
    gap: 22px;
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 8px 5px 25px;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}

.event-carousel::-webkit-scrollbar {
    display: none;
}

.event-card {
    position: relative;
    display: flex;
    flex: 0 0 320px;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
    border: 1px solid rgba(124, 58, 237, 0.10);
    border-radius: 22px;
    background: #ffffff;
    color: inherit;
    text-decoration: none;
    scroll-snap-align: start;
    box-shadow: 0 8px 30px rgba(48, 25, 75, 0.07);
    transition: transform 0.3s ease, box-shadow 0.3s ease,
                border-color 0.3s ease;
}

.event-card:hover {
    transform: translateY(-6px);
    border-color: rgba(124, 58, 237, 0.22);
    box-shadow: 0 20px 45px rgba(48, 25, 75, 0.13);
}

.event-card:focus-visible {
    outline: 3px solid rgba(124, 58, 237, 0.45);
    outline-offset: 3px;
}

.event-image-wrapper {
    position: relative;
    flex-shrink: 0;
    height: 210px;
    overflow: hidden;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
}

.event-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    transition: transform 0.5s ease;
}

.event-card:hover .event-image {
    transform: scale(1.06);
}

.event-date-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 62px;
    min-height: 62px;
    padding: 9px 11px;
    border: 1px solid rgba(255, 255, 255, 0.75);
    border-radius: 13px;
    background: rgba(255, 255, 255, 0.95);
    text-align: center;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.event-date-badge .day {
    display: block;
    color: #7c3aed;
    font-size: 23px;
    font-weight: 900;
    line-height: 1;
}

.event-date-badge .month {
    display: block;
    margin-top: 5px;
    color: #70677a;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
}

.event-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 21px;
}

.event-content h3 {
    display: -webkit-box;
    overflow: hidden;
    margin: 0 0 10px;
    color: #21172a;
    font-size: 20px;
    font-weight: 850;
    line-height: 1.35;
    overflow-wrap: anywhere;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.event-description {
    display: -webkit-box;
    overflow: hidden;
    min-height: 42px;
    margin: 0 0 17px;
    color: #817789;
    font-size: 13px;
    line-height: 1.65;
    overflow-wrap: anywhere;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.event-info-row {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
    color: #686071;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.event-info-icon {
    display: inline-flex;
    flex: 0 0 29px;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    border-radius: 9px;
    background: rgba(124, 58, 237, 0.08);
    color: #7c3aed;
}

.event-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid #f0ebf5;
}

.event-price {
    color: #21172a;
    font-size: 17px;
    font-weight: 900;
    white-space: nowrap;
}

.event-price.free {
    color: #16a34a;
}

.event-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: 9px 13px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7c3aed, #c026d3);
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    text-align: center;
    white-space: nowrap;
    box-shadow: 0 5px 13px rgba(124, 58, 237, 0.15);
    transition: box-shadow 0.25s ease, transform 0.25s ease;
}

.event-card:hover .event-action {
    box-shadow: 0 7px 18px rgba(124, 58, 237, 0.25);
}

.past-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 10px;
    border: 1px solid #e8e0ef;
    border-radius: 9px;
    background: #f5f1f8;
    color: #766d80;
    font-size: 10px;
    font-weight: 800;
    text-align: center;
    white-space: nowrap;
}

.empty-events {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 230px;
    padding: 45px 20px;
    border: 1px dashed #dcd0e9;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.75);
    text-align: center;
}

.empty-events-icon {
    margin-bottom: 14px;
    font-size: 42px;
    line-height: 1;
}

.empty-events h3 {
    margin: 0 0 9px;
    color: #302438;
    font-size: 21px;
    font-weight: 800;
}

.empty-events p {
    max-width: 400px;
    margin: 0;
    color: #817789;
    font-size: 13px;
    line-height: 1.7;
}

@media (min-width: 1400px) {
    .events-container {
        width: min(1350px, 90%);
    }
}

@media (max-width: 900px) {
    .event-section {
        padding: 50px 0;
    }

    .event-card {
        flex-basis: 300px;
    }

    .event-image-wrapper {
        height: 200px;
    }
}

@media (max-width: 700px) {
    .events-container {
        width: 94%;
    }

    .event-section {
        padding: 40px 0;
    }

    .event-section + .event-section {
        padding-top: 20px;
    }

    .section-header {
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
    }

    .section-title-wrapper h2 {
        font-size: 25px;
        letter-spacing: -0.6px;
    }

    .section-title-wrapper p {
        max-width: 270px;
        font-size: 12px;
    }

    .section-title-line {
        width: 45px;
        height: 3px;
        margin-top: 11px;
    }

    .carousel-buttons {
        gap: 7px;
    }

    .carousel-btn {
        width: 38px;
        height: 38px;
        font-size: 18px;
    }

    .event-carousel {
        gap: 16px;
        padding-right: 3px;
        padding-left: 3px;
    }

    .event-card {
        flex-basis: 285px;
        border-radius: 19px;
    }

    .event-image-wrapper {
        height: 190px;
    }

    .event-content {
        padding: 18px;
    }

    .event-content h3 {
        font-size: 19px;
    }

    .event-card-footer {
        gap: 8px;
    }
}

@media (max-width: 450px) {
    .section-header {
        align-items: flex-end;
        gap: 8px;
    }

    .section-title-wrapper h2 {
        font-size: 22px;
    }

    .section-title-wrapper p {
        max-width: 220px;
        font-size: 11px;
    }

    .carousel-buttons {
        gap: 5px;
    }

    .carousel-btn {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }

    .event-card {
        flex-basis: 275px;
    }

    .event-image-wrapper {
        height: 180px;
    }

    .event-content {
        padding: 16px;
    }

    .event-content h3 {
        font-size: 18px;
    }

    .event-price {
        font-size: 15px;
    }

    .event-action {
        padding: 8px 10px;
        font-size: 10px;
    }

    .past-badge {
        padding: 7px 8px;
        font-size: 9px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .event-carousel {
        scroll-behavior: auto;
    }

    .event-card,
    .event-image,
    .carousel-btn,
    .event-action {
        transition: none;
    }
}
</style>

<main class="events-page">

    <section class="event-section" id="upcoming">
        <div class="events-container">

            <div class="section-header">
                <div class="section-title-wrapper">
                    <h2>Upcoming Events</h2>
                    <p>Don't miss what's coming next.</p>
                    <div class="section-title-line"></div>
                </div>

                <?php if (count($upcoming_events) > 1): ?>
                    <div class="carousel-buttons">
                        <button type="button" class="carousel-btn" onclick="scrollEvents('upcoming-carousel', -1)">←</button>
                        <button type="button" class="carousel-btn" onclick="scrollEvents('upcoming-carousel', 1)">→</button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($upcoming_events) > 0): ?>
                <div class="event-carousel" id="upcoming-carousel">

                    <?php foreach ($upcoming_events as $event): ?>
                        <?php
                        $price = $event['price'] ?? 0;
                        $eventDate = strtotime($event['event_date']);

                        $imageName = $event['image'] ?? '';
                        $imagePath = '../uploads/events/' . basename($imageName);
                        $physicalPath = __DIR__ . '/../uploads/events/' . basename($imageName);

                        if (
                            !empty($imageName) &&
                            file_exists($physicalPath)
                        ) {
                            $imageUrl = $imagePath;
                        } else {
                            $imageUrl = 'assets/images/event-placeholder.jpg';
                        }
                        ?>

                        <a href="event_details.php?id=<?= (int)$event['id'] ?>" class="event-card">

                            <div class="event-image-wrapper">
                                <img
                                    src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($event['title'] ?? 'Event', ENT_QUOTES, 'UTF-8') ?>"
                                    class="event-image"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='assets/images/event-placeholder.jpg';"
                                >

                                <div class="event-date-badge">
                                    <span class="day"><?= date("d", $eventDate) ?></span>
                                    <span class="month"><?= date("M", $eventDate) ?></span>
                                </div>
                            </div>

                            <div class="event-content">
                                <h3><?= htmlspecialchars($event['title'] ?? 'Untitled Event', ENT_QUOTES, 'UTF-8') ?></h3>

                                <p class="event-description">
                                    <?= htmlspecialchars($event['description'] ?? 'Join us for this exciting event.', ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <div class="event-info">
                                    <div class="event-info-row">
                                        <span class="event-info-icon">📅</span>
                                        <?= formatEventDate($event['event_date']) ?>
                                    </div>

                                    <?php if (!empty($event['start_time'])): ?>
                                        <div class="event-info-row">
                                            <span class="event-info-icon">🕐</span>
                                            <?= formatEventTime($event['start_time']) ?>

                                            <?php if (!empty($event['end_time'])): ?>
                                                - <?= formatEventTime($event['end_time']) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="event-info-row">
                                        <span class="event-info-icon">📍</span>
                                        <?= htmlspecialchars($event['venue'] ?? 'Venue TBA', ENT_QUOTES, 'UTF-8') ?>

                                        <?php if (!empty($event['city'])): ?>
                                            , <?= htmlspecialchars($event['city'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="event-card-footer">
                                    <?php if ((float)$price > 0): ?>
                                        <span class="event-price">₹<?= number_format((float)$price, 2) ?></span>
                                    <?php else: ?>
                                        <span class="event-price free">FREE</span>
                                    <?php endif; ?>

                                    <span class="event-action">
                                        <?php
                                        if (
                                            isset($event['registration_open']) &&
                                            (int)$event['registration_open'] === 1
                                        ) {
                                            echo "Register Now";
                                        } else {
                                            echo "View Event";
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <div class="empty-events">
                    <div class="empty-events-icon">📅</div>
                    <h3>No Upcoming Events</h3>
                    <p>New events will appear here when they are added.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <section class="event-section" id="past">
        <div class="events-container">

            <div class="section-header">
                <div class="section-title-wrapper">
                    <h2>Past Events</h2>
                    <p>Explore memories from our previous events.</p>
                    <div class="section-title-line"></div>
                </div>

                <?php if (count($past_events) > 1): ?>
                    <div class="carousel-buttons">
                        <button type="button" class="carousel-btn" onclick="scrollEvents('past-carousel', -1)">←</button>
                        <button type="button" class="carousel-btn" onclick="scrollEvents('past-carousel', 1)">→</button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($past_events) > 0): ?>
                <div class="event-carousel" id="past-carousel">

                    <?php foreach ($past_events as $event): ?>
                        <?php
                        $image = eventImage($event['image'] ?? null);
                        $price = $event['price'] ?? 0;
                        $eventDate = strtotime($event['event_date']);
                        ?>

                        <a href="event-details.php?id=<?= (int)$event['id'] ?>" class="event-card">

                            <div class="event-image-wrapper">
                                <img
                                    src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($event['title'] ?? 'Event', ENT_QUOTES, 'UTF-8') ?>"
                                    class="event-image"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='assets/images/event-placeholder.jpg';"
                                >

                                <div class="event-date-badge">
                                    <span class="day"><?= date("d", $eventDate) ?></span>
                                    <span class="month"><?= date("M", $eventDate) ?></span>
                                </div>
                            </div>

                            <div class="event-content">
                                <h3><?= htmlspecialchars($event['title'] ?? 'Untitled Event', ENT_QUOTES, 'UTF-8') ?></h3>

                                <p class="event-description">
                                    <?= htmlspecialchars($event['description'] ?? 'Event information', ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <div class="event-info">
                                    <div class="event-info-row">
                                        <span class="event-info-icon">📅</span>
                                        <?= formatEventDate($event['event_date']) ?>
                                    </div>

                                    <?php if (!empty($event['start_time'])): ?>
                                        <div class="event-info-row">
                                            <span class="event-info-icon">🕐</span>
                                            <?= formatEventTime($event['start_time']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="event-info-row">
                                        <span class="event-info-icon">📍</span>
                                        <?= htmlspecialchars($event['venue'] ?? 'Venue TBA', ENT_QUOTES, 'UTF-8') ?>

                                        <?php if (!empty($event['city'])): ?>
                                            , <?= htmlspecialchars($event['city'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="event-card-footer">
                                    <?php if ((float)$price > 0): ?>
                                        <span class="event-price">₹<?= number_format((float)$price, 2) ?></span>
                                    <?php else: ?>
                                        <span class="event-price free">FREE</span>
                                    <?php endif; ?>

                                    <span class="past-badge">EVENT COMPLETED</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <div class="empty-events">
                    <div class="empty-events-icon">🗓️</div>
                    <h3>No Past Events</h3>
                    <p>Previous events will appear here.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<script>
function scrollEvents(carouselId, direction) {
    const carousel = document.getElementById(carouselId);

    if (!carousel) {
        return;
    }

    const card = carousel.querySelector(".event-card");

    if (!card) {
        return;
    }

    const styles = window.getComputedStyle(carousel);
    const gap = parseFloat(styles.columnGap) || 22;
    const scrollAmount = card.getBoundingClientRect().width + gap;

    carousel.scrollBy({
        left: direction * scrollAmount,
        behavior: "smooth"
    });
}

function startAutoScroll(carouselId) {
    const carousel = document.getElementById(carouselId);

    if (!carousel) {
        return;
    }

    let paused = false;

    carousel.addEventListener("mouseenter", function () {
        paused = true;
    });

    carousel.addEventListener("mouseleave", function () {
        paused = false;
    });

    carousel.addEventListener("touchstart", function () {
        paused = true;
    }, { passive: true });

    carousel.addEventListener("touchend", function () {
        paused = false;
    }, { passive: true });

    setInterval(function () {
        if (paused) {
            return;
        }

        const maxScroll = carousel.scrollWidth - carousel.clientWidth;

        if (maxScroll <= 5) {
            return;
        }

        if (carousel.scrollLeft >= maxScroll - 5) {
            carousel.scrollTo({
                left: 0,
                behavior: "smooth"
            });
        } else {
            const card = carousel.querySelector(".event-card");

            if (!card) {
                return;
            }

            const styles = window.getComputedStyle(carousel);
            const gap = parseFloat(styles.columnGap) || 22;

            carousel.scrollBy({
                left: card.getBoundingClientRect().width + gap,
                behavior: "smooth"
            });
        }
    }, 3000);
}

startAutoScroll("upcoming-carousel");
startAutoScroll("past-carousel");
</script>

<?php
require_once "footer.php";
?>