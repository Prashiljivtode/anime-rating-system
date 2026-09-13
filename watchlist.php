<?php
session_start();
require_once "database.php";
require_once "security.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        anime.*,
        COALESCE(AVG(ratings.rating), 0) AS average_rating,
        COUNT(DISTINCT ratings.id) AS total_ratings
    FROM watchlist
    INNER JOIN anime
        ON watchlist.anime_id = anime.id
    LEFT JOIN ratings
        ON anime.id = ratings.anime_id
    WHERE watchlist.user_id = ?
    GROUP BY anime.id
    ORDER BY watchlist.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Watchlist - AniRate</title>

<link rel="stylesheet" href="css/style.css">

<style>

.watchlist-page {
    padding: 50px 20px;
    min-height: 70vh;
}

.watchlist-title {
    text-align: center;
    margin-bottom: 40px;
}

.watchlist-title h1 {
    font-size: 38px;
}

.watchlist-title p {
    color: #aaa;
}

.anime-poster {
    width: 100%;
    height: 280px;
    object-fit: cover;
    border-radius: 12px;
}

.no-poster {
    height: 280px;
    background: #181818;
    border-radius: 12px;

    display: flex;
    justify-content: center;
    align-items: center;

    font-size: 70px;
}

.details-btn {
    display: inline-block;
    margin-top: 12px;
    padding: 10px 16px;

    background: #ff4d8d;
    color: white;

    text-decoration: none;
    border-radius: 8px;
}

.remove-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 9px 14px;

    background: #292929;
    color: #ff6b9d;

    text-decoration: none;
    border-radius: 8px;
}

.empty-watchlist {
    text-align: center;
    padding: 80px 20px;
    color: #aaa;
}

.empty-watchlist h2 {
    color: white;
    margin-bottom: 10px;
}

</style>

</head>

<body>

<nav class="navbar">

<div class="nav-container">

<a href="index.php" class="logo">
AniRate
</a>

<div class="nav-links">

<a href="index.php">
Home
</a>

<a href="watchlist.php">
❤️ Watchlist
</a>

<a href="profile.php">
👤 Profile
</a>

<a href="logout.php">
Logout
</a>

</div>

</div>

</nav>


<section class="watchlist-page">

<div class="container">

<div class="watchlist-title">

<h1>❤️ My Watchlist</h1>

<p>Your saved anime collection</p>

</div>


<?php if ($result->num_rows > 0): ?>

<div class="anime-grid">

<?php while ($anime = $result->fetch_assoc()): ?>

<div class="anime-card">

<?php

$image_path = "images/" . $anime["image"];

if (
    !empty($anime["image"]) &&
    file_exists($image_path)
):

?>

<img
src="<?php echo htmlspecialchars($image_path); ?>"
alt="<?php echo htmlspecialchars($anime["title"]); ?>"
class="anime-poster"
>

<?php else: ?>

<div class="no-poster">
🎬
</div>

<?php endif; ?>


<div class="anime-info">

<h3>
<?php echo htmlspecialchars($anime["title"]); ?>
</h3>

<p class="genre">
<?php echo htmlspecialchars($anime["genre"]); ?>
</p>

<p>
⭐ <?php echo number_format(
    (float)$anime["average_rating"],
    1
); ?>/5
</p>

<a
href="anime_details.php?id=<?php echo $anime["id"]; ?>"
class="details-btn"
>
View Details →
</a>

<br>

<form method="POST" action="remove_watchlist.php" style="display:inline;" onsubmit="return confirm('Remove this anime from your watchlist?');">
<input type="hidden" name="anime_id" value="<?php echo (int)$anime["id"]; ?>">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
<button type="submit" class="remove-btn" style="border:none;cursor:pointer;">
✕ Remove
</button>
</form>

</div>

</div>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-watchlist">

<h2>
📺 Your Watchlist is Empty
</h2>

<p>
Find an anime you like and add it to your watchlist.
</p>

<br>

<a href="index.php" class="details-btn">
Explore Anime
</a>

</div>

<?php endif; ?>

</div>

</section>


<footer class="footer">

<p>
© <?php echo date("Y"); ?> AniRate | Anime Rating & Review System
</p>

</footer>

</body>

</html>