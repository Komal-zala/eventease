<?php
session_start();

$page_title = "EventEase | Discover Amazing Events";

$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

include './header.php';
?>

<style>
:root {
    --ee-primary: #7c3aed;
    --ee-primary-light: #a855f7;
    --ee-secondary: #d946ef;
    --ee-pink: #ec4899;
    --ee-cyan: #22d3ee;
    --ee-blue: #6366f1;
    --ee-gold: #fbbf24;
    --ee-dark: #160d24;
    --ee-dark-soft: #241336;
    --ee-text: #33283d;
    --ee-muted: #766b80;
    --ee-bg: #faf8ff;
    --ee-white: #ffffff;
    --ee-border: rgba(124, 58, 237, 0.10);
    --ee-gradient: linear-gradient(135deg, #6d28d9, #9333ea 48%, #d946ef);
    --ee-gradient-dark: linear-gradient(135deg, #1b0c2b, #4c1d75 55%, #86198f);
    --ee-shadow: 0 20px 60px rgba(74, 32, 113, 0.10);
    --ee-shadow-hover: 0 30px 80px rgba(74, 32, 113, 0.18);
}

html {
    scroll-behavior: smooth;
}

.index-page {
    position: relative;
    overflow: hidden;
}

.index-page *,
.index-page *::before,
.index-page *::after {
    box-sizing: border-box;
}

.index-page a {
    text-decoration: none;
}

/* HERO */

.ee-hero {
    position: relative;
    min-height: 690px;
    display: flex;
    align-items: center;
    padding: 90px 24px 135px;
    overflow: hidden;
    isolation: isolate;
    color: #fff;
    background:
        radial-gradient(circle at 78% 22%, rgba(217,70,239,.35), transparent 25%),
        radial-gradient(circle at 15% 80%, rgba(124,58,237,.35), transparent 30%),
        linear-gradient(120deg, #170b25 0%, #2d1248 42%, #641c82 72%, #92278e 100%);
}

.ee-hero::before {
    content: "";
    position: absolute;
    width: 650px;
    height: 650px;
    top: -340px;
    right: -170px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 50%;
    box-shadow:
        0 0 0 70px rgba(255,255,255,.018),
        0 0 0 140px rgba(255,255,255,.012);
}

.ee-hero::after {
    content: "";
    position: absolute;
    width: 500px;
    height: 500px;
    bottom: -360px;
    left: -170px;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 50%;
}

.ee-hero-grid {
    position: relative;
    z-index: 3;
    width: min(1200px,100%);
    margin: auto;
    display: grid;
    grid-template-columns: 1.08fr .92fr;
    align-items: center;
    gap: 70px;
}

.ee-hero-content {
    max-width: 680px;
}

.ee-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 15px;
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 100px;
    background: rgba(255,255,255,.08);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
    backdrop-filter: blur(14px);
    color: #f7eaff;
    font-size: 12px;
    font-weight: 800;
}

.ee-live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d8ff8b;
    box-shadow: 0 0 12px #d8ff8b;
    animation: livePulse 1.8s infinite;
}

@keyframes livePulse {
    0%,100% {
        box-shadow: 0 0 7px #d8ff8b;
    }
    50% {
        box-shadow: 0 0 20px #d8ff8b;
    }
}

.ee-hero-title {
    margin: 0 0 25px;
    font-size: clamp(46px,5.5vw,72px);
    line-height: 1.02;
    letter-spacing: -3.2px;
    font-weight: 900;
}

.ee-hero-title .gradient-text {
    display: inline-block;
    background: linear-gradient(90deg,#fff,#f0abff 55%,#ffc5ee);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.ee-hero-description {
    max-width: 600px;
    margin: 0 0 34px;
    color: rgba(255,255,255,.78);
    font-size: 17px;
    line-height: 1.85;
}

.ee-hero-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 13px;
}

.ee-btn-primary,
.ee-btn-secondary {
    min-height: 51px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 22px;
    border-radius: 13px;
    font-size: 14px;
    font-weight: 800;
    transition: .3s ease;
}

.ee-btn-primary {
    color: #6720a8;
    background: #fff;
    box-shadow: 0 12px 30px rgba(0,0,0,.20);
}

.ee-btn-primary:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px rgba(0,0,0,.28);
}

.ee-btn-secondary {
    border: 1px solid rgba(255,255,255,.24);
    background: rgba(255,255,255,.06);
    color: #fff;
    backdrop-filter: blur(10px);
}

