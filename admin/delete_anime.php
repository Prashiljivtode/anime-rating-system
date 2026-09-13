<?php
session_start();
require_once "../database.php";
require_once "../security.php";

// Admin login check
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

// Only POST request allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

verify_csrf();

// Check anime ID
if (
    !isset($_POST["id"]) ||
    !is_numeric($_POST["id"])
) {
    header("Location: dashboard.php");
    exit();
}

$anime_id = (int) $_POST["id"];

// Get anime information
$stmt = $conn->prepare(
    "SELECT title, image FROM anime WHERE id = ?"
);

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

$anime = $result->fetch_assoc();

$stmt->close();


// ===============================
// DELETE RATINGS
// ===============================

$stmt = $conn->prepare(
    "DELETE FROM ratings WHERE anime_id = ?"
);

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$stmt->close();


// ===============================
// DELETE REVIEWS
// ===============================

$stmt = $conn->prepare(
    "DELETE FROM reviews WHERE anime_id = ?"
);

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$stmt->close();


// ===============================
// DELETE WATCHLIST
// ===============================

$stmt = $conn->prepare(
    "DELETE FROM watchlist WHERE anime_id = ?"
);

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$stmt->close();


// ===============================
// DELETE ANIME
// ===============================

$stmt = $conn->prepare(
    "DELETE FROM anime WHERE id = ?"
);

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$stmt->close();


// ===============================
// DELETE POSTER IMAGE
// ===============================

if (!empty($anime["image"])) {

    $image_name = basename($anime["image"]);

    $image_path = "../images/" . $image_name;

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}


// ===============================
// REDIRECT
// ===============================

header("Location: dashboard.php?deleted=1");
exit();

?>