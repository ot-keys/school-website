<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission</title>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            max-width: 100%;
        }

        body {
            background-color: #fdfdefdc;
        }

        /* Nav bar */
        .navbar-nav .nav-link {
            color: white;
            transition: 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: #FFD700;
            /* Gold */
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

        /* hero */
        .hero-h6 {
            color: #d2732e;
            font-size: 1.5rem;
        }

        .hero-h1 {
            font-size: 3rem;
            font-family: serif;
        }

        /* article card */
        .article-card-h2 {
            color: #d2732e;
            font-family: serif;
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
                0 10px 35px rgba(224, 117, 3, 0.228),
                inset 0 1px 0 rgba(255, 255, 255, .95),
                inset 0 -1px 1px rgb(6, 69, 228);

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

        /* after article */
        section {
            background: #fdfcf8;
        }

        .border-bottom {
            border-color: #ddd !important;
        }

        .border-bottom span {
            color: #505c73;
            font-size: 1.15rem;
        }

        .border-bottom strong {
            color: #0d274d;
            font-size: 1.25rem;
        }

        .text-secondary {
            color: #6c757d !important;
        }

        .btn-warning {
            background: #d96b2c;
            border: none;
            color: #fff;
            transition: .3s;
        }

        .btn-warning:hover {
            background: #bf5921;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, .15);
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

                    <button class="btn btn-outline-warning " type="submit" onclick="location.href='enroll.php'">Enroll Now <i
                            class="bi bi-box-arrow-right"></i></button>
                </div>


                </ul>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <section class="container py-5">
        <h6 class="hero-h6">Admissions</h6>
        <h1 class="hero-h1">Applications for the 2026–27<br>academic year are open.</h1>
    </section>

    <!-- article cards -->
    <!-- top article cards -->
    <section class="px-5 py-5 article">
        <div class="row justify-content-center g-4 gap-3">
            <article class="col-lg-5 col-md-5 card">
                <div class="row g-0">
                    <div class="col-md py-2 article-card-h2">
                        <h2>01</h2>
                    </div>
                    <div class="col-md-11 px-2">
                        <h6>Submit the enquiry form</h6>
                        <p class="fw-light">Takes five minutes. We reply within two working days.</p>
                    </div>
                </div>
            </article>

            <article class="col-lg-5 col-md-5 card">
                <div class="row g-0">
                    <div class="col-md py-2 article-card-h2">
                        <h2>02</h2>
                    </div>
                    <div class="col-md-11 px-2">
                        <h6>Campus visit</h6>
                        <p class="fw-light">A 90-minute tour with a senior student and a family interview.</p>
                    </div>
                </div>
            </article>

            <!-- bottom article cards -->
            <article class="col-lg-5 col-md-5 card">
                <div class="row g-0">
                    <div class="col-md py-2 article-card-h2">
                        <h2>03</h2>
                    </div>
                    <div class="col-md-11 px-2">
                        <h6>Placement assessment</h6>
                        <p class="fw-light">English and mathematics, sized to the applicant's grade.</p>
                    </div>
                </div>
            </article>

            <article class="col-lg-5 col-md-5 card">
                <div class="row g-0">
                    <div class="col-md py-2 article-card-h2">
                        <h2>04</h2>
                    </div>
                    <div class="col-md-11 px-2">
                        <h6>Offer and enrolment</h6>
                        <p class="fw-light">Offers go out in rolling batches; a place is held for 14 days.</p>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- befor articls cards -->
    <section class="container py-5">
        <div class="row g-5">

            <!-- Left Side -->
            <div class="col-lg-6">

                <h2 class="fw-bold display-6 mb-4" style="font-family: serif;">
                    Tuition
                </h2>

                <div class="border-bottom py-3 d-flex justify-content-between">
                    <span>Primary (K–5)</span>
                    <strong>$4,200 / term</strong>
                </div>

                <div class="border-bottom py-3 d-flex justify-content-between">
                    <span>Middle (6–8)</span>
                    <strong>$4,900 / term</strong>
                </div>

                <div class="border-bottom py-3 d-flex justify-content-between">
                    <span>Senior (9–12)</span>
                    <strong>$5,600 / term</strong>
                </div>

                <p class="text-secondary mt-4">
                    Need-based bursaries cover up to 60% of tuition for qualifying families.
                </p>

            </div>

            <!-- Right Side -->
            <div class="col-lg-6">

                <h2 class="fw-bold display-6 mb-4" style="font-family: serif;">
                    What to prepare
                </h2>

                <ul class="list-unstyled fs-5 text-secondary mb-4">
                    <li class="mb-3">• Birth certificate and two passport photographs</li>
                    <li class="mb-3">• Reports from the two most recent school years</li>
                    <li class="mb-3">• Transfer certificate for Grades 2 and above</li>
                    <li class="mb-3">• Immunisation record</li>
                </ul>

                <a href="enroll.php" class="btn btn-warning px-5 py-3 fw-bold rounded-3 shadow">
                    Start an application
                </a>

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

                        <li><a href="./about.php">School Fees</a></li>

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
</body>

</html>