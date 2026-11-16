<?php

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/handlers/sanitize.php';
/* =========================================================
   ADMIN DASHBOARD
   ========================================================= */


/* =========================================================
   DATABASE
   ========================================================= */

// require "./includes/db.php";


/* =========================================================
   HELPER
   ========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   CHECK ADMIN LOGIN
   ========================================================= */

if (
    !isset($_SESSION["admin_id"]) ||
    empty($_SESSION["admin_id"])
) {

    header("Location: admin_login.php");

    exit;

}


$adminId = (int) $_SESSION["admin_id"];


/* =========================================================
   GET LOGGED-IN ADMIN
   ========================================================= */

$admin = null;


$stmt = $conn->prepare("
    SELECT
        id,
        full_name,
        email,
        profile_picture
    FROM admin_table
    WHERE id = ?
    LIMIT 1
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $adminId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $admin = $result->fetch_assoc();

    $stmt->close();

}


if (!$admin) {

    session_unset();

    session_destroy();

    header("Location: admin_login.php");

    exit;

}


/* =========================================================
   VARIABLES
   ========================================================= */

$status = "";

$statusType = "success";

$search = sanitize(
    $_GET["search"] ?? ""
);


/* =========================================================
   UPLOAD DIRECTORIES
   ========================================================= */

/*
   Dashboard is directly inside:

   school_website/admin_dashboard.php

   Uploads are inside:

   school_website/uploads/passports/
*/

$passportDirectory =
    __DIR__ .
    "/uploads/passports";


$passportWebDirectory =
    "uploads/passports";


/* Create directory if it does not exist */

if (!is_dir($passportDirectory)) {

    @mkdir(
        $passportDirectory,
        0755,
        true
    );

}


/* =========================================================
   DELETE STUDENT
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_student"])
) {

    $studentId = (int) (
        $_POST["student_id"] ?? 0
    );


    if ($studentId > 0) {


        /*
         * Get existing passport first.
         */

        $stmt = $conn->prepare("
            SELECT passport
            FROM enroll_table
            WHERE id = ?
            LIMIT 1
        ");


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $studentId
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            $student =
                $result->fetch_assoc();

            $stmt->close();


            /*
             * Delete passport file.
             */

            if (
                $student &&
                !empty($student["passport"])
            ) {

                $oldPassport =
                    sanitize(
                        $student["passport"]
                    );


                $oldPassport =
                    ltrim(
                        $oldPassport,
                        "/\\"
                    );


                $oldPassportFile =
                    __DIR__ .
                    "/" .
                    $oldPassport;


                if (
                    is_file(
                        $oldPassportFile
                    )
                ) {

                    @unlink(
                        $oldPassportFile
                    );

                }

            }


            /*
             * Delete database record.
             */

            $stmt =
                $conn->prepare("
                    DELETE FROM enroll_table
                    WHERE id = ?
                ");


            if ($stmt) {

                $stmt->bind_param(
                    "i",
                    $studentId
                );


                if (
                    $stmt->execute()
                ) {

                    $status =
                        "Student record deleted successfully.";

                    $statusType =
                        "success";

                } else {

                    $status =
                        "Unable to delete student record.";

                    $statusType =
                        "danger";

                }


                $stmt->close();

            }

        }

    }


    /*
     * Redirect to prevent form resubmission.
     */

    $redirect =
        "admin_dashboard.php";


    if ($search !== "") {

        $redirect .=
            "?search=" .
            urlencode($search);

    }


    header(
        "Location: " .
        $redirect
    );

    exit;

}


