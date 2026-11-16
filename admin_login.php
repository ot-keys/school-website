<?php

session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";

/*
|--------------------------------------------------------------------------
| PHPMailer
|--------------------------------------------------------------------------
|
| PHPMailer is loaded through Composer.
|
*/

$phpMailerAutoload =
    __DIR__ . "/vendor/autoload.php";

$phpMailerAvailable =
    file_exists($phpMailerAutoload);


if ($phpMailerAvailable) {

    require_once $phpMailerAutoload;

}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;


/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
*/

$gmailUser = "favour.efado@gmail.com";


$gmailAppPassword = "nhoc itbp uwni gavj";


$schoolEmail =
    "favour.efado@gmail.com";


/*
|--------------------------------------------------------------------------
| OTP SETTINGS
|--------------------------------------------------------------------------
*/

$otpExpirySeconds =
    5 * 60;


/*
| Maximum wrong OTP attempts
*/

$maxOtpAttempts =
    5;


/*
| Minimum time between OTP resends
*/

$otpResendCooldown =
    60;


/*
| Maximum OTP resends during
| one login session
*/

$maxOtpResends =
    5;


/*
|--------------------------------------------------------------------------
| DATABASE CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    die(
        "Database connection was not found. " .
        "Please check includes/db.php."
    );

}


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| GET CLIENT IP
|--------------------------------------------------------------------------
*/

function getClientIp()
{

    return $_SERVER["REMOTE_ADDR"]
        ?? "Unknown";

}


/*
|--------------------------------------------------------------------------
| CREATE APPROVAL LINK
|--------------------------------------------------------------------------
*/

function createApprovalLink($token)
{

    $https =
        (
            !empty($_SERVER["HTTPS"]) &&
            $_SERVER["HTTPS"] !== "off"
        )
        ||
        (
            isset($_SERVER["SERVER_PORT"]) &&
            (int)$_SERVER["SERVER_PORT"] === 443
        );


    $protocol =
        $https
            ? "https"
            : "http";


    $host =
        $_SERVER["HTTP_HOST"]
        ?? "localhost";


    $directory =
        rtrim(
            dirname(
                $_SERVER["SCRIPT_NAME"]
                ?? ""
            ),
            "/\\"
        );


    if (
        $directory === "." ||
        $directory === "/"
    ) {

        $directory = "";

    }


    return
        $protocol .
        "://" .
        $host .
        $directory .
        "/admin_reg.php?approve=" .
        urlencode($token);

}


/*
|--------------------------------------------------------------------------
| SMTP ERROR MESSAGE
|--------------------------------------------------------------------------
*/

function smtpErrorMessage($error)
{

    $message =
        sanitize(
            (string)$error
        );


    if ($message === "") {

        $message =
            "Unknown SMTP error.";

    }


    return
        "PHPMailer SMTP error: " .
        $message;

}


/*
|--------------------------------------------------------------------------
| CREATE PHPMailer
|--------------------------------------------------------------------------
*/

function createMailer(
    $gmailUser,
    $gmailAppPassword
) {

    global $phpMailerAvailable;


    if (!$phpMailerAvailable) {

        throw new RuntimeException(
            "PHPMailer could not be loaded. " .
            "vendor/autoload.php was not found."
        );

    }


    if ($gmailAppPassword === "" || $gmailAppPassword === "nhoc itbp uwni gavj") {

        // throw new RuntimeException(
        //     "Google App Password has not been configured " .
        //     "in admin_login.php."
        // );

    }


    $mail =
        new PHPMailer(true);


    /*
    |--------------------------------------------------------------------------
    | SMTP
    |--------------------------------------------------------------------------
    */

    $mail->isSMTP();


    $mail->Host =
        "smtp.gmail.com";


    $mail->SMTPAuth =
        true;


    $mail->Username =
        $gmailUser;


    $mail->Password =
        $gmailAppPassword;


    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_SMTPS;


    $mail->Port =
        465;


    $mail->CharSet =
        "UTF-8";


    /*
    |--------------------------------------------------------------------------
    | FROM
    |--------------------------------------------------------------------------
    */

    $mail->setFrom(
        $gmailUser,
        "School Model Schools"
    );


    return $mail;

}


/*
|--------------------------------------------------------------------------
| SEND APPROVAL EMAIL
|--------------------------------------------------------------------------
*/

