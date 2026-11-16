<?php

session_start();

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";


/*
|--------------------------------------------------------------------------
| CHECK DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

if (!isset($conn) || !($conn instanceof mysqli)) {

    die("Database connection was not found. Please check ./includes/db.php.");

}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;

}


$adminId = (int) $_SESSION["admin_id"];


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
| GET ADMIN
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        full_name,
        email,
        password,
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

$result = $stmt->get_result();

$admin = $result->fetch_assoc();

$stmt->close();


if (!$admin) {

    session_destroy();

    header("Location: admin_login.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$status = "";
$statusType = "success";


/*
|--------------------------------------------------------------------------
| PROFILE UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | UPDATE PERSONAL INFORMATION
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["update_profile"])) {


        $fullName = sanitize(
            $_POST["full_name"] ?? ""
        );

        $email = sanitize(
            $_POST["email"] ?? ""
        );


        if ($fullName === "" || $email === "") {

            $status = "Full name and email are required.";

            $statusType = "danger";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $status = "Please enter a valid email address.";

            $statusType = "danger";

        } else {


            /*
            | Check whether another admin already uses email
            */

            $check = $conn->prepare("
                SELECT id
                FROM admins
                WHERE email = ?
                AND id != ?
                LIMIT 1
            ");

            $check->bind_param(
                "si",
                $email,
                $adminId
            );

            $check->execute();

            $checkResult = $check->get_result();

            $emailExists = $checkResult->num_rows > 0;

            $check->close();


            if ($emailExists) {

                $status =
                    "Another administrator is already using this email address.";

                $statusType = "danger";

            } else {


                $update = $conn->prepare("
                    UPDATE admins
                    SET
                        full_name = ?,
                        email = ?
                    WHERE id = ?
                ");

                $update->bind_param(
                    "ssi",
                    $fullName,
                    $email,
                    $adminId
                );


                if ($update->execute()) {

                    /*
                    | Update session information
                    */

                    $_SESSION["admin_name"] = $fullName;

                    $_SESSION["admin_email"] = $email;


                    $status =
                        "Your profile information has been updated successfully.";

                    $statusType = "success";


                } else {

                    $status =
                        "Unable to update your profile.";

                    $statusType = "danger";

                }


                $update->close();

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["change_password"])) {


        $currentPassword =
            $_POST["current_password"] ?? "";

        $newPassword =
            $_POST["new_password"] ?? "";

        $confirmPassword =
            $_POST["confirm_password"] ?? "";


        if (
            $currentPassword === "" ||
            $newPassword === "" ||
            $confirmPassword === ""
        ) {

            $status =
                "Please fill in all password fields.";

            $statusType = "danger";

        } elseif (
            !password_verify(
                $currentPassword,
                $admin["password"]
            )
        ) {

            $status =
                "Your current password is incorrect.";

            $statusType = "danger";

        } elseif (strlen($newPassword) < 6) {

            $status =
                "Your new password must contain at least 6 characters.";

            $statusType = "danger";

        } elseif ($newPassword !== $confirmPassword) {

            $status =
                "The new passwords do not match.";

            $statusType = "danger";

        } else {


            $hashedPassword =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );


            $updatePassword = $conn->prepare("
                UPDATE admin_table
                SET password = ?
                WHERE id = ?
            ");

            $updatePassword->bind_param(
                "si",
                $hashedPassword,
                $adminId
            );


            if ($updatePassword->execute()) {

                $status =
                    "Your password has been changed successfully.";

                $statusType = "success";


                /*
                | Update local admin password
                */

                $admin["password"] =
                    $hashedPassword;

            } else {

                $status =
                    "Unable to change your password.";

                $statusType = "danger";

            }


            $updatePassword->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | PROFILE PICTURE
    |--------------------------------------------------------------------------
    */

    if (isset($_POST["upload_picture"])) {


        if (
            !isset($_FILES["profile_picture"]) ||
            $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_OK
        ) {

            $status =
                "Please select a profile picture.";

            $statusType = "danger";

        } else {


            $file =
                $_FILES["profile_picture"];


            /*
            | Maximum file size: 5 MB
            */

            if ($file["size"] > 5 * 1024 * 1024) {

                $status =
                    "The profile picture must not be larger than 5 MB.";

                $statusType = "danger";

            } else {


                /*
                | Check MIME type
                */

                $allowedTypes = [

                    "image/jpeg" => "jpg",

                    "image/png" => "png",

                    "image/webp" => "webp"

                ];


                $finfo =
                    new finfo(FILEINFO_MIME_TYPE);


                $mime =
                    $finfo->file(
                        $file["tmp_name"]
                    );


                if (!isset($allowedTypes[$mime])) {

                    $status =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $statusType = "danger";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | UPLOAD DIRECTORY
                    |--------------------------------------------------------------------------
                    */

                    $uploadDirectory =
                        __DIR__ . "/uploads/admin_profiles/";


                    if (
                        !is_dir(
                            $uploadDirectory
                        )
                    ) {

                        mkdir(
                            $uploadDirectory,
                            0755,
                            true
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE UNIQUE FILE NAME
                    |--------------------------------------------------------------------------
                    */

                    $extension =
                        $allowedTypes[$mime];


                    $newFileName =
                        "admin_" .
                        $adminId .
                        "_" .
                        time() .
                        "_" .
                        bin2hex(
                            random_bytes(4)
                        ) .
                        "." .
                        $extension;


                    $destination =
                        $uploadDirectory .
                        $newFileName;


                    /*
                    |--------------------------------------------------------------------------
                    | MOVE FILE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {


                        /*
                        |--------------------------------------------------------------------------
                        | OLD PROFILE PICTURE
                        |--------------------------------------------------------------------------
                        */

                        $oldPicture =
                            $admin["profile_picture"] ?? "";


                        /*
                        |--------------------------------------------------------------------------
                        | DATABASE PATH
                        |--------------------------------------------------------------------------
                        */

                        $databasePath =
                            "uploads/admin_profiles/" .
                            $newFileName;


                        /*
                        |--------------------------------------------------------------------------
                        | SAVE NEW PICTURE
                        |--------------------------------------------------------------------------
                        */

                        $updatePicture =
                            $conn->prepare("
                                UPDATE admin_table
                                SET profile_picture = ?
                                WHERE id = ?
                            ");

                        $updatePicture->bind_param(
                            "si",
                            $databasePath,
                            $adminId
                        );


                        if (
                            $updatePicture->execute()
                        ) {


                            /*
                            |--------------------------------------------------------------------------
                            | DELETE OLD PICTURE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !empty($oldPicture)
                            ) {


                                /*
                                | Prevent deleting arbitrary files.
                                */

                                $oldPicture =
                                    str_replace(
                                        "\\",
                                        "/",
                                        $oldPicture
                                    );


                                if (
                                    str_starts_with(
                                        $oldPicture,
                                        "uploads/admin_profiles/"
                                    )
                                ) {


                                    $oldFile =
                                        __DIR__ .
                                        "/" .
                                        $oldPicture;


                                    if (
                                        is_file(
                                            $oldFile
                                        )
                                    ) {

                                        unlink(
                                            $oldFile
                                        );

                                    }

                                }

                            }


                            $admin["profile_picture"] =
                                $databasePath;


                            $status =
                                "Your profile picture has been updated successfully.";

                            $statusType = "success";


                        } else {


                            /*
                            | Delete newly uploaded image
                            | if database update fails.
                            */

                            if (
                                is_file(
                                    $destination
                                )
                            ) {

                                unlink(
                                    $destination
                                );

                            }


                            $status =
                                "The picture was uploaded but could not be saved to your profile.";

                            $statusType = "danger";

                        }


                        $updatePicture->close();

                    } else {

                        $status =
                            "Unable to upload the profile picture.";

                        $statusType = "danger";

                    }

                }

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| REFRESH ADMIN DATA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        full_name,
        email,
        password,
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

$result = $stmt->get_result();

$admin = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| PROFILE IMAGE
|--------------------------------------------------------------------------
*/

$profilePicture =
    $admin["profile_picture"] ?? "";


$profilePictureExists = false;


if (!empty($profilePicture)) {

    $profilePicture =
        str_replace(
            "\\",
            "/",
            $profilePicture
        );


    $profileFile =
        __DIR__ .
        "/" .
        $profilePicture;


    if (
        is_file(
            $profileFile
        )
    ) {

        $profilePictureExists = true;

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
    My Profile - School Admin
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>


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

    top: 0;
    left: 0;

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

    color: rgba(
        255,
        255,
        255,
        .75
    );

    text-decoration: none;

    border-radius: 5px;

    transition: .3s;

}


.sidebar-link:hover,
.sidebar-link.active {

    background:
        rgba(
            255,
            255,
            255,
            .1
        );

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

    justify-content: space-between;

    align-items: center;

    padding:
        15px 30px;

    gap: 20px;

}


.welcome h2 {

    margin: 0;

    font-family:
        Georgia,
        serif;

    font-size: 22px;

}


.welcome p {

    margin: 3px 0 0;

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

    align-items: center;

    justify-content: center;

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

    font-size: 12px;

}


/* =====================================================
   CONTENT
===================================================== */

.content {

    width: 100%;

    padding: 30px;

}


.profile-grid {

    display: grid;

    grid-template-columns:
        320px
        1fr;

    gap: 25px;

}


/* =====================================================
   CARD
===================================================== */

.card-box {

    background: white;

    border:
        1px solid #ddd9d1;

    border-radius: 8px;

    padding: 25px;

}


.card-title {

    margin: 0 0 7px;

    font-family:
        Georgia,
        serif;

    font-size: 21px;

}


.card-description {

    margin: 0 0 20px;

    color: #777;

    font-size: 12px;

    line-height: 1.6;

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    font-size: 13px;

}


/* =====================================================
   PROFILE PICTURE
===================================================== */

.picture-section {

    text-align: center;

}


.profile-image {

    width: 170px;

    height: 170px;

    border-radius: 50%;

    object-fit: cover;

    border:
        4px solid #d66a2b;

    display: block;

    margin:
        10px auto 20px;

}


.profile-placeholder {

    width: 170px;

    height: 170px;

    border-radius: 50%;

    background: #eef1f5;

    border:
        4px solid #d66a2b;

    display: flex;

    align-items: center;

    justify-content: center;

    margin:
        10px auto 20px;

    color: #777;

    font-size: 65px;

}


.admin-role {

    color: #777;

    font-size: 12px;

    margin-bottom: 20px;

}


/* =====================================================
   FORM
===================================================== */

.form-label {

    font-size: 12px;

    font-weight: 600;

    color: #061f49;

}


.form-control {

    font-size: 13px;

    min-height: 40px;

}


.form-control:focus {

    border-color: #d66a2b;

    box-shadow:
        0 0 0 2px
        rgba(
            214,
            106,
            43,
            .08
        );

}


.orange-btn {

    background: #d66a2b;

    color: white;

    border: none;

    padding:
        10px 20px;

    border-radius: 4px;

    font-size: 13px;

    font-weight: 600;

}


.orange-btn:hover {

    background: #c55d25;

    color: white;

}


.dark-btn {

    background: #061f49;

    color: white;

    border: none;

    padding:
        10px 20px;

    border-radius: 4px;

    font-size: 13px;

    font-weight: 600;

}


.dark-btn:hover {

    background: #0b2d64;

    color: white;

}


.small-note {

    color: #777;

    font-size: 11px;

    line-height: 1.5;

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

        justify-content: center;

    }


    .main {

        margin-left: 70px;

    }


    .profile-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .topbar {

        padding:
            15px;

        align-items:
            flex-start;

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


    .admin-name {

        display: none;

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


    <a
        href="admin_dashboard.php"
        class="sidebar-link"
    >

        <i class="bi bi-speedometer2"></i>

        <span>
            Dashboard
        </span>

    </a>


    


    <a
        href="upload_result.php"
        class="sidebar-link"
    >

        <i class="bi bi-file-earmark-pdf"></i>

        <span>
            Student Results
        </span>

    </a>


    <a
        href="admin_profile.php"
        class="sidebar-link active"
    >

        <i class="bi bi-person-circle"></i>

        <span>
            My Profile
        </span>

    </a>


    <a
        href="admin_logout.php"
        class="sidebar-link"
    >

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
                <?php echo e($admin["full_name"]); ?>

            </h2>

            <p>
                Manage your administrator profile
            </p>

        </div>


        <div class="admin-profile">


            <?php if ($profilePictureExists): ?>

                <img
                    src="<?php echo e($profilePicture); ?>"
                    class="admin-photo"
                    alt="Admin profile picture"
                >

            <?php else: ?>

                <div class="admin-placeholder">

                    <i class="bi bi-person"></i>

                </div>

            <?php endif; ?>


            <div>

                <div class="admin-name">

                    <?php
                    echo e(
                        $admin["full_name"]
                    );
                    ?>

                </div>

                <div class="admin-email">

                    <?php
                    echo e(
                        $admin["email"]
                    );
                    ?>

                </div>

            </div>


            <a
                href="logout.php"
                class="logout-btn"
                title="Logout"
            >

                <i class="bi bi-box-arrow-right"></i>

            </a>

        </div>


    </header>



    <!-- =================================================
         CONTENT
    ================================================= -->

    <main class="content">


        <?php if ($status !== ""): ?>

            <div
                class="alert alert-<?php echo e($statusType); ?>"
            >

                <?php echo e($status); ?>

            </div>

        <?php endif; ?>



        <div class="profile-grid">


            <!-- =================================================
                 PROFILE PICTURE CARD
            ================================================= -->

            <section class="card-box picture-section">


                <h2 class="card-title">
                    Profile Picture
                </h2>


                <p class="card-description">

                    This picture will appear on your
                    administrator dashboard.

                </p>


                <?php if ($profilePictureExists): ?>

                    <img
                        src="<?php echo e($profilePicture); ?>"
                        class="profile-image"
                        alt="Admin Profile Picture"
                    >

                <?php else: ?>

                    <div class="profile-placeholder">

                        <i class="bi bi-person"></i>

                    </div>

                <?php endif; ?>


                <div class="admin-role">

                    <i class="bi bi-shield-check"></i>

                    School Administrator

                </div>


                <form
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <input
                        type="hidden"
                        name="upload_picture"
                        value="1"
                    >


                    <input
                        type="file"
                        name="profile_picture"
                        class="form-control mb-3"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >


                    <button
                        type="submit"
                        class="orange-btn w-100"
                    >

                        <i class="bi bi-camera"></i>

                        <?php if ($profilePictureExists): ?>

                            Change Picture

                        <?php else: ?>

                            Upload Picture

                        <?php endif; ?>

                    </button>


                </form>


                <p class="small-note mt-3 mb-0">

                    JPG, PNG or WEBP.
                    Maximum file size: 5 MB.

                </p>


            </section>



            <!-- =================================================
                 RIGHT SIDE
            ================================================= -->

            <div>


                <!-- =================================================
                     PERSONAL INFORMATION
                ================================================= -->

                <section class="card-box mb-4">


                    <h2 class="card-title">

                        Personal Information

                    </h2>


                    <p class="card-description">

                        Update the name and email address
                        associated with your administrator account.

                    </p>


                    <form method="POST">


                        <input
                            type="hidden"
                            name="update_profile"
                            value="1"
                        >


                        <div class="row g-3">


                            <div class="col-md-6">


                                <label
                                    class="form-label"
                                    for="full_name"
                                >

                                    Full Name

                                </label>


                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control"
                                    value="<?php echo e($admin["full_name"]); ?>"
                                    required
                                >


                            </div>


                            <div class="col-md-6">


                                <label
                                    class="form-label"
                                    for="email"
                                >

                                    Email Address

                                </label>


                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php echo e($admin["email"]); ?>"
                                    required
                                >


                            </div>


                        </div>


                        <button
                            type="submit"
                            class="orange-btn mt-4"
                        >

                            <i class="bi bi-check-lg"></i>

                            Save Changes

                        </button>


                    </form>


                </section>



                <!-- =================================================
                     CHANGE PASSWORD
                ================================================= -->

                <section class="card-box">


                    <h2 class="card-title">

                        Change Password

                    </h2>


                    <p class="card-description">

                        Change your administrator password.
                        You will need to enter your current
                        password first.

                    </p>


                    <form method="POST">


                        <input
                            type="hidden"
                            name="change_password"
                            value="1"
                        >


                        <div class="row g-3">


                            <div class="col-12">


                                <label
                                    class="form-label"
                                    for="current_password"
                                >

                                    Current Password

                                </label>


                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >


                            </div>


                            <div class="col-md-6">


                                <label
                                    class="form-label"
                                    for="new_password"
                                >

                                    New Password

                                </label>


                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="form-control"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                            </div>


                            <div class="col-md-6">


                                <label
                                    class="form-label"
                                    for="confirm_password"
                                >

                                    Confirm New Password

                                </label>


                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                            </div>


                        </div>


                        <button
                            type="submit"
                            class="dark-btn mt-4"
                        >

                            <i class="bi bi-lock"></i>

                            Change Password

                        </button>


                    </form>


                </section>


            </div>


        </div>


    </main>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>