/* =========================================================
   UPDATE STUDENT
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_student"])
) {


    $studentId =
        (int) (
            $_POST["student_id"] ?? 0
        );


    $studentName =
        sanitize(
            $_POST["student_name"] ?? ""
        );


    $dateOfBirth =
        sanitize(
            $_POST["date_of_birth"] ?? ""
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
        sanitize(
            $_POST["email"] ?? ""
        );


    $notes =
        sanitize(
            $_POST["notes"] ?? ""
        );


    if (
        $studentId <= 0 ||
        $studentName === "" ||
        $dateOfBirth === "" ||
        $applyingFor === "" ||
        $parentName === "" ||
        $phone === "" ||
        $email === ""
    ) {

        $status =
            "Please fill in all required student information.";

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


        $stmt = $conn->prepare("
            UPDATE enroll_table
            SET
                student_name = ?,
                date_of_birth = ?,
                applying_for = ?,
                parent_name = ?,
                phone = ?,
                email = ?,
                notes = ?
            WHERE id = ?
        ");


        if ($stmt) {

            $stmt->bind_param(
                "sssssssi",
                $studentName,
                $dateOfBirth,
                $applyingFor,
                $parentName,
                $phone,
                $email,
                $notes,
                $studentId
            );


            if (
                $stmt->execute()
            ) {

                $status =
                    "Student record updated successfully.";

                $statusType =
                    "success";

            } else {

                $status =
                    "Unable to update student record.";

                $statusType =
                    "danger";

            }


            $stmt->close();

        } else {

            $status =
                "Database error while updating student.";

            $statusType =
                "danger";

        }

    }

}


/* =========================================================
   ADD / UPDATE PASSPORT
   ========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["upload_passport"])
) {


    $studentId =
        (int) (
            $_POST["student_id"] ?? 0
        );


    if (
        $studentId <= 0
    ) {

        $status =
            "Invalid student record.";

        $statusType =
            "danger";

    } elseif (
        !isset(
        $_FILES["passport"]
    ) ||
        $_FILES["passport"]["error"] !== UPLOAD_ERR_OK
    ) {

        $status =
            "Please select a passport picture.";

        $statusType =
            "danger";

    } else {


        $file =
            $_FILES["passport"];


        /*
         * Maximum file size:
         * 5 MB
         */

        if (
            $file["size"] >
            5 * 1024 * 1024
        ) {

            $status =
                "Passport picture must not be larger than 5 MB.";

            $statusType =
                "danger";

        } else {


            /*
             * Check actual image.
             */

            $imageInfo =
                @getimagesize(
                    $file["tmp_name"]
                );


            if (
                $imageInfo === false
            ) {

                $status =
                    "The uploaded file is not a valid image.";

                $statusType =
                    "danger";

            } else {


                /*
                 * Allowed image types.
                 */

                $allowedTypes = [

                    "image/jpeg",
                    "image/png",
                    "image/webp"

                ];


                $mimeType =
                    $imageInfo["mime"] ??
                    "";


                if (
                    !in_array(
                        $mimeType,
                        $allowedTypes,
                        true
                    )
                ) {

                    $status =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $statusType =
                        "danger";

                } else {


                    /*
                     * Get old passport.
                     */

                    $stmt =
                        $conn->prepare("
                            SELECT passport
                            FROM enroll_table
                            WHERE id = ?
                            LIMIT 1
                        ");


                    $oldPassport = "";


                    if ($stmt) {

                        $stmt->bind_param(
                            "i",
                            $studentId
                        );

                        $stmt->execute();

                        $result =
                            $stmt->get_result();

                        $student =
                            $result->fetch_assoc();

                        $stmt->close();


                        if (
                            $student &&
                            !empty(
                            $student["passport"]
                        )
                        ) {

                            $oldPassport =
                                sanitize(
                                    $student["passport"]
                                );

                        }

                    }


                    /*
                     * Generate new filename.
                     */

                    $extension = "jpg";


                    if (
                        $mimeType ===
                        "image/png"
                    ) {

                        $extension =
                            "png";

                    } elseif (
                        $mimeType ===
                        "image/webp"
                    ) {

                        $extension =
                            "webp";

                    }


                    $newFileName =
                        "student_" .
                        $studentId .
                        "_" .
                        bin2hex(
                            random_bytes(8)
                        ) .
                        "." .
                        $extension;


                    $newFilePath =
                        $passportDirectory .
                        "/" .
                        $newFileName;


                    $newDatabasePath =
                        "uploads/passports/" .
                        $newFileName;


                    /*
                     * Move uploaded file.
                     */

                    if (
                        move_uploaded_file(
                            $file["tmp_name"],
                            $newFilePath
                        )
                    ) {


                        /*
                         * Update database.
                         */

                        $stmt =
                            $conn->prepare("
                                UPDATE enroll_table
                                SET passport = ?
                                WHERE id = ?
                            ");


                        if ($stmt) {

                            $stmt->bind_param(
                                "si",
                                $newDatabasePath,
                                $studentId
                            );


                            if (
                                $stmt->execute()
                            ) {


                                /*
                                 * Delete old passport
                                 * ONLY after database
                                 * update succeeds.
                                 */

                                if (
                                    $oldPassport !== ""
                                ) {

                                    $oldPassport =
                                        ltrim(
                                            $oldPassport,
                                            "/\\"
                                        );


                                    $oldPassportFile =
                                        __DIR__ .
                                        "/" .
                                        $oldPassport;


                                    /*
                                     * Do not accidentally
                                     * delete outside uploads.
                                     */

                                    $realOld =
                                        realpath(
                                            $oldPassportFile
                                        );


                                    $realUploads =
                                        realpath(
                                            __DIR__ .
                                            "/uploads/passports"
                                        );


                                    if (
                                        $realOld &&
                                        $realUploads &&
                                        str_starts_with(
                                            $realOld,
                                            $realUploads .
                                            DIRECTORY_SEPARATOR
                                        )
                                    ) {

                                        @unlink(
                                            $realOld
                                        );

                                    }

                                }


                                $status =
                                    "Student passport uploaded successfully.";

                                $statusType =
                                    "success";

                            } else {


                                /*
                                 * Database update failed.
                                 * Delete newly uploaded file.
                                 */

                                @unlink(
                                    $newFilePath
                                );


                                $status =
                                    "Unable to save passport information.";

                                $statusType =
                                    "danger";

                            }


                            $stmt->close();

                        } else {

                            @unlink(
                                $newFilePath
                            );


                            $status =
                                "Database error while saving passport.";

                            $statusType =
                                "danger";

                        }

                    } else {

                        $status =
                            "Unable to upload passport picture.";

                        $statusType =
                            "danger";

                    }

                }

            }

        }

    }

}