function sendApprovalEmail(
    $fullName,
    $email,
    $approvalLink,
    $gmailUser,
    $gmailAppPassword,
    $schoolEmail
) {

    try {

        $mail =
            createMailer(
                $gmailUser,
                $gmailAppPassword
            );


        $mail->addAddress(
            $schoolEmail
        );


        $mail->isHTML(true);


        $mail->Subject =
            "Administrator Approval - " .
            $fullName;


        $safeName =
            e($fullName);


        $safeEmail =
            e($email);


        $safeLink =
            e($approvalLink);


        $mail->Body = "

        <div style=\"
            font-family:Arial,Helvetica,sans-serif;
            max-width:650px;
            margin:auto;
            padding:20px;
            color:#061f49;
        \">

            <div style=\"
                background:#061f49;
                color:white;
                padding:20px;
                border-radius:6px 6px 0 0;
            \">

                <h2 style=\"margin:0;\">
                    School Model Schools
                </h2>

            </div>


            <div style=\"
                background:#ffffff;
                border:1px solid #ddd;
                padding:25px;
                border-radius:0 0 6px 6px;
            \">

                <h2>
                    Administrator Approval Required
                </h2>


                <p>
                    A person has registered to become
                    an administrator on the school website.
                </p>


                <hr>


                <p>
                    <strong>
                        Full Name:
                    </strong>

                    {$safeName}
                </p>


                <p>
                    <strong>
                        Email:
                    </strong>

                    {$safeEmail}
                </p>


                <p>
                    <strong>
                        Status:
                    </strong>

                    Pending Approval
                </p>


                <hr>


                <p>
                    This person will NOT become an administrator
                    until the approval link below is opened.
                </p>


                <p>

                    <a
                        href=\"{$safeLink}\"
                        style=\"
                            display:inline-block;
                            padding:13px 24px;
                            background:#d66a2b;
                            color:white;
                            text-decoration:none;
                            border-radius:5px;
                            font-weight:bold;
                        \"
                    >

                        Make Admin

                    </a>

                </p>


                <p style=\"
                    margin-top:25px;
                    color:#777;
                    font-size:12px;
                \">

                    If you did not expect this registration,
                    simply ignore this email.

                </p>

            </div>

        </div>

        ";


        $mail->AltBody =
            "Administrator Approval Required\n\n" .

            "Name: " .
            $fullName .
            "\n" .

            "Email: " .
            $email .
            "\n\n" .

            "Approve this administrator using the link below:\n\n" .

            $approvalLink;


        $mail->send();


        return true;


    } catch (Throwable $e) {


        if (
            isset($mail) &&
            $mail instanceof PHPMailer &&
            $mail->ErrorInfo !== ""
        ) {

            return $mail->ErrorInfo;

        }


        return $e->getMessage();

    }

}


/*
|--------------------------------------------------------------------------
| SEND LOGIN OTP
|--------------------------------------------------------------------------
*/

function sendLoginOtpEmail(
    $fullName,
    $email,
    $otp,
    $gmailUser,
    $gmailAppPassword
) {

    try {

        $mail =
            createMailer(
                $gmailUser,
                $gmailAppPassword
            );


        $mail->addAddress(
            $email,
            $fullName
        );


        $mail->isHTML(true);


        $mail->Subject =
            "Your School Model Schools Login OTP";


        $safeName =
            e($fullName);


        $safeOtp =
            e($otp);


        $mail->Body = "

        <div style=\"
            font-family:Arial,Helvetica,sans-serif;
            max-width:650px;
            margin:auto;
            padding:20px;
            color:#061f49;
        \">


            <div style=\"
                background:#061f49;
                color:#fff;
                padding:20px;
                border-radius:6px 6px 0 0;
            \">

                <h2 style=\"margin:0;\">
                    School Model Schools
                </h2>

            </div>


            <div style=\"
                background:#fff;
                border:1px solid #ddd;
                padding:25px;
                border-radius:0 0 6px 6px;
            \">

                <h2>
                    Login Verification
                </h2>


                <p>
                    Dear
                    <strong>
                        {$safeName}
                    </strong>,
                </p>


                <p>
                    A login attempt was made using
                    your administrator account.
                </p>


                <p>
                    Use the verification code below
                    to complete your login:
                </p>


                <div style=\"
                    margin:25px 0;
                    text-align:center;
                \">

                    <span style=\"
                        display:inline-block;
                        background:#fdfdef;
                        border:2px solid #d66a2b;
                        color:#061f49;
                        font-size:32px;
                        letter-spacing:8px;
                        font-weight:bold;
                        padding:15px 22px;
                        border-radius:6px;
                    \">

                        {$safeOtp}

                    </span>

                </div>


                <p>

                    <strong>
                        This OTP expires in 5 minutes.
                    </strong>

                </p>


                <p style=\"
                    color:#777;
                    font-size:12px;
                \">

                    If you did not attempt to log in,
                    do not share this code with anyone.

                </p>

            </div>

        </div>

        ";


        $mail->AltBody =
            "School Model Schools Login Verification\n\n" .

            "Dear " .
            $fullName .
            ",\n\n" .

            "Your administrator login verification code is: " .
            $otp .
            "\n\n" .

            "This OTP expires in 5 minutes.\n\n" .

            "If you did not attempt to log in, " .
            "do not share this code with anyone.";


        $mail->send();


        return true;


    } catch (Throwable $e) {


        if (
            isset($mail) &&
            $mail instanceof PHPMailer &&
            $mail->ErrorInfo !== ""
        ) {

            return $mail->ErrorInfo;

        }


        return $e->getMessage();

    }

}


/*
|--------------------------------------------------------------------------
| SEND LOGIN NOTIFICATION TO MAIN ADMIN
|--------------------------------------------------------------------------
*/

