<?php

require "./includes/db.php";
require_once __DIR__ . "/handlers/sanitize.php";


session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}


/* Get student ID */

$studentId = (int)($_POST['student_id'] ?? 0);

if ($studentId <= 0) {
    die("Invalid student.");
}


/* Check uploaded file */

if (!isset($_FILES['passport']) || $_FILES['passport']['error'] !== UPLOAD_ERR_OK) {
    die("Please select a picture.");
}


$file = $_FILES['passport'];


/* Allowed file types */

$allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($fileType, $allowedTypes, true)) {
    die("Only JPG, PNG and WEBP images are allowed.");
}


/* Maximum size: 5MB */

if ($file['size'] > 5 * 1024 * 1024) {
    die("Picture must not exceed 5MB.");
}


/* Get existing passport */

$stmt = $mysqli->prepare("
    SELECT passport
    FROM enroll_table
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();

$stmt->close();


/* Upload directory */

$uploadDir = __DIR__ . "/uploads/passports/";


if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}


/* Delete old passport */

$oldPassport = sanitize($student['passport'] ?? '');

if ($oldPassport !== '') {

    $oldFile = $uploadDir . basename($oldPassport);

    if (is_file($oldFile)) {
        unlink($oldFile);
    }
}


/* Generate new filename */

$extension = match ($fileType) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'jpg'
};

$newFileName =
    "student_" .
    $studentId .
    "_" .
    bin2hex(random_bytes(8)) .
    "." .
    $extension;


$newFilePath = $uploadDir . $newFileName;


/* Move new picture */

if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
    die("Failed to upload the new picture.");
}


/* Save filename in database */

$stmt = $mysqli->prepare("
    UPDATE enroll_table
    SET passport = ?
    WHERE id = ?
");

$stmt->bind_param(
    "si",
    $newFileName,
    $studentId
);


if (!$stmt->execute()) {

    /* Delete uploaded file if database update failed */

    if (is_file($newFilePath)) {
        unlink($newFilePath);
    }

    die("Could not update the student's passport.");
}


$stmt->close();

$mysqli->close();


/* Return to dashboard */

header("Location: admin_dashboard.php?passport=updated");

exit;