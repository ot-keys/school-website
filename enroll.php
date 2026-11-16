<?php

session_start();

/*
===========================================================
DATABASE
===========================================================
*/

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";



/*
===========================================================
CHECK DATABASE CONNECTION
===========================================================
*/

if (!isset($conn) || !($conn instanceof mysqli)) {

    die(
        "Database connection was not found. " .
        "Please check includes/db.php."
    );

}


/*
===========================================================
GMAIL SETTINGS
===========================================================
*/

$gmailUser = "favour.efado@gmail.com";

/*
IMPORTANT:
Use your Gmail App Password here.
Remove spaces if necessary.
*/

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolEmail = "favour.efado@gmail.com";

$schoolName = "School Model Schools";


/*
===========================================================
VARIABLES
===========================================================
*/

$status = "";

$statusType = "";


/*
===========================================================
HELPER FUNCTION
===========================================================
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
===========================================================
GENERATE OTP
===========================================================
*/

function generateOTP()
{
    return (string) random_int(
        100000,
        999999
    );
}


/*
===========================================================
SEND EMAIL USING GMAIL SMTP
===========================================================
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
    -------------------------------------------------------
    CONNECT TO GMAIL
    -------------------------------------------------------
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
    -------------------------------------------------------
    READ SMTP RESPONSE
    -------------------------------------------------------
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
    -------------------------------------------------------
    SEND SMTP COMMAND
    -------------------------------------------------------
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

        $response = $readResponse();

        $code = substr(
            sanitize($response),
            0,
            3
        );

        return [
            "success" => (
                $code === $expectedCode
            ),
            "response" => $response
        ];

    };


    /*
    -------------------------------------------------------
    GMAIL GREETING
    -------------------------------------------------------
    */

    $result = $sendCommand(
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
    -------------------------------------------------------
    EHLO
    -------------------------------------------------------
    */

    $result = $sendCommand(
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
    -------------------------------------------------------
    AUTH LOGIN
    -------------------------------------------------------
    */

    $result = $sendCommand(
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
    -------------------------------------------------------
    GMAIL USERNAME
    -------------------------------------------------------
    */

    $result = $sendCommand(
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
    -------------------------------------------------------
    GMAIL APP PASSWORD
    -------------------------------------------------------
    */

    $cleanAppPassword = str_replace(
        " ",
        "",
        $gmailAppPassword
    );


    $result = $sendCommand(
        base64_encode($cleanAppPassword),
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
    -------------------------------------------------------
    MAIL FROM
    -------------------------------------------------------
    */

    $result = $sendCommand(
        "MAIL FROM:<" . $gmailUser . ">",
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
    -------------------------------------------------------
    RCPT TO
    -------------------------------------------------------
    */

    $result = $sendCommand(
        "RCPT TO:<" . $to . ">",
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
    -------------------------------------------------------
    DATA
    -------------------------------------------------------
    */

    $result = $sendCommand(
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
    -------------------------------------------------------
    EMAIL HEADERS
    -------------------------------------------------------
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
    -------------------------------------------------------
    SEND MESSAGE
    -------------------------------------------------------
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
    -------------------------------------------------------
    QUIT
    -------------------------------------------------------
    */

    fwrite(
        $socket,
        "QUIT\r\n"
    );


    fclose($socket);


    /*
    -------------------------------------------------------
    CHECK RESPONSE
    -------------------------------------------------------
    */

    if (
        substr(
            sanitize($response),
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
===========================================================
SEND OTP EMAIL
===========================================================
*/

function sendGmailOTP(
    $to,
    $studentName,
    $otp,
    $gmailUser,
    $gmailAppPassword,
    $schoolEmail,
    $schoolName
) {

    $safeName =
        e($studentName);

    $safeSchool =
        e($schoolName);


    $subject =
        "Email Verification - " .
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
                    Thank you for beginning your
                    enrollment with
                    <strong>$safeSchool</strong>.
                </p>


                <p>
                    Please use the verification
                    code below to confirm that this
                    email address belongs to you.
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

                    If you did not start an enrollment,
                    you can safely ignore this email.

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
===========================================================
START ENROLLMENT
===========================================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["start_enrollment"])
) {

    /*
    -------------------------------------------------------
    COLLECT DATA
    -------------------------------------------------------
    */

    $studentName =
        sanitize(
            $_POST["student_name"] ?? ""
        );


    $dateOfBirth =
        sanitize(
            $_POST["date_of_birth"] ?? ""
        );


    $gender =
        sanitize(
            $_POST["gender"] ?? ""
        );


    $applyingFor =
        sanitize(
            $_POST["applying_for"] ?? ""
        );


    $parentName =
        sanitize(
            $_POST["parent_name"] ?? ""
        );


    $phone =
        sanitize(
            $_POST["phone"] ?? ""
        );


    $email =
        strtolower(
            sanitize(
                $_POST["email"] ?? ""
            )
        );


    $notes =
        sanitize(
            $_POST["notes"] ?? ""
        );


    /*
    -------------------------------------------------------
    VALIDATE REQUIRED FIELDS
    -------------------------------------------------------
    */

    if (
        $studentName === "" ||
        $dateOfBirth === "" ||
        $gender === "" ||
        $applyingFor === "" ||
        $parentName === "" ||
        $phone === "" ||
        $email === ""
    ) {

        $status =
            "Please complete all required fields.";

        $statusType =
            "danger";

    }


    /*
    -------------------------------------------------------
    VALIDATE GENDER
    -------------------------------------------------------
    */

    elseif (
        !in_array(
            $gender,
            [
                "Male",
                "Female"
            ],
            true
        )
    ) {

        $status =
            "Please select a valid gender.";

        $statusType =
            "danger";

    }


    /*
    -------------------------------------------------------
    VALIDATE EMAIL
    -------------------------------------------------------
    */

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $status =
            "Please enter a valid email address.";

        $statusType =
            "danger";

    }


    /*
    -------------------------------------------------------
    CHECK DUPLICATE EMAIL
    -------------------------------------------------------
    */

    else {

        $check =
            $conn->prepare(
                "
                SELECT id
                FROM enroll_table
                WHERE email = ?
                LIMIT 1
                "
            );


        if (!$check) {

            $status =
                "Database error: " .
                $conn->error;

            $statusType =
                "danger";

        } else {

            $check->bind_param(
                "s",
                $email
            );


            $check->execute();


            $checkResult =
                $check->get_result();


            if (
                $checkResult->num_rows > 0
            ) {

                $status =
                    "This email address has already been used for an enrollment.";

                $statusType =
                    "warning";


                $check->close();

            } else {

                $check->close();


                /*
                ------------------------------------------------
                PASSPORT UPLOAD
                ------------------------------------------------
                */

                $passportPath = "";


                if (
                    isset($_FILES["passport"]) &&
                    $_FILES["passport"]["error"] !==
                    UPLOAD_ERR_NO_FILE
                ) {

                    /*
                    --------------------------------------------
                    CHECK UPLOAD ERROR
                    --------------------------------------------
                    */

                    if (
                        $_FILES["passport"]["error"] !==
                        UPLOAD_ERR_OK
                    ) {

                        $status =
                            "There was a problem uploading the passport.";

                        $statusType =
                            "danger";

                    } else {

                        /*
                        ----------------------------------------
                        MAXIMUM FILE SIZE
                        ----------------------------------------
                        */

                        $maxSize =
                            5 * 1024 * 1024;


                        if (
                            $_FILES["passport"]["size"] >
                            $maxSize
                        ) {

                            $status =
                                "Passport image must not exceed 5MB.";

                            $statusType =
                                "danger";

                        } else {

                            $tmpName =
                                $_FILES["passport"]["tmp_name"];


                            /*
                            ------------------------------------
                            CHECK IMAGE
                            ------------------------------------
                            */

                            $imageInfo =
                                @getimagesize(
                                    $tmpName
                                );


                            if (
                                $imageInfo === false
                            ) {

                                $status =
                                    "Please upload a valid image.";

                                $statusType =
                                    "danger";

                            } else {

                                $mime =
                                    $imageInfo["mime"];


                                /*
                                --------------------------------
                                ALLOWED IMAGE TYPES
                                --------------------------------
                                */

                                $allowedTypes = [

                                    "image/jpeg" =>
                                        "jpg",

                                    "image/png" =>
                                        "png",

                                    "image/webp" =>
                                        "webp"

                                ];


                                if (
                                    !isset(
                                        $allowedTypes[$mime]
                                    )
                                ) {

                                    $status =
                                        "Only JPG, PNG and WEBP passport images are allowed.";

                                    $statusType =
                                        "danger";

                                } else {

                                    /*
                                    --------------------------------
                                    CREATE UPLOAD DIRECTORY
                                    --------------------------------
                                    */

                                    $uploadDirectory =
                                        __DIR__ .
                                        "/uploads/passports";


                                    if (
                                        !is_dir(
                                            $uploadDirectory
                                        )
                                    ) {

                                        if (
                                            !mkdir(
                                                $uploadDirectory,
                                                0755,
                                                true
                                            )
                                        ) {

                                            $status =
                                                "The passport upload directory could not be created.";

                                            $statusType =
                                                "danger";

                                        }

                                    }


                                    /*
                                    --------------------------------
                                    CONTINUE IF DIRECTORY EXISTS
                                    --------------------------------
                                    */

                                    if (
                                        $status === ""
                                    ) {

                                        $extension =
                                            $allowedTypes[
                                                $mime
                                            ];


                                        $filename =
                                            "passport_" .
                                            bin2hex(
                                                random_bytes(12)
                                            ) .
                                            "." .
                                            $extension;


                                        $destination =
                                            $uploadDirectory .
                                            "/" .
                                            $filename;


                                        /*
                                        --------------------------------
                                        MOVE UPLOADED FILE
                                        --------------------------------
                                        */

                                        if (
                                            move_uploaded_file(
                                                $tmpName,
                                                $destination
                                            )
                                        ) {

                                            $passportPath =
                                                "uploads/passports/" .
                                                $filename;

                                        } else {

                                            $status =
                                                "The passport could not be uploaded.";

                                            $statusType =
                                                "danger";

                                        }

                                    }

                                }

                            }

                        }

                    }

                }


                /*
                ------------------------------------------------
                CONTINUE IF PASSPORT PROCESSING SUCCEEDED
                ------------------------------------------------
                */

                if (
                    $status === ""
                ) {

                    /*
                    --------------------------------------------
                    STORE ENROLLMENT DATA IN SESSION
                    --------------------------------------------
                    */

                    $_SESSION["enrollment_data"] = [

                        "student_name" =>
                            $studentName,

                        "date_of_birth" =>
                            $dateOfBirth,

                        "gender" =>
                            $gender,

                        "applying_for" =>
                            $applyingFor,

                        "parent_name" =>
                            $parentName,

                        "phone" =>
                            $phone,

                        "email" =>
                            $email,

                        "notes" =>
                            $notes,

                        "passport" =>
                            $passportPath

                    ];


                    /*
                    --------------------------------------------
                    GENERATE OTP
                    --------------------------------------------
                    */

                    $otp =
                        generateOTP();


                    /*
                    --------------------------------------------
                    SEND OTP EMAIL
                    --------------------------------------------
                    */

                    $emailResult =
                        sendGmailOTP(
                            $email,
                            $studentName,
                            $otp,
                            $gmailUser,
                            $gmailAppPassword,
                            $schoolEmail,
                            $schoolName
                        );


                    /*
                    --------------------------------------------
                    OTP SENT SUCCESSFULLY
                    --------------------------------------------
                    */

                    if (
                        $emailResult["success"]
                    ) {

                        /*
                        Store OTP in session
                        */

                        $_SESSION["enrollment_otp"] =
                            $otp;


                        /*
                        OTP expires after 10 minutes
                        */

                        $_SESSION["otp_expires"] =
                            time() + 600;


                        /*
                        Reset OTP attempts
                        */

                        $_SESSION["otp_attempts"] =
                            0;


                        /*
                        Record time OTP was sent
                        */

                        $_SESSION["last_otp_sent"] =
                            time();


                        /*
                        Redirect to separate OTP page
                        */

                        header(
                            "Location: verify_otp.php"
                        );

                        exit;

                    }


                    /*
                    --------------------------------------------
                    OTP COULD NOT BE SENT
                    --------------------------------------------
                    */

                    else {

                        /*
                        Delete uploaded passport
                        */

                        if (
                            !empty(
                                $passportPath
                            )
                        ) {

                            $passportFile =
                                __DIR__ .
                                "/" .
                                $passportPath;


                            if (
                                file_exists(
                                    $passportFile
                                )
                            ) {

                                unlink(
                                    $passportFile
                                );

                            }

                        }


                        /*
                        Clear enrollment session
                        */

                        unset(
                            $_SESSION["enrollment_data"],
                            $_SESSION["enrollment_otp"],
                            $_SESSION["otp_expires"],
                            $_SESSION["otp_attempts"],
                            $_SESSION["last_otp_sent"]
                        );


                        $status =
                            "We could not send the verification code. " .
                            $emailResult["error"];


                        $statusType =
                            "danger";

                    }

                }

            }

        }

    }

}


/*
===========================================================
PAGE DATA
===========================================================
*/

    $schoolName =
    $schoolName;

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
        Student Enrollment -
        <?php echo e($schoolName); ?>
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

            gap: 20px;

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

        .enrollment-page {

            width: 100%;

            max-width: 850px;

            margin: 0 auto;

            padding: 50px 20px;

        }


        .eyebrow {

            margin: 0 0 8px;

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

            color: #52657f;

            font-size: 13px;

            line-height: 1.7;

            margin:
                10px 0 25px;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .card {

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 8px;

            padding: 30px;

            box-shadow:
                0 3px 15px rgba(6, 31, 73, .05);

        }


        .section-title {

            margin:
                0 0 20px;

            padding-bottom: 10px;

            border-bottom:
                1px solid #eee;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 20px;

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
           FORM
        ===================================================== */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;

        }


        .field {

            margin-bottom: 5px;

        }


        .field.full {

            grid-column:
                1 / -1;

        }


        .field label {

            display: block;

            margin-bottom: 7px;

            font-size: 12px;

            font-weight: bold;

        }


        .field input,
        .field select,
        .field textarea {

            width: 100%;

            border:
                1px solid #d7d4ce;

            border-radius: 4px;

            background: white;

            color: #061f49;

            outline: none;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        .field input,
        .field select {

            height: 43px;

            padding: 0 12px;

        }


        .field textarea {

            padding: 12px;

            resize: vertical;

        }


        .field input:focus,
        .field select:focus,
        .field textarea:focus {

            border-color: #d66a2b;

            box-shadow:
                0 0 0 2px rgba(214, 106, 43, .08);

        }


        .file-help {

            margin-top: 6px;

            color: #777;

            font-size: 10px;

        }


        .submit-btn {

            width: 100%;

            height: 45px;

            border: none;

            border-radius: 4px;

            background: #d66a2b;

            color: white;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 25px;

        }


        .submit-btn:hover {

            background: #c55d25;

        }


        /* =====================================================
           FOOTER
        ===================================================== */

        footer {

            margin-top: 30px;

            padding: 25px 20px;

            background: #061f49;

            color: #cbd5e1;

            text-align: center;

            font-size: 12px;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 650px) {

            .navbar {

                padding: 0 18px;

                min-height: 65px;

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


            .enrollment-page {

                padding:
                    35px 15px;

            }


            h1 {

                font-size: 28px;

            }


            .card {

                padding: 20px;

            }


            .form-grid {

                grid-template-columns: 1fr;

            }


            .field.full {

                grid-column: auto;

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


            <a href="check_result.php">

                Check Result

            </a>


            <a href="./admin_login.php">

                Admin

            </a>


        </div>


    </nav>



    <!-- =====================================================
         PAGE
    ===================================================== -->

    <main class="enrollment-page">


        <p class="eyebrow">

            ADMISSIONS

        </p>


        <h1>

            Student Enrollment

        </h1>


        <p class="intro">

            Complete the form below to begin the
            enrollment process. You will receive a
            verification code by email before your
            application is submitted.

        </p>



        <?php if ($status !== ""): ?>


            <div
                class="alert <?php echo e($statusType); ?>"
            >

                <?php echo e($status); ?>

            </div>


        <?php endif; ?>



        <!-- =====================================================
             ENROLLMENT FORM
        ===================================================== -->

        <section class="card">


            <h2 class="section-title">

                Student Information

            </h2>


            <form
                method="POST"
                enctype="multipart/form-data"
                action=""
            >


                <div class="form-grid">


                    <!-- STUDENT NAME -->

                    <div class="field">


                        <label for="student_name">

                            Student's Full Name *

                        </label>


                        <input
                            type="text"
                            id="student_name"
                            name="student_name"
                            placeholder="Enter student's full name"
                            value="<?php

                                echo e(
                                    $_POST["student_name"]
                                    ?? ""
                                );

                            ?>"
                            required
                        >


                    </div>



                    <!-- DATE OF BIRTH -->

                    <div class="field">


                        <label for="date_of_birth">

                            Date of Birth *

                        </label>


                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="<?php

                                echo e(
                                    $_POST["date_of_birth"]
                                    ?? ""
                                );

                            ?>"
                            required
                        >


                    </div>



                    <!-- GENDER -->

                    <div class="field">


                        <label for="gender">

                            Gender *

                        </label>


                        <select
                            id="gender"
                            name="gender"
                            required
                        >


                            <option value="">

                                Select Gender

                            </option>


                            <option
                                value="Male"

                                <?php

                                    echo (
                                        (
                                            $_POST["gender"]
                                            ?? ""
                                        ) === "Male"
                                    )
                                        ? "selected"
                                        : "";

                                ?>

                            >

                                Male

                            </option>


                            <option
                                value="Female"

                                <?php

                                    echo (
                                        (
                                            $_POST["gender"]
                                            ?? ""
                                        ) === "Female"
                                    )
                                        ? "selected"
                                        : "";

                                ?>

                            >

                                Female

                            </option>


                        </select>


                    </div>



                    <!-- CLASS -->

                    <div class="field">


                        <label for="applying_for">

                            Applying For *

                        </label>


                        <select
                            id="applying_for"
                            name="applying_for"
                            required
                        >


                            <option value="">

                                Select Class

                            </option>


                            <?php

                            $grades = [

                                "Creche",

                                "Nursery 1",
                                "Nursery 2",
                                "Nursery 3",

                                "Primary 1",
                                "Primary 2",
                                "Primary 3",
                                "Primary 4",
                                "Primary 5",
                                "Primary 6",

                                "JSS 1",
                                "JSS 2",
                                "JSS 3",

                                "SS 1",
                                "SS 2",
                                "SS 3"

                            ];

                            ?>


                            <?php foreach (
                                $grades as $grade
                            ): ?>


                                <option
                                    value="<?php echo e($grade); ?>"

                                    <?php

                                        echo (
                                            (
                                                $_POST[
                                                    "applying_for"
                                                ]
                                                ?? ""
                                            ) === $grade
                                        )
                                            ? "selected"
                                            : "";

                                    ?>

                                >

                                    <?php

                                        echo e(
                                            $grade
                                        );

                                    ?>

                                </option>


                            <?php endforeach; ?>


                        </select>


                    </div>



                    <!-- PARENT -->

                    <div class="field">


                        <label for="parent_name">

                            Parent / Guardian *

                        </label>


                        <input
                            type="text"
                            id="parent_name"
                            name="parent_name"
                            placeholder="Parent or guardian name"
                            value="<?php

                                echo e(
                                    $_POST["parent_name"]
                                    ?? ""
                                );

                            ?>"
                            required
                        >


                    </div>



                    <!-- PHONE -->

                    <div class="field">


                        <label for="phone">

                            Phone Number *

                        </label>


                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="08012345678"
                            value="<?php

                                echo e(
                                    $_POST["phone"]
                                    ?? ""
                                );

                            ?>"
                            required
                        >


                    </div>



                    <!-- EMAIL -->

                    <div class="field">


                        <label for="email">

                            Email Address *

                        </label>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="parent@example.com"
                            value="<?php

                                echo e(
                                    $_POST["email"]
                                    ?? ""
                                );

                            ?>"
                            autocomplete="email"
                            required
                        >


                    </div>



                    <!-- PASSPORT -->

                    <div class="field">


                        <label for="passport">

                            Student Passport

                        </label>


                        <input
                            type="file"
                            id="passport"
                            name="passport"
                            accept="image/jpeg,image/png,image/webp"
                        >


                        <div class="file-help">

                            JPG, PNG or WEBP.
                            Maximum size: 5MB.

                        </div>


                    </div>



                    <!-- NOTES -->

                    <div class="field full">


                        <label for="notes">

                            Additional Notes

                        </label>


                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            placeholder="Any additional information..."
                        ><?php

                            echo e(
                                $_POST["notes"]
                                ?? ""
                            );

                        ?></textarea>


                    </div>


                </div>



                <button
                    type="submit"
                    name="start_enrollment"
                    class="submit-btn"
                >

                    Continue & Verify Email

                </button>


            </form>


        </section>


    </main>



    <!-- =====================================================
         FOOTER
    ===================================================== -->

    <footer>


        <p>

            © 2026

            <?php echo e($schoolName); ?>.

            All Rights Reserved.

        </p>


    </footer>


</body>

</html>