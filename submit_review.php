<?php
session_start();
require_once "database.php";
require_once "security.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

verify_csrf();

$user_id = (int) $_SESSION["user_id"];
$anime_id = (int) ($_POST["anime_id"] ?? 0);
$rating = (int) ($_POST["rating"] ?? 0);
$review_text = trim($_POST["review_text"] ?? "");

if ($anime_id <= 0) { die("Invalid anime."); }
if ($rating < 1 || $rating > 5) { die("Rating must be between 1 and 5."); }
if (mb_strlen($review_text) > 2000) { die("Review is too long. Maximum 2000 characters."); }

$stmt = $conn->prepare("SELECT id FROM anime WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $anime_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows === 0) { die("Anime not found."); }

$check = $conn->prepare("SELECT id FROM ratings WHERE user_id = ? AND anime_id = ? LIMIT 1");
$check->bind_param("ii", $user_id, $anime_id);
$check->execute();
$existing = $check->get_result();

if ($existing->num_rows > 0) {
    $row = $existing->fetch_assoc();
    $rating_id = (int) $row["id"];
    $update = $conn->prepare("UPDATE ratings SET rating = ? WHERE id = ?");
    $update->bind_param("ii", $rating, $rating_id);
    $update->execute();
    $update->close();
} else {
    $insert = $conn->prepare("INSERT INTO ratings (user_id, anime_id, rating) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $user_id, $anime_id, $rating);
    $insert->execute();
    $insert->close();
}
$check->close();

if ($review_text !== "") {
    $review = $conn->prepare("INSERT INTO reviews (user_id, anime_id, review_text) VALUES (?, ?, ?)");
    $review->bind_param("iis", $user_id, $anime_id, $review_text);
    $review->execute();
    $review->close();
}

header("Location: anime_details.php?id=" . $anime_id . "&success=1");
exit();
?>
