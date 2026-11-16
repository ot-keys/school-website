<?php

session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";



/*
|--------------------------------------------------------------------------
| GMAIL SETTINGS
|--------------------------------------------------------------------------
*/

$gmailUser = "favour.efado@gmail.com";

/*
IMPORTANT:
Use your Gmail App Password here.
Remove the spaces if necessary.
*/

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolEmail = "favour.efado@gmail.com";

$schoolName = "School Model Schools";


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$status = "";
$statusType = "";

$student = null;

$otpEmail = "";

$otpExpires = 0;

$secondsRemaining = 0;


/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
|--------------------------------------------------------------------------
| GENERATE OTP
|--------------------------------------------------------------------------
*/

function generateOTP()
{
    return (string) random_int(
        100000,
        999999
    );
}


/*
|--------------------------------------------------------------------------
| SEND EMAIL USING GMAIL SMTP
|--------------------------------------------------------------------------
*/

function sendGmailEmail(
    $to,
    $subject,
    $htmlBody,
    $gmailUser,
    $gmailAppPassword,
    $schoolName
) {

    $host = "ssl://smtp.gmail.com";

    $port = 465;

    $errno = 0;

    $errstr = "";


    /*
    |--------------------------------------------------------------------------
    | CONNECT TO GMAIL
    |--------------------------------------------------------------------------
    */

    $socket = @fsockopen(
        $host,
        $port,
        $errno,
        $errstr,
        30
    );


    if (!$socket) {

        return [
            "success" => false,
            "error" =>
                "Could not connect to Gmail SMTP. " .
                $errstr
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | READ SMTP RESPONSE
    |--------------------------------------------------------------------------
    */

    $readResponse = function () use ($socket) {

        $response = "";

        while (
            ($line = fgets($socket, 515)) !== false
        ) {

            $response .= $line;

            if (
                strlen($line) >= 4 &&
                $line[3] === " "
            ) {

                break;

            }

        }

        return $response;

    };


    /*
    |--------------------------------------------------------------------------
    | SEND SMTP COMMAND
    |--------------------------------------------------------------------------
    */

    $sendCommand = function (
        $command,
        $expectedCode
    ) use (
        $socket,
        $readResponse
    ) {

        if ($command !== "") {

            fwrite(
                $socket,
                $command . "\r\n"
            );

        }

        $response =
            $readResponse();


        $code =
            substr(
                ($response),
                0,
                3
            );


        return [

            "success" =>
                ($code === $expectedCode),

            "response" =>
                $response

        ];

    };


    /*
    |--------------------------------------------------------------------------
    | GREETING
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "",
            "220"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail SMTP greeting failed."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | EHLO
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "EHLO localhost",
            "250"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail SMTP EHLO failed."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | AUTH LOGIN
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "AUTH LOGIN",
            "334"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail authentication could not start."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | USERNAME
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            base64_encode($gmailUser),
            "334"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail username was rejected."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | APP PASSWORD
    |--------------------------------------------------------------------------
    */

    $cleanAppPassword =
        str_replace(
            " ",
            "",
            $gmailAppPassword
        );


    $result =
        $sendCommand(
            base64_encode(
                $cleanAppPassword
            ),
            "235"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail App Password was rejected."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | MAIL FROM
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "MAIL FROM:<" .
            $gmailUser .
            ">",
            "250"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail rejected the sender address."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | RCPT TO
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "RCPT TO:<" .
            $to .
            ">",
            "250"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail rejected the recipient address."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            "DATA",
            "354"
        );


    if (!$result["success"]) {

        fclose($socket);

        return [

            "success" => false,

            "error" =>
                "Gmail did not accept the email data."

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    $headers = "";

    $headers .=
        "From: " .
        $schoolName .
        " <" .
        $gmailUser .
        ">\r\n";

    $headers .=
        "To: " .
        $to .
        "\r\n";

    $headers .=
        "Subject: " .
        $subject .
        "\r\n";

    $headers .=
        "MIME-Version: 1.0\r\n";

    $headers .=
        "Content-Type: text/html; charset=UTF-8\r\n";

    $headers .=
        "Content-Transfer-Encoding: 8bit\r\n";

    $headers .=
        "\r\n";


    /*
    |--------------------------------------------------------------------------
    | MESSAGE
    |--------------------------------------------------------------------------
    */

    $message =
        $headers .
        $htmlBody .
        "\r\n.\r\n";


    fwrite(
        $socket,
        $message
    );


    $response =
        $readResponse();


    /*
    |--------------------------------------------------------------------------
    | QUIT
    |--------------------------------------------------------------------------
    */

    fwrite(
        $socket,
        "QUIT\r\n"
    );


    fclose($socket);


    /*
    |--------------------------------------------------------------------------
    | CHECK RESPONSE
    |--------------------------------------------------------------------------
    */

    if (
        substr(
            ($response),
            0,
            3
        ) !== "250"
    ) {

        return [

            "success" => false,

            "error" =>
                "Gmail did not accept the email."

        ];

    }


    return [

        "success" => true,

        "error" => ""

    ];

}


/*
|--------------------------------------------------------------------------
| SEND RESULT OTP
|--------------------------------------------------------------------------
*/

function sendResultOTP(
    $to,
    $studentName,
    $otp,
    $gmailUser,
    $gmailAppPassword,
    $schoolName
) {

    $safeName =
        e($studentName);

    $safeSchool =
        e($schoolName);


    $subject =
        "Result Verification Code - " .
        $schoolName;


    $htmlBody = "

<!DOCTYPE html>

<html>

<head>

    <meta charset='UTF-8'>

    <meta
        name='viewport'
        content='width=device-width,
                 initial-scale=1.0'
    >

</head>


<body
    style='
        margin:0;
        padding:0;
        background:#fdfdef;
        font-family:Arial,
                     Helvetica,
                     sans-serif;
    '
>


    <div
        style='
            max-width:600px;
            margin:30px auto;
            background:#ffffff;
            border:1px solid #ddd9d1;
        '
    >


        <div
            style='
                background:#061f49;
                color:#ffffff;
                padding:25px;
                text-align:center;
            '
        >

            <h2
                style='
                    margin:0;
                    font-family:Georgia,serif;
                '
            >

                $safeSchool

            </h2>


            <p
                style='
                    margin:8px 0 0;
                    color:#cbd5e1;
                '
            >

                Student Result Portal

            </p>

        </div>


        <div
            style='
                padding:30px;
                color:#333333;
            '
        >


            <h2>
                Verify Your Email
            </h2>


            <p>

                Dear $safeName,

            </p>


            <p>

                You requested access to a student
                result from
                <strong>$safeSchool</strong>.

            </p>


            <p>

                Please use the verification code
                below to continue.

            </p>


            <div
                style='
                    text-align:center;
                    margin:30px 0;
                '
            >

                <span
                    style='
                        display:inline-block;
                        background:#f8f7f3;
                        border:1px solid #ddd9d1;
                        padding:18px 30px;
                        font-size:32px;
                        font-weight:bold;
                        letter-spacing:8px;
                        color:#061f49;
                    '
                >

                    $otp

                </span>

            </div>


            <p>

                This verification code will expire
                in
                <strong>10 minutes</strong>.

            </p>


            <p>

                If you did not request access to a
                result, you can safely ignore this
                email.

            </p>


            <br>


            <p>

                Regards,<br>

                <strong>
                    $safeSchool
                </strong>

            </p>


        </div>


        <div
            style='
                background:#061f49;
                color:#cbd5e1;
                padding:15px;
                text-align:center;
                font-size:12px;
            '
        >

            © 2026 $safeSchool.
            All Rights Reserved.

        </div>


    </div>


</body>

</html>

";


    return sendGmailEmail(
        $to,
        $subject,
        $htmlBody,
        $gmailUser,
        $gmailAppPassword,
        $schoolName
    );

}


/*
|--------------------------------------------------------------------------
| CHECK WHETHER RESULT VERIFICATION SESSION EXISTS
|--------------------------------------------------------------------------
*/

if (
    empty(
        $_SESSION["result_otp_data"]
    )
) {

    header(
        "Location: result.php"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| GET STUDENT DATA FROM SESSION
|--------------------------------------------------------------------------
*/

$resultVerificationData =
    $_SESSION[
        "result_otp_data"
    ];


$studentName =
    $resultVerificationData[
        "student_name"
    ] ?? "";


$studentEmail =
    $resultVerificationData[
        "email"
    ] ?? "";


$resultFile =
    $resultVerificationData[
        "result_pdf"
    ] ?? "";


/*
|--------------------------------------------------------------------------
| CHECK OTP SESSION
|--------------------------------------------------------------------------
*/

if (
    isset(
        $_SESSION["result_otp"]
    )
) {

    $otpExpires =
        $_SESSION[
            "result_otp_expires"
        ] ?? 0;

}


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["verify_result_otp"])
) {

    $enteredOTP = $_POST["otp"] ?? "";


    $savedOTP =
        $_SESSION[
            "result_otp"
        ] ?? "";


    $expires =
        $_SESSION[
            "result_otp_expires"
        ] ?? 0;


    /*
    |--------------------------------------------------------------------------
    | OTP DOES NOT EXIST
    |--------------------------------------------------------------------------
    */

    if (
        empty($savedOTP)
    ) {

        $status =
            "No verification code is active. Please request a new code.";

        $statusType =
            "danger";

    }


    /*
    |--------------------------------------------------------------------------
    | OTP EXPIRED
    |--------------------------------------------------------------------------
    */

    elseif (
        time() > $expires
    ) {

        unset(
            $_SESSION["result_otp"],
            $_SESSION["result_otp_expires"],
            $_SESSION["result_otp_attempts"]
        );


        $status =
            "Your verification code has expired. Please request a new code.";

        $statusType =
            "warning";

    }


    /*
    |--------------------------------------------------------------------------
    | INVALID FORMAT
    |--------------------------------------------------------------------------
    */

    elseif (
        !preg_match(
            "/^[0-9]{6}$/",
            $enteredOTP
        )
    ) {

        $status =
            "Please enter the 6-digit verification code.";

        $statusType =
            "danger";

    }


    /*
    |--------------------------------------------------------------------------
    | INCORRECT OTP
    |--------------------------------------------------------------------------
    */

    elseif (
        !hash_equals(
            (string) $savedOTP,
            (string) $enteredOTP
        )
    ) {

        $_SESSION[
            "result_otp_attempts"
        ] =
            (
                $_SESSION[
                    "result_otp_attempts"
                ] ?? 0
            ) + 1;


        $status =
            "Incorrect verification code.";

        $statusType =
            "danger";

    }


    /*
    |--------------------------------------------------------------------------
    | OTP VERIFIED
    |--------------------------------------------------------------------------
    */

    else {

        /*
        Mark result verification as complete.
        */

        $_SESSION[
            "result_verified"
        ] = true;


        /*
        Remove OTP so it cannot be reused.
        */

        unset(
            $_SESSION["result_otp"],
            $_SESSION["result_otp_expires"],
            $_SESSION["result_otp_attempts"],
            $_SESSION["result_last_otp_sent"]
        );


        /*
        Redirect back to check_result.php.
        */

        header(
            "Location: result.php"
        );

        exit;

    }

}


/*
|--------------------------------------------------------------------------
| RESEND OTP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["resend_result_otp"])
) {

    $otp =
        generateOTP();


    $emailResult =
        sendResultOTP(
            $studentEmail,
            $studentName,
            $otp,
            $gmailUser,
            $gmailAppPassword,
            $schoolName
        );


    if (
        $emailResult["success"]
    ) {

        $_SESSION[
            "result_otp"
        ] =
            $otp;


        $_SESSION[
            "result_otp_expires"
        ] =
            time() + 600;


        $_SESSION[
            "result_otp_attempts"
        ] =
            0;


        $_SESSION[
            "result_last_otp_sent"
        ] =
            time();


        $status =
            "A new verification code has been sent to your email.";

        $statusType =
            "success";

    } else {

        $status =
            "The verification code could not be sent. " .
            $emailResult["error"];

        $statusType =
            "danger";

    }

}


/*
|--------------------------------------------------------------------------
| OTP INFORMATION
|--------------------------------------------------------------------------
*/

$otpExpires =
    $_SESSION[
        "result_otp_expires"
    ] ?? 0;


$secondsRemaining =
    max(
        0,
        $otpExpires - time()
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Verify Result | <?php echo e($schoolName); ?>
</title>


<style>

* {
    box-sizing: border-box;
}


html,
body {

    margin: 0;
    padding: 0;

}


body {

    min-height: 100vh;

    display: flex;

    flex-direction: column;

    background: #fdfdef;

    color: #061f49;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


/* =====================================================
   NAVBAR
===================================================== */

.navbar {

    width: 100%;

    min-height: 70px;

    padding: 0 40px;

    background: #061f49;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.brand {

    color: white;

    text-decoration: none;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 21px;

    font-weight: bold;

}


.nav-links {

    display: flex;

    align-items: center;

    gap: 20px;

}


.nav-links a {

    color: white;

    text-decoration: none;

    font-size: 13px;

}


.nav-links a:hover {

    color: #d66a2b;

}


/* =====================================================
   PAGE
===================================================== */

.page {

    width: 100%;

    max-width: 600px;

    margin: auto;

    padding:
        55px 20px 70px;

}


.eyebrow {

    margin:
        0 0 8px;

    color: #d66a2b;

    font-size: 10px;

    font-weight: bold;

    letter-spacing: 3px;

}


h1 {

    margin: 0;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 34px;

}


.intro {

    margin:
        10px 0 28px;

    color: #31517c;

    font-size: 14px;

    line-height: 1.7;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background: white;

    border:
        1px solid #ddd9d1;

    border-radius: 7px;

    padding: 30px;

    box-shadow:
        0 4px 15px
        rgba(6,31,73,.04);

}


.otp-icon {

    width: 65px;

    height: 65px;

    margin:
        0 auto 20px;

    border-radius: 50%;

    background:
        rgba(214,106,43,.1);

    color: #d66a2b;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;

}


.otp-title {

    text-align: center;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 24px;

    margin:
        0 0 10px;

}


.otp-description {

    text-align: center;

    color: #60708a;

    font-size: 13px;

    line-height: 1.7;

}


.otp-email {

    text-align: center;

    font-weight: bold;

    color: #061f49;

    margin:
        15px 0 25px;

    word-break: break-word;

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding: 13px 15px;

    border-radius: 5px;

    margin-bottom: 20px;

    font-size: 13px;

    line-height: 1.6;

}


.success {

    background: #d1e7dd;

    color: #0f5132;

}


.danger {

    background: #f8d7da;

    color: #842029;

}


.warning {

    background: #fff3cd;

    color: #664d03;

}


/* =====================================================
   OTP INPUT
===================================================== */

.otp-input {

    width: 100%;

    height: 58px;

    border:
        1px solid #d7d4ce;

    border-radius: 5px;

    text-align: center;

    font-size: 27px;

    letter-spacing: 10px;

    color: #061f49;

    outline: none;

}


.otp-input:focus {

    border-color: #d66a2b;

    box-shadow:
        0 0 0 2px
        rgba(214,106,43,.08);

}


/* =====================================================
   BUTTONS
===================================================== */

.verify-btn {

    width: 100%;

    height: 45px;

    border: none;

    border-radius: 4px;

    background: #061f49;

    color: white;

    font-weight: bold;

    cursor: pointer;

    margin-top: 15px;

}


.verify-btn:hover {

    background: #0b2d62;

}


.resend-btn {

    width: 100%;

    height: 43px;

    border:
        1px solid #061f49;

    border-radius: 4px;

    background: white;

    color: #061f49;

    font-weight: bold;

    cursor: pointer;

    margin-top: 10px;

}


.resend-btn:hover {

    background: #061f49;

    color: white;

}


/* =====================================================
   TIMER
===================================================== */

.otp-timer {

    text-align: center;

    margin-top: 15px;

    font-size: 12px;

    color: #777;

}


.otp-note {

    margin-top: 20px;

    padding: 12px;

    background: #fafaf6;

    border:
        1px solid #e5e2da;

    border-radius: 4px;

    color: #667085;

    font-size: 11px;

    line-height: 1.6;

}


/* =====================================================
   FOOTER
===================================================== */

footer {

    margin-top: auto;

    padding:
        24px 20px;

    background: #061f49;

    color: #cbd5e1;

    text-align: center;

    font-size: 12px;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 600px) {

    .navbar {

        padding: 0 18px;

    }


    .brand {

        font-size: 17px;

    }


    .nav-links {

        gap: 10px;

    }


    .nav-links a {

        font-size: 11px;

    }


    .page {

        padding:
            40px 15px 50px;

    }


    h1 {

        font-size: 28px;

    }


    .card {

        padding:
            22px 18px;

    }


    .otp-input {

        letter-spacing: 6px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">


    <a
        href="index.php"
        class="brand"
    >

        <?php echo e($schoolName); ?>

    </a>


    <div class="nav-links">


        <a href="index.php">
            Home
        </a>


        <a href="enroll.php">
            Enrollment
        </a>


        <a href="check_result.php">
            Check Result
        </a>


        <a href="admin_login.php">
            Admin
        </a>


    </div>


</nav>



<!-- =====================================================
     PAGE
===================================================== -->

<main class="page">


    <p class="eyebrow">

        RESULT VERIFICATION

    </p>


    <h1>

        Verify Your Email

    </h1>


    <p class="intro">

        Verify your email address before accessing
        your student result.

    </p>



    <?php if ($status !== ""): ?>

        <div
            class="alert <?php echo e($statusType); ?>"
        >

            <?php echo e($status); ?>

        </div>

    <?php endif; ?>



    <section class="card">


        <div class="otp-icon">

            ✓

        </div>


        <h2 class="otp-title">

            Enter Verification Code

        </h2>


        <p class="otp-description">

            We sent a 6-digit verification code
            to the email address used to check
            the result.

        </p>


        <div class="otp-email">

            <?php echo e($studentEmail); ?>

        </div>



        <!-- =================================================
             VERIFY OTP FORM
        ================================================= -->

        <form
            method="POST"
            action=""
        >


            <input
                type="text"
                name="otp"
                class="otp-input"
                maxlength="6"
                minlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="000000"
                required
            >


            <button
                type="submit"
                name="verify_result_otp"
                class="verify-btn"
            >

                Verify Email & View Result

            </button>


        </form>



        <!-- =================================================
             RESEND OTP
        ================================================= -->

        <form
            method="POST"
            action=""
        >


            <button
                type="submit"
                name="resend_result_otp"
                class="resend-btn"
            >

                Resend OTP

            </button>


        </form>



        <!-- =================================================
             TIMER
        ================================================= -->

        <div class="otp-timer">


            <?php if (
                $secondsRemaining > 0
            ): ?>


                Code expires in

                <strong>

                    <span id="otpTimer">

                        <?php
                        echo (int)
                            $secondsRemaining;
                        ?>

                    </span>

                </strong>

                seconds.


            <?php else: ?>


                Your verification code has expired.


            <?php endif; ?>


        </div>



        <div class="otp-note">

            <strong>
                Didn't receive the email?
            </strong>

            Check your spam or junk folder.
            If it is not there, click
            <strong>Resend OTP</strong>.

        </div>


    </section>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    © 2026

    <?php echo e($schoolName); ?>.

    All Rights Reserved.

</footer>



<?php if (
    $secondsRemaining > 0
): ?>

<script>

let seconds =
    <?php
    echo (int)
        $secondsRemaining;
    ?>;


const timer =
    document.getElementById(
        "otpTimer"
    );


if (timer) {

    const interval =
        setInterval(
            function () {

                seconds--;


                if (
                    seconds <= 0
                ) {

                    clearInterval(
                        interval
                    );


                    timer.textContent =
                        "0";


                    return;

                }


                timer.textContent =
                    seconds;

            },
            1000
        );

}

</script>

<?php endif; ?>


</body>

</html>