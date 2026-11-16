<?php

/* =========================================================
   DATABASE
========================================================= */

require_once "./includes/db.php";
require_once "./handlers/sanitize.php";


/* =========================================================
   DEFAULT STATUS
========================================================= */

$approved = false;
$message = "";
$adminName = "";


/* =========================================================
   GET APPROVAL TOKEN
========================================================= */

$token = sanitize($_GET["token"] ?? "");


/* =========================================================
   CHECK TOKEN
========================================================= */

if ($token === "") {

    $message = "No approval token was provided.";

} else {

    /* ---------------------------------------------------------
       FIND PENDING ADMIN
    --------------------------------------------------------- */

    $stmt = $mysqli->prepare("
        SELECT
            id,
            full_name,
            email,
            status
        FROM admin_table
        WHERE approval_token = ?
        LIMIT 1
    ");

    if (!$stmt) {

        $message = "A database error occurred.";

    } else {

        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        /* -----------------------------------------------------
           CHECK IF ADMIN EXISTS
        ----------------------------------------------------- */

        if ($result->num_rows === 0) {

            $message =
                "This approval link is invalid, has already been used, or has expired.";

        } else {

            $admin = $result->fetch_assoc();

            $adminId = $admin["id"];
            $adminName = $admin["full_name"];
            $adminEmail = $admin["email"];
            $adminStatus = $admin["status"];


            /* -------------------------------------------------
               CHECK STATUS
            ------------------------------------------------- */

            if ($adminStatus === "approved") {

                $message =
                    "This administrator account has already been approved.";

            } else {

                /* -------------------------------------------------
                   APPROVE ADMIN
                ------------------------------------------------- */

                $update = $mysqli->prepare("
                    UPDATE admin_table
                    SET
                        status = 'approved',
                        approval_token = NULL
                    WHERE id = ?
                      AND approval_token = ?
                      AND status = 'pending'
                ");


                if (!$update) {

                    $message =
                        "Could not prepare the approval request.";

                } else {

                    $update->bind_param(
                        "is",
                        $adminId,
                        $token
                    );


                    if ($update->execute()) {

                        if ($update->affected_rows === 1) {

                            $approved = true;

                        } else {

                            $message =
                                "This account could not be approved. It may have already been approved.";
                        }

                    } else {

                        $message =
                            "Something went wrong while approving the account.";
                    }


                    $update->close();
                }
            }
        }


        $stmt->close();
    }
}


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();

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
        <?php echo $approved ? "Admin Approved" : "Approval Failed"; ?>
    </title>


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

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;
        }


        .message {

            width: 100%;

            max-width: 500px;

            background: #ffffff;

            border: 1px solid #ddd9d1;

            border-radius: 6px;

            padding: 40px 30px;

            text-align: center;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.05);
        }


        .icon {

            width: 65px;

            height: 65px;

            margin: 0 auto 20px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

            font-weight: bold;
        }


        .success-icon {

            background: #d1e7dd;

            color: #0f5132;
        }


        .error-icon {

            background: #f8d7da;

            color: #842029;
        }


        h1 {

            margin: 0 0 15px;

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 30px;

            color: #061f49;
        }


        p {

            color: #555;

            line-height: 1.6;

            font-size: 14px;

            margin: 8px 0;
        }


        .admin-name {

            color: #061f49;

            font-weight: bold;
        }


        .button {

            display: inline-block;

            margin-top: 20px;

            padding: 13px 28px;

            background: #d66a2b;

            color: white;

            text-decoration: none;

            border-radius: 4px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;
        }


        .button:hover {

            background: #c55d25;

        }

    </style>

</head>


<body>


<div class="message">


    <?php if ($approved): ?>


        <!-- =================================================
             APPROVED
        ================================================== -->


        <div class="icon success-icon">
            ✓
        </div>


        <h1>
            Admin Approved
        </h1>


        <p>
            The administrator account for
        </p>


        <p class="admin-name">
            <?php echo htmlspecialchars($adminName); ?>
        </p>


        <p>
            has been successfully approved.
        </p>


        <p>
            The applicant can now use their email
            and password to sign in to the admin dashboard.
        </p>


        <a
            href="admin_login.php"
            class="button"
        >
            Go to Admin Login
        </a>


    <?php else: ?>


        <!-- =================================================
             APPROVAL FAILED
        ================================================== -->


        <div class="icon error-icon">
            !
        </div>


        <h1>
            Approval Failed
        </h1>


        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>


        <a
            href="admin_login.php"
            class="button"
        >
            Go to Admin Login
        </a>


    <?php endif; ?>


</div>


</body>

</html>