.ee-btn-secondary:hover {
    transform: translateY(-4px);
    background: rgba(255,255,255,.12);
}

/* PREMIUM HERO VISUAL */

.ee-visual {
    position: relative;
    min-height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ee-glow {
    position: absolute;
    width: 350px;
    height: 350px;
    border-radius: 50%;
    background: rgba(217,70,239,.24);
    filter: blur(70px);
    animation: glowPulse 4s ease-in-out infinite;
}

@keyframes glowPulse {
    0%,100% {
        transform: scale(.92);
        opacity: .65;
    }
    50% {
        transform: scale(1.08);
        opacity: 1;
    }
}

.ee-orbit {
    position: absolute;
    width: 420px;
    height: 420px;
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 50%;
    animation: eeRotate 20s linear infinite;
}

.ee-orbit::before,
.ee-orbit::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: #f2a7ff;
    box-shadow: 0 0 25px rgba(242,167,255,.9);
}

.ee-orbit::before {
    width: 11px;
    height: 11px;
    top: 55px;
    right: 25px;
}

.ee-orbit::after {
    width: 7px;
    height: 7px;
    bottom: 35px;
    left: 42px;
}

@keyframes eeRotate {
    from {
        transform: rotate(0);
    }
    to {
        transform: rotate(360deg);
    }
}

.ee-event-showcase {
    position: relative;
    z-index: 4;
    width: 330px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.19);
    border-radius: 30px;
    background: rgba(255,255,255,.095);
    box-shadow:
        0 40px 90px rgba(0,0,0,.30),
        inset 0 1px 0 rgba(255,255,255,.15);
    backdrop-filter: blur(24px);
    transform: rotate(2deg);
    transition: .5s ease;
}

.ee-event-showcase:hover {
    transform: rotate(0) translateY(-10px) scale(1.02);
}

.ee-showcase-cover {
    height: 215px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 30% 30%,rgba(255,255,255,.28),transparent 18%),
        radial-gradient(circle at 75% 65%,rgba(255,190,255,.25),transparent 25%),
        linear-gradient(135deg,#4c1d95,#a21caf 50%,#db2777);
}

.ee-showcase-cover::before,
.ee-showcase-cover::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,.17);
}

.ee-showcase-cover::before {
    width: 250px;
    height: 250px;
}

.ee-showcase-cover::after {
    width: 165px;
    height: 165px;
}

.ee-showcase-emoji {
    position: relative;
    z-index: 3;
    width: 108px;
    height: 108px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 32px;
    background: rgba(255,255,255,.12);
    box-shadow:
        0 25px 45px rgba(0,0,0,.22),
        inset 0 1px 0 rgba(255,255,255,.25);
    backdrop-filter: blur(14px);
    font-size: 64px;
    filter: drop-shadow(0 15px 20px rgba(0,0,0,.28));
    animation: premiumFloat 4s ease-in-out infinite;
}

@keyframes premiumFloat {
    0%,100% {
        transform: translateY(0) rotate(-2deg);
    }
    50% {
        transform: translateY(-10px) rotate(2deg);
    }
}

.ee-showcase-content {
    padding: 25px;
}

.ee-showcase-label {
    margin-bottom: 8px;
    color: #e9c7f7;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
    text-transform: uppercase;
}

.ee-showcase-content h3 {
    margin: 0 0 11px;
    color: #fff;
    font-size: 23px;
}

.ee-showcase-meta {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    color: #dfd2e9;
    font-size: 11px;
}

.ee-floating-card {
    position: absolute;
    z-index: 5;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 12px 15px;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 15px;
    background: rgba(25,10,39,.60);
    box-shadow: 0 20px 50px rgba(0,0,0,.25);
    backdrop-filter: blur(18px);
}

.ee-floating-card strong {
    display: block;
    margin-bottom: 2px;
    color: #fff;
    font-size: 12px;
}

.ee-floating-card span {
    color: #cfc1da;
    font-size: 10px;
}

.ee-floating-icon {
    width: 39px;
    height: 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 11px;
    background: linear-gradient(
        135deg,
        rgba(124,58,237,.30),
        rgba(217,70,239,.18)
    );
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.15),
        0 8px 20px rgba(0,0,0,.18);
    font-size: 19px;
}

.ee-floating-one {
    top: 52px;
    right: 4px;
    animation: eeFloat 4s ease-in-out infinite;
}

