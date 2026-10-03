<?php

$page_title = "EventEase | All Events";

require_once __DIR__ . '/../config/database.php';


// --------------------------------------------------
// FETCH UPCOMING EVENTS
// --------------------------------------------------

$today = date("Y-m-d");

$upcoming_sql = "
    SELECT *
    FROM events
    WHERE event_date >= :today
    ORDER BY event_date ASC, start_time ASC
";

$upcoming_stmt = $conn->prepare($upcoming_sql);
$upcoming_stmt->execute([
    ':today' => $today
]);

$upcoming_events = $upcoming_stmt->fetchAll();


// --------------------------------------------------
// FETCH PAST EVENTS
// --------------------------------------------------

$past_sql = "
    SELECT *
    FROM events
    WHERE event_date < :today
    ORDER BY event_date DESC, start_time DESC
";

$past_stmt = $conn->prepare($past_sql);
$past_stmt->execute([
    ':today' => $today
]);

$past_events = $past_stmt->fetchAll();


// --------------------------------------------------
// HELPER FUNCTIONS
// --------------------------------------------------

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

    // If database contains a complete URL
    if (
        strpos($image, "http://") === 0 ||
        strpos($image, "https://") === 0
    ) {
        return $image;
    }

    return "assets/images/" . ltrim($image, "/");
}


// --------------------------------------------------
// HEADER
// --------------------------------------------------

require_once "header.php";

?>

