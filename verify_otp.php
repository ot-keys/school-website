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
    Use your Gmail App Password here.
    Remove spaces if necessary.
*/

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolEmail = "favour.efado@gmail.com";

$schoolName = "School Model Schools";


/*
===========================================================
STATUS
===========================================================
*/

$status = "";

$statusType = "";


/*
===========================================================
HELPER
===========================================================
*/

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
===========================================================
CHECK ENROLLMENT SESSION
===========================================================
*/

if (
    empty($_SESSION["enrollment_data"]) ||
    empty($_SESSION["enrollment_otp"])
) {

    header("Location: enroll.php");

    exit;

}


$enrollmentData =
    $_SESSION["enrollment_data"];


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
    SMTP COMMAND
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

        $response =
            $readResponse();


        $code =
            substr(
                sanitize($response),
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
    -------------------------------------------------------
    GREETING
    -------------------------------------------------------
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
    -------------------------------------------------------
    EHLO
    -------------------------------------------------------
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
    -------------------------------------------------------
    AUTH LOGIN
    -------------------------------------------------------
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
    -------------------------------------------------------
    USERNAME
    -------------------------------------------------------
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
    -------------------------------------------------------
    APP PASSWORD
    -------------------------------------------------------
    */

    $appPassword =
        str_replace(
            " ",
            "",
            $gmailAppPassword
        );


    $result =
        $sendCommand(
            base64_encode($appPassword),
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
    -------------------------------------------------------
    RECIPIENT
    -------------------------------------------------------
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
    -------------------------------------------------------
    DATA
    -------------------------------------------------------
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
    -------------------------------------------------------
    HEADERS
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
RESEND OTP
===========================================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["resend_otp"])
) {

    /*
    -------------------------------------------------------
    Check enrollment data
    -------------------------------------------------------
    */

    if (
        empty($_SESSION["enrollment_data"])
    ) {

        header("Location: enroll.php");

        exit;

    }


    /*
    -------------------------------------------------------
    Prevent excessive resend
    -------------------------------------------------------
    */

    $lastSent =
        $_SESSION["last_otp_sent"] ?? 0;


    if (
        time() - $lastSent < 60
    ) {

        $remaining =
            60 -
            (
                time() -
                $lastSent
            );


        $status =
            "Please wait " .
            $remaining .
            " seconds before requesting another OTP.";

        $statusType =
            "warning";

    } else {


        /*
        ---------------------------------------------------
        Generate new OTP
        ---------------------------------------------------
        */

        $newOTP =
            (string)random_int(
                100000,
                999999
            );


        $studentName =
            $enrollmentData["student_name"];


        $studentEmail =
            $enrollmentData["email"];


        /*
        ---------------------------------------------------
        Email HTML
        ---------------------------------------------------
        */

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

        <body
            style='
                margin:0;
                padding:0;
                background:#fdfdef;
                font-family:Arial,
                Helvetica,sans-serif;
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

                    <h2>
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
                        Your new email verification
                        code is:
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
                            $newOTP
                        </span>

                    </div>


                    <p>
                        This code will expire in
                        <strong>10 minutes</strong>.
                    </p>

                    <p>
                        If you did not request this
                        code, please ignore this email.
                    </p>

                    <p>
                        Regards,<br>
                        <strong>
                            $safeSchool
                        </strong>
                    </p>

                </div>

            </div>

        </body>

        </html>

        ";


        /*
        ---------------------------------------------------
        Send OTP
        ---------------------------------------------------
        */

        $emailResult =
            sendGmailEmail(
                $studentEmail,
                $subject,
                $htmlBody,
                $gmailUser,
                $gmailAppPassword,
                $schoolName
            );


        if (
            $emailResult["success"]
        ) {

            $_SESSION["enrollment_otp"] =
                $newOTP;


            $_SESSION["otp_expires"] =
                time() + 600;


            $_SESSION["otp_attempts"] =
                0;


            $_SESSION["last_otp_sent"] =
                time();


            $status =
                "A new verification code has been sent to your email.";

            $statusType =
                "success";

        } else {

            $status =
                "Could not send the new OTP. " .
                $emailResult["error"];

            $statusType =
                "danger";

        }

    }

}


