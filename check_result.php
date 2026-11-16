
<?php

session_start();

require_once __DIR__ . "/includes/db.php";
require "./handlers/sanitize.php";


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$status = "";
$statusType = "";

$studentName = "";
$studentEmail = "";

$searched = false;


/*
|--------------------------------------------------------------------------
| SCHOOL / GMAIL SETTINGS
|--------------------------------------------------------------------------
*/

$gmailUser = "favour.efado@gmail.com";

/*
IMPORTANT:
Use your Gmail App Password here.
Do NOT use your normal Gmail password.
*/

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolEmail = "favour.efado@gmail.com";

$schoolName = "School Model Schools";


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

    $sendCommand = function ($command, $expectedCode) use ($socket, $readResponse) {

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
                trim($response),
                0,
                3
            );


        return [

            "success" =>
                (
                    $code ===
                    $expectedCode
                ),

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
    | EMAIL HEADERS
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
            trim($response),
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

                    You requested access to your
                    student result.

                </p>


                <p>

                    Please use the verification
                    code below to verify your
                    email address before viewing
                    your result.

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

                    This verification code will
                    expire in
                    <strong>10 minutes</strong>.

                </p>


                <p>

                    If you did not request access
                    to a student result, you can
                    safely ignore this email.

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
| CHECK RESULT REQUEST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {
    sanitize($studentName = $_POST["student_name"] ?? "");
    sanitize($studentEmail = $_POST["email"] ?? "");


    $searched = true;


    /*
    |--------------------------------------------------------------------------
    | VALIDATE INPUT
    |--------------------------------------------------------------------------
    */

    if (
        $studentName === "" ||
        $studentEmail === ""
    ) {

        $status =
            "Please enter your name and email.";

        $statusType =
            "danger";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE EMAIL
    |--------------------------------------------------------------------------
    */ elseif (
        !filter_var(
            $studentEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $status =
            "Please enter a valid email address.";

        $statusType =
            "danger";

    }


    /*
    |--------------------------------------------------------------------------
    | FIND STUDENT
    |--------------------------------------------------------------------------
    */ else {

        $stmt =
            $conn->prepare(
                "
                SELECT
                    id,
                    student_name,
                    email,
                    result_pdf
                FROM enroll_table
                WHERE
                    student_name = ?
                    AND email = ?
                LIMIT 1
                "
            );


        if (!$stmt) {

            $status =
                "Unable to check the result at this time.";

            $statusType =
                "danger";

        } else {

            $stmt->bind_param(
                "ss",
                $studentName,
                $studentEmail
            );


            $stmt->execute();


            $queryResult =
                $stmt->get_result();


            $student =
                $queryResult->fetch_assoc();


            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | STUDENT NOT FOUND
            |--------------------------------------------------------------------------
            */

            if (!$student) {

                $status =
                    "No student record was found with the name and email provided.";

                $statusType =
                    "danger";

            }


            /*
            |--------------------------------------------------------------------------
            | STUDENT FOUND
            |--------------------------------------------------------------------------
            */ else {

                /*
                |--------------------------------------------------------------------------
                | CHECK WHETHER RESULT EXISTS
                |--------------------------------------------------------------------------
                */

                $resultFile = $student["result_pdf"] ?? "";


                if ($resultFile === "") {

                    $status =
                        "Your result is not available yet. Please check again later.";

                    $statusType =
                        "warning";

                }


                /*
                |--------------------------------------------------------------------------
                | RESULT EXISTS
                |--------------------------------------------------------------------------
                */ else {

                    /*
                    |--------------------------------------------------------------------------
                    | GENERATE OTP
                    |--------------------------------------------------------------------------
                    */

                    $otp =
                        generateOTP();


                    /*
                    |--------------------------------------------------------------------------
                    | SEND OTP
                    |--------------------------------------------------------------------------
                    */

                    $emailResult =
                        sendResultOTP(
                            $student["email"],
                            $student["student_name"],
                            $otp,
                            $gmailUser,
                            $gmailAppPassword,
                            $schoolName
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | OTP SENT SUCCESSFULLY
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $emailResult["success"]
                    ) {

                        /*
                        Store student information
                        temporarily in session.
                        */

                        $_SESSION[
                            "result_otp_data"
                        ] = [

                            "student_id" =>
                                $student["id"],

                            "student_name" =>
                                $student["student_name"],

                            "email" =>
                                $student["email"],

                            "result_pdf" =>
                                $student["result_pdf"]

                        ];
                        header(
                            "Location: verify_result_otp.php"
                        );


                        /*
                        Store OTP
                        */

                        $_SESSION[
                            "result_otp"
                        ] =
                            $otp;


                        /*
                        OTP expires after
                        10 minutes.
                        */

                        $_SESSION[
                            "result_otp_expires"
                        ] =
                            time() + 600;


                        /*
                        Reset attempts
                        */

                        $_SESSION[
                            "result_otp_attempts"
                        ] = 0;

                        /*
                        Redirect to OTP page
                        */

                        exit;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OTP FAILED
                    |--------------------------------------------------------------------------
                    */ else {

                        $status =
                            "We found your result, but we could not send the verification code. " .
                            $emailResult["error"];

                        $statusType =
                            "danger";

                    }

                }

            }

        }

    }

}

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Check Result | School Model Schools
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

            background: #061f49;

            padding: 0 40px;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        .brand {

            color: #fff;

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

            gap: 22px;

        }


        .nav-links a {

            color: #fff;

            text-decoration: none;

            font-size: 13px;

        }


        .nav-links a:hover {

            color: #d66a2b;

        }


        .nav-admin {

            border: 1px solid #91a2be;

            padding: 9px 15px;

            border-radius: 4px;

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

            background: #fff;

            border:
                1px solid #ddd9d1;

            border-radius: 7px;

            padding: 28px;

            box-shadow:
                0 4px 15px rgba(6, 31, 73, .04);

        }


        .card h2 {

            margin:
                0 0 8px;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 22px;

        }


        .card-description {

            margin:
                0 0 22px;

            color: #60708a;

            font-size: 13px;

            line-height: 1.6;

        }


        /* =====================================================
   ALERT
===================================================== */

        .alert {

            padding: 13px 15px;

            border-radius: 5px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.5;

        }


        .alert-danger {

            background: #f8d7da;

            color: #842029;

        }


        .alert-warning {

            background: #fff3cd;

            color: #664d03;

        }


        .alert-success {

            background: #d1e7dd;

            color: #0f5132;

        }


        /* =====================================================
   FORM
===================================================== */

        .field {

            margin-bottom: 18px;

        }


        .field label {

            display: block;

            margin-bottom: 7px;

            font-size: 12px;

            font-weight: 600;

        }


        .field input {

            width: 100%;

            height: 44px;

            border:
                1px solid #d7d4ce;

            border-radius: 4px;

            padding:
                0 12px;

            outline: none;

            font-size: 14px;

            color: #061f49;

        }


        .field input:focus {

            border-color: #d66a2b;

            box-shadow:
                0 0 0 2px rgba(214, 106, 43, .08);

        }


        .check-btn {

            width: 100%;

            height: 45px;

            border: none;

            border-radius: 4px;

            background: #d66a2b;

            color: #fff;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

        }


        .check-btn:hover {

            background: #c55d25;

        }


        /* =====================================================
   FOOTER
===================================================== */

        footer {

            margin-top: auto;

            background: #061f49;

            color: #fff;

            padding:
                24px 20px;

            text-align: center;

            font-size: 12px;

        }


        footer p {

            margin: 0;

            color: #cbd5e1;

        }


        /* =====================================================
   MOBILE
===================================================== */

        @media (max-width: 600px) {

            .navbar {

                padding:
                    0 18px;

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


            .nav-admin {

                padding:
                    8px 10px;

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

        }
    </style>

</head>


<body>


    <!-- =====================================================
     NAVBAR
===================================================== -->

    <nav class="navbar">


        <a href="index.php" class="brand">

            School Model Schools

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


            <a href="admin_login.php" class="nav-admin">
                Admin
            </a>


        </div>

    </nav>



    <!-- =====================================================
     PAGE
===================================================== -->

    <main class="page">


        <p class="eyebrow">
            STUDENT PORTAL
        </p>


        <h1>
            Check Your Result
        </h1>


        <p class="intro">

            Enter the name and email address you used
            during enrollment. A verification code
            will be sent to your email before you can
            access your result.

        </p>



        <section class="card">


            <h2>
                Find Your Result
            </h2>


            <p class="card-description">

                Please make sure the name and email
                match the information provided during
                your enrollment.

            </p>



            <?php if ($status !== ""): ?>

                <div class="alert alert-<?php
                echo e($statusType);
                ?>">

                    <?php
                    echo e($status);
                    ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
             CHECK FORM
        ================================================= -->

            <form method="POST" action="">


                <div class="field">


                    <label for="student_name">

                        Student's Full Name

                    </label>


                    <input type="text" id="student_name" name="student_name" placeholder="Enter your full name" value="<?php
                    echo e(
                        $studentName
                    );
                    ?>" required>


                </div>



                <div class="field">


                    <label for="email">

                        Email Address

                    </label>


                    <input type="email" id="email" name="email" placeholder="Enter your email address" value="<?php
                    echo e(
                        $studentEmail
                    );
                    ?>" autocomplete="email" required>


                </div>



                <button type="submit" class="check-btn">

                    Find My Result

                </button>


            </form>


        </section>


    </main>



    <!-- =====================================================
     FOOTER
===================================================== -->

    <footer>

        <p>

            © 2026 School Model Schools.
            All Rights Reserved.

        </p>

    </footer>


</body>

</html>