<style>

    /* ==================================================
       GENERAL
    ================================================== */

    .events-page {
        min-height: 100vh;
        background:
            radial-gradient(
                circle at top left,
                rgba(124, 58, 237, 0.08),
                transparent 35%
            ),
            linear-gradient(
                180deg,
                #ffffff 0%,
                #faf8ff 50%,
                #ffffff 100%
            );
    }


    .events-container {
        width: min(1350px, 92%);
        margin: 0 auto;
    }


    /* ==================================================
       PAGE HERO
    ================================================== */

    .events-hero {
        text-align: center;
        padding: 75px 20px 55px;
    }


    .events-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 8px 16px;

        border-radius: 50px;

        background: rgba(124, 58, 237, 0.08);
        color: #7c3aed;

        font-size: 13px;
        font-weight: 800;

        margin-bottom: 18px;
    }


    .events-hero h1 {
        margin: 0;

        font-size: clamp(38px, 5vw, 68px);

        line-height: 1.05;

        font-weight: 900;

        letter-spacing: -2.5px;

        color: #18111f;
    }


    .events-hero h1 span {
        background: linear-gradient(
            135deg,
            #7c3aed,
            #c026d3
        );

        -webkit-background-clip: text;
        background-clip: text;

        color: transparent;
    }


    .events-hero p {
        max-width: 700px;

        margin: 20px auto 0;

        color: #766d80;

        font-size: 16px;

        line-height: 1.7;
    }


    /* ==================================================
       EVENT SECTION
    ================================================== */

    .event-section {
        padding: 30px 0 65px;
    }


    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 25px;

        gap: 20px;
    }


    .section-title-wrapper h2 {
        margin: 0;

        font-size: 30px;

        font-weight: 900;

        color: #18111f;
    }


    .section-title-wrapper p {
        margin: 7px 0 0;

        color: #817789;

        font-size: 14px;
    }


    .section-title-line {
        width: 55px;
        height: 4px;

        border-radius: 20px;

        background: linear-gradient(
            135deg,
            #7c3aed,
            #c026d3
        );

        margin-top: 12px;
    }


    /* ==================================================
       ARROWS
    ================================================== */

    .carousel-buttons {
        display: flex;
        gap: 10px;
    }


    .carousel-btn {
        width: 45px;
        height: 45px;

        border: 1px solid rgba(124, 58, 237, 0.12);

        border-radius: 50%;

        background: white;

        color: #7c3aed;

        font-size: 21px;

        cursor: pointer;

        box-shadow: 0 8px 25px rgba(48, 25, 75, 0.08);

        transition: 0.25s ease;
    }


    .carousel-btn:hover {
        color: white;

        background: linear-gradient(
            135deg,
            #7c3aed,
            #c026d3
        );

        transform: translateY(-2px);

        box-shadow:
            0 12px 28px rgba(124, 58, 237, 0.25);
    }


    /* ==================================================
       CAROUSEL
    ================================================== */

    .event-carousel {
        display: flex;

        gap: 22px;

        overflow-x: auto;

        scroll-behavior: smooth;

        scroll-snap-type: x mandatory;

        padding: 5px 5px 25px;

        scrollbar-width: none;
    }


    .event-carousel::-webkit-scrollbar {
        display: none;
    }


    /* ==================================================
       EVENT CARD
    ================================================== */

    .event-card {
        position: relative;

        flex: 0 0 320px;

        scroll-snap-align: start;

        background: white;

        border-radius: 22px;

        overflow: hidden;

        border: 1px solid rgba(124, 58, 237, 0.09);

        box-shadow:
            0 10px 35px rgba(48, 25, 75, 0.08);

        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease;

        text-decoration: none;

        color: inherit;
    }


    .event-card:hover {
        transform: translateY(-8px);

        box-shadow:
            0 22px 50px rgba(48, 25, 75, 0.14);
    }


    /* ==================================================
       IMAGE
    ================================================== */

    .event-image-wrapper {
        position: relative;

        height: 210px;

        overflow: hidden;

        background:
            linear-gradient(
                135deg,
                #7c3aed,
                #c026d3
            );
    }


    .event-image {
        width: 100%;
        height: 100%;

        object-fit: cover;

        transition: transform 0.5s ease;
    }


    .event-card:hover .event-image {
        transform: scale(1.07);
    }


    .event-date-badge {
        position: absolute;

        top: 14px;
        left: 14px;

        min-width: 62px;

        padding: 9px 10px;

        border-radius: 13px;

        background: rgba(255,255,255,0.94);

        backdrop-filter: blur(10px);

        text-align: center;

        box-shadow:
            0 8px 20px rgba(0,0,0,0.12);
    }


    .event-date-badge .day {
        display: block;

        color: #7c3aed;

        font-size: 21px;

        font-weight: 900;

        line-height: 1;
    }


    .event-date-badge .month {
        display: block;

        margin-top: 3px;

        color: #70677a;

        font-size: 10px;

        font-weight: 800;

        text-transform: uppercase;
    }


    /* ==================================================
       CARD CONTENT
    ================================================== */

    .event-content {
        padding: 20px;
    }


    .event-content h3 {
        margin: 0 0 10px;

        color: #21172a;

        font-size: 20px;

        line-height: 1.3;

        font-weight: 850;
    }


    .event-description {
        margin: 0 0 16px;

        color: #817789;

        font-size: 13px;

        line-height: 1.6;

        display: -webkit-box;

        -webkit-line-clamp: 2;

        -webkit-box-orient: vertical;

        overflow: hidden;
    }


    .event-info {
        display: flex;

        flex-direction: column;

        gap: 9px;

        margin-bottom: 18px;
    }


    .event-info-row {
        display: flex;

        align-items: center;

        gap: 9px;

        color: #686071;

        font-size: 12px;

        font-weight: 600;
    }


    .event-info-icon {
        width: 28px;
        height: 28px;

        display: flex;

        align-items: center;
        justify-content: center;

        border-radius: 9px;

        background: rgba(124,58,237,0.08);

        color: #7c3aed;

        flex-shrink: 0;
    }


    /* ==================================================
       CARD FOOTER
    ================================================== */

    .event-card-footer {
        display: flex;

        align-items: center;

        justify-content: space-between;

        padding-top: 15px;

        border-top: 1px solid #f0ebf5;
    }


    .event-price {
        color: #21172a;

        font-size: 17px;

        font-weight: 900;
    }


    .event-price.free {
        color: #16a34a;
    }


    .event-action {
        padding: 9px 14px;

        border-radius: 10px;

        background: linear-gradient(
            135deg,
            #7c3aed,
            #c026d3
        );

        color: white;

        font-size: 11px;

        font-weight: 800;

        text-decoration: none;
    }


    .past-badge {
        padding: 7px 11px;

        border-radius: 9px;

        background: #f1edf5;

        color: #766d80;

        font-size: 10px;

        font-weight: 800;
    }


    /* ==================================================
       EMPTY STATE
    ================================================== */

    .empty-events {
        width: 100%;

        padding: 55px 20px;

        text-align: center;

        border: 1px dashed #ddd3e7;

        border-radius: 20px;

        background: rgba(255,255,255,0.7);

        color: #817789;
    }


    .empty-events-icon {
        font-size: 42px;

        margin-bottom: 12px;
    }


    .empty-events h3 {
        margin: 0 0 7px;

        color: #302438;
    }


    .empty-events p {
        margin: 0;

        font-size: 13px;
    }


    /* ==================================================
       RESPONSIVE
    ================================================== */

    @media (max-width: 700px) {

        .events-hero {
            padding: 50px 15px 35px;
        }


        .events-hero h1 {
            letter-spacing: -1.5px;
        }


        .section-header {
            align-items: flex-end;
        }


        .section-title-wrapper h2 {
            font-size: 24px;
        }


        .section-title-wrapper p {
            font-size: 12px;
        }


        .carousel-btn {
            width: 39px;
            height: 39px;
        }


        .event-card {
            flex-basis: 285px;
        }


        .event-image-wrapper {
            height: 190px;
        }
    }


    @media (max-width: 450px) {

        .events-container {
            width: 94%;
        }


        .carousel-buttons {
            gap: 5px;
        }


        .carousel-btn {
            width: 35px;
            height: 35px;

            font-size: 17px;
        }


        .event-card {
            flex-basis: 275px;
        }
    }

