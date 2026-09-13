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

if ($anime_id <= 0) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND anime_id = ?");
$stmt->bind_param("ii", $user_id, $anime_id);
$stmt->execute();
$stmt->close();

header("Location: anime_details.php?id=" . $anime_id . "&watchlist=removed");
exit();
?>