/* =========================================================
   TOTAL STUDENTS
   ========================================================= */

$totalStudents = 0;


$result =
    $conn->query("
        SELECT COUNT(*) AS total
        FROM enroll_table
    ");


if ($result) {

    $row =
        $result->fetch_assoc();

    $totalStudents =
        (int) (
            $row["total"] ?? 0
        );

}


/* =========================================================
   STUDENTS QUERY
   ========================================================= */

$students = [];


if ($search !== "") {


    $searchLike =
        "%" .
        $search .
        "%";


    $stmt =
        $conn->prepare("
            SELECT
                id,
                student_name,
                date_of_birth,
                applying_for,
                parent_name,
                phone,
                email,
                notes,
                passport
            FROM enroll_table
            WHERE
                student_name LIKE ?
                OR email LIKE ?
                OR parent_name LIKE ?
                OR phone LIKE ?
                OR applying_for LIKE ?
            ORDER BY id DESC
        ");


    if ($stmt) {

        $stmt->bind_param(
            "sssss",
            $searchLike,
            $searchLike,
            $searchLike,
            $searchLike,
            $searchLike
        );


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

    }

} else {


    $result =
        $conn->query("
            SELECT
                id,
                student_name,
                date_of_birth,
                applying_for,
                parent_name,
                phone,
                email,
                notes,
                passport
            FROM enroll_table
            ORDER BY id DESC
        ");


    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $students[] =
                $row;

        }

    }

}


/* =========================================================
   ADMIN PROFILE IMAGE
========================================================= */

$adminPicture = sanitize($admin["profile_picture"] ?? "");

$adminPictureFile = "";
$adminPictureUrl = "";

if ($adminPicture !== "") {

    // Remove any folder information and keep only the filename
    $adminPicture = basename($adminPicture);

    // Actual location of the admin profile photo
    $adminPictureFile =
        __DIR__ .
        "/uploads/admin_profiles/" .
        $adminPicture;

    // Browser URL
    $adminPictureUrl =
        "uploads/admin_profiles/" .
        $adminPicture;
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Admin Dashboard
    </title>


    <!-- =====================================================
     BOOTSTRAP
===================================================== -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- =====================================================
     BOOTSTRAP ICONS
===================================================== -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


    <style>
        /* =====================================================
   GLOBAL
===================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
        }


        body {

            background: #fdfdef;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #061f49;

            overflow-x: hidden;
        }


        /* =====================================================
   SIDEBAR
===================================================== */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;

            width: 250px;

            height: 100vh;

            background: #061f49;

            color: white;

            padding: 25px 18px;

            z-index: 1000;

            overflow-y: auto;
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

            color: rgba(255,
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

            width: calc(100% - 250px);

            min-height: 100vh;

            max-width: 100%;

            overflow-x: hidden;
        }


        /* =====================================================
   TOPBAR
===================================================== */

        .topbar {

            min-height: 85px;

            width: 100%;

            max-width: 100%;

            background: white;

            border-bottom:
                1px solid #e3e0da;

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding:
                15px 30px;

            gap: 20px;
        }


        .welcome {

            min-width: 0;
        }


        .welcome h2 {

            margin: 0;

            font-family:
                Georgia,
                serif;

            font-size: 22px;

            word-break: break-word;
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

            min-width: 0;
        }


        .admin-photo,
        .admin-placeholder {

            width: 50px;

            height: 50px;

            border-radius: 50%;

            object-fit: cover;

            flex-shrink: 0;

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


        .admin-details {

            min-width: 0;
        }


        .admin-name {

            font-weight: bold;

            font-size: 13px;

            word-break: break-word;
        }


        .admin-email {

            color: #777;

            font-size: 11px;

            word-break: break-word;
        }


        .logout-btn {

            color: #d66a2b;

            text-decoration: none;

            margin-left: 10px;

            font-size: 16px;

            flex-shrink: 0;
        }


        .logout-btn:hover {

            color: #061f49;
        }


        /* =====================================================
   CONTENT
===================================================== */

        .content {

            width: 100%;

            max-width: 100%;

            padding: 30px;

            overflow-x: hidden;
        }


        .alert {

            font-size: 13px;

            word-break: break-word;
        }


        /* =====================================================
   STAT CARD
===================================================== */

        .stat-card {

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 8px;

            padding: 22px;

            height: 100%;
        }


        .stat-icon {

            width: 45px;

            height: 45px;

            border-radius: 50%;

            background:
                rgba(214,
                    106,
                    43,
                    .1);

            color: #d66a2b;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

            margin-bottom: 12px;
        }


        .stat-number {

            font-size: 28px;

            font-weight: bold;

            color: #061f49;
        }


        .stat-label {

            color: #777;

            font-size: 12px;
        }


        /* =====================================================
   SEARCH
===================================================== */

        .search-card {

            width: 100%;

            max-width: 100%;

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 8px;

            padding: 20px;

            margin-top: 25px;
        }


        .search-form {

            width: 100%;

            display: flex;

            gap: 10px;
        }


        .search-form input {

            flex: 1;

            min-width: 0;

            height: 42px;

            border:
                1px solid #d7d4ce;

            border-radius: 4px;

            padding:
                0 12px;

            outline: none;
        }


        .search-form input:focus {

            border-color: #d66a2b;

            box-shadow:
                0 0 0 2px rgba(214,
                    106,
                    43,
                    .08);
        }


        .search-btn {

            border: none;

            background: #d66a2b;

            color: white;

            padding:
                0 20px;

            border-radius: 4px;

            white-space: nowrap;
        }


        .clear-btn {

            display: flex;

            align-items: center;

            justify-content: center;

            padding:
                0 15px;

            border:
                1px solid #ddd;

            color: #555;

            text-decoration: none;

            border-radius: 4px;

            white-space: nowrap;
        }


        /* =====================================================
   STUDENTS CARD
===================================================== */

        .students-card {

            width: 100%;

            max-width: 100%;

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 8px;

            margin-top: 25px;

            overflow: hidden;
        }


        .students-header {

            padding: 20px;

            border-bottom:
                1px solid #eee;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;
        }


        .students-header h3 {

            margin: 0;

            font-family:
                Georgia,
                serif;

            font-size: 20px;
        }


        /* =====================================================
   TABLE
===================================================== */

        .table-responsive {

            width: 100%;

            max-width: 100%;

            overflow-x: hidden;
        }


        table {

            width: 100%;

            max-width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }


        th {

            background: #f8f7f3;

            color: #061f49;

            font-size: 11px;

            padding:
                13px 10px;

            text-align: left;

            white-space: normal;

            word-break: break-word;
        }


        td {

            padding:
                13px 10px;

            border-top:
                1px solid #eee;

            font-size: 12px;

            vertical-align: middle;

            word-break: break-word;

            overflow-wrap: anywhere;
        }


        /* Column widths */

        th:nth-child(1),
        td:nth-child(1) {
            width: 9%;
        }


        th:nth-child(2),
        td:nth-child(2) {
            width: 14%;
        }


        th:nth-child(3),
        td:nth-child(3) {
            width: 10%;
        }


        th:nth-child(4),
        td:nth-child(4) {
            width: 9%;
        }


        th:nth-child(5),
        td:nth-child(5) {
            width: 14%;
        }


        th:nth-child(6),
        td:nth-child(6) {
            width: 11%;
        }


        th:nth-child(7),
        td:nth-child(7) {
            width: 17%;
        }


        th:nth-child(8),
        td:nth-child(8) {
            width: 8%;
        }


        /* =====================================================
   STUDENT PHOTO
===================================================== */

        .student-photo {

            width: 55px;

            height: 55px;

            border-radius: 50%;

            object-fit: cover;

            display: block;

            border:
                2px solid #eee;
        }


        .no-photo {

            width: 55px;

            height: 55px;

            border-radius: 50%;

            background: #eee;

            color: #999;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 23px;
        }


        .student-name {

            font-weight: bold;

            color: #061f49;
        }


        .grade-badge {

            display: inline-block;

            padding:
                5px 9px;

            border-radius: 20px;

            background: #fff0e8;

            color: #d66a2b;

            font-size: 10px;

            font-weight: bold;
        }


        /* =====================================================
   PASSPORT FORM
===================================================== */

        .passport-form {

            margin-top: 7px;

            width: 100%;
        }


        .passport-form input {

            width: 100%;

            max-width: 130px;

            font-size: 9px;

            display: block;
        }


        .passport-form button {

            margin-top: 5px;

            border: none;

            background: #d66a2b;

            color: white;

            padding:
                5px 8px;

            border-radius: 3px;

            font-size: 9px;

            cursor: pointer;
        }


        .passport-form button:hover {

            background: #c55d25;
        }


        /* =====================================================
   ACTIONS
===================================================== */

        .action-btn {

            border: none;

            background: transparent;

            font-size: 17px;

            margin-right: 5px;

            cursor: pointer;
        }


        .edit-btn {

            color: #0d6efd;
        }


        .delete-btn {

            color: #dc3545;
        }


        .action-btn:hover {

            opacity: .7;
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
   MODAL
===================================================== */

        .modal-content {

            border: none;

            border-radius: 8px;
        }


        .modal-header {

            background: #061f49;

            color: white;
        }


        .modal-title {

            font-family:
                Georgia,
                serif;
        }


        .modal-header .btn-close {

            filter: invert(1);
        }


        .edit-input {

            width: 100%;

            height: 38px;

            border:
                1px solid #ddd;

            border-radius: 4px;

            padding:
                0 10px;

            outline: none;
        }


        .edit-input:focus {

            border-color: #d66a2b;
        }


        .save-btn {

            background: #d66a2b;

            color: white;

            border: none;

            padding:
                10px 20px;

            border-radius: 4px;
        }


        /* =====================================================
   MOBILE / TABLET
===================================================== */

        @media (max-width: 1100px) {

            .sidebar {

                width: 70px;

                padding:
                    20px 10px;
            }


            .school-logo span,
            .sidebar-link span {

                display: none;
            }


            .school-logo {

                text-align: center;
            }


            .sidebar-link {

                justify-content: center;
            }


            .main {

                margin-left: 70px;

                width:
                    calc(100% - 70px);
            }


            table {

                table-layout: auto;
            }


            th,
            td {

                padding:
                    8px 6px;

                font-size: 10px;
            }


            .student-photo,
            .no-photo {

                width: 45px;

                height: 45px;
            }


            .passport-form input {

                max-width: 90px;
            }


            .passport-form button {

                font-size: 8px;

                padding:
                    4px 6px;
            }

        }


        @media (max-width: 700px) {

            .topbar {

                min-height: auto;

                padding:
                    15px;

                gap: 15px;

                align-items: flex-start;
            }


            .welcome h2 {

                font-size: 17px;
            }


            .admin-email {

                display: none;
            }


            .content {

                padding: 15px;
            }


            .search-form {

                flex-wrap: wrap;
            }


            .search-form input {

                width: 100%;

                flex-basis: 100%;
            }


            .search-btn,
            .clear-btn {

                height: 40px;
            }


            .students-header {

                padding:
                    15px;
            }


            .students-header h3 {

                font-size: 17px;
            }


            /*
     * Make table more compact
     */

            th,
            td {

                font-size: 9px;

                padding:
                    7px 4px;
            }


            .student-photo,
            .no-photo {

                width: 38px;

                height: 38px;
            }


            .grade-badge {

                font-size: 8px;

                padding:
                    4px 5px;
            }


            .action-btn {

                font-size: 14px;

                margin-right: 1px;
            }

        }


        @media (max-width: 450px) {

            .sidebar {

                width: 55px;

                padding:
                    15px 6px;
            }


            .main {

                margin-left: 55px;

                width:
                    calc(100% - 55px);
            }


            .topbar {

                padding:
                    12px 10px;
            }


            .content {

                padding:
                    10px;
            }


            .admin-photo,
            .admin-placeholder {

                width: 40px;

                height: 40px;
            }


            .welcome h2 {

                font-size: 14px;
            }


            .welcome p {

                display: none;
            }


            .admin-name {

                font-size: 10px;
            }


            .logout-btn {

                margin-left: 3px;

                font-size: 14px;
            }


            th,
            td {

                font-size: 8px;

                padding:
                    5px 3px;
            }


            .student-photo,
            .no-photo {

                width: 32px;

                height: 32px;
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


        <a href="admin_dashboard.php" class="sidebar-link active">

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>



        <a href="upload_result.php" class="sidebar-link">

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


        <!-- =====================================================
     TOPBAR
===================================================== -->

        <header class="topbar">


            <div class="welcome">

                <h2>

                    Welcome,
                    <?php
                    echo e(
                        $admin["full_name"]
                    );
                    ?>

                </h2>


                <p>
                    School administration dashboard
                </p>

            </div>


            <div class="admin-profile">

                <?php if (
                    $adminPicture !== "" &&
                    is_file($adminPictureFile)
                ): ?>

                    <img src="<?php echo e($adminPictureUrl); ?>" class="admin-photo" alt="Admin profile picture"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                    <div class="admin-placeholder" style="display:none;">
                        <i class="bi bi-person"></i>
                    </div>

                <?php else: ?>

                    <div class="admin-placeholder">
                        <i class="bi bi-person"></i>
                    </div>

                <?php endif; ?>

                <div class="admin-details">

                    <div class="admin-name">
                        <?php echo e($admin["full_name"]); ?>
                    </div>

                    <div class="admin-email">
                        <?php echo e($admin["email"]); ?>
                    </div>

                </div>

                <a href="admin_logout.php" class="logout-btn" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

            </div>


        </header>



        <!-- =====================================================
     CONTENT
===================================================== -->

        <main class="content">


            <!-- =================================================
         ALERT
    ================================================= -->

            <?php if (
                $status !== ""
            ): ?>


                <div class="alert alert-<?php echo e($statusType); ?>">

                    <?php
                    echo e($status);
                    ?>

                </div>


            <?php endif; ?>



            <!-- =================================================
         STATISTICS
    ================================================= -->

            <div class="row g-3">


                <div class="col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon">

                            <i class="bi bi-people"></i>

                        </div>


                        <div class="stat-number">

                            <?php
                            echo $totalStudents;
                            ?>

                        </div>


                        <div class="stat-label">

                            Total Students

                        </div>


                    </div>

                </div>



                <div class="col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon">

                            <i class="bi bi-person-check"></i>

                        </div>


                        <div class="stat-number">

                            <?php
                            echo count(
                                $students
                            );
                            ?>

                        </div>


                        <div class="stat-label">

                            Records Shown

                        </div>


                    </div>

                </div>



                <div class="col-md-4">

                    <div class="stat-card">


                        <div class="stat-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>


                        <div class="stat-number">

                            <i class="bi bi-check-circle"></i>

                        </div>


                        <div class="stat-label">

                            Administrator

                        </div>


                    </div>

                </div>


            </div>



            <!-- =================================================
         SEARCH
    ================================================= -->

            <section class="search-card">


                <form method="GET" action="admin_dashboard.php" class="search-form">


                    <input type="search" name="search" value="<?php echo e($search); ?>"
                        placeholder="Search student name, email, parent, phone or class...">


                    <button type="submit" class="search-btn">

                        <i class="bi bi-search"></i>

                        Search

                    </button>


                    <?php if (
                        $search !== ""
                    ): ?>


                        <a href="admin_dashboard.php" class="clear-btn">

                            Clear

                        </a>


                    <?php endif; ?>


                </form>


            </section>



            <!-- =================================================
         STUDENTS
    ================================================= -->

            <section class="students-card" id="students">


                <div class="students-header">


                    <h3>
                        Student Records
                    </h3>


                    <span class="text-muted small">

                        <?php
                        echo count(
                            $students
                        );
                        ?>

                        record(s)

                    </span>


                </div>



                <?php if (
                    empty($students)
                ): ?>


                    <div class="empty">


                        <i class="bi bi-person-x" style="font-size:40px;"></i>


                        <p class="mt-3">

                            No student records found.

                        </p>


                    </div>


                <?php else: ?>


                    <div class="table-responsive">


                        <table>


                            <thead>

                                <tr>

                                    <th>
                                        Passport
                                    </th>

                                    <th>
                                        Student
                                    </th>

                                    <th>
                                        Date of Birth
                                    </th>

                                    <th>
                                        Class
                                    </th>

                                    <th>
                                        Parent / Guardian
                                    </th>

                                    <th>
                                        Phone
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach (
                                    $students
                                    as $student
                                ): ?>


                                    <tr>


                                        <!-- =================================
                                 PASSPORT
                            ================================= -->


                                        <td>

                                            <?php
                                            $passport = sanitize($student["passport"] ?? "");

                                            /*
                                             * Convert the database path into the correct
                                             * browser path.
                                             */

                                            if ($passport !== "") {

                                                // Remove accidental leading slashes
                                                $passport = ltrim($passport, "/\\");

                                                /*
                                                 * If database already contains:
                                                 * uploads/passports/file.jpg
                                                 */
                                                if (
                                                    str_starts_with(
                                                        strtolower($passport),
                                                        "uploads/"
                                                    )
                                                ) {

                                                    $passportUrl = $passport;

                                                    $passportFile =
                                                        __DIR__ . "/" . $passport;

                                                }

                                                /*
                                                 * If database contains only:
                                                 * file.jpg
                                                 */ else {

                                                    $passportUrl =
                                                        "uploads/passports/" .
                                                        basename($passport);

                                                    $passportFile =
                                                        __DIR__ .
                                                        "/uploads/passports/" .
                                                        basename($passport);
                                                }

                                            } else {

                                                $passportUrl = "";
                                                $passportFile = "";
                                            }
                                            ?>


                                            <?php if (
                                                $passportUrl !== "" &&
                                                file_exists($passportFile)
                                            ): ?>

                                                <img src="<?php echo htmlspecialchars(
                                                    $passportUrl,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ); ?>" class="student-photo" alt="Student Passport"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">



                                                <div class="no-photo" style="display:none;">
                                                    <i class="bi bi-person"></i>
                                                </div>


                                                <!-- UPDATE PASSPORT -->

                                                <form method="POST" enctype="multipart/form-data" class="passport-form">

                                                    <input type="hidden" name="student_id"
                                                        value="<?php echo (int) $student["id"]; ?>">

                                                    <input type="file" name="passport" accept="image/jpeg,image/png,image/webp"
                                                        required>

                                                    <button type="submit" name="upload_passport">

                                                        <i class="bi bi-arrow-repeat"></i>

                                                        Update Picture

                                                    </button>

                                                </form>


                                            <?php else: ?>


                                                <div class="no-photo">

                                                    <i class="bi bi-person"></i>

                                                </div>


                                                <!-- ADD PASSPORT -->

                                                <form method="POST" enctype="multipart/form-data" class="passport-form">

                                                    <input type="hidden" name="student_id"
                                                        value="<?php echo (int) $student["id"]; ?>">

                                                    <input type="file" name="passport" accept="image/jpeg,image/png,image/webp"
                                                        required>

                                                    <button type="submit" name="upload_passport">

                                                        <i class="bi bi-upload"></i>

                                                        Add Picture

                                                    </button>

                                                </form>


                                            <?php endif; ?>

                                        </td>



                                        <!-- =================================
                                 STUDENT NAME
                            ================================= -->

                                        <td>


                                            <div class="student-name">

                                                <?php
                                                echo e(
                                                    $student["student_name"]
                                                );
                                                ?>

                                            </div>


                                        </td>



                                        <!-- =================================
                                 DOB
                            ================================= -->

                                        <td>

                                            <?php
                                            echo e(
                                                $student["date_of_birth"]
                                            );
                                            ?>

                                        </td>



                                        <!-- =================================
                                 CLASS
                            ================================= -->

                                        <td>

                                            <span class="grade-badge">

                                                <?php
                                                echo e(
                                                    $student["applying_for"]
                                                );
                                                ?>

                                            </span>

                                        </td>



                                        <!-- =================================
                                 PARENT
                            ================================= -->

                                        <td>

                                            <?php
                                            echo e(
                                                $student["parent_name"]
                                            );
                                            ?>

                                        </td>



                                        <!-- =================================
                                 PHONE
                            ================================= -->

                                        <td>

                                            <?php
                                            echo e(
                                                $student["phone"]
                                            );
                                            ?>

                                        </td>



                                        <!-- =================================
                                 EMAIL
                            ================================= -->

                                        <td>

                                            <?php
                                            echo e(
                                                $student["email"]
                                            );
                                            ?>

                                        </td>



                                        <!-- =================================
                                 ACTIONS
                            ================================= -->

                                        <td>


                                            <button type="button" class="action-btn edit-btn" data-bs-toggle="modal"
                                                data-bs-target="#editStudent<?php echo (int) $student["id"]; ?>"
                                                title="Edit student">

                                                <i class="bi bi-pencil-square"></i>

                                            </button>


                                            <form method="POST" style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this student record?');">


                                                <input type="hidden" name="student_id"
                                                    value="<?php echo (int) $student["id"]; ?>">


                                                <button type="submit" name="delete_student" class="action-btn delete-btn"
                                                    title="Delete student">

                                                    <i class="bi bi-trash"></i>

                                                </button>


                                            </form>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>


                    </div>


                <?php endif; ?>


            </section>


        </main>


    </div>



    <!-- =====================================================
     EDIT MODALS
===================================================== -->

    <?php foreach (
        $students
        as $student
    ): ?>


        <div class="modal fade" id="editStudent<?php echo (int) $student["id"]; ?>" tabindex="-1" aria-hidden="true">


            <div class="modal-dialog modal-lg">


                <div class="modal-content">


                    <div class="modal-header">


                        <h5 class="modal-title">

                            Edit Student

                        </h5>


                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>


                    </div>


                    <form method="POST" action="admin_dashboard.php">


                        <div class="modal-body">


                            <input type="hidden" name="student_id" value="<?php echo (int) $student["id"]; ?>">


                            <div class="row g-3">


                                <div class="col-md-6">


                                    <label class="form-label">

                                        Student's Full Name

                                    </label>


                                    <input type="text" name="student_name" class="edit-input"
                                        value="<?php echo e($student["student_name"]); ?>" required>


                                </div>



                                <div class="col-md-6">


                                    <label class="form-label">

                                        Date of Birth

                                    </label>


                                    <input type="date" name="date_of_birth" class="edit-input"
                                        value="<?php echo e($student["date_of_birth"]); ?>" required>


                                </div>



                                <div class="col-md-6">


                                    <label class="form-label">

                                        Applying For

                                    </label>


                                    <select name="applying_for" class="edit-input" required>


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
                                            $grades
                                            as $grade
                                        ): ?>


                                            <option value="<?php echo e($grade); ?>" <?php
                                               echo (
                                                   $student[
                                                       "applying_for"
                                                   ] === $grade
                                               )
                                                   ? "selected"
                                                   : "";
                                               ?>>

                                                <?php
                                                echo e(
                                                    $grade
                                                );
                                                ?>

                                            </option>


                                        <?php endforeach; ?>


                                    </select>


                                </div>



                                <div class="col-md-6">


                                    <label class="form-label">

                                        Parent / Guardian

                                    </label>


                                    <input type="text" name="parent_name" class="edit-input"
                                        value="<?php echo e($student["parent_name"]); ?>" required>


                                </div>



                                <div class="col-md-6">


                                    <label class="form-label">

                                        Phone

                                    </label>


                                    <input type="tel" name="phone" class="edit-input"
                                        value="<?php echo e($student["phone"]); ?>" required>


                                </div>



                                <div class="col-md-6">


                                    <label class="form-label">

                                        Email

                                    </label>


                                    <input type="email" name="email" class="edit-input"
                                        value="<?php echo e($student["email"]); ?>" required>


                                </div>



                                <div class="col-12">


                                    <label class="form-label">

                                        Notes

                                    </label>


                                    <textarea name="notes" class="form-control"
                                        rows="4"><?php echo e($student["notes"] ?? ""); ?></textarea>


                                </div>


                            </div>


                        </div>


                        <div class="modal-footer">


                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                                Cancel

                            </button>


                            <button type="submit" name="update_student" class="save-btn">

                                <i class="bi bi-check-lg"></i>

                                Save Changes

                            </button>


                        </div>


                    </form>


                </div>


            </div>


        </div>


    <?php endforeach; ?>



    <!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


    <script>

        /*
         * Automatically scroll to the student section
         * after a search.
         */

        <?php if ($search !== ""): ?>

            window.addEventListener(
                "load",
                function () {

                    const students =
                        document.getElementById(
                            "students"
                        );

                    if (students) {

                        students.scrollIntoView({
                            behavior: "smooth",
                            block: "start"
                        });

                    }

                }
            );

        <?php endif; ?>


        /*
         * Hide the alert automatically after
         * a few seconds.
         */

        setTimeout(
            function () {

                const alert =
                    document.querySelector(
                        ".alert"
                    );

                if (alert) {

                    alert.style.transition =
                        "opacity .5s";

                    alert.style.opacity =
                        "0";

                    setTimeout(
                        function () {

                            alert.remove();

                        },
                        500
                    );

                }

            },
            5000
        );

    </script>


</body>

</html>