</style>


<main class="events-page">

    <!-- ==================================================
         HERO
    ================================================== -->

    <section class="events-hero">

        <div class="events-container">

            <div class="events-badge">
                ✦ Discover College Events
            </div>

            <h1>
                Discover.
                <span>Register.</span>
                Participate.
            </h1>

            <p>
                Explore upcoming and past college events,
                discover exciting experiences, and register
                for the events you don't want to miss.
            </p>

        </div>

    </section>


    <!-- ==================================================
         UPCOMING EVENTS
    ================================================== -->

    <section
        class="event-section"
        id="upcoming"
    >

        <div class="events-container">

            <div class="section-header">

                <div class="section-title-wrapper">

                    <h2>
                        Upcoming Events
                    </h2>

                    <p>
                        Don't miss what's coming next.
                    </p>

                    <div class="section-title-line"></div>

                </div>


                <?php if (count($upcoming_events) > 1): ?>

                    <div class="carousel-buttons">

                        <button
                            type="button"
                            class="carousel-btn"
                            onclick="scrollEvents('upcoming-carousel', -1)"
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            class="carousel-btn"
                            onclick="scrollEvents('upcoming-carousel', 1)"
                        >
                            →
                        </button>

                    </div>

                <?php endif; ?>

            </div>


            <?php if (count($upcoming_events) > 0): ?>

                <div
                    class="event-carousel"
                    id="upcoming-carousel"
                >

                    <?php foreach ($upcoming_events as $event): ?>

                        <?php

                        $image = eventImage($event['image'] ?? null);

                        $price = $event['price'] ?? 0;

                        $eventDate = strtotime($event['event_date']);

                        ?>

                        <a
                            href="event-details.php?id=<?= (int)$event['id'] ?>"
                            class="event-card"
                        >

                            <div class="event-image-wrapper">

                                <img
                                    src="<?= htmlspecialchars($image) ?>"
                                    alt="<?= htmlspecialchars($event['title']) ?>"
                                    class="event-image"
                                    loading="lazy"
                                    onerror="this.src='assets/images/event-placeholder.jpg';"
                                >


                                <div class="event-date-badge">

                                    <span class="day">
                                        <?= date("d", $eventDate) ?>
                                    </span>

                                    <span class="month">
                                        <?= date("M", $eventDate) ?>
                                    </span>

                                </div>

                            </div>


                            <div class="event-content">

                                <h3>
                                    <?= htmlspecialchars($event['title']) ?>
                                </h3>


                                <p class="event-description">

                                    <?= htmlspecialchars(
                                        $event['description'] ?? 'Join us for this exciting event.'
                                    ) ?>

                                </p>


                                <div class="event-info">

                                    <div class="event-info-row">

                                        <span class="event-info-icon">
                                            📅
                                        </span>

                                        <?= formatEventDate($event['event_date']) ?>

                                    </div>


                                    <?php if (!empty($event['start_time'])): ?>

                                        <div class="event-info-row">

                                            <span class="event-info-icon">
                                                🕐
                                            </span>

                                            <?= formatEventTime($event['start_time']) ?>

                                            <?php if (!empty($event['end_time'])): ?>

                                                -
                                                <?= formatEventTime($event['end_time']) ?>

                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="event-info-row">

                                        <span class="event-info-icon">
                                            📍
                                        </span>

                                        <?= htmlspecialchars($event['venue'] ?? 'Venue TBA') ?>

                                        <?php if (!empty($event['city'])): ?>

                                            , <?= htmlspecialchars($event['city']) ?>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <div class="event-card-footer">

                                    <?php if ((float)$price > 0): ?>

                                        <span class="event-price">
                                            ₹<?= number_format((float)$price, 2) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="event-price free">
                                            FREE
                                        </span>

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

                    <div class="empty-events-icon">
                        📅
                    </div>

                    <h3>
                        No Upcoming Events
                    </h3>

                    <p>
                        New events will appear here when they are added.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- ==================================================
         PAST EVENTS
    ================================================== -->

    <section
        class="event-section"
        id="past"
    >

        <div class="events-container">

            <div class="section-header">

                <div class="section-title-wrapper">

                    <h2>
                        Past Events
                    </h2>

                    <p>
                        Explore memories from our previous events.
                    </p>

                    <div class="section-title-line"></div>

                </div>


                <?php if (count($past_events) > 1): ?>

                    <div class="carousel-buttons">

                        <button
                            type="button"
                            class="carousel-btn"
                            onclick="scrollEvents('past-carousel', -1)"
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            class="carousel-btn"
                            onclick="scrollEvents('past-carousel', 1)"
                        >
                            →
                        </button>

                    </div>

                <?php endif; ?>

            </div>


            <?php if (count($past_events) > 0): ?>

                <div
                    class="event-carousel"
                    id="past-carousel"
                >

                    <?php foreach ($past_events as $event): ?>

                        <?php

                        $image = eventImage($event['image'] ?? null);

                        $price = $event['price'] ?? 0;

                        $eventDate = strtotime($event['event_date']);

                        ?>

                        <a
                            href="event-details.php?id=<?= (int)$event['id'] ?>"
                            class="event-card"
                        >

                            <div class="event-image-wrapper">

                                <img
                                    src="<?= htmlspecialchars($image) ?>"
                                    alt="<?= htmlspecialchars($event['title']) ?>"
                                    class="event-image"
                                    loading="lazy"
                                    onerror="this.src='assets/images/event-placeholder.jpg';"
                                >


                                <div class="event-date-badge">

                                    <span class="day">
                                        <?= date("d", $eventDate) ?>
                                    </span>

                                    <span class="month">
                                        <?= date("M", $eventDate) ?>
                                    </span>

                                </div>

                            </div>


                            <div class="event-content">

                                <h3>
                                    <?= htmlspecialchars($event['title']) ?>
                                </h3>


                                <p class="event-description">

                                    <?= htmlspecialchars(
                                        $event['description'] ?? 'Event information'
                                    ) ?>

                                </p>


                                <div class="event-info">

                                    <div class="event-info-row">

                                        <span class="event-info-icon">
                                            📅
                                        </span>

                                        <?= formatEventDate($event['event_date']) ?>

                                    </div>


                                    <?php if (!empty($event['start_time'])): ?>

                                        <div class="event-info-row">

                                            <span class="event-info-icon">
                                                🕐
                                            </span>

                                            <?= formatEventTime($event['start_time']) ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="event-info-row">

                                        <span class="event-info-icon">
                                            📍
                                        </span>

                                        <?= htmlspecialchars($event['venue'] ?? 'Venue TBA') ?>

                                        <?php if (!empty($event['city'])): ?>

                                            , <?= htmlspecialchars($event['city']) ?>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <div class="event-card-footer">

                                    <?php if ((float)$price > 0): ?>

                                        <span class="event-price">
                                            ₹<?= number_format((float)$price, 2) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="event-price free">
                                            FREE
                                        </span>

                                    <?php endif; ?>


                                    <span class="past-badge">
                                        EVENT COMPLETED
                                    </span>

                                </div>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-events">

                    <div class="empty-events-icon">
                        🗓️
                    </div>

                    <h3>
                        No Past Events
                    </h3>

                    <p>
                        Previous events will appear here.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<script>

    // ==================================================
    // CAROUSEL BUTTONS
    // ==================================================

    function scrollEvents(carouselId, direction) {

        const carousel = document.getElementById(carouselId);

        if (!carousel) {
            return;
        }

        const card = carousel.querySelector(".event-card");

        if (!card) {
            return;
        }

        const gap = 22;

        const scrollAmount =
            card.offsetWidth + gap;

        carousel.scrollBy({
            left: direction * scrollAmount,
            behavior: "smooth"
        });

    }


    // ==================================================
    // AUTOMATIC RIGHT → LEFT SCROLL
    // ==================================================

    function startAutoScroll(carouselId) {

        const carousel =
            document.getElementById(carouselId);

        if (!carousel) {
            return;
        }


        let paused = false;


        carousel.addEventListener(
            "mouseenter",
            function () {
                paused = true;
            }
        );


        carousel.addEventListener(
            "mouseleave",
            function () {
                paused = false;
            }
        );


        setInterval(function () {

            if (paused) {
                return;
            }


            const maxScroll =
                carousel.scrollWidth -
                carousel.clientWidth;


            if (carousel.scrollLeft >= maxScroll - 5) {

                carousel.scrollTo({
                    left: 0,
                    behavior: "smooth"
                });

            } else {

                const card =
                    carousel.querySelector(".event-card");

                if (!card) {
                    return;
                }


                carousel.scrollBy({
                    left: card.offsetWidth + 22,
                    behavior: "smooth"
                });

            }

        }, 3000);

    }


    // Start both carousels

    startAutoScroll("upcoming-carousel");

    startAutoScroll("past-carousel");

</script>


<?php

require_once "footer.php";

?>