/*
===========================================================
VERIFY OTP
===========================================================
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["verify_otp"])
) {

    $enteredOTP =
        sanitize(
            $_POST["otp"] ?? ""
        );


    $savedOTP =
        $_SESSION["enrollment_otp"]
        ?? "";


    $expires =
        $_SESSION["otp_expires"]
        ?? 0;


    $attempts =
        $_SESSION["otp_attempts"]
        ?? 0;


    /*
    -------------------------------------------------------
    No OTP
    -------------------------------------------------------
    */

    if (
        empty($savedOTP)
    ) {

        $status =
            "No active verification code was found. " .
            "Please request a new OTP.";

        $statusType =
            "danger";

    }


    /*
    -------------------------------------------------------
    Maximum attempts
    -------------------------------------------------------
    */

    elseif (
        $attempts >= 5
    ) {

        $status =
            "Too many incorrect attempts. " .
            "Please request a new OTP.";

        $statusType =
            "danger";


        unset(
            $_SESSION["enrollment_otp"]
        );

    }


    /*
    -------------------------------------------------------
    OTP expired
    -------------------------------------------------------
    */

    elseif (
        time() > $expires
    ) {

        unset(
            $_SESSION["enrollment_otp"],
            $_SESSION["otp_expires"]
        );


        $status =
            "Your verification code has expired. " .
            "Please click Resend OTP.";

        $statusType =
            "warning";

    }


    /*
    -------------------------------------------------------
    Validate OTP format
    -------------------------------------------------------
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
    -------------------------------------------------------
    Compare OTP
    -------------------------------------------------------
    */

    elseif (
        !hash_equals(
            (string)$savedOTP,
            (string)$enteredOTP
        )
    ) {

        $_SESSION["otp_attempts"] =
            $attempts + 1;


        $remainingAttempts =
            5 -
            $_SESSION["otp_attempts"];


        $status =
            "Incorrect verification code. " .
            $remainingAttempts .
            " attempt(s) remaining.";

        $statusType =
            "danger";

    }


    /*
    -------------------------------------------------------
    OTP CORRECT
    -------------------------------------------------------
    */

    else {


        /*
        ---------------------------------------------------
        Get enrollment data
        ---------------------------------------------------
        */

        $data =
            $_SESSION["enrollment_data"]
            ?? null;


        if (!$data) {

            $status =
                "Your enrollment session has expired. " .
                "Please start the enrollment again.";

            $statusType =
                "danger";

        } else {


            /*
            -----------------------------------------------
            Check duplicate email
            -----------------------------------------------
            */

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
                    $data["email"]
                );


                $check->execute();


                $result =
                    $check->get_result();


                /*
                -------------------------------------------
                Duplicate
                -------------------------------------------
                */

                if (
                    $result->num_rows > 0
                ) {

                    $check->close();


                    /*
                    Delete uploaded passport
                    */

                    if (
                        !empty(
                            $data["passport"]
                        )
                    ) {

                        $passportFile =
                            __DIR__ .
                            "/" .
                            $data["passport"];


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


                    unset(
                        $_SESSION["enrollment_data"],
                        $_SESSION["enrollment_otp"],
                        $_SESSION["otp_expires"],
                        $_SESSION["otp_attempts"],
                        $_SESSION["last_otp_sent"]
                    );


                    $status =
                        "This email address has already been used for an enrollment.";

                    $statusType =
                        "warning";

                } else {


                    $check->close();


                    /*
                    ---------------------------------------
                    INSERT STUDENT
                    ---------------------------------------
                    */

                    $stmt =
                        $conn->prepare(
                            "
                            INSERT INTO enroll_table
                            (
                                student_name,
                                date_of_birth,
                                gender,
                                applying_for,
                                parent_name,
                                phone,
                                email,
                                notes,
                                passport
                            )
                            VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?)
                            "
                        );


                    if (!$stmt) {

                        $status =
                            "Database error while saving enrollment: " .
                            $conn->error;

                        $statusType =
                            "danger";

                    } else {


                        /*
                        -----------------------------------
                        IMPORTANT:
                        9 values = 9 s characters
                        -----------------------------------
                        */

                        $stmt->bind_param(
                            "sssssssss",

                            $data["student_name"],

                            $data["date_of_birth"],

                            $data["gender"],

                            $data["applying_for"],

                            $data["parent_name"],

                            $data["phone"],

                            $data["email"],

                            $data["notes"],

                            $data["passport"]
                        );


                        /*
                        -----------------------------------
                        Execute
                        -----------------------------------
                        */

                        if (
                            $stmt->execute()
                        ) {


                            $newStudentID =
                                $stmt->insert_id;


                            $stmt->close();


                            /*
                            --------------------------------
                            ADMIN NOTIFICATION EMAIL
                            --------------------------------
                            */

                            $safeStudentName =
                                e(
                                    $data["student_name"]
                                );


                            $safeDOB =
                                e(
                                    $data["date_of_birth"]
                                );


                            $safeGender =
                                e(
                                    $data["gender"]
                                );


                            $safeClass =
                                e(
                                    $data["applying_for"]
                                );


                            $safeParent =
                                e(
                                    $data["parent_name"]
                                );


                            $safePhone =
                                e(
                                    $data["phone"]
                                );


                            $safeEmail =
                                e(
                                    $data["email"]
                                );


                            $safeNotes =
                                nl2br(
                                    e(
                                        $data["notes"]
                                    )
                                );


                            $adminSubject =
                                "New Student Enrollment - " .
                                $schoolName;


                            $adminBody = "

                            <!DOCTYPE html>

                            <html>

                            <body
                                style='
                                    margin:0;
                                    padding:20px;
                                    background:#f5f6f8;
                                    font-family:Arial,
                                    Helvetica,sans-serif;
                                    color:#333;
                                '
                            >

                                <div
                                    style='
                                        max-width:650px;
                                        margin:auto;
                                        background:#ffffff;
                                        border:1px solid #ddd;
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

                                        <h2>
                                            $schoolName
                                        </h2>

                                        <p>
                                            New Student Enrollment
                                        </p>

                                    </div>


                                    <div
                                        style='
                                            padding:30px;
                                        '
                                    >

                                        <h3>
                                            A new student
                                            has enrolled.
                                        </h3>


                                        <table
                                            width='100%'
                                            cellpadding='10'
                                            cellspacing='0'
                                            style='
                                                border-collapse:
                                                collapse;
                                            '
                                        >

                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Student Name
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeStudentName
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Date of Birth
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeDOB
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Gender
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeGender
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Applying For
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeClass
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Parent / Guardian
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeParent
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Phone
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safePhone
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Email
                                                </td>

                                                <td
                                                    style='
                                                        border-bottom:
                                                        1px solid #eee;
                                                    '
                                                >
                                                    $safeEmail
                                                </td>
                                            </tr>


                                            <tr>
                                                <td
                                                    style='
                                                        font-weight:bold;
                                                    '
                                                >
                                                    Notes
                                                </td>

                                                <td>
                                                    $safeNotes
                                                </td>
                                            </tr>

                                        </table>


                                        <p
                                            style='
                                                margin-top:25px;
                                                color:#666;
                                                font-size:12px;
                                            '
                                        >
                                            Student ID:
                                            <strong>
                                                $newStudentID
                                            </strong>
                                        </p>


                                        <p
                                            style='
                                                color:#666;
                                                font-size:12px;
                                            '
                                        >
                                            The student's email
                                            address was successfully
                                            verified using OTP.
                                        </p>

                                    </div>

                                </div>

                            </body>

                            </html>

                            ";


                            /*
                            --------------------------------
                            Send admin notification
                            --------------------------------
                            */

                            $adminEmailResult =
                                sendGmailEmail(
                                    $schoolEmail,
                                    $adminSubject,
                                    $adminBody,
                                    $gmailUser,
                                    $gmailAppPassword,
                                    $schoolName
                                );


                            /*
                            --------------------------------
                            Clear session
                            --------------------------------
                            */

                            unset(
                                $_SESSION["enrollment_data"],
                                $_SESSION["enrollment_otp"],
                                $_SESSION["otp_expires"],
                                $_SESSION["otp_attempts"],
                                $_SESSION["last_otp_sent"]
                            );


                            /*
                            --------------------------------
                            Success
                            --------------------------------
                            */

                            $status =
                                "Enrollment completed successfully! " .
                                "Your email has been verified and " .
                                "your application has been submitted.";

                            $statusType =
                                "success";


                            /*
                            --------------------------------
                            Redirect after success
                            --------------------------------
                            */

                            $_SESSION[
                                "enrollment_success"
                            ] = $status;


                            header(
                                "Location: index.php"
                            );

                            exit;

                        } else {


                            $status =
                                "Enrollment could not be saved. " .
                                $stmt->error;

                            $statusType =
                                "danger";


                            $stmt->close();

                        }

                    }

                }

            }

        }

    }

}