.ee-floating-two {
    bottom: 48px;
    left: -5px;
    animation: eeFloat 4.5s ease-in-out infinite reverse;
}

@keyframes eeFloat {
    0%,100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-11px);
    }
}

/* SEARCH */

.ee-search-area {
    position: relative;
    z-index: 20;
    width: min(1030px,calc(100% - 30px));
    margin: -42px auto 0;
}

.ee-search-box {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    padding: 10px;
    border: 1px solid rgba(124,58,237,.09);
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 25px 60px rgba(57,25,87,.15);
}

.ee-search-input-wrap {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
    padding: 0 15px;
}

.ee-search-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #f4ebff;
    color: #7434bc;
    font-size: 21px;
}

.ee-search-input {
    width: 100%;
    height: 52px;
    border: 0;
    outline: 0;
    color: var(--ee-text);
    background: transparent;
    font-size: 14px;
}

.ee-search-input::placeholder {
    color: #a49aa9;
}

.ee-search-button {
    height: 52px;
    padding: 0 28px;
    border: 0;
    border-radius: 12px;
    background: var(--ee-gradient);
    color: #fff;
    cursor: pointer;
    font-size: 13px;
    font-weight: 800;
    transition: .3s ease;
}

.ee-search-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(124,58,237,.28);
}

/* SECTIONS */

.ee-section {
    width: min(1200px,calc(100% - 48px));
    margin: auto;
    padding: 100px 0;
}

.ee-section-heading {
    max-width: 690px;
    margin: 0 auto 48px;
    text-align: center;
}

.ee-eyebrow {
    display: inline-block;
    margin-bottom: 10px;
    color: var(--ee-primary);
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.ee-section-heading h2 {
    margin: 0 0 12px;
    color: var(--ee-dark);
    font-size: clamp(29px,4vw,39px);
    line-height: 1.15;
    letter-spacing: -1.1px;
}

.ee-section-heading p {
    margin: 0;
    color: var(--ee-muted);
    font-size: 14px;
    line-height: 1.8;
}

/* CATEGORIES */

.ee-category-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 20px;
}

.ee-category {
    position: relative;
    overflow: hidden;
    padding: 30px 23px;
    border: 1px solid var(--ee-border);
    border-radius: 23px;
    background: #fff;
    box-shadow: 0 8px 28px rgba(60,30,100,.04);
    transition: .4s ease;
}

.ee-category::before {
    content: "";
    position: absolute;
    top: -70px;
    right: -70px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(124,58,237,.07);
    filter: blur(15px);
    transition: .4s ease;
}

.ee-category::after {
    content: "";
    position: absolute;
    width: 100px;
    height: 100px;
    right: -55px;
    bottom: -55px;
    border-radius: 50%;
    background: rgba(217,70,239,.05);
    transition: .4s ease;
}

.ee-category:hover {
    transform: translateY(-9px);
    border-color: rgba(124,58,237,.18);
    box-shadow: var(--ee-shadow-hover);
}

.ee-category:hover::before {
    transform: scale(1.5);
}

.ee-category:hover::after {
    transform: scale(2.2);
}

.ee-category-icon {
    position: relative;
    width: 72px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 22px;
    border: 1px solid rgba(124,58,237,.10);
    border-radius: 21px;
    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f5edff 48%,
            #fce7f3
        );
    box-shadow:
        0 14px 30px rgba(104,45,154,.10),
        inset 0 1px 0 #fff;
    font-size: 30px;
    transition: .4s ease;
}

.ee-category-icon::before {
    content: "";
    position: absolute;
    inset: 7px;
    border: 1px solid rgba(124,58,237,.07);
    border-radius: 16px;
}

.ee-category-icon::after {
    content: "";
    position: absolute;
    width: 8px;
    height: 8px;
    top: 7px;
    right: 8px;
    border-radius: 50%;
    background: #c084fc;
    box-shadow: 0 0 12px rgba(192,132,252,.8);
}

.ee-category:hover .ee-category-icon {
    transform: translateY(-4px) scale(1.08) rotate(-3deg);
    box-shadow: 0 20px 38px rgba(104,45,154,.16);
}

.ee-category h3 {
    position: relative;
    z-index: 2;
    margin: 0 0 8px;
    color: var(--ee-dark);
    font-size: 17px;
}

