<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FAQs | School Model Schools</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

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

        /* NAVBAR */

        .navbar-nav .nav-link {
            color: #222;
            transition: 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: #ff6a00;
        }

        /* GLASS BUTTON */

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

        .glass:hover {
            transform: translateY(-3px) scale(1.02);

            background: rgba(255, 255, 255, .78);

            box-shadow:
                0 15px 35px rgba(0, 0, 0, .12),
                inset 0 1px 2px rgba(255, 255, 255, 1);
        }

        /* FAQ HERO */

        .faq-hero {
            padding: 90px 20px 70px;
            text-align: center;
            background: linear-gradient(135deg,
                    #ffffff,
                    #fff7ef);
        }

        .faq-tag {
            color: #ff6a00;
            font-weight: 700;
            letter-spacing: 2px;
            font-size: 14px;
        }

        .faq-title {
            font-family: Georgia, serif;
            font-weight: 700;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            margin-top: 15px;
        }

        .faq-subtitle {
            max-width: 700px;
            margin: 20px auto 0;
            color: #666;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        /* FAQ SECTION */

        .faq-section {
            padding: 80px 0;
        }

        .faq-container {
            max-width: 950px;
            margin: auto;
        }

        /* ACCORDION */

        .accordion-item {
            border: 1px solid #e4e4e4;
            border-radius: 12px !important;
            margin-bottom: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .accordion-button {
            padding: 22px 25px;
            font-weight: 600;
            font-size: 1.05rem;
            background: #fff;
            color: #222;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: #fff7ef;
            color: #d66a2b;
        }

        .accordion-button::after {
            background-size: 16px;
        }

        .accordion-body {
            padding: 0 25px 25px;
            color: #666;
            line-height: 1.8;
            background: #fff7ef;
        }

        /* CTA */

        .faq-cta {
            margin-top: 40px;
            padding: 50px 30px;
            border-radius: 25px;
            text-align: center;
            background: #12305e;
            color: white;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .15);
        }

        .faq-cta h2 {
            font-family: Georgia, serif;
            font-weight: 700;
        }

        .faq-cta p {
            color: rgba(255, 255, 255, .75);
            margin: 15px auto 25px;
            max-width: 600px;
        }

        .contact-btn {
            background-color: #ff6a00;
            border: none;
            color: white;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: .3s;
        }

        .contact-btn:hover {
            background-color: #d95700;
            color: white;
            transform: translateY(-2px);
        }

        /* FOOTER */

        .glass-footer {
            position: relative;
            overflow: hidden;
            padding: 60px;
            border-radius: 35px;

            background: rgba(18, 48, 94, .92);

            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);

            border: 1px solid rgba(255, 255, 255, .12);

            box-shadow:
                0 20px 60px rgba(0, 0, 0, .25),
                inset 0 1px 1px rgba(255, 255, 255, .15);
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

        /* MOBILE */

        @media (max-width: 768px) {

            .faq-hero {
                padding: 60px 20px 50px;
            }

            .faq-section {
                padding: 50px 15px;
            }

            .accordion-button {
                font-size: 0.95rem;
                padding: 18px;
            }

            .accordion-body {
                padding: 0 18px 20px;
            }

            .glass-footer {
                padding: 35px 25px;
            }
        }
    </style>

</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar navbar-expand-lg bg-white border-bottom border-1 border-dark-subtle">

        <div class="container-fluid">

            <a class="navbar-brand col-md-4 fw-bold fs-3" href="./index.php">

                <span class="badge text-info fs-3">
                    <i class="bi bi-mortarboard"></i>
                </span>

                School

            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarNavDropdown">

                <div>

                    <ul class="navbar-nav">

                        <li class="nav-item">
                            <a class="nav-link" href="./index.php">
                                Home
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./about.php">
                                About
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./admission.php">
                                Admission
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link active" href="./faq.php">
                                FAQ
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="./contact.php">
                                Contact
                            </a>
                        </li>

                    </ul>

                </div>

                <div class="d-flex gap-2">

                    <button type="button" class="btn btn-outline-info"
                        onclick="window.location.href='./admin_login.php'"><i class="bi bi-shield-check"></i>
                        Admin</button>

                    <button type="button" class="btn glass" onclick="location.href='check_result.php'">
                        Check Result
                    </button>
                    <button class="btn btn-outline-warning" type="button">

                        Enroll Now
                        <i class="bi bi-box-arrow-right"></i>

                    </button>

                </div>

            </div>

        </div>

    </nav>


    <!-- HERO -->

    <section class="faq-hero">

        <div class="container">

            <span class="faq-tag">
                FREQUENTLY ASKED QUESTIONS
            </span>

            <h1 class="faq-title">
                How can we help?
            </h1>

            <p class="faq-subtitle">
                Find answers to some of the most common questions
                about our school, admissions, academics, facilities,
                and student life.
            </p>

        </div>

    </section>


    <!-- FAQ -->

    <section class="faq-section">

        <div class="container faq-container">

            <div class="accordion" id="faqAccordion">


                <!-- QUESTION 1 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqOne">

                            Do you have boarding facilities?

                        </button>

                    </h2>

                    <div id="faqOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            Yes, the school provides boarding facilities
                            for students who require residential accommodation.
                            Boarding students are provided with a safe,
                            structured and supportive environment that promotes
                            both academic and personal development.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 2 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqTwo">

                            How do I apply for admission?

                        </button>

                    </h2>

                    <div id="faqTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            You can apply for admission by visiting our
                            Admissions page and completing the admission
                            application process. Parents and guardians may
                            also contact the school office for further
                            information and assistance.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 3 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqThree">

                            What classes or levels do you offer?

                        </button>

                    </h2>

                    <div id="faqThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            We provide education across different levels
                            of learning. Please contact the school office
                            or visit the Admissions page for the current
                            classes and programmes available.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 4 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqFour">

                            What are the school hours?

                        </button>

                    </h2>

                    <div id="faqFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            Our regular school hours run from Monday to
                            Friday. For exact opening, closing and
                            extracurricular activity times, please contact
                            the school office.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 5 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqFive">

                            How much are the school fees?

                        </button>

                    </h2>

                    <div id="faqFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            School fees vary depending on the student's
                            class and programme. Please contact the school
                            office for the current fee structure and
                            payment information.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 6 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqSix">

                            Does the school provide meals for students?

                        </button>

                    </h2>

                    <div id="faqSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            Meal arrangements depend on the student's
                            programme and accommodation option. Please
                            contact the school office for details about
                            available meal plans.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 7 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqSeven">

                            Can parents visit the school before admission?

                        </button>

                    </h2>

                    <div id="faqSeven" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            Yes. Parents and guardians are welcome to visit
                            the school and learn more about our learning
                            environment, facilities and programmes.
                            We recommend contacting the school beforehand
                            to arrange a suitable time.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 8 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqEight">

                            Does the school have extracurricular activities?

                        </button>

                    </h2>

                    <div id="faqEight" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            Yes. Students are encouraged to participate in
                            extracurricular activities that support their
                            talents, creativity, leadership skills and
                            personal development.

                        </div>

                    </div>

                </div>


                <!-- QUESTION 9 -->

                <div class="accordion-item">

                    <h2 class="accordion-header">

                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqNine">

                            How can I contact the school?

                        </button>

                    </h2>

                    <div id="faqNine" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">

                        <div class="accordion-body">

                            You can contact the school by phone, email or
                            through the Contact page on this website.
                            Our office will be happy to assist you with
                            your questions.

                        </div>

                    </div>

                </div>


            </div>


            <!-- CTA -->

            <div class="faq-cta">

                <h2>
                    Still have questions?
                </h2>

                <p>
                    Can't find the answer you're looking for?
                    Our school office is ready to help.
                </p>

                <a href="./contact.php" class="contact-btn">

                    Contact the School
                    <i class="bi bi-arrow-right ms-2"></i>

                </a>

            </div>

        </div>

    </section>


    <!-- FOOTER -->

    <div class="container footer">

        <footer class="glass-footer container my-5">

            <div class="row g-5">


                <div class="col-lg-4">

                    <h2 class="logo">

                        <i class="bi bi-book-half"></i>

                        School Model Schools

                    </h2>

                    <p class="mt-3">

                        Building bright minds through excellence
                        in education, innovation, and character development.

                    </p>


                    <div class="social-icons mt-4">

                        <a href="https://web.facebook.com/otaroghene.favour.odafejirorosua/">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="https://www.instagram.com/the_ot_keys?fbclid=IwY2xjawTnIfRwZG9mBWV4dG4DYWVtAjEwAGJyaWQRMWJ6TTNSV283NU9ocFVlbXhzcnRjBmFwcF9pZBAyMjIwMzkxNzg4MjAwODkyAAEe6j2yVF4_ZgI_-LPk3BD7zaoKFK7xiDQmoZxSxC2rdVpHuICjJfzIlZtwQNA_aem_k_khjxRUGDTdhj1AMpc0CQ">
                            <i class="bi bi-instagram"></i>
                        </a>

                        <a href="#">
                            <i class="bi bi-twitter-x"></i>
                        </a>

                        <a href="https://www.youtube.com/@The_OT-KEYS">
                            <i class="bi bi-youtube"></i>
                        </a>

                    </div>

                </div>


                <div class="col-md-4 col-lg-2">

                    <h5>
                        Quick Links
                    </h5>

                    <ul class="list-unstyled">

                        <li>
                            <a href="./index.php">
                                Home
                            </a>
                        </li>

                        <li>
                            <a href="./about.php">
                                About
                            </a>
                        </li>

                        <li>
                            <a href="./admission.php">
                                Admissions
                            </a>
                        </li>

                        <li>
                            <a href="./faq.php">
                                FAQs
                            </a>
                        </li>

                        <li>
                            <a href="./contact.php">
                                Contact
                            </a>
                        </li>

                    </ul>

                </div>


                <div class="col-md-4 col-lg-3">

                    <h5>
                        Academics
                    </h5>

                    <ul class="list-unstyled">

                        <li>
                            <a href="#">
                                Check Result
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Programs
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Facilities
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                School Fees
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                Gallery
                            </a>
                        </li>

                    </ul>

                </div>


                <div class="col-md-4 col-lg-3">

                    <h5>
                        Contact
                    </h5>

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

                © 2026 School Model Schools. All Rights Reserved.

            </div>

        </footer>

    </div>


    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>