/*
===========================================================
OTP DISPLAY DATA
===========================================================
*/

$otpEmail =
    $_SESSION[
        "enrollment_data"
    ]["email"]
    ?? "";


$otpExpires =
    $_SESSION["otp_expires"]
    ?? 0;


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
    Verify Email -
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

    display: flex;

    flex-direction: column;

}


/* =====================================================
   NAVBAR
===================================================== */

.navbar {

    width: 100%;

    min-height: 70px;

    padding:
        0 40px;

    background:
        #061f49;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

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

.page {

    width: 100%;

    max-width: 600px;

    margin:
        auto;

    padding:
        50px 20px;

}


.eyebrow {

    margin:
        0 0 8px;

    color:
        #d66a2b;

    font-size:
        10px;

    font-weight:
        bold;

    letter-spacing:
        3px;

    text-align:
        center;

}


h1 {

    margin:
        0;

    text-align:
        center;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        34px;

}


.intro {

    color:
        #52657f;

    font-size:
        13px;

    line-height:
        1.7;

    text-align:
        center;

    margin:
        10px auto 25px;

    max-width:
        500px;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background:
        #ffffff;

    border:
        1px solid #ddd9d1;

    border-radius:
        8px;

    padding:
        35px;

    box-shadow:
        0 3px 15px
        rgba(6,31,73,.05);

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding:
        13px 15px;

    border-radius:
        5px;

    margin-bottom:
        20px;

    font-size:
        13px;

    line-height:
        1.6;

}


.success {

    background:
        #d1e7dd;

    color:
        #0f5132;

}


.danger {

    background:
        #f8d7da;

    color:
        #842029;

}


.warning {

    background:
        #fff3cd;

    color:
        #664d03;

}


/* =====================================================
   ICON
===================================================== */

.otp-icon {

    width:
        70px;

    height:
        70px;

    margin:
        0 auto 20px;

    border-radius:
        50%;

    background:
        rgba(214,106,43,.1);

    color:
        #d66a2b;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        28px;

    font-weight:
        bold;

}


/* =====================================================
   OTP
===================================================== */

.otp-title {

    text-align:
        center;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        25px;

    margin:
        0 0 10px;

}


.otp-description {

    text-align:
        center;

    color:
        #65758d;

    font-size:
        13px;

    line-height:
        1.7;

}


.otp-email {

    text-align:
        center;

    font-weight:
        bold;

    color:
        #061f49;

    margin:
        12px 0 25px;

    word-break:
        break-word;

}


.otp-input {

    width:
        100%;

    height:
        60px;

    border:
        1px solid #d7d4ce;

    border-radius:
        5px;

    text-align:
        center;

    font-size:
        28px;

    letter-spacing:
        10px;

    color:
        #061f49;

    outline:
        none;

}


.otp-input:focus {

    border-color:
        #d66a2b;

    box-shadow:
        0 0 0 2px
        rgba(214,106,43,.08);

}


.verify-btn {

    width:
        100%;

    height:
        46px;

    border:
        none;

    border-radius:
        4px;

    background:
        #061f49;

    color:
        white;

    font-weight:
        bold;

    cursor:
        pointer;

    margin-top:
        15px;

}


.verify-btn:hover {

    background:
        #0b2d62;

}


.resend-btn {

    width:
        100%;

    height:
        44px;

    border:
        1px solid #061f49;

    border-radius:
        4px;

    background:
        white;

    color:
        #061f49;

    font-weight:
        bold;

    cursor:
        pointer;

    margin-top:
        10px;

}


.resend-btn:hover {

    background:
        #061f49;

    color:
        white;

}


.otp-timer {

    text-align:
        center;

    margin-top:
        15px;

    font-size:
        12px;

    color:
        #777;

}


.otp-note {

    margin-top:
        20px;

    padding:
        12px;

    background:
        #fafaf6;

    border:
        1px solid #e5e2da;

    border-radius:
        4px;

    color:
        #667085;

    font-size:
        11px;

    line-height:
        1.6;

}


/* =====================================================
   FOOTER
===================================================== */

footer {

    margin-top:
        auto;

    padding:
        25px 20px;

    background:
        #061f49;

    color:
        #cbd5e1;

    text-align:
        center;

    font-size:
        12px;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width:650px) {

    .navbar {

        padding:
            0 18px;

        min-height:
            65px;

    }


    .brand {

        font-size:
            17px;

    }


    .nav-links {

        gap:
            10px;

    }


    .nav-links a {

        font-size:
            11px;

    }


    .page {

        padding:
            35px 15px;

    }


    h1 {

        font-size:
            28px;

    }


    .card {

        padding:
            22px;

    }


    .otp-input {

        letter-spacing:
            6px;

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

        <?php
        echo e($schoolName);
        ?>

    </a>


    <div class="nav-links">

        <a href="index.php">
            Home
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
        EMAIL VERIFICATION
    </p>


    <h1>
        Verify Your Email
    </h1>


    <p class="intro">

        Enter the 6-digit verification code
        sent to your email address to complete
        your enrollment.

    </p>



    <?php if ($status !== ""): ?>

        <div
            class="alert
            <?php echo e($statusType); ?>"
        >

            <?php
            echo e($status);
            ?>

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
            to:

        </p>


        <div class="otp-email">

            <?php
            echo e($otpEmail);
            ?>

        </div>



        <!-- =================================================
             VERIFY FORM
        ================================================== -->

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
                autofocus
            >


            <button
                type="submit"
                name="verify_otp"
                class="verify-btn"
            >

                Verify Email & Complete Enrollment

            </button>

        </form>



        <!-- =================================================
             RESEND FORM
        ================================================== -->

        <form
            method="POST"
            action=""
        >

            <button
                type="submit"
                name="resend_otp"
                class="resend-btn"
            >

                Resend OTP

            </button>

        </form>



        <!-- =================================================
             TIMER
        ================================================== -->

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



        <!-- =================================================
             NOTE
        ================================================== -->

        <div class="otp-note">

            <strong>
                Didn't receive the email?
            </strong>

            <br>

            Check your spam or junk folder.

            If you still cannot find it,
            click <strong>Resend OTP</strong>.

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



<!-- =====================================================
     TIMER SCRIPT
===================================================== -->

<script>

let seconds =
    <?php
    echo (int)$secondsRemaining;
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


                if (seconds <= 0) {

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


</body>

</html>