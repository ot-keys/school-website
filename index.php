<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            max-width: 100%;
        }

        .bot {
            background-image: url('./images/young-boy-learning-more-about-chemistry-class.jpg');
            background-size: cover;
            background-attachment: fixed;
            height: 80vh;
        }

        .royal-blue {
            background-color: #ffffea;
            /* Royal Blue */
        }

        .card {
            transition: all .4s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 15px 35px rgba(0, 0, 0, .25);
        }

        .card img {
            transition: transform .4s ease;
        }

        .card:hover img {
            transform: scale(1.1);
        }

        .card {
            overflow: hidden;
        }

        /* body {
            background: linear-gradient(135deg, #1E88E5, #00BCD4);
        } */

        .navbar-nav .nav-link {
            color: white;
            transition: 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: #FFD700;
            /* Gold */
        }

        body {
            background-color: #fdfdefdc;
        }

        @keyframes spin {
            0% {
                transform: rotate(180deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .box {
            --spring: linear(0, 0.01, 0.04 1.8%, 0.161 3.7%, 0.81 10.6%, 1.038, 1.181 16.4%, 1.223, 1.247 19.3%, 1.253 20.2% 21.1%, 1.232, 1.19 25.4%, 1.058 30.8%, 1.001 33.5%, 0.958 36.5%, 0.945, 0.938 39.6%, 0.936 41.6%, 0.941 43.8%, 0.999 53.9%, 1.01 56.7%, 1.015 59.7% 64.3%, 1.001 74.2%, 0.996 79.6%, 1.001);
            animation: spin var(--spring);
            animation-timeline: view();
        }

        @keyframes slideIn {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(0%);
            }
        }

        .shape {
            animation: slideIn backwards;
            animation-timeline: view();
            animation-range-start: cover 0%;
            animation-range-end: cover 50%;
        }

        .hero {
            background:
                linear-gradient(rgba(12, 16, 47, 0.297), rgba(3, 7, 30, 0.683)),
                url('./images/campus-hero.jpg');
            background-size: cover;
            height: 100vh;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;


        }

        .font {
            font-family: serif;
            font-size: 3rem;
            font-weight: 600px;
        }

        .hero-content {
            padding: 20vh 5%
        }

        .num {
            font-size: 3rem;
            font-weight: bold;

        }

        .cl {
            color: rgb(255, 166, 0);
            padding-top: 5px;
        }

        .sc {
            font-size: 25px;
            font-family: serif;
            font-weight: 900;
        }

        article {
            background-color: white;
        }

        .count {
            font: 800 40px system-ui;
            color: rgb(31, 31, 125);
        }

        .liquid-glass {
            color: #fff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 50px;

            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border: 1px solid rgba(255, 255, 255, 0.35);

            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.2),
                inset 0 1px 1px rgba(255, 255, 255, 0.4),
                inset 0 -1px 1px rgba(255, 255, 255, 0.15);

            transition: all .35s ease;
        }

        .liquid-glass:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.2);

            box-shadow:
                0 12px 40px rgba(0, 0, 0, 0.25),
                inset 0 1px 2px rgba(255, 255, 255, 0.6);
        }

        .liquid-glass:active {
            transform: scale(.97);
        }

        .border-radius {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 12px 30px;
            border-radius: 999px;

            color: #fff;
            font-weight: 600;
            text-decoration: none;

            background: linear-gradient(135deg,
                    rgba(255, 255, 255, .25),
                    rgba(255, 255, 255, .08));

            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);

            border: 1px solid rgba(255, 255, 255, .35);

            box-shadow:
                0 8px 30px rgba(0, 0, 0, .18),
                inset 0 1px 0 rgba(255, 255, 255, .6),
                inset 0 -1px 0 rgba(255, 255, 255, .1);

            position: relative;
            overflow: hidden;

            transition: all .35s ease;
        }


        /* Moving Reflection */

        .border-radius:hover {
            transform: translateY(-4px) scale(1.03);

            background: linear-gradient(135deg,
                    rgba(255, 255, 255, .35),
                    rgba(255, 255, 255, .12));

            box-shadow:
                0 15px 40px rgba(0, 0, 0, .25),
                inset 0 1px 0 rgba(255, 255, 255, .7);
        }

        .border-radius:hover::after {
            left: 150%;
        }

        .glass {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 12px 30px;
            border-radius: 50px;

            color: #1a1a1a;
            font-weight: 600;
            text-decoration: none;

            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);

            border: 1px solid rgba(255, 255, 255, 0.9);

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.08),
                inset 0 1px 1px rgba(255, 255, 255, 0.9),
                inset 0 -1px 1px rgba(0, 0, 0, 0.05);

            overflow: hidden;
            transition: all .35s ease;
        }

        /* Top glossy highlight */
        .glass::before {
            content: "";
            position: absolute;
            top: 3px;
            left: 8%;
            width: 84%;
            height: 45%;
            border-radius: 50px;

            background: linear-gradient(to bottom,
                    rgba(255, 255, 255, .9),
                    rgba(255, 255, 255, 0));
        }

        /* Moving light reflection */
        .glass::after {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 55%;
            height: 100%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, .8),
                    transparent);

            transform: skewX(-25deg);
            transition: .7s;
        }

        .glass:hover {
            transform: translateY(-3px) scale(1.02);

            background: rgba(255, 255, 255, .78);

            box-shadow:
                0 15px 35px rgba(0, 0, 0, .12),
                inset 0 1px 2px rgba(255, 255, 255, 1);
        }

        .glass:hover::after {
            left: 140%;
        }

        .glass:active {
            transform: scale(.97);
        }

        /* Liquid Glass Cards */
        article.card {
            position: relative;
            overflow: hidden;
            border-radius: 24px !important;

            background: rgba(255, 255, 255, .65);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);

            border: 1px solid rgba(255, 255, 255, .8) !important;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, .08),
                inset 0 1px 0 rgba(255, 255, 255, .95),
                inset 0 -1px 1px rgb(6, 43, 228);

            padding: 30px;

            transition: all .4s ease;
        }

        /* Top glossy reflection */
        article.card::before {
            content: "";
            position: absolute;
            top: 5px;
            left: 7%;
            width: 86%;
            height: 45%;

            border-radius: 30px;
        }

        /* Moving light */
        article.card::after {
            content: "";
            position: absolute;

            top: -50%;
            left: -130%;

            width: 60%;
            height: 220%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, .9),
                    transparent);

            transform: rotate(18deg);

            transition: 1s;
        }

        /* Hover */
        article.card:hover {

            transform: translateY(-12px) scale(1.02);

            background: rgba(255, 255, 255, .82);

            box-shadow:
                0 20px 45px rgba(0, 0, 0, .12),
                inset 0 1px 1px rgba(255, 255, 255, 1);
        }

        article.card:hover::after {
            left: 160%;
        }

        /* Active click */
        article.card:active {
            transform: scale(.98);
        }

        /* Typography */
        article.card h6 {
            color: #0d6efd;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        article.card h4 {
            margin: 15px 0;
            font-weight: 700;
        }

        article.card p {
            color: #555;
            line-height: 1.7;
        }

        /* SECTION */
        .cta-section {
            padding: 80px 6%;

        }

        /* GLASS PANEL */
        .glass-cta {

            position: relative;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 40px;

            padding: 60px;

            border-radius: 30px;

            background:
                linear-gradient(135deg,
                    rgba(20, 60, 120, .92),
                    rgba(15, 45, 95, .95));

            overflow: hidden;

            box-shadow:
                0 25px 50px rgba(0, 0, 0, .15),
                inset 0 1px 1px rgba(255, 255, 255, .15);

        }

        /* Liquid shine */
        .glass-cta::before {

            content: "";

            position: absolute;

            top: -100px;
            left: -120px;

            width: 280px;
            height: 280px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .08);

            filter: blur(20px);

        }

        .glass-cta::after {

            content: "";

            position: absolute;

            right: -120px;
            bottom: -120px;

            width: 260px;
            height: 260px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .05);

            filter: blur(25px);

        }

        /* TEXT */

        .cta-content {

            position: relative;
            z-index: 2;

        }

        .cta-content h2 {

            color: #fff;

            font-size: 3rem;

            font-family: serif;

            margin-bottom: 15px;

        }

        .cta-content p {

            color: rgba(255, 255, 255, .82);

            font-size: 1.15rem;

            margin: 0;

        }

        /* LIQUID GLASS BUTTON */

        .glass-btn {

            position: relative;

            display: inline-flex;
            align-items: center;
            gap: 12px;

            padding: 18px 38px;

            border-radius: 60px;

            text-decoration: none;

            color: #001d4f;
            font-weight: 700;

            background: rgba(255, 255, 255, .22);

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(255, 255, 255, .55);

            box-shadow:
                0 10px 25px rgba(0, 0, 0, .18),
                inset 0 1px 1px rgba(255, 255, 255, .9);

            overflow: hidden;

            transition: .4s;

        }

        /* Gloss */

        .glass-btn::before {

            content: "";

            position: absolute;

            top: 5%;
            left: 8%;

            width: 84%;
            height: 45%;

            border-radius: 50px;

            /* background: linear-gradient(to bottom,
                    rgba(255, 255, 255, .85),
                    transparent); */

        }

        /* Moving reflection */

        .glass-btn::after {

            content: "";

            position: absolute;

            top: -40%;
            left: -120%;

            width: 55%;
            height: 180%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, .8),
                    transparent);

            transform: rotate(20deg);

            transition: .8s;

        }

        .glass-btn:hover {

            transform: translateY(-5px);

            background: rgba(255, 255, 255, .3);

        }

        .glass-btn:hover::after {

            left: 140%;

        }

        /* Responsive */

        @media(max-width:768px) {

            .glass-cta {

                flex-direction: column;
                text-align: center;
                padding: 40px 30px;

            }

            .cta-content h2 {

                font-size: 2.2rem;

            }

        }

        /* SECTION */
        .two {
            background-color: #1d5cc7;
        }

        /* GLASS CARD */
        .two .card {

            position: relative;
            overflow: hidden;

            border: 1px solid rgba(255, 255, 255, .18) !important;
            border-radius: 24px;

            background: rgba(255, 255, 255, .08);

            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);

            box-shadow:
                0 12px 35px rgba(0, 0, 0, .25),
                inset 0 1px 1px rgba(255, 255, 255, .15);

            transition: all .45s ease;
        }

        /* Glass Shine */
        .two .card::before {

            content: "";

            position: absolute;

            top: 0;
            left: -100%;

            width: 60%;
            height: 100%;

            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, .35),
                    transparent);

            transform: skewX(-25deg);

            transition: .8s;
        }

        /* Soft Glow */
        .two .card::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            right: -70px;
            top: -70px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .08);

            filter: blur(25px);

        }

        /* Hover */
        .two .card:hover {

            transform: translateY(-15px) scale(1.03);

            background: rgba(255, 255, 255, .13);

            box-shadow:
                0 25px 50px rgba(0, 0, 0, .35),
                inset 0 1px 1px rgba(255, 255, 255, .25);

        }

        .two .card:hover::before {

            left: 150%;

        }

        /* IMAGE */

        .two .card img {

            height: 240px;
            object-fit: cover;

            transition: .45s;

        }

        .two .card:hover img {

            transform: scale(1.08);

        }

        /* TEXT */

        .two .card-body {

            color: white;

        }

        .two .card-title {

            font-weight: 700;
            font-size: 1.5rem;

        }

        .two .card-text {

            color: rgba(255, 255, 255, .82);

        }

        /* FOOTER */

        .two .card-footer {

            background: transparent;
            border: none;

        }

        /* BUTTON */

        .two .btn {

            border-radius: 50px;
            padding: 10px 22px;
            font-weight: 600;

            transition: .3s;

        }

        .two .btn:hover {

            transform: translateX(6px);

        }

        /* footer */
        .glass-footer {

            position: relative;

            overflow: hidden;

            padding: 60px;

            border-radius: 35px;

            background: rgba(18, 48, 94, .82);

            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);

            border: 1px solid rgba(255, 255, 255, .12);

            box-shadow:
                0 20px 60px rgba(0, 0, 0, .25),
                inset 0 1px 1px rgba(255, 255, 255, .15);

        }

        /* Liquid glow */

        .glass-footer::before {

            content: "";

            position: absolute;

            width: 280px;
            height: 280px;

            border-radius: 50%;

            top: -120px;
            left: -100px;

            background: rgba(255, 255, 255, .08);

            filter: blur(30px);

        }

        .glass-footer::after {

            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            right: -120px;
            bottom: -150px;

            background: rgba(255, 255, 255, .06);

            filter: blur(40px);

        }

        .glass-footer>* {

            position: relative;

            z-index: 2;

        }

        .logo {

            color: #fff;

            font-weight: 700;

            font-size: 2rem;

        }

        .glass-footer p {

            color: rgba(255, 255, 255, .75);

        }

        .glass-footer h5 {

            color: #fff;

            margin-bottom: 18px;

        }

        .glass-footer ul {

            padding: 0;

        }

        .glass-footer li {

            margin-bottom: 12px;

        }

        .glass-footer a {

            color: rgba(255, 255, 255, .75);

            text-decoration: none;

            transition: .35s;

        }

        .glass-footer a:hover {

            color: #fff;

            padding-left: 8px;

        }

        .social-icons {

            display: flex;

            gap: 15px;

        }

        .social-icons a {

            width: 45px;
            height: 45px;

            display: flex;

            justify-content: center;
            align-items: center;

            border-radius: 50%;

            background: rgba(255, 255, 255, .08);

            border: 1px solid rgba(255, 255, 255, .15);

            backdrop-filter: blur(15px);

            font-size: 1.2rem;

            transition: .35s;

        }

        .social-icons a:hover {

            background: #ffffff;

            color: #0d3b87;

            transform: translateY(-6px);

        }

        .glass-footer hr {

            border-color: rgba(255, 255, 255, .12);

            margin: 40px 0 25px;

        }

        .copyright {

            color: rgba(255, 255, 255, .7);

        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom border-1 border-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand col-md-4 fw-bold fs-3 " href="./index.php"><span class="badge text-info fs-3"><i
                        class="bi bi-mortarboard"></i></span> School</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNavDropdown">
                <div class="col-md">
                    <ul class="navbar-nav">
                        <li class="nav-item text-dark">
                            <a class="nav-link active" aria-current="page " href="./index.php">Home</a>
                        </li>
                        <li class="nav-item text-dark">
                            <a class="nav-link active" href="./about.php">About</a>
                        </li>
                        <li class="nav-item text-dark">
                            <a class="nav-link active" href="./admission.php">Admission</a>
                        </li>
                        <li class="nav-item text-dark">
                            <a class="nav-link active" href="./contact.php">Contact</a>
                        </li>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-info"
                        onclick="window.location.href='./admin_login.php'"><i class="bi bi-shield-check"></i>
                        Admin</button>


                    <button type="button" class="btn glass" onclick="location.href='check_result.php'">
                        Check Result
                    </button>

                    <button class="btn btn-outline-warning" type="button" onclick="window.location.href='./enroll.php'">
                        Enroll Now
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </div>


                </ul>
            </div>
        </div>
    </nav>


    <!-- hero -->
    <section class="hero">
        <div class="hero-content">
            <h6 class="text-warning h6">Est. 1962 · School</h6>
            <h1 class="text-white font ">A school where curiosity <br> is the curriculum.</h1>
            <h5 class="text-white fw-light">Independent K–12 education for 780 students on a twelve-acre<br>campus.
                Admissions for the 2026–27 year are open.</h5>
            <br>
            <button type="button" class="btn border-radius" onclick="window.location.href='./enroll.php'">
                Enroll Now
            </button>
            <button type="button" class="btn liquid-glass text-light"
                onclick="window.location.href='./check_result.php'">
                Check Result
            </button>
        </div>
    </section>

    <section>
        <div class="col-md row text-align-center num-container bg-light border-bottom border-1 border-dark-subtle">
            <div class="col-md-3 text-center py-5">
                <div style="display: inline;" class="count" id="value2">760+</div>
                <h3 class="count" style="display: inline;">+</h3>
                <p>Students</p>
            </div>
            <div class="col-md-3 text-center py-5">
                <div style="display: inline;" class="count" id="value1">18</div>
                <h3 class="count" style="display: inline;">+</h3>
                <p>Average Class Size</p>
            </div>
            <div class="col-md-3 text-center py-5">
                <h3 class="count">1:9</h3>
                <p>Teacher Ratio</p>
            </div>
            <div class="col-md-3 text-center py-5">
                <h3 class="count">96%</h3>
                <p>University Acceptance Rate</p>
            </div>
        </div>
    </section>
    <br>
    <section class="px-5 py-5 ">
        <h2 style="font-family: serif;" class="fw-bold fs-1 py-5">Three schools, one campus</h2>

        <div class="col-md row row-gap-4 px-3 gap-4">
            <article class="col-md border  border-1 card">
                <h6 class="cl">Kindergarten – Grade 5</h6>
                <h4 class="sc">Primary School</h4>
                <p class="fw-light">Phonics-first literacy, daily outdoor learning and a two-teacher model in every
                    room.</p>
            </article>

            <article class="col-md card border border-1">
                <h6 class="cl">Grades 6 – 8</h6>
                <h4 class="sc">Middle School</h4>
                <p class="fw-light">Subject specialists, a one-term robotics rotation and the start of student advisory.
                </p>
            </article>

            <article class="col-md card border border-1">
                <h6 class="cl">Grades 9 – 12</h6>
                <h4 class="sc">Senior School</h4>
                <p class="fw-light">Fourteen electives, research seminars and dedicated university counselling from
                    Grade 10.</p>
            </article>

        </div>
    </section>

    <!-- Section1 -->
    <section class="cta-section">
        <div class="glass-cta">

            <div class="cta-content">
                <h2>Visit us before you decide</h2>
                <p>Campus tours run every Saturday at 10:00 AM, led by our senior students.</p>
            </div>

            <a href="./admission.php" class="glass-btn text-light">
                See Admissions
                <i class="bi bi-arrow-right"></i>
            </a>

        </div>
    </section>


    <section class="py-5 two px-5">
        <h3 class="text-center text-light fs-1 fw-bold pb-3">Admission is currently on into</h3>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card h-100">
                    <img src="./images/cdc-gsRi9cWCIB0-unsplash.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Primary School</h5>
                        <p class="card-text">This is the place for young learners to start their educational
                            journey.
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="enroll.php" class="btn btn-primary">Enroll Now <i
                                class="bi bi-box-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <img src="./images/cdc-UqTrGSohyCs-unsplash.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Middle School</h5>
                        <p class="card-text">This is the place for young learners to start their educational
                            journey.
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="enroll.php" class="btn btn-primary">Enroll Now <i
                                class="bi bi-box-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <img src="./images/quilia-zFSo6bnZJTw-unsplash.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">High School</h5>
                        <p class="card-text">This is where students continue their education and develop critical
                            thinking skills.</p>
                    </div>
                    <div class="card-footer">
                        <a href="enroll.php" class="btn btn-primary">Enroll Now <i
                                class="bi bi-box-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>





    <section class="cta-section py-5 bot">

        <div class="cta-wrapper">
            <div class="row align-items-center">
                <div class="col-lg-5 ">
                    <div class="mt-0 mt-lg-5 text-white ps-4">
                        <h1>Shape a Brighter Future Join School Model Schools Today!</h1>
                        <p>
                            At School Model Schools, every child’s potential is nurtured with excellence,
                            discipline,
                            and
                            care.
                            We provide a dynamic learning environment that shapes confident minds, strong values,
                            and
                            purposeful futures.
                        </p>
                        <div class="cta-btn">
                            <a href="enroll.php" class="btn btn-primary">Get Started<i
                                    class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <!-- footer -->
    <div class="container footer">
        <footer class="glass-footer container my-5">

            <div class="row g-5">

                <div class="col-lg-4">

                    <h2 class="logo">
                        <i class="bi bi-book-half"></i>
                        School model schools
                    </h2>

                    <p class="mt-3">
                        Building bright minds through excellence in education, innovation,
                        and character development.
                    </p>

                    <div class="social-icons mt-4">

                        <a href="https://web.facebook.com/otaroghene.favour.odafejirorosua/"><i class="bi bi-facebook"></i></a>

                        <a href="https://www.instagram.com/the_ot_keys?fbclid=IwY2xjawTnIfRwZG9mBWV4dG4DYWVtAjEwAGJyaWQRMWJ6TTNSV283NU9ocFVlbXhzcnRjBmFwcF9pZBAyMjIwMzkxNzg4MjAwODkyAAEe6j2yVF4_ZgI_-LPk3BD7zaoKFK7xiDQmoZxSxC2rdVpHuICjJfzIlZtwQNA_aem_k_khjxRUGDTdhj1AMpc0CQ"><i class="bi bi-instagram"></i></a>

                        <a href="#"><i class="bi bi-twitter-x"></i></a>

                        <a href="https://www.youtube.com/@The_OT-KEYS"><i class="bi bi-youtube"></i></a>

                    </div>

                </div>


                <div class="col-md-4 col-lg-2">

                    <h5>Quick Links</h5>

                    <ul class="list-unstyled">

                        <li><a href="./index.php">Home</a></li>

                        <li><a href="./about.php">About</a></li>

                        <li><a href="./admission.php">Admissions</a></li>

                        <li><a href="./contact.php">Contact</a></li>

                        <li><a href="./faq.php">FAQs</a></li>

                    </ul>

                </div>


                <div class="col-md-4 col-lg-3">

                    <h5>Academics</h5>

                    <ul class="list-unstyled">

                        <li><a href="./check_result.php">Check Result</a></li>

                        <li><a href="#">Programs</a></li>

                        <li><a href="#">Facilities</a></li>

                        <li><a href="./admission.php">School Fees</a></li>

                        <li><a href="#">Gallery</a></li>

                    </ul>

                </div>


                <div class="col-md-4 col-lg-3">

                    <h5>Contact</h5>

                    <p>
                        <i class="bi bi-geo-alt-fill"></i>
                        Warri, Delta State
                    </p>

                    <p>
                        <i class="bi bi-envelope-fill"></i>
                        info@school.com
                    </p>

                    <p>
                        <i class="bi bi-telephone-fill"></i>
                        +234 000 000 0000
                    </p>

                </div>

            </div>

            <hr>

            <div class="text-center copyright">

                © 2026 OT'S Tech Hub. All Rights Reserved.

            </div>

        </footer>
    </div>
</body>

</html>

<script src="script.js"></script>