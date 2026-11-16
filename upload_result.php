<?php

session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";



/*
|--------------------------------------------------------------------------
| GMAIL SETTINGS
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Use a Gmail App Password, NOT your normal Gmail password.
|
*/

$gmailUser = "favour.efado@gmail.com";

$gmailAppPassword = "nhoc itbp uwni gavj";

$schoolName = "School Model Schools";


/*
|--------------------------------------------------------------------------
| CHECK DATABASE CONNECTION
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
| CHECK ADMIN LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["admin_id"])
) {

    header(
        "Location: admin_login.php"
    );

    exit;

}


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
| SEND EMAIL USING GMAIL SMTP
|--------------------------------------------------------------------------
|
| This replaces PHPMailer completely.
|
| Gmail SMTP:
|
| SSL/TLS: smtp.gmail.com:465
|
*/

function sendGmailEmail(
    $to,
    $toName,
    $subject,
    $htmlBody,
    $attachmentPath,
    $attachmentName,
    $gmailUser,
    $gmailAppPassword,
    $schoolName
) {

    /*
    |--------------------------------------------------------------------------
    | CHECK ATTACHMENT
    |--------------------------------------------------------------------------
    */

    if (
        !empty($attachmentPath) &&
        !file_exists($attachmentPath)
    ) {

        return [
            "success" => false,
            "error" =>
                "The result attachment could not be found."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | GMAIL SMTP SERVER
    |--------------------------------------------------------------------------
    */

    $host = "ssl://smtp.gmail.com";

    $port = 465;

    $errno = 0;

    $errstr = "";


    /*
    |--------------------------------------------------------------------------
    | CONNECT
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
    | SET TIMEOUT
    |--------------------------------------------------------------------------
    */

    stream_set_timeout(
        $socket,
        30
    );


    /*
    |--------------------------------------------------------------------------
    | READ SMTP RESPONSE
    |--------------------------------------------------------------------------
    */

    $readResponse = function () use ($socket) {

        $response = "";

        while (
            ($line = fgets(
                $socket,
                515
            )) !== false
        ) {

            $response .= $line;


            /*
            | SMTP response format:
            |
            | 250-message
            | 250-message
            | 250 message
            |
            | The final response has a space
            | after the 3-digit code.
            */

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
    | CHECK SMTP RESPONSE
    |--------------------------------------------------------------------------
    */

    $checkResponse = function ($response, $expectedCodes) {

        $code = substr(
            sanitize($response),
            0,
            3
        );


        if (
            in_array(
                $code,
                $expectedCodes,
                true
            )
        ) {

            return true;

        }


        return false;

    };


    /*
    |--------------------------------------------------------------------------
    | SMTP COMMAND
    |--------------------------------------------------------------------------
    */

    $sendCommand = function ($command, $expectedCodes) use ($socket, $readResponse, $checkResponse) {

        if (
            $command !== ""
        ) {

            fwrite(
                $socket,
                $command . "\r\n"
            );

        }


        $response =
            $readResponse();


        return [
            "success" =>
                $checkResponse(
                    $response,
                    $expectedCodes
                ),

            "response" =>
                $response
        ];

    };


    /*
    |--------------------------------------------------------------------------
    | SMTP GREETING
    |--------------------------------------------------------------------------
    */

    $response =
        $readResponse();


    if (
        !$checkResponse(
            $response,
            ["220"]
        )
    ) {

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
            ["250"]
        );


    if (
        !$result["success"]
    ) {

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
            ["334"]
        );


    if (
        !$result["success"]
    ) {

        fclose($socket);


        return [
            "success" => false,
            "error" =>
                "Gmail authentication could not start."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | GMAIL USERNAME
    |--------------------------------------------------------------------------
    */

    $result =
        $sendCommand(
            base64_encode(
                $gmailUser
            ),
            ["334"]
        );


    if (
        !$result["success"]
    ) {

        fclose($socket);


        return [
            "success" => false,
            "error" =>
                "Gmail username was rejected."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | GMAIL APP PASSWORD
    |--------------------------------------------------------------------------
    */

    $cleanPassword =
        str_replace(
            " ",
            "",
            sanitize(
                $gmailAppPassword
            )
        );


    $result =
        $sendCommand(
            base64_encode(
                $cleanPassword
            ),
            ["235"]
        );


    if (
        !$result["success"]
    ) {

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
            ["250"]
        );


    if (
        !$result["success"]
    ) {

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
            ["250"]
        );


    if (
        !$result["success"]
    ) {

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
            ["354"]
        );


    if (
        !$result["success"]
    ) {

        fclose($socket);


        return [
            "success" => false,
            "error" =>
                "Gmail did not accept the email data."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE BOUNDARY
    |--------------------------------------------------------------------------
    */

    $boundary =
        "=_SchoolModel_" .
        md5(
            uniqid(
                (string) mt_rand(),
                true
            )
        );


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
        $toName .
        " <" .
        $to .
        ">\r\n";


    $headers .=
        "Subject: " .
        $subject .
        "\r\n";


    $headers .=
        "MIME-Version: 1.0\r\n";


    $headers .=
        "Content-Type: multipart/mixed; " .
        "boundary=\"" .
        $boundary .
        "\"\r\n";


    $headers .=
        "X-Mailer: School Model Schools\r\n";


    $headers .=
        "\r\n";


    /*
    |--------------------------------------------------------------------------
    | EMAIL BODY
    |--------------------------------------------------------------------------
    */

    $message = "";


    $message .=
        "--" .
        $boundary .
        "\r\n";


    $message .=
        "Content-Type: text/html; " .
        "charset=UTF-8\r\n";


    $message .=
        "Content-Transfer-Encoding: 8bit\r\n";


    $message .=
        "\r\n";


    $message .=
        $htmlBody;


    $message .=
        "\r\n\r\n";


    /*
    |--------------------------------------------------------------------------
    | ATTACH PDF
    |--------------------------------------------------------------------------
    */

    if (
        !empty($attachmentPath) &&
        file_exists($attachmentPath)
    ) {

        $fileData =
            file_get_contents(
                $attachmentPath
            );


        if (
            $fileData === false
        ) {

            fclose($socket);


            return [
                "success" => false,
                "error" =>
                    "The result PDF could not be read."
            ];

        }


        $encodedFile =
            chunk_split(
                base64_encode(
                    $fileData
                )
            );


        $message .=
            "--" .
            $boundary .
            "\r\n";


        $message .=
            "Content-Type: application/pdf; " .
            "name=\"" .
            $attachmentName .
            "\"\r\n";


        $message .=
            "Content-Disposition: attachment; " .
            "filename=\"" .
            $attachmentName .
            "\"\r\n";


        $message .=
            "Content-Transfer-Encoding: base64\r\n";


        $message .=
            "\r\n";


        $message .=
            $encodedFile;


        $message .=
            "\r\n";

    }


    /*
    |--------------------------------------------------------------------------
    | END MIME MESSAGE
    |--------------------------------------------------------------------------
    */

    $message .=
        "--" .
        $boundary .
        "--\r\n";


    /*
    |--------------------------------------------------------------------------
    | SMTP MESSAGE TERMINATION
    |--------------------------------------------------------------------------
    |
    | SMTP requires:
    |
    | \r\n.\r\n
    |
    */

    $smtpMessage =
        $headers .
        $message .
        "\r\n.\r\n";


    /*
    |--------------------------------------------------------------------------
    | SEND MESSAGE
    |--------------------------------------------------------------------------
    */

    fwrite(
        $socket,
        $smtpMessage
    );


    /*
    |--------------------------------------------------------------------------
    | READ SEND RESPONSE
    |--------------------------------------------------------------------------
    */

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
    | CHECK EMAIL RESPONSE
    |--------------------------------------------------------------------------
    */

    if (
        !$checkResponse(
            $response,
            ["250"]
        )
    ) {

        return [
            "success" => false,
            "error" =>
                "Gmail did not accept the email."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    return [
        "success" => true,
        "error" => ""
    ];

}


/*
|--------------------------------------------------------------------------
| ADMIN INFORMATION
|--------------------------------------------------------------------------
*/

$adminId =
    (int) $_SESSION["admin_id"];


$admin = null;


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
            profile_picture
        FROM admin_table
        WHERE id = ?
        LIMIT 1
    ");


$stmt->bind_param(
    "i",
    $adminId
);


$stmt->execute();


$result =
    $stmt->get_result();


$admin =
    $result->fetch_assoc();


$stmt->close();


if (!$admin) {

    session_destroy();

    header(
        "Location: admin_login.php"
    );

    exit;

}

/* --------------------------------------------------------------------------
| ADMIN PROFILE IMAGE
|-------------------------------------------------------------------------- */

$adminPicture = sanitize($admin["profile_picture"] ?? "");

$adminPictureFile = "";
$adminPictureUrl = "";

if ($adminPicture !== "") {

    // Keep only the filename stored in the database.
    $adminPicture = basename($adminPicture);

    // Actual file location on the server.
    $adminPictureFile =
        __DIR__ . "/uploads/admin_profiles/" . $adminPicture;

    // Browser URL.
    $adminPictureUrl =
        "uploads/admin_profiles/" . $adminPicture;
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status = "";

$statusType = "danger";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search =
    sanitize(
        $_GET["search"] ?? ""
    );


/*
|--------------------------------------------------------------------------
| RESULT UPLOAD
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["upload_result"])
) {


    $studentId =
        (int) (
            $_POST["student_id"] ?? 0
        );


    /*
    |--------------------------------------------------------------------------
    | CHECK STUDENT ID
    |--------------------------------------------------------------------------
    */

    if (
        $studentId <= 0
    ) {

        $status =
            "Invalid student selected.";

        $statusType =
            "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | GET STUDENT
        |--------------------------------------------------------------------------
        */

        $stmt =
            $conn->prepare("
                SELECT
                    id,
                    student_name,
                    email,
                    applying_for
                FROM enroll_table
                WHERE id = ?
                LIMIT 1
            ");


        $stmt->bind_param(
            "i",
            $studentId
        );


        $stmt->execute();


        $studentResult =
            $stmt->get_result();


        $student =
            $studentResult->fetch_assoc();


        $stmt->close();


        if (!$student) {

            $status =
                "Student record was not found.";

            $statusType =
                "danger";

        } else {


            /*
            |--------------------------------------------------------------------------
            | VALIDATE STUDENT EMAIL
            |--------------------------------------------------------------------------
            */

            if (
                !filter_var(
                    $student["email"],
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $status =
                    "The student's email address is invalid.";

                $statusType =
                    "danger";

            } elseif (
                !isset(
                $_FILES["result_pdf"]
            )
            ) {

                $status =
                    "Please select a result PDF.";

                $statusType =
                    "danger";

            } else {


                $file =
                    $_FILES["result_pdf"];


                /*
                |--------------------------------------------------------------------------
                | UPLOAD ERROR
                |--------------------------------------------------------------------------
                */

                if (
                    $file["error"] !==
                    UPLOAD_ERR_OK
                ) {

                    $status =
                        "There was an error uploading the PDF.";

                    $statusType =
                        "danger";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | FILE SIZE
                    |--------------------------------------------------------------------------
                    */

                    $maxSize =
                        10 * 1024 * 1024;


                    if (
                        $file["size"] >
                        $maxSize
                    ) {

                        $status =
                            "The result PDF is too large. " .
                            "Maximum size is 10MB.";

                        $statusType =
                            "danger";

                    } else {


                        /*
                        |--------------------------------------------------------------------------
                        | FILE EXTENSION
                        |--------------------------------------------------------------------------
                        */

                        $extension =
                            strtolower(
                                pathinfo(
                                    $file["name"],
                                    PATHINFO_EXTENSION
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | MIME TYPE
                        |--------------------------------------------------------------------------
                        */

                        $mimeType =
                            mime_content_type(
                                $file["tmp_name"]
                            );


                        if (
                            $extension !== "pdf" ||
                            $mimeType !== "application/pdf"
                        ) {

                            $status =
                                "Only PDF result files are allowed.";

                            $statusType =
                                "danger";

                        } else {


                            /*
                            |--------------------------------------------------------------------------
                            | RESULT DIRECTORY
                            |--------------------------------------------------------------------------
                            */

                            $resultDirectory =
                                __DIR__ .
                                DIRECTORY_SEPARATOR .
                                "uploads" .
                                DIRECTORY_SEPARATOR .
                                "results";


                            /*
                            |--------------------------------------------------------------------------
                            | CREATE DIRECTORY
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !is_dir(
                                    $resultDirectory
                                )
                            ) {

                                if (
                                    !mkdir(
                                        $resultDirectory,
                                        0755,
                                        true
                                    )
                                ) {

                                    $status =
                                        "The result directory could not be created.";

                                    $statusType =
                                        "danger";

                                    goto upload_end;

                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SAFE STUDENT NAME
                            |--------------------------------------------------------------------------
                            */

                            $safeStudentName =
                                preg_replace(
                                    "/[^A-Za-z0-9_-]/",
                                    "_",
                                    $student[
                                        "student_name"
                                    ]
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | FILE NAME
                            |--------------------------------------------------------------------------
                            */

                            $fileName =
                                $safeStudentName .
                                "_" .
                                $studentId .
                                "_" .
                                time() .
                                ".pdf";


                            /*
                            |--------------------------------------------------------------------------
                            | DESTINATION
                            |--------------------------------------------------------------------------
                            */

                            $destination =
                                $resultDirectory .
                                DIRECTORY_SEPARATOR .
                                $fileName;


                            /*
                            |--------------------------------------------------------------------------
                            | MOVE UPLOAD
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !move_uploaded_file(
                                    $file["tmp_name"],
                                    $destination
                                )
                            ) {

                                $status =
                                    "The result could not be saved.";

                                $statusType =
                                    "danger";

                            } else {


                                /*
                                |--------------------------------------------------------------------------
                                | DATABASE PATH
                                |--------------------------------------------------------------------------
                                */

                                $resultPath =
                                    "uploads/results/" .
                                    $fileName;


                                /*
                                |--------------------------------------------------------------------------
                                | CHECK RESULT COLUMN
                                |--------------------------------------------------------------------------
                                */

                                $columnExists =
                                    false;


                                $columnCheck =
                                    $conn->query("
                                        SHOW COLUMNS
                                        FROM enroll_table
                                        LIKE 'result_pdf'
                                    ");


                                if (
                                    $columnCheck &&
                                    $columnCheck->num_rows > 0
                                ) {

                                    $columnExists =
                                        true;

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | DATABASE COLUMN DOES NOT EXIST
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !$columnExists
                                ) {

                                    unlink(
                                        $destination
                                    );


                                    $status =
                                        "The result file was uploaded, " .
                                        "but the result_pdf column does not " .
                                        "exist in enroll_table. Please add " .
                                        "the column first.";

                                    $statusType =
                                        "danger";

                                } else {


                                    /*
                                    |--------------------------------------------------------------------------
                                    | GET OLD RESULT
                                    |--------------------------------------------------------------------------
                                    */

                                    $oldResultPath =
                                        "";


                                    $stmt =
                                        $conn->prepare("
                                            SELECT
                                                result_pdf
                                            FROM enroll_table
                                            WHERE id = ?
                                            LIMIT 1
                                        ");


                                    $stmt->bind_param(
                                        "i",
                                        $studentId
                                    );


                                    $stmt->execute();


                                    $oldResult =
                                        $stmt
                                            ->get_result()
                                            ->fetch_assoc();


                                    $stmt->close();


                                    if (
                                        $oldResult
                                    ) {

                                        $oldResultPath =
                                            $oldResult[
                                                "result_pdf"
                                            ] ?? "";

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | UPDATE DATABASE
                                    |--------------------------------------------------------------------------
                                    */

                                    $stmt =
                                        $conn->prepare("
                                            UPDATE enroll_table
                                            SET result_pdf = ?
                                            WHERE id = ?
                                        ");


                                    $stmt->bind_param(
                                        "si",
                                        $resultPath,
                                        $studentId
                                    );


                                    $saved =
                                        $stmt->execute();


                                    $stmt->close();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | DATABASE UPDATE FAILED
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        !$saved
                                    ) {

                                        unlink(
                                            $destination
                                        );


                                        $status =
                                            "The result PDF was uploaded, " .
                                            "but the database could not be updated.";

                                        $statusType =
                                            "danger";

                                    } else {


                                        /*
                                        |--------------------------------------------------------------------------
                                        | DELETE OLD RESULT
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            !empty(
                                            $oldResultPath
                                        )
                                        ) {

                                            $oldFile =
                                                __DIR__ .
                                                DIRECTORY_SEPARATOR .
                                                str_replace(
                                                    "/",
                                                    DIRECTORY_SEPARATOR,
                                                    ltrim(
                                                        $oldResultPath,
                                                        "/\\"
                                                    )
                                                );


                                            /*
                                            | Only delete the old file
                                            | if it is actually inside
                                            | uploads/results.
                                            */

                                            $realResultDirectory =
                                                realpath(
                                                    $resultDirectory
                                                );


                                            $realOldFile =
                                                file_exists(
                                                    $oldFile
                                                )
                                                ? realpath(
                                                    $oldFile
                                                )
                                                : false;


                                            if (
                                                $realResultDirectory !== false &&
                                                $realOldFile !== false &&
                                                strpos(
                                                    $realOldFile,
                                                    $realResultDirectory .
                                                    DIRECTORY_SEPARATOR
                                                ) === 0
                                            ) {

                                                /*
                                                | Don't delete the
                                                | newly uploaded file.
                                                */

                                                if (
                                                    $realOldFile !==
                                                    realpath(
                                                        $destination
                                                    )
                                                ) {

                                                    @unlink(
                                                        $realOldFile
                                                    );

                                                }

                                            }

                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | PREPARE STUDENT EMAIL
                                        |--------------------------------------------------------------------------
                                        */

                                        $studentName =
                                            e(
                                                $student[
                                                    "student_name"
                                                ]
                                            );


                                        $studentEmail =
                                            e(
                                                $student[
                                                    "email"
                                                ]
                                            );


                                        $studentClass =
                                            e(
                                                $student[
                                                    "applying_for"
                                                ]
                                            );


                                        $safeSchoolName =
                                            e(
                                                $schoolName
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | EMAIL SUBJECT
                                        |--------------------------------------------------------------------------
                                        */

                                        $subject =
                                            "Your Student Result is Ready - " .
                                            $schoolName;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | EMAIL HTML
                                        |--------------------------------------------------------------------------
                                        */

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

<title>
    Student Result
</title>

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


    <!-- HEADER -->

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

            {$safeSchoolName}

        </h2>


        <p
            style='
                margin:8px 0 0;
                color:#cbd5e1;
                font-size:13px;
            '
        >

            Student Result Portal

        </p>

    </div>


    <!-- CONTENT -->

    <div
        style='
            padding:30px;
            color:#333333;
            line-height:1.7;
        '
    >


        <h2
            style='
                color:#061f49;
                font-family:Georgia,serif;
            '
        >

            Your Result is Ready

        </h2>


        <p>

            Dear
            <strong>
                {$studentName}
            </strong>,

        </p>


        <p>

            We are pleased to inform you that
            your student result is now available.

        </p>


        <div
            style='
                background:#f8f7f3;
                border:1px solid #ddd9d1;
                padding:18px;
                margin:20px 0;
            '
        >

            <p
                style='
                    margin:0 0 8px;
                '
            >

                <strong>
                    Student:
                </strong>

                {$studentName}

            </p>


            <p
                style='
                    margin:0;
                '
            >

                <strong>
                    Class:
                </strong>

                {$studentClass}

            </p>

        </div>


        <p>

            Your result PDF is attached to this
            email.

        </p>


        <p>

            You can also visit the student result
            portal to verify your email and access
            your result.

        </p>


        <p
            style='
                margin-top:30px;
            '
        >

            Regards,<br>

            <strong>
                {$safeSchoolName}
            </strong>

        </p>


    </div>


    <!-- FOOTER -->

    <div
        style='
            background:#061f49;
            color:#cbd5e1;
            padding:15px;
            text-align:center;
            font-size:12px;
        '
    >

        © 2026 {$safeSchoolName}.
        All Rights Reserved.

    </div>


</div>


</body>

</html>

";


                                        /*
                                        |--------------------------------------------------------------------------
                                        | SEND EMAIL
                                        |--------------------------------------------------------------------------
                                        */

                                        $emailResult =
                                            sendGmailEmail(
                                                $student[
                                                    "email"
                                                ],
                                                $student[
                                                    "student_name"
                                                ],
                                                $subject,
                                                $htmlBody,
                                                $destination,
                                                "Student_Result.pdf",
                                                $gmailUser,
                                                $gmailAppPassword,
                                                $schoolName
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | EMAIL SUCCESS
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            $emailResult[
                                                "success"
                                            ]
                                        ) {

                                            $status =
                                                "Result uploaded successfully " .
                                                "and emailed to " .
                                                $student[
                                                    "email"
                                                ] .
                                                ".";

                                            $statusType =
                                                "success";

                                        } else {


                                            /*
                                            |--------------------------------------------------------------------------
                                            | EMAIL FAILED
                                            |--------------------------------------------------------------------------
                                            */

                                            $status =
                                                "Result uploaded successfully, " .
                                                "but the student could not be emailed. " .
                                                "Reason: " .
                                                $emailResult[
                                                    "error"
                                                ];

                                            $statusType =
                                                "warning";

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| LABEL FOR GOTO
|--------------------------------------------------------------------------
*/

upload_end:


/*
|--------------------------------------------------------------------------
| GET STUDENTS
|--------------------------------------------------------------------------
*/

$students = [];


if (
    $search !== ""
) {


    $searchLike =
        "%" .
        $search .
        "%";


    $stmt =
        $conn->prepare("
            SELECT
                id,
                student_name,
                email,
                applying_for,
                passport,
                date_of_birth,
                parent_name,
                phone
            FROM enroll_table
            WHERE
                student_name LIKE ?
                OR email LIKE ?
                OR parent_name LIKE ?
                OR phone LIKE ?
                OR applying_for LIKE ?
            ORDER BY student_name ASC
        ");


    $stmt->bind_param(
        "sssss",
        $searchLike,
        $searchLike,
        $searchLike,
        $searchLike,
        $searchLike
    );


} else {


    $stmt =
        $conn->prepare("
            SELECT
                id,
                student_name,
                email,
                applying_for,
                passport,
                date_of_birth,
                parent_name,
                phone
            FROM enroll_table
            ORDER BY student_name ASC
        ");

}


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
    $result->fetch_assoc()
) {

    $students[] =
        $row;

}


$stmt->close();


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Upload Student Result
    </title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


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

            background: #fdfdef;

            color: #061f49;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 250px;

            height: 100vh;

            background: #061f49;

            color: white;

            padding: 25px 18px;

            z-index: 1000;

        }


        .school-logo {

            font-family:
                Georgia,
                serif;

            font-size: 22px;

            font-weight: bold;

            margin-bottom: 35px;

        }


        .school-logo i {

            color: #d66a2b;

            margin-right: 7px;

        }


        .sidebar-link {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 15px;

            margin-bottom: 8px;

            color:
                rgba(255,
                    255,
                    255,
                    .75);

            text-decoration: none;

            border-radius: 5px;

            transition: .3s;

        }


        .sidebar-link:hover,
        .sidebar-link.active {

            background:
                rgba(255,
                    255,
                    255,
                    .1);

            color: white;

        }


        .sidebar-link i {

            font-size: 18px;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 250px;

            min-height: 100vh;

        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {

            min-height: 85px;

            background: white;

            border-bottom:
                1px solid #e3e0da;

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding:
                15px 30px;

        }


        .welcome h2 {

            margin: 0;

            font-family:
                Georgia,
                serif;

            font-size: 22px;

        }


        .welcome p {

            margin:
                3px 0 0;

            color: #777;

            font-size: 12px;

        }


        .admin-profile {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        .admin-photo,
        .admin-placeholder {

            width: 50px;

            height: 50px;

            border-radius: 50%;

            object-fit: cover;

            border:
                2px solid #d66a2b;

        }


        .admin-placeholder {

            background: #e9ecef;

            display: flex;

            justify-content: center;

            align-items: center;

            color: #777;

            font-size: 22px;

        }


        .admin-name {

            font-weight: bold;

            font-size: 13px;

        }


        .admin-email {

            color: #777;

            font-size: 11px;

        }


        .logout-btn {

            color: #d66a2b;

            text-decoration: none;

            margin-left: 10px;

        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .content {

            width: 100%;

            padding: 30px;

        }


        .page-title {

            margin-bottom: 25px;

        }


        .page-title h1 {

            margin: 0;

            font-family:
                Georgia,
                serif;

            font-size: 28px;

        }


        .page-title p {

            margin-top: 7px;

            color: #777;

            font-size: 13px;

        }


        /* =====================================================
           ALERT
        ===================================================== */

        .alert {

            font-size: 13px;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .result-card {

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 8px;

            padding: 25px;

            margin-bottom: 25px;

        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .search-form {

            display: flex;

            gap: 10px;

            width: 100%;

        }


        .search-form input {

            flex: 1;

            height: 42px;

            border:
                1px solid #d7d4ce;

            border-radius: 4px;

            padding:
                0 12px;

            outline: none;

        }


        .search-form button {

            border: none;

            background: #d66a2b;

            color: white;

            padding:
                0 20px;

            border-radius: 4px;

        }


        /* =====================================================
           STUDENT
        ===================================================== */

        .student-item {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 17px;

            border:
                1px solid #e5e2da;

            border-radius: 6px;

            margin-bottom: 12px;

        }


        .student-info {

            min-width: 0;

        }


        .student-name {

            font-weight: bold;

            color: #061f49;

            font-size: 14px;

        }


        .student-email {

            color: #777;

            font-size: 12px;

            margin-top: 3px;

        }


        .student-class {

            display: inline-block;

            margin-top: 6px;

            padding:
                4px 9px;

            border-radius: 20px;

            background: #fff0e8;

            color: #d66a2b;

            font-size: 10px;

            font-weight: bold;

        }


        /* =====================================================
           UPLOAD
        ===================================================== */

        .upload-form {

            margin-top: 18px;

            padding: 20px;

            background: #fafaf6;

            border:
                1px solid #e5e2da;

            border-radius: 6px;

        }


        .file-input {

            width: 100%;

            border:
                1px solid #d7d4ce;

            background: white;

            padding: 9px;

            border-radius: 4px;

        }


        .upload-btn {

            margin-top: 15px;

            background: #d66a2b;

            color: white;

            border: none;

            border-radius: 4px;

            padding:
                10px 20px;

            font-weight: bold;

        }


        .upload-btn:hover {

            background: #c55d25;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {

            padding: 50px;

            text-align: center;

            color: #777;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 900px) {

            .sidebar {

                width: 70px;

                padding:
                    20px 10px;

            }


            .school-logo span,
            .sidebar-link span {

                display: none;

            }


            .sidebar-link {

                justify-content:
                    center;

            }


            .main {

                margin-left: 70px;

            }

        }


        @media (max-width: 600px) {

            .topbar {

                padding:
                    15px;

            }


            .content {

                padding:
                    15px;

            }


            .admin-email {

                display: none;

            }


            .student-item {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .search-form {

                flex-direction:
                    column;

            }


            .search-form button {

                height:
                    42px;

            }

        }
    </style>

</head>


<body>


    <!-- =====================================================
         SIDEBAR
    ===================================================== -->

    <aside class="sidebar">


        <div class="school-logo">

            <i class="bi bi-book-half"></i>

            <span>
                School Admin
            </span>

        </div>


        <a href="admin_dashboard.php" class="sidebar-link">

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <a href="upload_result.php" class="sidebar-link active">

            <i class="bi bi-file-earmark-pdf"></i>

            <span>
                Student Results
            </span>

        </a>


        <a href="admin_profile.php" class="sidebar-link">

            <i class="bi bi-person-circle"></i>

            <span>
                My Profile
            </span>

        </a>


        <a href="admin_logout.php" class="sidebar-link">

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>

    </aside>


    <!-- =====================================================
         MAIN
    ===================================================== -->

    <div class="main">


        <!-- =================================================
             TOPBAR
        ================================================= -->

        <header class="topbar">


            <div class="welcome">

                <h2>

                    Welcome,
                    <?php

                    echo e(
                        $admin[
                            "full_name"
                        ]
                    );

                    ?>

                </h2>


                <p>
                    Upload and send student results
                </p>

            </div>


            <div class="admin-profile">

                <?php if ($adminPicture !== "" && is_file($adminPictureFile)): ?>

                    <img src="<?php echo e($adminPictureUrl); ?>"
                         class="admin-photo"
                         alt="Admin profile picture"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">

                    <div class="admin-placeholder" style="display:none;">
                        <i class="bi bi-person"></i>
                    </div>

                <?php else: ?>

                    <div class="admin-placeholder">
                        <i class="bi bi-person"></i>
                    </div>

                <?php endif; ?>

                <div class="admin-details">
                    <div class="admin-name"><?php echo e($admin["full_name"]); ?></div>
                    <div class="admin-email"><?php echo e($admin["email"]); ?></div>
                </div>

                <a href="admin_logout.php" class="logout-btn" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

            </div>

        </header>

        <!-- =================================================
             CONTENT
        ================================================= -->

        <main class="content">

            <div class="page-title">


            <h1>
                Student Results
            </h1>


            <p>
                Search for a student and upload their result.
            </p>


        </div>


        <?php if (
            $status !== ""
        ): ?>


            <div class="alert alert-<?php

            echo e(
                $statusType
            );

            ?>">

                <?php

                echo e(
                    $status
                );

                ?>

            </div>


        <?php endif; ?>


        <!-- =================================================
                 SEARCH
            ================================================= -->

        <section class="result-card">


            <h4 class="mb-3">

                <i class="bi bi-search"></i>

                Find Student

            </h4>


            <form method="GET" class="search-form">


                <input type="search" name="search" value="<?php

                echo e(
                    $search
                );

                ?>" placeholder="Search student name, email, parent, phone or class...">


                <button type="submit">

                    <i class="bi bi-search"></i>

                    Search

                </button>


            </form>


        </section>


        <!-- =================================================
                 STUDENTS
            ================================================= -->

        <section class="result-card">


            <h4 class="mb-4">
                Student Records
            </h4>


            <?php if (
                empty(
                $students
            )
            ): ?>


                <div class="empty">


                    <i class="bi bi-person-x" style="font-size:40px;"></i>


                    <p class="mt-3">


                        <?php if (
                            $search !== ""
                        ): ?>


                            No student matching
                            "<?php

                            echo e(
                                $search
                            );

                            ?>"
                            was found.


                        <?php else: ?>


                            No student records found.


                        <?php endif; ?>


                    </p>


                </div>


            <?php else: ?>


                <?php foreach (
                    $students
                    as $student
                ): ?>


                    <!-- =================================================
                             STUDENT
                        ================================================= -->

                    <div class="student-item">


                        <div class="student-info">


                            <div class="student-name">

                                <?php

                                echo e(
                                    $student[
                                        "student_name"
                                    ]
                                );

                                ?>

                            </div>


                            <div class="student-email">

                                <?php

                                echo e(
                                    $student[
                                        "email"
                                    ]
                                );

                                ?>

                            </div>


                            <span class="student-class">

                                <?php

                                echo e(
                                    $student[
                                        "applying_for"
                                    ]
                                );

                                ?>

                            </span>


                        </div>


                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#upload<?php

                        echo (int) 
                            $student[
                                "id"
                            ];

                        ?>">

                            <i class="bi bi-upload"></i>

                            Upload Result

                        </button>


                    </div>


                    <!-- =================================================
                             UPLOAD FORM
                        ================================================= -->

                    <div class="collapse" id="upload<?php

                    echo (int) 
                        $student[
                            "id"
                        ];

                    ?>">


                        <div class="upload-form">


                            <form method="POST" enctype="multipart/form-data">


                                <input type="hidden" name="student_id" value="<?php

                                echo (int) 
                                    $student[
                                        "id"
                                    ];

                                ?>">


                                <div class="mb-3">


                                    <label class="form-label">

                                        Result PDF

                                    </label>


                                    <input type="file" name="result_pdf" class="file-input" accept="application/pdf,.pdf"
                                        required>


                                    <small class="text-muted">

                                        PDF only.
                                        Maximum file size:
                                        10MB.

                                    </small>


                                </div>


                                <button type="submit" name="upload_result" class="upload-btn">

                                    <i class="bi bi-cloud-arrow-up"></i>

                                    Upload & Send Result

                                </button>


                            </form>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </section>


    </main>


    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>