<?php

require_once __DIR__ . "/handlers/sanitize.php";


$status = "";
$statusType = "";

function smtp_read($socket)
{
    $response = "";

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (isset($line[3]) && $line[3] === " ") {
            break;
        }
    }

    return $response;
}

function smtp_expect($socket, $expectedCodes)
{
    $response = smtp_read($socket);
    $code = (int) substr($response, 0, 3);

    if (!in_array($code, $expectedCodes, true)) {
        throw new Exception("SMTP error: " . sanitize($response));
    }

    return $response;
}

function send_gmail_smtp($to, $replyTo, $name, $subject, $message)
{
    $username = "favour.efado@gmail.com";

    // PUT YOUR GMAIL APP PASSWORD HERE
    $password = "nhoc itbp uwni gavj";

    $socket = stream_socket_client(
        "ssl://smtp.gmail.com:465",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        throw new Exception("Could not connect to Gmail SMTP: " . $errstr);
    }

    try {

        smtp_expect($socket, [220]);

        fwrite($socket, "EHLO localhost\r\n");
        smtp_expect($socket, [250]);

        fwrite($socket, "AUTH LOGIN\r\n");
        smtp_expect($socket, [334]);

        fwrite($socket, base64_encode($username) . "\r\n");
        smtp_expect($socket, [334]);

        fwrite($socket, base64_encode($password) . "\r\n");
        smtp_expect($socket, [235]);

        fwrite($socket, "MAIL FROM:<" . $username . ">\r\n");
        smtp_expect($socket, [250]);

        fwrite($socket, "RCPT TO:<" . $to . ">\r\n");
        smtp_expect($socket, [250, 251]);

        $safeName = preg_replace('/[\r\n]+/', ' ', $name);
        $safeReplyTo = preg_replace('/[\r\n]+/', '', $replyTo);
        $safeSubject = preg_replace('/[\r\n]+/', ' ', $subject);

        $body = "You have received a new message from your website contact form.\r\n\r\n"
              . "Name: " . $safeName . "\r\n"
              . "Email: " . $safeReplyTo . "\r\n\r\n"
              . "Message:\r\n"
              . $message
              . "\r\n";

        $body = preg_replace('/^\./m', '..', $body);

        $headers  = "From: Website Contact Form <" . $username . ">\r\n";
        $headers .= "Reply-To: " . $safeReplyTo . "\r\n";
        $headers .= "To: " . $to . "\r\n";
        $headers .= "Subject: " . $safeSubject . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "\r\n";

        fwrite($socket, "DATA\r\n");
        smtp_expect($socket, [354]);

        fwrite($socket, $headers . $body . "\r\n.\r\n");
        smtp_expect($socket, [250]);

        fwrite($socket, "QUIT\r\n");

    } finally {
        fclose($socket);
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = sanitize($_POST["name"] ?? "");
    $email = sanitize($_POST["email"] ?? "");
    $message = sanitize($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {

        $status = "Please fill in all the fields.";
        $statusType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $status = "Please enter a valid email address.";
        $statusType = "danger";

    } elseif (preg_match('/[\r\n]/', $email) || preg_match('/[\r\n]/', $name)) {

        $status = "Invalid form submission.";
        $statusType = "danger";

    } else {

        try {

            send_gmail_smtp(
                "favour.efado@gmail.com",
                $email,
                $name,
                "New Contact Form Message",
                $message
            );

            $status = "Your message has been sent successfully!";
            $statusType = "success";

        } catch (Exception $e) {

            $status = "Your message could not be sent. Please check your Gmail App Password.";
            $statusType = "danger";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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

        .contact {
            color: #ff6a00;
        }

        .h1 {
            font-family: serif;
            font-weight: 700;
        }

        .icon {
            color: #ff6a00;
        }

        .article {
            background-color: #ffffff;
            border: rgba(128, 128, 128, 0.441) 1px solid;
            border-radius: 10px;
            padding: 10px;
        }

        .article-h3 {
            font-family: serif;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card {
            background-color: white;
            padding: 20px;
        }

        .buttom {
            background-color: #ff6a00;
            color: #ffffff;
        }

        /* =========================
   FORM INPUTS
========================= */

        .contact-form .form-control {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        /* Input focus */
        .contact-form .form-control:focus {
            border-color: #d66a2b;
            box-shadow: 0 4px 14px rgba(214, 106, 43, 0.18);
        }


        /* =========================
   SEND BUTTON
========================= */

        .contact-form .buttom {
            background-color: #d66a2b;
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            transition: background-color 0.3s ease,
                box-shadow 0.3s ease,
                transform 0.2s ease;
        }


        /* Hover */
        .contact-form .buttom:hover {
            background-color: #c55d25;
            color: #ffffff;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.16);
        }


        /* When clicked */
        .contact-form .buttom:active {
            background-color: #b65320;
            color: #ffffff;
            transform: translateY(1px);
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
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href='./admin_login.php'"><i class="bi bi-shield-check"></i>
                        Admin</button>


                    <button type="button" class="btn glass" onclick="location.href='check_result.php'">
                        Check Result
                    </button>


                    <button class="btn btn-outline-warning " onclick="location.href='enroll.php'" type="submit">Enroll Now <i
                            class="bi bi-box-arrow-right"></i></button>
                </div>


                </ul>
            </div>
        </div>
    </nav>


    <section class="contact-section py-5">
        <div class="container">

            <div class="row align-items-center g-5">

                <!-- LEFT SIDE -->
                <div class="col-lg-6">

                    <span class="contact-tag contact">CONTACT</span>

                    <h1 class="contact-title h1">
                        Talk to the office
                    </h1>

                    <!-- Contact Info -->
                    <div class="contact-info mt-5">

                        <div class="d-flex mb-4">
                            <span class="icon"><i class="bi bi-geo-alt"></i></span>

                            <div class="ms-3">
                                48 Harbour Road, school NB 4021
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <span class="icon"><i class="bi bi-telephone"></i></span>

                            <div class="ms-3">
                                +1 (555) 204-8890
                            </div>
                        </div>

                        <div class="d-flex">
                            <span class="icon"><i class="bi bi-envelope"></i></span>

                            <div class="ms-3">
                                office@school.edu
                            </div>
                        </div>

                    </div>

                    <!-- Office Hours Card -->

                    <div class="office-card mt-5 article">

                        <h3 class="article-h3">
                            Office hours
                        </h3>

                        <p>
                            Monday to Friday,
                            7:45am – 4:30pm.

                            Saturday campus tours
                            at 10am by appointment.
                        </p>

                    </div>




                </div>

                <!-- RIGHT SIDE -->

                <div class="col-lg-6 card">

                    <div class="contact-form">
                        <?php if ($status !== ""): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($statusType); ?> mb-4" role="alert">
                                <?php echo htmlspecialchars($status); ?>
                            </div>
                        <?php endif; ?>


                        <div class="mb-4">
                            <form action="contact.php" method="post">
                            <label class="form-label">
                                Full name
                            </label>

                            <input type="text" class="form-control" name="name" placeholder="Your name" required>

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email" class="form-control" name="email" placeholder="you@example.com"
                                required>

                        </div>


                        <div class="mb-4">

                            <label class="form-label">
                                Message
                            </label>

                            <textarea class="form-control" rows="6" name="message" placeholder="How can we help?"
                                required></textarea>

                        </div>


                        <button class="btn contact-btn w-100 buttom" type="submit">

                            Send message

                        </button>
                </form>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>