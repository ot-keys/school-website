<?php

require_once "./includes/db.php";
require_once "./handlers/sanitize.php";

/* =========================================================
   SESSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   GMAIL SETTINGS
========================================================= */

$gmailUser = "favour.efado@gmail.com";

/*
   PUT YOUR NEW GMAIL APP PASSWORD HERE.

   Remove spaces from the App Password.

   Example:
   abcdefghijklmnop
*/

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolEmail = "favour.efado@gmail.com";


/* =========================================================
   STATUS
========================================================= */

$status = "";
$statusType = "";


/* =========================================================
   OLD VALUE
========================================================= */

function oldv($field)
{
    return htmlspecialchars(
        $_POST[$field] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   SEND APPROVAL EMAIL
========================================================= */

function sendApprovalEmail(
    $username,
    $password,
    $to,
    $fullName,
    $email,
    $approvalLink,
    $picturePath
) {

    $smtpServer = "smtp.gmail.com";
    $smtpPort = 587;

    $subject = "New Admin Registration - " . $fullName;


    /* -----------------------------------------------------
       EMAIL BODY
    ----------------------------------------------------- */

    $body = "
    <html>
    <body style='
        font-family: Arial, Helvetica, sans-serif;
        background:#fdfdef;
        padding:30px;
    '>

        <div style='
            max-width:600px;
            margin:auto;
            background:#ffffff;
            border:1px solid #ddd;
            border-radius:8px;
            padding:30px;
        '>

            <h2 style='color:#061f49;'>
                New Administrator Registration
            </h2>

            <p>
                Someone has registered to become an administrator
                on your school website.
            </p>

            <hr>

            <h3>Registration Details</h3>

            <p>
                <strong>Full Name:</strong>
                " . htmlspecialchars($fullName) . "
            </p>

            <p>
                <strong>Email:</strong>
                " . htmlspecialchars($email) . "
            </p>

            <p>
                <strong>Status:</strong>
                Pending Approval
            </p>

            <hr>

            <p>
                The person will NOT become an administrator
                until you approve the account.
            </p>

            <p>
                The applicant's passport/profile picture
                is attached to this email.
            </p>

            <p style='margin-top:25px;'>

                <a href='" . htmlspecialchars($approvalLink) . "'
                   style='
                    display:inline-block;
                    padding:14px 25px;
                    background:#d66a2b;
                    color:#ffffff;
                    text-decoration:none;
                    border-radius:5px;
                    font-weight:bold;
                   '>

                    MAKE ADMIN

                </a>

            </p>

            <p style='
                margin-top:25px;
                color:#777;
                font-size:13px;
            '>

                If you did not expect this registration,
                simply ignore this email.

            </p>

        </div>

    </body>
    </html>
    ";


    /* -----------------------------------------------------
       CONNECT TO GMAIL
    ----------------------------------------------------- */

    $socket = fsockopen(
        $smtpServer,
        $smtpPort,
        $errno,
        $errstr,
        30
    );

    if (!$socket) {
        return "Could not connect to Gmail SMTP: " . $errstr;
    }


    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "220") {
        fclose($socket);
        return "Gmail SMTP connection failed.";
    }


    /* -----------------------------------------------------
       EHLO
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "EHLO localhost\r\n"
    );

    do {

        $response = fgets($socket, 515);

    } while (
        $response &&
        substr($response, 3, 1) == "-"
    );


    /* -----------------------------------------------------
       STARTTLS
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "STARTTLS\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "220") {

        fclose($socket);

        return "Gmail STARTTLS failed.";
    }


    /* -----------------------------------------------------
       ENABLE TLS
    ----------------------------------------------------- */

    $crypto = stream_socket_enable_crypto(
        $socket,
        true,
        STREAM_CRYPTO_METHOD_TLS_CLIENT
    );

    if (!$crypto) {

        fclose($socket);

        return "Could not establish secure connection with Gmail.";
    }


    /* -----------------------------------------------------
       EHLO AFTER TLS
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "EHLO localhost\r\n"
    );

    do {

        $response = fgets($socket, 515);

    } while (
        $response &&
        substr($response, 3, 1) == "-"
    );


    /* -----------------------------------------------------
       AUTH LOGIN
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "AUTH LOGIN\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "334") {

        fclose($socket);

        return "Gmail authentication could not start.";
    }


    fwrite(
        $socket,
        base64_encode($username) . "\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "334") {

        fclose($socket);

        return "Gmail username was rejected.";
    }


    fwrite(
        $socket,
        base64_encode($password) . "\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "235") {

        fclose($socket);

        return "Gmail authentication failed. Check your App Password.";
    }


    /* -----------------------------------------------------
       MAIL FROM
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "MAIL FROM:<" . $username . ">\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "250") {

        fclose($socket);

        return "Gmail rejected the sender.";
    }


    /* -----------------------------------------------------
       RCPT TO
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "RCPT TO:<" . $to . ">\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "250") {

        fclose($socket);

        return "Gmail rejected the recipient.";
    }


    /* -----------------------------------------------------
       DATA
    ----------------------------------------------------- */

    fwrite(
        $socket,
        "DATA\r\n"
    );

    $response = fgets($socket, 515);

    if (substr($response, 0, 3) != "354") {

        fclose($socket);

        return "Gmail did not accept the email.";
    }


    /* -----------------------------------------------------
       CREATE MIME BOUNDARY
    ----------------------------------------------------- */

    $boundary = md5(
        uniqid(
            time(),
            true
        )
    );


    /* -----------------------------------------------------
       EMAIL HEADERS
    ----------------------------------------------------- */

    $headers = "";

    $headers .= "From: School Website <" . $username . ">\r\n";

    $headers .= "To: " . $to . "\r\n";

    $headers .= "Subject: " . $subject . "\r\n";

    $headers .= "MIME-Version: 1.0\r\n";

    $headers .= "Content-Type: multipart/mixed; boundary=\"" .
        $boundary .
        "\"\r\n";

    $headers .= "\r\n";


    /* -----------------------------------------------------
       HTML MESSAGE
    ----------------------------------------------------- */

    $message = "";

    $message .= "--" . $boundary . "\r\n";

    $message .=
        "Content-Type: text/html; charset=UTF-8\r\n";

    $message .=
        "Content-Transfer-Encoding: 8bit\r\n\r\n";

    $message .= $body;

    $message .= "\r\n";


    /* -----------------------------------------------------
       ATTACH PROFILE PICTURE
    ----------------------------------------------------- */

    if (
        !empty($picturePath) &&
        file_exists($picturePath)
    ) {

        $fileName = basename($picturePath);

        $fileData = file_get_contents(
            $picturePath
        );

        $encodedFile = chunk_split(
            base64_encode($fileData)
        );


        $message .=
            "--" . $boundary . "\r\n";

        $message .=
            "Content-Type: application/octet-stream; name=\"" .
            $fileName .
            "\"\r\n";

        $message .=
            "Content-Transfer-Encoding: base64\r\n";

        $message .=
            "Content-Disposition: attachment; filename=\"" .
            $fileName .
            "\"\r\n\r\n";

        $message .=
            $encodedFile . "\r\n";
    }


    $message .=
        "--" . $boundary . "--\r\n";


    /* -----------------------------------------------------
       SEND EMAIL
    ----------------------------------------------------- */

    fwrite(
        $socket,
        $headers .
        $message .
        "\r\n.\r\n"
    );


    $response = fgets($socket, 515);


    if (substr($response, 0, 3) != "250") {

        fclose($socket);

        return "Gmail could not send the email.";
    }


    fwrite(
        $socket,
        "QUIT\r\n"
    );

    fclose($socket);


    return true;
}


/* =========================================================
   ADMIN APPROVAL
========================================================= */

if (
    isset($_GET["approve"]) &&
    !empty($_GET["approve"])
) {

    $token = sanitize(
        $_GET["approve"]
    );


    $stmt = $conn->prepare("
        SELECT id
        FROM admin_table
        WHERE approval_token = ?
        AND status = 'pending'
        LIMIT 1
    ");


    $stmt->bind_param(
        "s",
        $token
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        $adminId = $admin["id"];


        $update = $conn->prepare("
            UPDATE admin_table
            SET status = 'approved',
                approval_token = NULL
            WHERE id = ?
        ");


        $update->bind_param(
            "i",
            $adminId
        );

        $update->execute();


        ?>

        <!DOCTYPE html>

        <html lang="en">

        <head>

            <meta charset="UTF-8">

            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            >

            <title>Admin Approved</title>

            <style>

                * {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    background: #fdfdef;
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }

                .message {
                    width: 90%;
                    max-width: 450px;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 35px;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,.08);
                }

                .message h1 {
                    font-family: Georgia, serif;
                    color: #061f49;
                }

                .message p {
                    color: #555;
                    line-height: 1.6;
                }

                .message a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 12px 25px;
                    background: #d66a2b;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                }

            </style>

        </head>

        <body>

            <div class="message">

                <h1>
                    Admin Approved
                </h1>

                <p>
                    The administrator account has been
                    successfully approved.
                </p>

                <a href="admin_login.php">
                    Go to Admin Login
                </a>

            </div>

        </body>

        </html>

        <?php

        exit;

    } else {

        ?>

        <!DOCTYPE html>

        <html lang="en">

        <head>

            <meta charset="UTF-8">

            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            >

            <title>Approval Failed</title>

            <style>

                body {
                    margin: 0;
                    background: #fdfdef;
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }

                .message {
                    background: white;
                    padding: 35px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    text-align: center;
                }

                .message h2 {
                    color: #061f49;
                }

                .message p {
                    color: #555;
                }

            </style>

        </head>

        <body>

            <div class="message">

                <h2>
                    Approval Link Invalid
                </h2>

                <p>
                    This account may already have been approved,
                    or the link is no longer valid.
                </p>

            </div>

        </body>

        </html>

        <?php

        exit;
    }
}


/* =========================================================
   REGISTRATION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = sanitize(
        $_POST["full_name"] ?? ""
    );

    $email = sanitize(
        $_POST["email"] ?? ""
    );

    $password = $_POST["password"] ?? "";


    /* -----------------------------------------------------
       PROFILE PICTURE
    ----------------------------------------------------- */

    $pictureName = "";

    $picturePath = "";


    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if (
        $full_name === "" ||
        $email === "" ||
        $password === ""
    ) {

        $status = "Please fill in all fields.";

        $statusType = "danger";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $status = "Please enter a valid email address.";

        $statusType = "danger";

    } elseif (
        strlen($password) < 6
    ) {

        $status =
            "Password must be at least 6 characters.";

        $statusType = "danger";

    } elseif (
        !isset($_FILES["profile_picture"]) ||
        $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_OK
    ) {

        $status =
            "Please upload your profile picture.";

        $statusType = "danger";

    } else {


        /* -------------------------------------------------
           CHECK EMAIL
        ------------------------------------------------- */

        $check = $conn->prepare("
            SELECT id
            FROM admin_table
            WHERE email = ?
            LIMIT 1
        ");


        $check->bind_param(
            "s",
            $email
        );

        $check->execute();

        $check->store_result();


        if ($check->num_rows > 0) {

            $status =
                "An account with this email already exists.";

            $statusType = "danger";

        } else {


            /* ---------------------------------------------
               CHECK IMAGE
            --------------------------------------------- */

            $allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            $imageType = $_FILES["profile_picture"]["type"];

            $imageSize = $_FILES["profile_picture"]["size"];


            if (
                !in_array(
                    $imageType,
                    $allowedTypes
                )
            ) {

                $status =
                    "Only JPG, PNG and WEBP images are allowed.";

                $statusType = "danger";

            } elseif (
                $imageSize > 5 * 1024 * 1024
            ) {

                $status =
                    "Profile picture must not exceed 5MB.";

                $statusType = "danger";

            } else {


                /* -----------------------------------------
                   CREATE UPLOAD DIRECTORY
                ----------------------------------------- */

                $uploadDirectory =
                    __DIR__ .
                    "/uploads/admins/";


                if (
                    !is_dir(
                        $uploadDirectory
                    )
                ) {

                    mkdir(
                        $uploadDirectory,
                        0777,
                        true
                    );
                }


                /* -----------------------------------------
                   CREATE UNIQUE FILE NAME
                ----------------------------------------- */

                $extension = strtolower(
                    pathinfo(
                        $_FILES["profile_picture"]["name"],
                        PATHINFO_EXTENSION
                    )
                );


                $pictureName =
                    "admin_" .
                    time() .
                    "_" .
                    bin2hex(
                        random_bytes(5)
                    ) .
                    "." .
                    $extension;


                $picturePath =
                    $uploadDirectory .
                    $pictureName;


                /* -----------------------------------------
                   MOVE IMAGE
                ----------------------------------------- */

                if (
                    !move_uploaded_file(
                        $_FILES["profile_picture"]["tmp_name"],
                        $picturePath
                    )
                ) {

                    $status =
                        "Could not upload the profile picture.";

                    $statusType = "danger";

                } else {


                    /* -------------------------------------
                       HASH PASSWORD
                    ------------------------------------- */

                    $hashedPassword =
                        password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );


                    /* -------------------------------------
                       APPROVAL TOKEN
                    ------------------------------------- */

                    $approvalToken =
                        bin2hex(
                            random_bytes(32)
                        );


                    /* -------------------------------------
                       SAVE TO DATABASE
                    ------------------------------------- */

                    $stmt = $conn->prepare("
                        INSERT INTO admin_table
                        (
                            full_name,
                            email,
                            password,
                            profile_picture,
                            approval_token,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, 'pending')
                    ");


                    if (!$stmt) {

                        unlink($picturePath);

                        $status =
                            "Database error: " .
                            $conn->error;

                        $statusType = "danger";

                    } else {


                        $stmt->bind_param(
                            "sssss",
                            $full_name,
                            $email,
                            $hashedPassword,
                            $pictureName,
                            $approvalToken
                        );


                        if (
                            $stmt->execute()
                        ) {


                            /* -----------------------------
                               APPROVAL LINK
                            ----------------------------- */

                            $approvalLink =
                                "http://localhost/school_website/admin_approve.php?approve=" .
                                urlencode(
                                    $approvalToken
                                );


                            /* -----------------------------
                               SEND EMAIL
                            ----------------------------- */

                            $emailResult =
                                sendApprovalEmail(
                                    $gmailUser,
                                    $gmailAppPassword,
                                    $schoolEmail,
                                    $full_name,
                                    $email,
                                    $approvalLink,
                                    $picturePath
                                );


                            if (
                                $emailResult === true
                            ) {

                                $status =
                                    "Registration successful! Your account is waiting for administrator approval.";

                                $statusType =
                                    "success";


                                $_POST = [];

                            } else {

                                $status =
                                    "Registration was saved successfully, but the approval email could not be sent.<br><br>" .
                                    "<strong>Email error:</strong> " .
                                    htmlspecialchars(
                                        $emailResult,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                $statusType =
                                    "warning";
                            }

                        } else {

                            unlink($picturePath);

                            $status =
                                "Could not create the administrator account: " .
                                $stmt->error;

                            $statusType =
                                "danger";
                        }


                        $stmt->close();
                    }
                }
            }
        }

        $check->close();
    }
}

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
        Register as Administrator
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background: #fdfdef;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #061f49;

        }


        /* =================================================
           NAVBAR
        ================================================= */

        .navbar {

            background: #061f49 !important;

            padding: 15px 0;

        }


        .navbar-brand {

            color: white !important;

            font-weight: 700;

            font-size: 20px;

        }


        .navbar-brand i {

            color: #ff6a00;

        }


        .navbar-nav .nav-link {

            color: white;

            transition: .3s;

        }


        .navbar-nav .nav-link:hover {

            color: #FFD700;

        }


        .glass {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 10px 22px;

            border-radius: 50px;

            color: #1a1a1a;

            font-weight: 600;

            text-decoration: none;

            background:
                rgba(255,255,255,.65);

            border:
                1px solid
                rgba(255,255,255,.9);

            transition: .3s;

        }


        .glass:hover {

            transform:
                translateY(-2px);

            background:
                rgba(255,255,255,.85);

        }


        /* =================================================
           REGISTER PAGE
        ================================================= */

        .register-page {

            width: 100%;

            max-width: 600px;

            margin: auto;

            padding:
                70px 20px 80px;

        }


        .eyebrow {

            margin:
                0 0 8px;

            color:
                #d55f2a;

            font-size:
                9px;

            font-weight:
                bold;

            letter-spacing:
                3px;

        }


        .register-page h1 {

            margin: 0;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size:
                32px;

            line-height:
                1.15;

            color:
                #061f49;

        }


        .intro {

            color:
                #31517c;

            font-size:
                13px;

            line-height:
                1.6;

            margin:
                12px 0 25px;

        }


        .form-card {

            background:
                #ffffff;

            border:
                1px solid
                #ddd9d1;

            border-radius:
                8px;

            padding:
                25px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.05);

        }


        .field {

            margin-bottom:
                18px;

        }


        label {

            display:
                block;

            margin-bottom:
                6px;

            font-size:
                12px;

            font-weight:
                600;

            color:
                #061f49;

        }


        input {

            width:
                100%;

            height:
                40px;

            padding:
                0 11px;

            border:
                1px solid
                #d7d4ce;

            border-radius:
                4px;

            outline:
                none;

            background:
                #fff;

        }


        input:focus {

            border-color:
                #d05f2a;

            box-shadow:
                0 0 0 3px
                rgba(208,95,42,.08);

        }


        /* =================================================
           PROFILE PICTURE
        ================================================= */

        .picture-upload {

            text-align:
                center;

            margin-bottom:
                25px;

        }


        .picture-preview {

            width:
                120px;

            height:
                120px;

            border-radius:
                50%;

            margin:
                0 auto 15px;

            background:
                #f1f1f1;

            border:
                3px solid
                #d66a2b;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            overflow:
                hidden;

            color:
                #999;

            font-size:
                40px;

        }


        .picture-preview img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .upload-label {

            display:
                inline-block;

            padding:
                9px 18px;

            background:
                #061f49;

            color:
                white;

            border-radius:
                4px;

            cursor:
                pointer;

            font-size:
                12px;

        }


        .upload-label:hover {

            background:
                #0d3b87;

        }


        #profile_picture {

            display:
                none;

        }


        .picture-help {

            margin-top:
                8px;

            color:
                #777;

            font-size:
                11px;

        }


        /* =================================================
           BUTTON
        ================================================= */

        .register-button {

            width:
                100%;

            height:
                42px;

            border:
                none;

            border-radius:
                4px;

            background:
                #d66a2b;

            color:
                white;

            font-weight:
                bold;

            cursor:
                pointer;

            transition:
                .3s;

        }


        .register-button:hover {

            background:
                #c55d25;

            transform:
                translateY(-1px);

        }


        /* =================================================
           ALERT
        ================================================= */

        .alert {

            padding:
                13px;

            border-radius:
                5px;

            margin-bottom:
                20px;

            font-size:
                13px;

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


        .login-link {

            text-align:
                center;

            margin-top:
                20px;

            font-size:
                12px;

        }


        .login-link a {

            color:
                #d66a2b;

            text-decoration:
                none;

            font-weight:
                600;

        }


        /* =================================================
           FOOTER
        ================================================= */

        .glass-footer {

            position:
                relative;

            overflow:
                hidden;

            padding:
                50px;

            border-radius:
                30px;

            background:
                rgba(18,48,94,.92);

            border:
                1px solid
                rgba(255,255,255,.12);

            box-shadow:
                0 20px 60px
                rgba(0,0,0,.20);

        }


        .glass-footer h2 {

            color:
                white;

            font-weight:
                700;

        }


        .glass-footer h5 {

            color:
                white;

            margin-bottom:
                18px;

        }


        .glass-footer p {

            color:
                rgba(255,255,255,.75);

        }


        .glass-footer ul {

            padding:
                0;

        }


        .glass-footer li {

            margin-bottom:
                12px;

        }


        .glass-footer a {

            color:
                rgba(255,255,255,.75);

            text-decoration:
                none;

            transition:
                .3s;

        }


        .glass-footer a:hover {

            color:
                white;

            padding-left:
                5px;

        }


        .social-icons {

            display:
                flex;

            gap:
                12px;

        }


        .social-icons a {

            width:
                42px;

            height:
                42px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            background:
                rgba(255,255,255,.08);

            border:
                1px solid
                rgba(255,255,255,.15);

        }


        .social-icons a:hover {

            background:
                white;

            color:
                #0d3b87;

            transform:
                translateY(-4px);

        }


        .glass-footer hr {

            border-color:
                rgba(255,255,255,.12);

            margin:
                35px 0 20px;

        }


        .copyright {

            color:
                rgba(255,255,255,.7);

            font-size:
                13px;

        }


        @media(max-width:600px) {

            .register-page {

                padding:
                    45px 15px 60px;

            }

            .form-card {

                padding:
                    20px 15px;

            }

            .glass-footer {

                padding:
                    35px 25px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <a
            class="navbar-brand"
            href="./index.php"
        >

            <i class="bi bi-book-half"></i>

            School Model Schools

        </a>


        <button
            class="navbar-toggler bg-light"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNav"
        >

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="./index.php"
                    >
                        Home
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="./about.php"
                    >
                        About
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="./admission.php"
                    >
                        Admissions
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="./faq.php"
                    >
                        FAQs
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="./contact.php"
                    >
                        Contact
                    </a>

                </li>


                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">

                    <a
                        href="./admin_login.php"
                        class="glass"
                    >

                        Admin Login

                        <i
                            class="bi bi-box-arrow-in-right ms-2"
                        ></i>

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>



<!-- =====================================================
     REGISTRATION
===================================================== -->

<main class="register-page">

    <p class="eyebrow">
        STAFF AREA
    </p>


    <h1>
        Create administrator account
    </h1>


    <p class="intro">

        Register as a school administrator.
        Your account will be reviewed before you
        can access the administrator dashboard.

    </p>


    <section class="form-card">


        <?php if ($status !== ""): ?>

            <div
                class="alert <?php echo $statusType; ?>"
            >

                <?php echo $status; ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
        >


            <!-- PROFILE PICTURE -->

            <div class="picture-upload">

                <div
                    class="picture-preview"
                    id="picturePreview"
                >

                    <i class="bi bi-person"></i>

                </div>


                <label
                    for="profile_picture"
                    class="upload-label"
                >

                    <i class="bi bi-camera me-1"></i>

                    Upload Profile Picture

                </label>


                <input
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >


                <div class="picture-help">

                    JPG, PNG or WEBP • Maximum 5MB

                </div>

            </div>



            <!-- FULL NAME -->

            <div class="field">

                <label for="full_name">

                    Full name

                </label>


                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?php echo oldv("full_name"); ?>"
                    required
                >

            </div>



            <!-- EMAIL -->

            <div class="field">

                <label for="email">

                    Email

                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="admin@school.com"
                    value="<?php echo oldv("email"); ?>"
                    required
                >

            </div>



            <!-- PASSWORD -->

            <div class="field">

                <label for="password">

                    Password

                </label>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="6"
                    required
                >

            </div>



            <!-- SUBMIT -->

            <button
                type="submit"
                class="register-button"
            >

                <i class="bi bi-person-plus me-2"></i>

                Create Administrator Account

            </button>


        </form>


        <div class="login-link">

            Already registered?

            <a href="admin_login.php">

                Login here

            </a>

        </div>


    </section>

</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<div class="container">

    <footer class="glass-footer my-5">

        <div class="row g-5">


            <div class="col-lg-4">

                <h2>

                    <i class="bi bi-book-half"></i>

                    School Model Schools

                </h2>


                <p class="mt-3">

                    Building bright minds through excellence
                    in education, innovation, and character
                    development.

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
                        <a href="./contact.php">
                            Contact
                        </a>
                    </li>

                    <li>
                        <a href="./faq.php">
                            FAQs
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
                        <a href="./check_result.php">
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
                        <a href="./about.php">
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

                    favour.efado@gmail.com

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



<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>



<!-- =====================================================
     IMAGE PREVIEW
===================================================== -->

<script>

const profileInput =
    document.getElementById("profile_picture");

const picturePreview =
    document.getElementById("picturePreview");


profileInput.addEventListener(
    "change",
    function () {

        const file = this.files[0];

        if (!file) {
            return;
        }


        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                picturePreview.innerHTML =
                    "<img src='" +
                    event.target.result +
                    "' alt='Profile Picture'>";

            };


        reader.readAsDataURL(file);

    }
);

</script>


</body>

</html>