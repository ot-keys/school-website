<?php

session_start();

if (
    empty($_SESSION["result_verified"]) ||
    empty($_SESSION["result_otp_data"])
) {
    header("Location: check_result.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| SCHOOL INFORMATION
|--------------------------------------------------------------------------
*/

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
| GET STUDENT INFORMATION
|--------------------------------------------------------------------------
*/

$resultData = $_SESSION["result_otp_data"];

$studentName = $resultData["student_name"] ?? "";
$resultFile  = $resultData["result_pdf"] ?? "";


/*
|--------------------------------------------------------------------------
| RESULT DIRECTORY
|--------------------------------------------------------------------------
*/

$resultDirectory = __DIR__ . "/uploads/results/";


/*
|--------------------------------------------------------------------------
| SECURITY
|--------------------------------------------------------------------------
| Only use the filename itself.
| This prevents directory traversal such as ../../file.pdf
|--------------------------------------------------------------------------
*/

$resultFileName = basename($resultFile);


/*
|--------------------------------------------------------------------------
| FULL SERVER PATH
|--------------------------------------------------------------------------
*/

$resultPath = $resultDirectory . $resultFileName;


/*
|--------------------------------------------------------------------------
| RESULT URL
|--------------------------------------------------------------------------
*/

$resultUrl = "uploads/results/" . rawurlencode($resultFileName);

/*
|--------------------------------------------------------------------------
| CHECK RESULT FILE
|--------------------------------------------------------------------------
*/

$resultExists = false;
$errorMessage = "";

if ($resultFileName === "") {

    $errorMessage = "No result file was assigned to this student.";

} elseif (!is_file($resultPath)) {

    $errorMessage = "The student's result file could not be found.";

} else {

    $resultExists = true;
}


/*
|--------------------------------------------------------------------------
| ALLOWED FILE TYPE
|--------------------------------------------------------------------------
*/

if ($resultExists) {

    $extension = strtolower(
        pathinfo($resultFileName, PATHINFO_EXTENSION)
    );

    if ($extension !== "pdf") {

        $resultExists = false;

        $errorMessage =
            "Invalid result file. Only PDF result files are allowed.";

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
        Student Result | <?php echo e($schoolName); ?>
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

            max-width: 1100px;

            margin: auto;

            padding:
                45px 20px 60px;
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
                10px 0 25px;

            color: #31517c;

            font-size: 14px;

            line-height: 1.7;
        }


        /* =====================================================
           STUDENT CARD
        ===================================================== */

        .student-card {

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 7px;

            padding: 20px 25px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            box-shadow:
                0 4px 15px
                rgba(6,31,73,.04);
        }


        .student-label {

            color: #777;

            font-size: 11px;

            margin-bottom: 5px;
        }


        .student-name {

            font-size: 19px;

            font-weight: bold;

            color: #061f49;
        }


        /* =====================================================
           BUTTONS
        ===================================================== */

        .download-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                12px 20px;

            background: #061f49;

            color: white;

            text-decoration: none;

            border-radius: 4px;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;
        }


        .download-btn:hover {

            background: #0b2d62;
        }


        /* =====================================================
           RESULT VIEWER
        ===================================================== */

        .result-card {

            background: white;

            border:
                1px solid #ddd9d1;

            border-radius: 7px;

            overflow: hidden;

            box-shadow:
                0 4px 15px
                rgba(6,31,73,.04);
        }


        .result-header {

            min-height: 55px;

            padding:
                0 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background: #061f49;

            color: white;

            gap: 15px;
        }


        .result-title {

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: 17px;

            font-weight: bold;
        }


        .result-file {

            color: #cbd5e1;

            font-size: 11px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            max-width: 45%;
        }


        .pdf-viewer {

            width: 100%;

            height: 750px;

            border: none;

            display: block;

            background: #eeeeee;
        }


        /* =====================================================
           ERROR
        ===================================================== */

        .error-card {

            background: #f8d7da;

            color: #842029;

            border:
                1px solid #f1aeb5;

            border-radius: 6px;

            padding: 20px;

            font-size: 14px;

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

        @media (max-width: 700px) {

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


            .page {

                padding:
                    35px 15px 45px;
            }


            h1 {

                font-size: 28px;
            }


            .student-card {

                flex-direction: column;

                align-items: flex-start;
            }


            .download-btn {

                width: 100%;
            }


            .result-header {

                padding:
                    12px 15px;

                flex-direction: column;

                align-items: flex-start;
            }


            .result-file {

                max-width: 100%;
            }


            .pdf-viewer {

                height: 600px;
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
        STUDENT RESULT
    </p>


    <h1>
        Result Statement
    </h1>


    <p class="intro">
        Your result has been successfully verified.
        You can view your result below or download a copy.
    </p>



    <?php if (!$resultExists): ?>

        <!-- =================================================
             ERROR
        ================================================= -->

        <div class="error-card">

            <strong>
                Unable to display result
            </strong>

            <br>

            <?php echo e($errorMessage); ?>

        </div>


    <?php else: ?>


        <!-- =================================================
             STUDENT INFORMATION
        ================================================= -->

        <div class="student-card">

            <div>

                <div class="student-label">
                    STUDENT NAME
                </div>

                <div class="student-name">

                    <?php echo e($studentName); ?>

                </div>

            </div>


            <a
                href="<?php echo e($resultUrl); ?>"
                class="download-btn"
                download="<?php echo e($resultFileName); ?>"
            >

                ↓

                Download Result

            </a>

        </div>



        <!-- =================================================
             RESULT VIEWER
        ================================================= -->

        <section class="result-card">


            <div class="result-header">

                <div class="result-title">

                    Your Result

                </div>


                <div class="result-file">

                    <?php echo e($resultFileName); ?>

                </div>

            </div>


            <iframe
                class="pdf-viewer"
                src="<?php echo e($resultUrl); ?>"
                title="Student Result"
            ></iframe>


        </section>


    <?php endif; ?>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    © 2026

    <?php echo e($schoolName); ?>.

    All Rights Reserved.

</footer>


</body>

</html>