.ee-category p {
    position: relative;
    z-index: 2;
    margin: 0;
    color: var(--ee-muted);
    font-size: 12px;
    line-height: 1.65;
}

/* EVENTS */

.ee-events-section {
    width: min(1250px,calc(100% - 48px));
}

.ee-event-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 25px;
}

.ee-event-card {
    overflow: hidden;
    border: 1px solid var(--ee-border);
    border-radius: 24px;
    background: #fff;
    box-shadow: var(--ee-shadow);
    transition: .4s ease;
}

.ee-event-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--ee-shadow-hover);
}

.ee-event-image {
    position: relative;
    height: 195px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background:
        radial-gradient(circle at 25% 20%,rgba(255,255,255,.8),transparent 14%),
        radial-gradient(circle at 80% 70%,rgba(217,70,239,.13),transparent 30%),
        linear-gradient(135deg,#ede5ff,#fce7f3);
}

.ee-event-image::before {
    content: "";
    position: absolute;
    width: 190px;
    height: 190px;
    border: 1px solid rgba(124,58,237,.10);
    border-radius: 50%;
    box-shadow:
        0 0 0 28px rgba(124,58,237,.025),
        0 0 0 58px rgba(124,58,237,.018);
}

.ee-event-image::after {
    content: "";
    position: absolute;
    width: 115px;
    height: 115px;
    border: 1px solid rgba(217,70,239,.12);
    border-radius: 50%;
}

.ee-event-icon {
    position: relative;
    z-index: 3;
    width: 90px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.9);
    border-radius: 27px;
    background: rgba(255,255,255,.62);
    box-shadow:
        0 18px 38px rgba(90,42,128,.13),
        inset 0 1px 0 #fff;
    backdrop-filter: blur(12px);
    font-size: 42px;
    transition: .4s ease;
}

.ee-event-card:hover .ee-event-icon {
    transform: scale(1.12) translateY(-5px) rotate(-3deg);
    box-shadow: 0 25px 45px rgba(90,42,128,.20);
}

.ee-event-tag {
    position: absolute;
    z-index: 5;
    top: 15px;
    left: 15px;
    padding: 7px 11px;
    border: 1px solid rgba(255,255,255,.7);
    border-radius: 8px;
    background: rgba(255,255,255,.87);
    color: #6d32bd;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .6px;
    text-transform: uppercase;
    backdrop-filter: blur(8px);
}

.ee-event-content {
    padding: 23px;
}

.ee-event-date {
    margin-bottom: 8px;
    color: var(--ee-primary);
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .7px;
}

.ee-event-title {
    margin: 0 0 11px;
    color: var(--ee-dark);
    font-size: 20px;
    line-height: 1.3;
}

.ee-event-location {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--ee-muted);
    font-size: 12px;
}

.ee-event-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 21px;
    padding-top: 18px;
    border-top: 1px solid #f0ebf5;
}

.ee-price small {
    display: block;
    margin-bottom: 2px;
    color: #aaa0b1;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
}

.ee-price strong {
    color: #6829ae;
    font-size: 20px;
}

.ee-book {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 15px;
    border-radius: 10px;
    background: #f2eaff;
    color: #7139bb;
    font-size: 11px;
    font-weight: 900;
    transition: .25s ease;
}

.ee-book:hover {
    color: #fff;
    background: var(--ee-gradient);
}

/* STATS */

.ee-stats-section {
    width: min(1150px,calc(100% - 48px));
    margin: 10px auto 0;
    padding: 35px 20px;
    border: 1px solid rgba(124,58,237,.10);
    border-radius: 25px;
    background: #fff;
    box-shadow: 0 15px 45px rgba(65,32,96,.07);
}

.ee-stats-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
}

.ee-stat {
    position: relative;
    text-align: center;
    padding: 10px 20px;
}

.ee-stat:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 10%;
    right: 0;
    width: 1px;
    height: 80%;
    background: #eee7f3;
}

.ee-stat strong {
    display: block;
    margin-bottom: 5px;
    color: var(--ee-dark);
    font-size: 28px;
    font-weight: 900;
}

.ee-stat span {
    color: var(--ee-muted);
    font-size: 11px;
    font-weight: 700;
}

/* FEATURE */

.ee-feature-section {
    position: relative;
}

.ee-feature-layout {
    display: grid;
    grid-template-columns: .9fr 1.1fr;
    align-items: center;
    gap: 80px;
}