function sendLoginNotificationEmail(
    $admin,
    $ip,
    $loginTime,
    $gmailUser,
    $gmailAppPassword,
    $schoolEmail
) {

    try {

        $mail =
            createMailer(
                $gmailUser,
                $gmailAppPassword
            );


        $mail->addAddress(
            $schoolEmail
        );


        $mail->isHTML(true);


        $mail->Subject =
            "Administrator Login Alert - " .
            $admin["full_name"];


        $safeName =
            e($admin["full_name"]);


        $safeEmail =
            e($admin["email"]);


        $safeIp =
            e($ip);


        $safeTime =
            e($loginTime);


        $mail->Body = "

        <div style=\"
            font-family:Arial,Helvetica,sans-serif;
            max-width:650px;
            margin:auto;
            padding:20px;
            color:#061f49;
        \">


            <div style=\"
                background:#061f49;
                color:#fff;
                padding:20px;
                border-radius:6px 6px 0 0;
            \">

                <h2 style=\"margin:0;\">
                    School Model Schools
                </h2>

            </div>


            <div style=\"
                background:#fff;
                border:1px solid #ddd;
                padding:25px;
                border-radius:0 0 6px 6px;
            \">

                <h2>
                    Administrator Login Alert
                </h2>


                <p>
                    An administrator has successfully
                    logged into the school administration system.
                </p>


                <hr>


                <p>
                    <strong>
                        Name:
                    </strong>

                    {$safeName}
                </p>


                <p>
                    <strong>
                        Email:
                    </strong>

                    {$safeEmail}
                </p>


                <p>
                    <strong>
                        IP Address:
                    </strong>

                    {$safeIp}
                </p>


                <p>
                    <strong>
                        Login Time:
                    </strong>

                    {$safeTime}
                </p>


                <hr>


                <p style=\"
                    color:#777;
                    font-size:12px;
                \">

                    The login passed password authentication
                    and email OTP verification.

                </p>

            </div>

        </div>

        ";


        $mail->AltBody =
            "Administrator Login Alert\n\n" .

            "Name: " .
            $admin["full_name"] .
            "\n" .

            "Email: " .
            $admin["email"] .
            "\n" .

            "IP Address: " .
            $ip .
            "\n" .

            "Login Time: " .
            $loginTime .
            "\n\n" .

            "The login passed password authentication " .
            "and email OTP verification.";


        $mail->send();


        return true;


    } catch (Throwable $e) {


        if (
            isset($mail) &&
            $mail instanceof PHPMailer &&
            $mail->ErrorInfo !== ""
        ) {

            return $mail->ErrorInfo;

        }


        return $e->getMessage();

    }

}


/*
|--------------------------------------------------------------------------
| GET ADMIN BY ID
|--------------------------------------------------------------------------
*/