.ee-feature-visual {
    position: relative;
    min-height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ee-feature-main {
    position: relative;
    z-index: 3;
    width: 300px;
    padding: 30px;
    border-radius: 28px;
    color: #fff;
    background: var(--ee-gradient-dark);
    box-shadow: 0 30px 70px rgba(61,24,92,.22);
}

.ee-feature-main-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 35px;
}

.ee-feature-main-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #cdbbd9;
}

.ee-feature-check {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255,255,255,.11);
}

.ee-feature-main h3 {
    margin: 0 0 10px;
    font-size: 27px;
}

.ee-feature-main p {
    margin: 0 0 28px;
    color: #d9cde0;
    font-size: 12px;
    line-height: 1.7;
}

.ee-progress {
    height: 7px;
    overflow: hidden;
    border-radius: 10px;
    background: rgba(255,255,255,.10);
}

.ee-progress span {
    display: block;
    width: 78%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg,#d8a4ff,#ff9fe8);
}

.ee-mini-card {
    position: absolute;
    z-index: 4;
    padding: 15px;
    border: 1px solid rgba(124,58,237,.10);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 20px 45px rgba(60,30,100,.12);
}

.ee-mini-card strong {
    display: block;
    color: var(--ee-dark);
    font-size: 13px;
}

.ee-mini-card span {
    display: block;
    margin-top: 4px;
    color: var(--ee-muted);
    font-size: 10px;
}

.ee-mini-one {
    top: 38px;
    right: 15px;
}

.ee-mini-two {
    bottom: 42px;
    left: 10px;
}

.ee-feature-content {
    max-width: 560px;
}

.ee-feature-content .ee-section-heading {
    margin: 0 0 30px;
    text-align: left;
}

.ee-feature-list {
    display: grid;
    gap: 15px;
}

.ee-feature-item {
    display: grid;
    grid-template-columns: 50px 1fr;
    gap: 15px;
    padding: 17px;
    border: 1px solid transparent;
    border-radius: 17px;
    transition: .3s ease;
}

.ee-feature-item:hover {
    border-color: var(--ee-border);
    background: #fff;
    box-shadow: 0 12px 30px rgba(60,30,100,.06);
}

.ee-feature-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(124,58,237,.08);
    border-radius: 15px;
    background: linear-gradient(145deg,#fff,#f3eaff);
    box-shadow: 0 10px 25px rgba(91,33,182,.08);
    font-size: 22px;
}

/* CTA */

.ee-cta {
    position: relative;
    width: min(1150px,calc(100% - 48px));
    margin: 30px auto 95px;
    padding: 80px 30px;
    overflow: hidden;
    border-radius: 32px;
    color: #fff;
    text-align: center;
    background:
        radial-gradient(circle at 20% 25%,rgba(255,255,255,.12),transparent 22%),
        radial-gradient(circle at 80% 75%,rgba(255,255,255,.12),transparent 22%),
        linear-gradient(120deg,#32104c,#7621a9 50%,#ce2ccf);
    box-shadow: 0 25px 70px rgba(89,34,126,.18);
}

.ee-cta::before,
.ee-cta::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 50%;
}

.ee-cta::before {
    width: 350px;
    height: 350px;
    top: -240px;
    left: -100px;
}

.ee-cta::after {
    width: 300px;
    height: 300px;
    right: -100px;
    bottom: -220px;
}

.ee-cta-content {
    position: relative;
    z-index: 2;
}

.ee-cta h2 {
    margin: 0 0 13px;
    font-size: clamp(29px,4vw,40px);
    letter-spacing: -1px;
}

.ee-cta p {
    max-width: 600px;
    margin: 0 auto 30px;
    color: rgba(255,255,255,.77);
    font-size: 14px;
    line-height: 1.8;
}

.ee-cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    padding: 0 23px;
    border-radius: 12px;
    background: #fff;
    color: #7228b0;
    font-size: 13px;
    font-weight: 900;
    box-shadow: 0 15px 30px rgba(0,0,0,.16);
    transition: .3s ease;
}

.ee-cta-button:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,.23);
}

/* RESPONSIVE */

@media (max-width:1050px) {
    .ee-hero-grid {
        gap: 35px;
    }

    .ee-hero-title {
        font-size: 58px;
    }

    .ee-category-grid {
        grid-template-columns: repeat(2,1fr);
    }

    .ee-feature-layout {
        gap: 50px;
    }
}

@media (max-width:850px) {
    .ee-hero {
        min-height: auto;
        padding: 75px 22px 115px;
    }

    .ee-hero-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .ee-hero-content {
        margin: auto;
    }

    .ee-hero-description {
        margin-left: auto;
        margin-right: auto;
    }

    .ee-hero-actions {
        justify-content: center;
    }

    .ee-visual {
        min-height: 400px;
    }

    .ee-event-grid {
        grid-template-columns: repeat(2,1fr);
    }

    .ee-stats-grid {
        grid-template-columns: repeat(2,1fr);
        gap: 25px 0;
    }

    .ee-stat:nth-child(2)::after {
        display: none;
    }

    .ee-feature-layout {
        grid-template-columns: 1fr;
    }

    .ee-feature-visual {
        order: 2;
    }

    .ee-feature-content {
        max-width: 100%;
    }

    .ee-feature-content .ee-section-heading {
        text-align: center;
    }
}

@media (max-width:600px) {
    .ee-hero {
        padding: 65px 18px 105px;
    }

    .ee-hero-title {
        font-size: 43px;
        letter-spacing: -2px;
    }

    .ee-hero-description {
        font-size: 14px;
    }

    .ee-hero-actions {
        flex-direction: column;
        width: 100%;
    }

    .ee-btn-primary,
    .ee-btn-secondary {
        width: 100%;
    }

    .ee-visual {
        min-height: 350px;
    }

    .ee-orbit {
        width: 290px;
        height: 290px;
    }

    .ee-event-showcase {
        width: 270px;
    }

    .ee-showcase-cover {
        height: 170px;
    }

    .ee-showcase-emoji {
        width: 88px;
        height: 88px;
        font-size: 50px;
        border-radius: 25px;
    }

    .ee-floating-one {
        right: -2px;
        top: 30px;
    }

    .ee-floating-two {
        left: -5px;
        bottom: 25px;
    }

    .ee-search-area {
        width: calc(100% - 24px);
    }

    .ee-search-box {
        grid-template-columns: 1fr;
    }

    .ee-search-button {
        width: 100%;
    }

    .ee-section,
    .ee-events-section {
        width: calc(100% - 36px);
        padding: 70px 0;
    }

    .ee-category-grid,
    .ee-event-grid {
        grid-template-columns: 1fr;
    }

    .ee-stats-section {
        width: calc(100% - 36px);
        padding: 25px 10px;
    }

    .ee-stat strong {
        font-size: 23px;
    }

    .ee-feature-visual {
        min-height: 350px;
    }

    .ee-feature-main {
        width: 270px;
    }

    .ee-mini-one {
        right: 0;
    }

    .ee-mini-two {
        left: 0;
    }

    .ee-cta {
        width: calc(100% - 30px);
        margin-bottom: 65px;
        padding: 60px 22px;
        border-radius: 25px;
    }
}

@media (max-width:390px) {
    .ee-hero-title {
        font-size: 38px;
    }

    .ee-floating-card {
        padding: 9px 11px;
    }

    .ee-floating-card span {
        display: none;
    }

    .ee-event-showcase {
        width: 250px;
    }
}
</style>