function getAdminById(
    $conn,
    $id
) {

    $stmt =
        $conn->prepare("

            SELECT
                id,
                full_name,
                email,
                password,
                profile_picture,
                status

            FROM admin_table

            WHERE id = ?

            LIMIT 1

        ");


    if (!$stmt) {

        return null;

    }


    $stmt->bind_param(
        "i",
        $id
    );


    $stmt->execute();


    $admin =
        $stmt
            ->get_result()
            ->fetch_assoc();


    $stmt->close();


    return $admin ?: null;

}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status = "";

$statusType = "";


/*
|--------------------------------------------------------------------------
| RESEND APPROVAL LINK
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["resend_approval"])
) {


    $resendEmail =
        sanitize(
            $_POST["resend_email"] ?? ""
        );


    if (
        $resendEmail === ""
    ) {


        $status =
            "Please enter your registered email address.";


        $statusType =
            "danger";


    } elseif (
        !filter_var(
            $resendEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {


        $status =
            "Please enter a valid email address.";


        $statusType =
            "danger";


    } else {


        $stmt =
            $conn->prepare("

                SELECT
                    id,
                    full_name,
                    email,
                    approval_token,
                    status

                FROM admin_table

                WHERE email = ?

                LIMIT 1

            ");


        if (!$stmt) {


            $status =
                "Database error: " .
                $conn->error;


            $statusType =
                "danger";


        } else {


            $stmt->bind_param(
                "s",
                $resendEmail
            );


            $stmt->execute();


            $admin =
                $stmt
                    ->get_result()
                    ->fetch_assoc();


            $stmt->close();


            if (!$admin) {


                $status =
                    "No administrator account was found with that email address.";


                $statusType =
                    "danger";


            } elseif (
                strtolower(
                    sanitize(
                        $admin["status"]
                    )
                ) === "approved"
            ) {


                $status =
                    "This administrator account has already been approved. You can log in.";


                $statusType =
                    "success";


            } else {


                try {

                    $newToken =
                        bin2hex(
                            random_bytes(32)
                        );

                } catch (Throwable $e) {

                    $newToken =
                        hash(
                            "sha256",
                            uniqid(
                                (string)mt_rand(),
                                true
                            )
                        );

                }


                $update =
                    $conn->prepare("

                        UPDATE admin_table

                        SET
                            approval_token = ?,
                            status = 'pending'

                        WHERE id = ?

                    ");


                if (!$update) {


                    $status =
                        "Could not create a new approval link: " .
                        $conn->error;


                    $statusType =
                        "danger";


                } else {


                    $update->bind_param(
                        "si",
                        $newToken,
                        $admin["id"]
                    );


                    if (
                        $update->execute()
                    ) {


                        $approvalLink =
                            createApprovalLink(
                                $newToken
                            );


                        $emailResult =
                            sendApprovalEmail(
                                $admin["full_name"],
                                $admin["email"],
                                $approvalLink,
                                $gmailUser,
                                $gmailAppPassword,
                                $schoolEmail
                            );


                        if (
                            $emailResult === true
                        ) {


                            $status =
                                "A new approval link has been sent to the school administrator email.";


                            $statusType =
                                "success";


                        } else {


                            $status =
                                "The new approval link was created, but the email could not be sent.\n\n" .
                                smtpErrorMessage(
                                    $emailResult
                                );


                            $statusType =
                                "danger";

                        }


                    } else {


                        $status =
                            "Could not create a new approval link: " .
                            $update->error;


                        $statusType =
                            "danger";

                    }


                    $update->close();

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| RESEND LOGIN OTP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["resend_otp"])
) {


    if (
        empty(
            $_SESSION["admin_otp_pending"]
        )
    ) {


        $status =
            "There is no active login verification. Please enter your email and password again.";


        $statusType =
            "warning";


    } else {


        $pendingId =
            (int)(
                $_SESSION[
                    "admin_otp_admin_id"
                ] ?? 0
            );


        $lastSent =
            (int)(
                $_SESSION[
                    "admin_otp_last_sent"
                ] ?? 0
            );


        $resendCount =
            (int)(
                $_SESSION[
                    "admin_otp_resend_count"
                ] ?? 0
            );


        $secondsSinceSend =
            time() -
            $lastSent;


        if (
            $pendingId <= 0
        ) {


            unset(
                $_SESSION[
                    "admin_otp_pending"
                ]
            );


            $status =
                "Your login verification session is invalid. Please log in again.";


            $statusType =
                "warning";


        } elseif (
            $secondsSinceSend <
            $otpResendCooldown
        ) {


            $remaining =
                $otpResendCooldown -
                $secondsSinceSend;


            $status =
                "Please wait " .
                $remaining .
                " seconds before requesting another OTP.";


            $statusType =
                "warning";


        } elseif (
            $resendCount >=
            $maxOtpResends
        ) {


            unset(

                $_SESSION[
                    "admin_otp_pending"
                ],

                $_SESSION[
                    "admin_otp_admin_id"
                ],

                $_SESSION[
                    "admin_otp_hash"
                ],

                $_SESSION[
                    "admin_otp_expires"
                ],

                $_SESSION[
                    "admin_otp_attempts"
                ],

                $_SESSION[
                    "admin_otp_last_sent"
                ],

                $_SESSION[
                    "admin_otp_resend_count"
                ]

            );


            $status =
                "You have reached the maximum number of OTP resends for this login. Please start a new login attempt.";


            $statusType =
                "danger";


        } else {


            $admin =
                getAdminById(
                    $conn,
                    $pendingId
                );


            if (
                !$admin ||
                strtolower(
                    sanitize(
                        $admin["status"]
                    )
                ) !== "approved"
            ) {


                unset(
                    $_SESSION[
                        "admin_otp_pending"
                    ]
                );


                $status =
                    "This administrator account is no longer approved. Please contact the main administrator.";


                $statusType =
                    "danger";


            } else {


                $otp =
                    str_pad(
                        (string)random_int(
                            0,
                            999999
                        ),
                        6,
                        "0",
                        STR_PAD_LEFT
                    );


                $emailResult =
                    sendLoginOtpEmail(
                        $admin["full_name"],
                        $admin["email"],
                        $otp,
                        $gmailUser,
                        $gmailAppPassword
                    );


                if (
                    $emailResult === true
                ) {


                    $_SESSION[
                        "admin_otp_hash"
                    ] =
                        password_hash(
                            $otp,
                            PASSWORD_DEFAULT
                        );


                    $_SESSION[
                        "admin_otp_expires"
                    ] =
                        time() +
                        $otpExpirySeconds;


                    $_SESSION[
                        "admin_otp_attempts"
                    ] =
                        0;


                    $_SESSION[
                        "admin_otp_last_sent"
                    ] =
                        time();


                    $_SESSION[
                        "admin_otp_resend_count"
                    ] =
                        $resendCount + 1;


                    $status =
                        "A new OTP has been sent to " .
                        $admin["email"] .
                        ". It expires in 5 minutes.";


                    $statusType =
                        "success";


                } else {


                    $status =
                        "The new OTP could not be sent. Your current OTP has not been replaced.\n\n" .
                        smtpErrorMessage(
                            $emailResult
                        );


                    $statusType =
                        "danger";

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["verify_otp"])
) {


    if (
        empty(
            $_SESSION["admin_otp_pending"]
        )
    ) {


        $status =
            "Your OTP session has expired. Please enter your email and password again.";


        $statusType =
            "warning";


    } else {


        $otp =
            sanitize(
                $_POST["otp"] ?? ""
            );


        $expires =
            (int)(
                $_SESSION[
                    "admin_otp_expires"
                ] ?? 0
            );


        $attempts =
            (int)(
                $_SESSION[
                    "admin_otp_attempts"
                ] ?? 0
            );


        $pendingId =
            (int)(
                $_SESSION[
                    "admin_otp_admin_id"
                ] ?? 0
            );


        $otpHash =
            $_SESSION[
                "admin_otp_hash"
            ] ?? "";


        /*
        |--------------------------------------------------------------------------
        | EXPIRED
        |--------------------------------------------------------------------------
        */

        if (
            $expires <= time()
        ) {


            unset(

                $_SESSION[
                    "admin_otp_pending"
                ],

                $_SESSION[
                    "admin_otp_admin_id"
                ],

                $_SESSION[
                    "admin_otp_hash"
                ],

                $_SESSION[
                    "admin_otp_expires"
                ],

                $_SESSION[
                    "admin_otp_attempts"
                ],

                $_SESSION[
                    "admin_otp_last_sent"
                ],

                $_SESSION[
                    "admin_otp_resend_count"
                ]

            );


            $status =
                "Your OTP has expired. Please log in again to receive a new OTP.";


            $statusType =
                "warning";


        /*
        |--------------------------------------------------------------------------
        | TOO MANY ATTEMPTS
        |--------------------------------------------------------------------------
        */

        } elseif (
            $attempts >=
            $maxOtpAttempts
        ) {


            unset(

                $_SESSION[
                    "admin_otp_pending"
                ],

                $_SESSION[
                    "admin_otp_admin_id"
                ],

                $_SESSION[
                    "admin_otp_hash"
                ],

                $_SESSION[
                    "admin_otp_expires"
                ],

                $_SESSION[
                    "admin_otp_attempts"
                ],

                $_SESSION[
                    "admin_otp_last_sent"
                ],

                $_SESSION[
                    "admin_otp_resend_count"
                ]

            );


            $status =
                "Too many incorrect OTP attempts. Please start a new login attempt.";


            $statusType =
                "danger";


        /*
        |--------------------------------------------------------------------------
        | INVALID FORMAT
        |--------------------------------------------------------------------------
        */

        } elseif (
            !preg_match(
                '/^\d{6}$/',
                $otp
            )
        ) {


            $_SESSION[
                "admin_otp_attempts"
            ] =
                $attempts + 1;


            $remaining =
                max(
                    0,
                    $maxOtpAttempts -
                    $_SESSION[
                        "admin_otp_attempts"
                    ]
                );


            $status =
                "Please enter the 6-digit OTP. Attempts remaining: " .
                $remaining .
                ".";


            $statusType =
                "danger";


        /*
        |--------------------------------------------------------------------------
        | WRONG OTP
        |--------------------------------------------------------------------------
        */

        } elseif (
            $pendingId <= 0 ||
            $otpHash === "" ||
            !password_verify(
                $otp,
                $otpHash
            )
        ) {


            $_SESSION[
                "admin_otp_attempts"
            ] =
                $attempts + 1;


            $remaining =
                max(
                    0,
                    $maxOtpAttempts -
                    $_SESSION[
                        "admin_otp_attempts"
                    ]
                );


            if (
                $remaining <= 0
            ) {


                unset(

                    $_SESSION[
                        "admin_otp_pending"
                    ],

                    $_SESSION[
                        "admin_otp_admin_id"
                    ],

                    $_SESSION[
                        "admin_otp_hash"
                    ],

                    $_SESSION[
                        "admin_otp_expires"
                    ],

                    $_SESSION[
                        "admin_otp_attempts"
                    ],

                    $_SESSION[
                        "admin_otp_last_sent"
                    ],

                    $_SESSION[
                        "admin_otp_resend_count"
                    ]

                );


                $status =
                    "Incorrect OTP. You have reached the maximum number of attempts. Please start a new login attempt.";


            } else {


                $status =
                    "Incorrect OTP. Attempts remaining: " .
                    $remaining .
                    ".";

            }


            $statusType =
                "danger";


        /*
        |--------------------------------------------------------------------------
        | CORRECT OTP
        |--------------------------------------------------------------------------
        */

        } else {


            $admin =
                getAdminById(
                    $conn,
                    $pendingId
                );


            if (!$admin) {


                $status =
                    "Administrator account could not be found. Please contact the main administrator.";


                $statusType =
                    "danger";


                unset(
                    $_SESSION[
                        "admin_otp_pending"
                    ]
                );


            } elseif (
                strtolower(
                    sanitize(
                        $admin["status"]
                    )
                ) !== "approved"
            ) {


                $status =
                    "Your administrator account is no longer approved. Please contact the main administrator.";


                $statusType =
                    "danger";


                unset(
                    $_SESSION[
                        "admin_otp_pending"
                    ]
                );


            } else {


                /*
                |--------------------------------------------------------------------------
                | LOGIN DETAILS
                |--------------------------------------------------------------------------
                */

                $ip =
                    getClientIp();


                $loginTime =
                    date(
                        "F j, Y g:i A"
                    );


                /*
                |--------------------------------------------------------------------------
                | SEND MAIN ADMIN NOTIFICATION
                |--------------------------------------------------------------------------
                */

                $notificationResult =
                    sendLoginNotificationEmail(
                        $admin,
                        $ip,
                        $loginTime,
                        $gmailUser,
                        $gmailAppPassword,
                        $schoolEmail
                    );


                /*
                |--------------------------------------------------------------------------
                | CREATE AUTHENTICATED SESSION
                |--------------------------------------------------------------------------
                |
                | THIS is the important part.
                |
                | admin_id is NOT created until the OTP
                | has been successfully verified.
                |
                */

                session_regenerate_id(true);


                $_SESSION[
                    "admin_id"
                ] =
                    (int)$admin["id"];


                $_SESSION[
                    "admin_name"
                ] =
                    $admin["full_name"];


                $_SESSION[
                    "admin_email"
                ] =
                    $admin["email"];


                $_SESSION[
                    "admin_passport"
                ] =
                    $admin[
                        "profile_picture"
                    ]
                    ?? "";


                $_SESSION[
                    "admin_logged_in_at"
                ] =
                    time();


                $_SESSION[
                    "admin_login_ip"
                ] =
                    $ip;


                /*
                |--------------------------------------------------------------------------
                | CLEAR OTP SESSION
                |--------------------------------------------------------------------------
                */

                unset(

                    $_SESSION[
                        "admin_otp_pending"
                    ],

                    $_SESSION[
                        "admin_otp_admin_id"
                    ],

                    $_SESSION[
                        "admin_otp_hash"
                    ],

                    $_SESSION[
                        "admin_otp_expires"
                    ],

                    $_SESSION[
                        "admin_otp_attempts"
                    ],

                    $_SESSION[
                        "admin_otp_last_sent"
                    ],

                    $_SESSION[
                        "admin_otp_resend_count"
                    ]

                );


                /*
                |--------------------------------------------------------------------------
                | NOTIFICATION ERROR
                |--------------------------------------------------------------------------
                |
                | OTP was correct, so the administrator is authenticated.
                |
                | If the notification email fails, do not block login.
                | Write the actual SMTP error to the PHP error log.
                |
                */

                if (
                    $notificationResult !== true
                ) {

                    error_log(
                        "School Model Schools admin login notification failed for " .
                        $admin["email"] .
                        ": " .
                        $notificationResult
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | DASHBOARD
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: admin_dashboard.php"
                );

                exit;

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| PASSWORD LOGIN
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["login"])
) {


    /*
    | Never process another password login while
    | an OTP is already pending.
    */

    if (
        !empty(
            $_SESSION[
                "admin_otp_pending"
            ]
        )
    ) {


        $status =
            "A login verification is already in progress. Please enter the OTP sent to your email.";


        $statusType =
            "warning";


    } else {


        $email =
            sanitize(
                $_POST["email"] ?? ""
            );


        $password =
            $_POST["password"] ?? "";


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $email === "" ||
            $password === ""
        ) {


            $status =
                "Please enter your email and password.";


            $statusType =
                "danger";


        } elseif (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {


            $status =
                "Please enter a valid email address.";


            $statusType =
                "danger";


        } else {


            /*
            |--------------------------------------------------------------------------
            | GET ADMIN
            |--------------------------------------------------------------------------
            */

            $stmt =
                $conn->prepare("

                    SELECT

                        id,
                        full_name,
                        email,
                        password,
                        profile_picture,
                        status

                    FROM admin_table

                    WHERE email = ?

                    LIMIT 1

                ");


            if (!$stmt) {


                $status =
                    "Database error: " .
                    $conn->error;


                $statusType =
                    "danger";


            } else {


                $stmt->bind_param(
                    "s",
                    $email
                );


                $stmt->execute();


                $admin =
                    $stmt
                        ->get_result()
                        ->fetch_assoc();


                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | ACCOUNT NOT FOUND
                |--------------------------------------------------------------------------
                */

                if (!$admin) {


                    $status =
                        "Incorrect email or password.";


                    $statusType =
                        "danger";


                /*
                |--------------------------------------------------------------------------
                | PASSWORD
                |--------------------------------------------------------------------------
                */

                } elseif (
                    !password_verify(
                        $password,
                        $admin["password"]
                    )
                ) {


                    $status =
                        "Incorrect email or password.";


                    $statusType =
                        "danger";


                /*
                |--------------------------------------------------------------------------
                | APPROVAL
                |--------------------------------------------------------------------------
                */

                } elseif (
                    strtolower(
                        sanitize(
                            $admin["status"]
                        )
                    ) !== "approved"
                ) {


                    $status =
                        "Your administrator account has not been approved yet.";


                    $statusType =
                        "warning";


                /*
                |--------------------------------------------------------------------------
                | PASSWORD CORRECT
                |--------------------------------------------------------------------------
                |
                | DO NOT LOG IN YET.
                |
                | Generate and send OTP.
                |--------------------------------------------------------------------------
                */

                } else {


                    $otp =
                        str_pad(
                            (string)random_int(
                                0,
                                999999
                            ),
                            6,
                            "0",
                            STR_PAD_LEFT
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SEND OTP
                    |--------------------------------------------------------------------------
                    */

                    $emailResult =
                        sendLoginOtpEmail(
                            $admin["full_name"],
                            $admin["email"],
                            $otp,
                            $gmailUser,
                            $gmailAppPassword
                        );


                    if (
                        $emailResult === true
                    ) {


                        /*
                        |--------------------------------------------------------------------------
                        | SAVE OTP IN SESSION
                        |--------------------------------------------------------------------------
                        */

                        $_SESSION[
                            "admin_otp_pending"
                        ] =
                            true;


                        $_SESSION[
                            "admin_otp_admin_id"
                        ] =
                            (int)$admin["id"];


                        /*
                        | Store a HASH, not the actual OTP.
                        */

                        $_SESSION[
                            "admin_otp_hash"
                        ] =
                            password_hash(
                                $otp,
                                PASSWORD_DEFAULT
                            );


                        /*
                        | 5-minute expiry
                        */

                        $_SESSION[
                            "admin_otp_expires"
                        ] =
                            time() +
                            $otpExpirySeconds;


                        /*
                        | Reset attempts
                        */

                        $_SESSION[
                            "admin_otp_attempts"
                        ] =
                            0;


                        $_SESSION[
                            "admin_otp_last_sent"
                        ] =
                            time();


                        $_SESSION[
                            "admin_otp_resend_count"
                        ] =
                            0;


                        $status =
                            "A 6-digit OTP has been sent to " .
                            $admin["email"] .
                            ". The OTP expires in 5 minutes.";


                        $statusType =
                            "success";


                    } else {


                        /*
                        |--------------------------------------------------------------------------
                        | OTP FAILED
                        |--------------------------------------------------------------------------
                        |
                        | IMPORTANT:
                        | admin_id is NOT created.
                        |
                        */

                        unset(

                            $_SESSION[
                                "admin_otp_pending"
                            ],

                            $_SESSION[
                                "admin_otp_admin_id"
                            ],

                            $_SESSION[
                                "admin_otp_hash"
                            ],

                            $_SESSION[
                                "admin_otp_expires"
                            ],

                            $_SESSION[
                                "admin_otp_attempts"
                            ],

                            $_SESSION[
                                "admin_otp_last_sent"
                            ],

                            $_SESSION[
                                "admin_otp_resend_count"
                            ]

                        );


                        $status =
                            "Your password is correct, but the OTP could not be sent. You have NOT been logged in.\n\n" .
                            smtpErrorMessage(
                                $emailResult
                            );


                        $statusType =
                            "danger";

                    }

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| OTP VIEW STATE
|--------------------------------------------------------------------------
*/

$otpPending =
    !empty(
        $_SESSION[
            "admin_otp_pending"
        ]
    );


$otpEmail =
    "";


$otpRemainingSeconds =
    0;


if ($otpPending) {


    $pendingAdmin =
        getAdminById(
            $conn,
            (int)(
                $_SESSION[
                    "admin_otp_admin_id"
                ] ?? 0
            )
        );


    if ($pendingAdmin) {

        $otpEmail =
            $pendingAdmin["email"];

    }


    $otpRemainingSeconds =
        max(
            0,
            (int)(
                $_SESSION[
                    "admin_otp_expires"
                ] ?? 0
            )
            - time()
        );

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
    Admin Login | School Model Schools
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

    gap: 20px;

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

    border:
        1px solid #91a2be;

    padding:
        9px 16px;

    border-radius: 4px;

}


.nav-admin:hover {

    background: #fff;

    color: #061f49 !important;

}


/* =====================================================
   PAGE
===================================================== */

.login-page {

    width: 100%;

    max-width: 500px;

    margin: auto;

    padding:
        55px 20px;

}


.eyebrow {

    margin:
        0 0 8px;

    color: #d55f2a;

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

    font-size: 32px;

    line-height: 1.15;

}


.intro {

    color: #31517c;

    font-size: 13px;

    margin:
        10px 0 25px;

    line-height: 1.6;

}


/* =====================================================
   CARD
===================================================== */

.login-card {

    background: #fff;

    border:
        1px solid #ddd9d1;

    border-radius: 6px;

    padding:
        28px 24px;

    box-shadow:
        0 3px 12px
        rgba(6,31,73,.04);

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding:
        12px 14px;

    border-radius: 4px;

    margin-bottom: 20px;

    font-size: 13px;

    line-height: 1.5;

    white-space: pre-line;

}


.danger {

    background: #f8d7da;

    color: #842029;

}


.warning {

    background: #fff3cd;

    color: #664d03;

}


.success {

    background: #d1e7dd;

    color: #0f5132;

}


/* =====================================================
   FIELD
===================================================== */

.field {

    margin-bottom: 20px;

}


.field label {

    display: block;

    margin-bottom: 7px;

    font-size: 12px;

    font-weight: 600;

}


.field input {

    width: 100%;

    height: 42px;

    padding:
        0 12px;

    border:
        1px solid #d7d4ce;

    border-radius: 4px;

    outline: none;

    background: #fff;

    font-size: 14px;

    color: #061f49;

}


.field input:focus {

    border-color: #d66a2b;

    box-shadow:
        0 0 0 2px
        rgba(214,106,43,.08);

}


/* =====================================================
   OTP
===================================================== */

.otp-box {

    background: #fafaf6;

    border:
        1px solid #e5e2da;

    border-radius: 5px;

    padding: 18px;

    margin-top: 5px;

}


.otp-box h3 {

    margin:
        0 0 7px;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 19px;

}


.otp-box p {

    margin:
        0 0 15px;

    color: #60708a;

    font-size: 12px;

    line-height: 1.6;

}


.otp-input {

    text-align: center;

    font-size: 25px !important;

    letter-spacing: 8px;

    font-weight: bold;

}


.timer {

    text-align: center;

    color: #d66a2b;

    font-weight: bold;

    font-size: 13px;

    margin:
        8px 0 15px;

}


/* =====================================================
   BUTTONS
===================================================== */

.login-button {

    width: 100%;

    height: 43px;

    border: none;

    border-radius: 4px;

    background: #d66a2b;

    color: #fff;

    font-size: 14px;

    font-weight: bold;

    cursor: pointer;

}


.login-button:hover {

    background: #c55d25;

}


.resend-button {

    width: 100%;

    height: 42px;

    border:
        1px solid #061f49;

    border-radius: 4px;

    background: #fff;

    color: #061f49;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

    margin-top: 10px;

}


.resend-button:hover {

    background: #061f49;

    color: #fff;

}


/* =====================================================
   DIVIDER
===================================================== */

.divider {

    display: flex;

    align-items: center;

    gap: 12px;

    margin:
        25px 0;

}


.divider::before,
.divider::after {

    content: "";

    flex: 1;

    height: 1px;

    background: #e2e2de;

}


.divider span {

    font-size: 11px;

    color: #8993a3;

    text-transform: uppercase;

    letter-spacing: 1px;

}


/* =====================================================
   RESEND APPROVAL
===================================================== */

.resend-section {

    background: #fafaf6;

    border:
        1px solid #e5e2da;

    border-radius: 5px;

    padding: 18px;

    margin-top: 20px;

}


.resend-section h3 {

    margin:
        0 0 7px;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 19px;

    color: #061f49;

}


.resend-section p {

    margin:
        0 0 15px;

    color: #60708a;

    font-size: 12px;

    line-height: 1.6;

}


.resend-section .field {

    margin-bottom: 12px;

}


/* =====================================================
   REGISTER
===================================================== */

.register-link {

    text-align: center;

    margin-top: 20px;

    color: #60708a;

    font-size: 12px;

}


.register-link a {

    color: #d66a2b;

    text-decoration: none;

    font-weight: 600;

}


.register-link a:hover {

    text-decoration: underline;

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


    .nav-admin {

        padding:
            8px 10px;

    }


    .login-page {

        padding:
            40px 15px;

    }


    h1 {

        font-size: 27px;

    }


    .login-card {

        padding:
            24px 18px;

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

        School Model Schools

    </a>


    <div class="nav-links">


        <a href="index.php">

            Home

        </a>


        <a href="enroll.php">

            Enrollment

        </a>


        <a
            href="admin_reg.php"
            class="nav-admin"
        >

            Register

        </a>


    </div>


</nav>


<!-- =====================================================
     LOGIN PAGE
===================================================== -->

<main class="login-page">


    <p class="eyebrow">

        STAFF AREA

    </p>


    <h1>

        Administrator Login

    </h1>


    <p class="intro">

        Sign in to access the school administration
        dashboard.

    </p>


    <section class="login-card">


        <!-- =================================================
             ALERT
        ================================================= -->

        <?php if ($status !== ""): ?>


            <div
                class="alert
                <?php
                echo e($statusType);
                ?>"
            >

                <?php

                echo nl2br(
                    e($status)
                );

                ?>

            </div>


        <?php endif; ?>


        <!-- =================================================
             OTP VERIFICATION
        ================================================= -->

        <?php if ($otpPending): ?>


            <div class="otp-box">


                <h3>

                    Verify Your Login

                </h3>


                <p>

                    We sent a 6-digit verification code to

                    <strong>

                        <?php
                        echo e(
                            $otpEmail
                        );
                        ?>

                    </strong>.

                    Enter the code below to complete
                    your login.

                </p>


                <form
                    method="POST"
                    action=""
                >


                    <input
                        type="hidden"
                        name="verify_otp"
                        value="1"
                    >


                    <div class="field">


                        <label for="otp">

                            6-Digit OTP

                        </label>


                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            class="otp-input"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="000000"
                            required
                        >


                    </div>


                    <div class="timer">

                        OTP expires in

                        <span id="otpTimer">

                            <?php
                            echo (int)
                                $otpRemainingSeconds;
                            ?>

                        </span>

                        seconds

                    </div>


                    <button
                        type="submit"
                        class="login-button"
                    >

                        Verify OTP &amp; Login

                    </button>


                </form>


                <!-- =================================================
                     RESEND OTP
                ================================================= -->

                <form
                    method="POST"
                    action=""
                >


                    <input
                        type="hidden"
                        name="resend_otp"
                        value="1"
                    >


                    <button
                        type="submit"
                        class="resend-button"
                    >

                        Resend OTP

                    </button>


                </form>


            </div>


        <?php else: ?>


            <!-- =================================================
                 NORMAL LOGIN
            ================================================= -->

            <form
                method="POST"
                action=""
            >


                <input
                    type="hidden"
                    name="login"
                    value="1"
                >


                <div class="field">


                    <label for="email">

                        Email

                    </label>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@school.com"
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


                <div class="field">


                    <label for="password">

                        Password

                    </label>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >


                </div>


                <button
                    type="submit"
                    class="login-button"
                >

                    Login

                </button>


            </form>


        <?php endif; ?>


        <!-- =================================================
             ACCOUNT APPROVAL
        ================================================= -->

        <div class="divider">


            <span>

                Account Approval

            </span>


        </div>


        <div class="resend-section">


            <h3>

                Waiting for approval?

            </h3>


            <p>

                If you have already registered as an
                administrator but have not received an
                approval email, you can request a new
                approval link.

            </p>


            <form
                method="POST"
                action=""
            >


                <input
                    type="hidden"
                    name="resend_approval"
                    value="1"
                >


                <div class="field">


                    <label for="resend_email">

                        Registered Email

                    </label>


                    <input
                        type="email"
                        id="resend_email"
                        name="resend_email"
                        placeholder="Enter your registered email"
                        autocomplete="email"
                        value="<?php

                        echo e(
                            $_POST[
                                "resend_email"
                            ] ?? ""
                        );

                        ?>"
                        required
                    >


                </div>


                <button
                    type="submit"
                    class="resend-button"
                >

                    Resend Approval Link

                </button>


            </form>


        </div>


        <!-- =================================================
             REGISTER
        ================================================= -->

        <div class="register-link">


            Don't have an administrator account?


            <a href="admin_reg.php">

                Create account

            </a>


        </div>


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


<!-- =====================================================
     OTP COUNTDOWN
===================================================== -->

<?php if ($otpPending): ?>

<script>

(function () {

    let remaining =
        <?php
        echo (int)$otpRemainingSeconds;
        ?>;


    const timer =
        document.getElementById(
            "otpTimer"
        );


    function updateTimer() {

        if (!timer) {

            return;

        }


        if (remaining <= 0) {

            timer.textContent = "0";

            return;

        }


        timer.textContent =
            remaining;


        remaining--;


        setTimeout(
            updateTimer,
            1000
        );

    }


    updateTimer();

})();

</script>

<?php endif; ?>


</body>

</html>