<main class="index-page">

    <section class="ee-hero" id="home">

        <div class="ee-hero-grid">

            <div class="ee-hero-content">

                <div class="ee-hero-badge">
                    <span class="ee-live-dot"></span>
                    Your Event Journey Starts Here
                </div>

                <h1 class="ee-hero-title">
                    Discover Events.<br>
                    <span class="gradient-text">Create Memories.</span>
                </h1>

                <p class="ee-hero-description">
                    Discover concerts, exhibitions, business conferences,
                    sports and unforgettable experiences happening around you.
                    EventEase brings discovery and booking together in one place.
                </p>

                <div class="ee-hero-actions">

                    <a href="#events" class="ee-btn-primary">
                        Explore Events
                        <span>→</span>
                    </a>

                    <?php if ($is_logged_in): ?>

                        <a href="events.php" class="ee-btn-secondary">
                            Explore Events
                        </a>

                    <?php else: ?>

                        <a href="register.php" class="ee-btn-secondary">
                            Join EventEase
                        </a>

                    <?php endif; ?>

                </div>

            </div>

            <div class="ee-visual">

                <div class="ee-glow"></div>
                <div class="ee-orbit"></div>

                <div class="ee-event-showcase">

                    <div class="ee-showcase-cover">
                        <span class="ee-showcase-emoji">🎉</span>
                    </div>

                    <div class="ee-showcase-content">

                        <div class="ee-showcase-label">
                            Featured Experience
                        </div>

                        <h3>Live Music Festival</h3>

                        <div class="ee-showcase-meta">
                            <span>📍 Ahmedabad</span>
                            <span>15 Oct 2026</span>
                        </div>

                    </div>

                </div>

                <div class="ee-floating-card ee-floating-one">

                    <div class="ee-floating-icon">
                        🎟️
                    </div>

                    <div>
                        <strong>Easy Booking</strong>
                        <span>Book in a few clicks</span>
                    </div>

                </div>

                <div class="ee-floating-card ee-floating-two">

                    <div class="ee-floating-icon">
                        ✨
                    </div>

                    <div>
                        <strong>Great Experiences</strong>
                        <span>Discover what's next</span>
                    </div>

                </div>

            </div>

        </div>

    </section>

    <section class="ee-search-area" aria-label="Event Search">

        <form class="ee-search-box" action="events.php" method="GET">

            <div class="ee-search-input-wrap">

                <span class="ee-search-icon">⌕</span>

                <input
                    type="search"
                    name="search"
                    class="ee-search-input"
                    placeholder="Search events, categories or locations..."
                    aria-label="Search events"
                >

            </div>

            <button type="submit" class="ee-search-button">
                Search Events
            </button>

        </form>

    </section>

    <section class="ee-section" id="categories">

        <div class="ee-section-heading">

            <span class="ee-eyebrow">
                Explore
            </span>

            <h2>
                Find Your Kind of Experience
            </h2>

            <p>
                Explore popular categories and discover events
                that fit your interests and lifestyle.
            </p>

        </div>

        <div class="ee-category-grid">

            <a href="events.php?category=music" class="ee-category">

                <div class="ee-category-icon">
                    🎵
                </div>

                <h3>
                    Music & Festivals
                </h3>

                <p>
                    Concerts, festivals and live performances.
                </p>

            </a>

            <a href="events.php?category=arts" class="ee-category">

                <div class="ee-category-icon">
                    🎨
                </div>

                <h3>
                    Arts & Culture
                </h3>

                <p>
                    Exhibitions, creative showcases and cultural events.
                </p>

            </a>

            <a href="events.php?category=business" class="ee-category">

                <div class="ee-category-icon">
                    💼
                </div>

                <h3>
                    Business
                </h3>

                <p>
                    Conferences, seminars and professional networking.
                </p>

            </a>

            <a href="events.php?category=sports" class="ee-category">

                <div class="ee-category-icon">
                    🏆
                </div>

                <h3>
                    Sports
                </h3>

                <p>
                    Matches, competitions and sporting experiences.
                </p>

            </a>

        </div>

    </section>

    <section class="ee-section ee-events-section" id="events">

        <div class="ee-section-heading">

            <span class="ee-eyebrow">
                What's Happening
            </span>

            <h2>
                Upcoming Events
            </h2>

            <p>
                Explore selected upcoming events and discover
                something worth experiencing.
            </p>

        </div>

        <div class="ee-event-grid">

            <article class="ee-event-card">

                <div class="ee-event-image">

                    <span class="ee-event-tag">
                        Music
                    </span>

                    <span class="ee-event-icon">
                        🎵
                    </span>

                </div>

                <div class="ee-event-content">

                    <div class="ee-event-date">
                        15 OCT 2026
                    </div>

                    <h3 class="ee-event-title">
                        Music Festival
                    </h3>

                    <div class="ee-event-location">
                        <span>📍</span>
                        <span>Ahmedabad, Gujarat</span>
                    </div>

                    <div class="ee-event-bottom">

                        <div class="ee-price">
                            <small>Starting from</small>
                            <strong>₹499</strong>
                        </div>

                        <?php if ($is_logged_in): ?>

                            <a href="events.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php else: ?>

                            <a href="login.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </article>

            <article class="ee-event-card">

                <div class="ee-event-image">

                    <span class="ee-event-tag">
                        Business
                    </span>

                    <span class="ee-event-icon">
                        💼
                    </span>

                </div>

                <div class="ee-event-content">

                    <div class="ee-event-date">
                        22 OCT 2026
                    </div>

                    <h3 class="ee-event-title">
                        Business Summit
                    </h3>

                    <div class="ee-event-location">
                        <span>📍</span>
                        <span>Rajkot, Gujarat</span>
                    </div>

                    <div class="ee-event-bottom">

                        <div class="ee-price">
                            <small>Starting from</small>
                            <strong>₹799</strong>
                        </div>

                        <?php if ($is_logged_in): ?>

                            <a href="events.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php else: ?>

                            <a href="login.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </article>

            <article class="ee-event-card">

                <div class="ee-event-image">

                    <span class="ee-event-tag">
                        Arts
                    </span>

                    <span class="ee-event-icon">
                        🎨
                    </span>

                </div>

                <div class="ee-event-content">

                    <div class="ee-event-date">
                        05 NOV 2026
                    </div>

                    <h3 class="ee-event-title">
                        Art Exhibition
                    </h3>

                    <div class="ee-event-location">
                        <span>📍</span>
                        <span>Vadodara, Gujarat</span>
                    </div>

                    <div class="ee-event-bottom">

                        <div class="ee-price">
                            <small>Starting from</small>
                            <strong>₹299</strong>
                        </div>

                        <?php if ($is_logged_in): ?>

                            <a href="events.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php else: ?>

                            <a href="login.php" class="ee-book">
                                Book Now →
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </article>

        </div>

    </section>

    <section class="ee-stats-section">

        <div class="ee-stats-grid">

            <div class="ee-stat">
                <strong>500+</strong>
                <span>Events Listed</span>
            </div>

            <div class="ee-stat">
                <strong>10K+</strong>
                <span>Happy Attendees</span>
            </div>

            <div class="ee-stat">
                <strong>50+</strong>
                <span>Event Organizers</span>
            </div>

            <div class="ee-stat">
                <strong>20+</strong>
                <span>Locations Covered</span>
            </div>

        </div>

    </section>

    <section class="ee-section ee-feature-section" id="about">

        <div class="ee-feature-layout">

            <div class="ee-feature-visual">

                <div class="ee-feature-main">

                    <div class="ee-feature-main-top">

                        <span class="ee-feature-main-label">
                            EventEase Experience
                        </span>

                        <span class="ee-feature-check">
                            ✓
                        </span>

                    </div>

                    <h3>
                        Everything in One Place
                    </h3>

                    <p>
                        Discover, choose and manage your event
                        experience with a simple digital journey.
                    </p>

                    <div class="ee-progress">
                        <span></span>
                    </div>

                </div>

                <div class="ee-mini-card ee-mini-one">
                    <strong>Digital Tickets</strong>
                    <span>Always accessible</span>
                </div>

                <div class="ee-mini-card ee-mini-two">
                    <strong>Simple Booking</strong>
                    <span>Fast & convenient</span>
                </div>

            </div>

            <div class="ee-feature-content">

                <div class="ee-section-heading">

                    <span class="ee-eyebrow">
                        Why EventEase
                    </span>

                    <h2>
                        Built Around Your Event Experience
                    </h2>

                    <p>
                        EventEase is designed to make finding and
                        attending events simple, convenient and engaging.
                    </p>

                </div>

                <div class="ee-feature-list">

                    <div class="ee-feature-item">

                        <div class="ee-feature-icon">
                            🔎
                        </div>

                        <div>
                            <h3>Smart Event Discovery</h3>

                            <p>
                                Find events using categories, locations
                                and relevant search options.
                            </p>
                        </div>

                    </div>

                    <div class="ee-feature-item">

                        <div class="ee-feature-icon">
                            🎟️
                        </div>

                        <div>
                            <h3>Simple Ticket Booking</h3>

                            <p>
                                Choose an event and move through a
                                straightforward booking experience.
                            </p>
                        </div>

                    </div>

                    <div class="ee-feature-item">

                        <div class="ee-feature-icon">
                            📱
                        </div>

                        <div>
                            <h3>Digital Access</h3>

                            <p>
                                Keep your booking details and digital
                                tickets accessible from your account.
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <section class="ee-cta">

        <div class="ee-cta-content">

            <h2>
                Your Next Great Experience Awaits
            </h2>

            <p>
                Join EventEase and discover events, experiences
                and moments worth remembering.
            </p>

            <?php if ($is_logged_in): ?>

                <a href="events.php" class="ee-cta-button">
                    Explore Events →
                </a>

            <?php else: ?>

                <a href="register.php" class="ee-cta-button">
                    Create Your Account →
                </a>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php include './footer.php'; ?>