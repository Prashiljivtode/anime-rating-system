<?php
session_start();
require_once "database.php";
require_once "security.php";

/* ---------------- ANIME ID ---------------- */

$anime_id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($anime_id <= 0) {
    header("Location: index.php");
    exit();
}


/* ---------------- ANIME DETAILS ---------------- */

$stmt = $conn->prepare("
    SELECT
        anime.*,
        COALESCE(
            (SELECT AVG(r.rating)
             FROM ratings r
             WHERE r.anime_id = anime.id),
            0
        ) AS average_rating,

        (SELECT COUNT(*)
         FROM ratings r
         WHERE r.anime_id = anime.id) AS total_ratings

    FROM anime
    WHERE anime.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $anime_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$anime = $result->fetch_assoc();


/* ---------------- WATCHLIST CHECK ---------------- */

$is_in_watchlist = false;

if (isset($_SESSION["user_id"])) {

    $user_id = $_SESSION["user_id"];

    $watch_stmt = $conn->prepare("
        SELECT id
        FROM watchlist
        WHERE user_id = ? AND anime_id = ?
        LIMIT 1
    ");

    $watch_stmt->bind_param(
        "ii",
        $user_id,
        $anime_id
    );

    $watch_stmt->execute();

    $watch_result = $watch_stmt->get_result();

    if ($watch_result->num_rows > 0) {
        $is_in_watchlist = true;
    }
}


/* ---------------- REVIEWS ---------------- */

$review_stmt = $conn->prepare("
    SELECT
        reviews.review_text,
        reviews.created_at,
        users.name,
        ratings.rating

    FROM reviews

    INNER JOIN users
        ON reviews.user_id = users.id

    LEFT JOIN ratings
        ON ratings.user_id = reviews.user_id
        AND ratings.anime_id = reviews.anime_id

    WHERE reviews.anime_id = ?

    ORDER BY reviews.created_at DESC
");

$review_stmt->bind_param("i", $anime_id);
$review_stmt->execute();

$reviews = $review_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($anime["title"]); ?> - AniRate
</title>

<link rel="stylesheet" href="css/style.css">


<style>

/* ---------------- PAGE ---------------- */

.details-page {
    padding: 50px 20px;
    min-height: 70vh;
}


/* ---------------- ANIME DETAILS ---------------- */

.anime-details-card {

    max-width: 1000px;

    margin: auto;

    background: #171717;

    border: 1px solid #292929;

    border-radius: 20px;

    padding: 30px;

    display: grid;

    grid-template-columns: 300px 1fr;

    gap: 35px;

    box-shadow: 0 10px 40px rgba(0,0,0,0.35);
}


/* ---------------- POSTER ---------------- */

.poster-container {
    width: 100%;
}

.anime-poster {

    width: 100%;

    height: 420px;

    object-fit: cover;

    border-radius: 15px;

}

.no-poster {

    width: 100%;

    height: 420px;

    border-radius: 15px;

    background: linear-gradient(
        135deg,
        #351424,
        #111
    );

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 90px;

}


/* ---------------- INFO ---------------- */

.anime-info h1 {

    font-size: 38px;

    margin-bottom: 12px;

}

.rating-big {

    font-size: 22px;

    color: #ffc107;

    margin-bottom: 20px;

}

.rating-count {

    color: #888;

    font-size: 14px;

}


/* ---------------- META ---------------- */

.meta-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 12px;

    margin: 25px 0;

}

.meta-box {

    background: #222;

    padding: 15px;

    border-radius: 10px;

}

.meta-box strong {

    display: block;

    color: #ff4d8d;

    margin-bottom: 5px;

}

.meta-box span {

    color: #ddd;

}


/* ---------------- DESCRIPTION ---------------- */

.description {

    color: #bbb;

    line-height: 1.7;

    margin-bottom: 25px;

}


/* ---------------- BUTTONS ---------------- */

.action-buttons {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}

.watch-btn,
.remove-btn,
.login-btn {

    display: inline-block;

    padding: 12px 18px;

    border-radius: 9px;

    text-decoration: none;

    font-weight: bold;

}

.watch-btn {

    background: #ff4d8d;

    color: white;

}

.watch-btn:hover {

    background: #ff2f78;

}

.remove-btn {

    background: #292929;

    color: #ff6b9d;

    border: 1px solid #444;

}

.remove-btn:hover {

    background: #333;

}

.login-btn {

    background: #292929;

    color: white;

}


/* ---------------- REVIEW SECTION ---------------- */

.review-section {

    max-width: 1000px;

    margin: 40px auto;

}

.review-section h2 {

    margin-bottom: 20px;

}


/* ---------------- REVIEW FORM ---------------- */

.review-form {

    background: #171717;

    border: 1px solid #292929;

    padding: 25px;

    border-radius: 15px;

    margin-bottom: 30px;

}

.review-form label {

    display: block;

    margin-bottom: 8px;

    color: #ddd;

    font-weight: bold;

}

.rating-select {

    display: flex;

    gap: 8px;

    margin-bottom: 20px;

}

.rating-select input {

    display: none;

}

.rating-select label {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    background: #292929;

    display: flex;

    justify-content: center;

    align-items: center;

    cursor: pointer;

    color: white;

}

.rating-select input:checked + label {

    background: #ff4d8d;

}

.rating-select label:hover {

    background: #ff4d8d;

}

.review-form textarea {

    width: 100%;

    min-height: 120px;

    resize: vertical;

    background: #222;

    border: 1px solid #444;

    color: white;

    border-radius: 10px;

    padding: 15px;

    font-family: inherit;

    box-sizing: border-box;

    outline: none;

}

.review-form textarea:focus {

    border-color: #ff4d8d;

}

.submit-btn {

    margin-top: 15px;

    padding: 12px 22px;

    background: #ff4d8d;

    border: none;

    color: white;

    border-radius: 9px;

    font-weight: bold;

    cursor: pointer;

}

.submit-btn:hover {

    background: #ff2f78;

}


/* ---------------- LOGIN MESSAGE ---------------- */

.login-message {

    background: #171717;

    border: 1px solid #292929;

    border-radius: 15px;

    padding: 25px;

    text-align: center;

    margin-bottom: 30px;

}

.login-message p {

    color: #aaa;

    margin-bottom: 15px;

}


/* ---------------- REVIEWS ---------------- */

.review-card {

    background: #171717;

    border: 1px solid #292929;

    border-radius: 15px;

    padding: 20px;

    margin-bottom: 15px;

}

.review-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-bottom: 10px;

}

.reviewer {

    color: #ff4d8d;

    font-weight: bold;

}

.review-rating {

    color: #ffc107;

}

.review-text {

    color: #ccc;

    line-height: 1.6;

}

.review-date {

    color: #666;

    font-size: 13px;

    margin-top: 10px;

}


/* ---------------- SUCCESS MESSAGE ---------------- */

.success-message {

    max-width: 1000px;

    margin: 0 auto 20px;

    padding: 14px 18px;

    background: #172b20;

    border: 1px solid #285a3b;

    color: #7ee2a8;

    border-radius: 10px;

}


/* ---------------- RESPONSIVE ---------------- */

@media(max-width: 750px) {

    .anime-details-card {

        grid-template-columns: 1fr;

    }

    .anime-poster,
    .no-poster {

        height: 380px;

    }

    .anime-info h1 {

        font-size: 30px;

    }

    .meta-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="nav-container">

<a href="index.php" class="logo">
AniRate
</a>

<div class="nav-links">

<a href="index.php">
Home
</a>


<?php if (isset($_SESSION["user_id"])): ?>

<a href="watchlist.php">
❤️ Watchlist
</a>

<a href="profile.php">
👤 Profile
</a>

<span style="color:#ff4d8d;">
Hi, <?php echo htmlspecialchars($_SESSION["user_name"]); ?> 👋
</span>

<a href="logout.php">
Logout
</a>


<?php else: ?>

<a href="login.php">
Login
</a>

<a href="register.php">
Register
</a>

<?php endif; ?>

</div>

</div>

</nav>



<!-- ================= MAIN ================= -->

<section class="details-page">


<?php if (isset($_GET["success"])): ?>

<div class="success-message">

✅ Your rating/review has been submitted successfully!

</div>

<?php endif; ?>


<?php if (isset($_GET["watchlist"])): ?>

<div class="success-message">

<?php if ($_GET["watchlist"] == "added"): ?>

❤️ Anime added to your watchlist!

<?php elseif ($_GET["watchlist"] == "removed"): ?>

Anime removed from your watchlist.

<?php endif; ?>

</div>

<?php endif; ?>



<!-- ================= ANIME CARD ================= -->

<div class="anime-details-card">


<!-- POSTER -->

<div class="poster-container">

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

</div>



<!-- INFO -->

<div class="anime-info">


<h1>

<?php echo htmlspecialchars($anime["title"]); ?>

</h1>


<!-- RATING -->

<div class="rating-big">

⭐

<?php

echo number_format(
    (float)$anime["average_rating"],
    1
);

?>

/ 5

<span class="rating-count">

(
<?php echo $anime["total_ratings"]; ?>
ratings
)

</span>

</div>



<!-- META -->

<div class="meta-grid">


<div class="meta-box">

<strong>
🎭 Genre
</strong>

<span>
<?php echo htmlspecialchars($anime["genre"] ?: "N/A"); ?>
</span>

</div>


<div class="meta-box">

<strong>
🏢 Studio
</strong>

<span>
<?php echo htmlspecialchars($anime["studio"] ?: "N/A"); ?>
</span>

</div>


<div class="meta-box">

<strong>
🎬 Episodes
</strong>

<span>
<?php echo htmlspecialchars($anime["episodes"] ?: "N/A"); ?>
</span>

</div>


<div class="meta-box">

<strong>
📅 Release Year
</strong>

<span>
<?php echo htmlspecialchars($anime["release_year"] ?: "N/A"); ?>
</span>

</div>


</div>



<!-- DESCRIPTION -->

<h3>
About this Anime
</h3>

<p class="description">

<?php

$description = trim(
    strip_tags($anime["description"] ?? "")
);

if ($description != "") {

    echo nl2br(
        htmlspecialchars($description)
    );

} else {

    echo "No description available.";

}

?>

</p>



<!-- WATCHLIST -->

<div class="action-buttons">


<?php if (isset($_SESSION["user_id"])): ?>


<?php if ($is_in_watchlist): ?>

<a

href="remove_watchlist.php?anime_id=<?php echo $anime_id; ?>"

class="remove-btn"

>

❤️ Remove from Watchlist

</a>


<?php else: ?>

<a

href="add_watchlist.php?anime_id=<?php echo $anime_id; ?>"

class="watch-btn"

>

❤️ Add to Watchlist

</a>

<?php endif; ?>


<?php else: ?>

<a

href="login.php"

class="login-btn"

>

🔐 Login to Add Watchlist

</a>

<?php endif; ?>


</div>


</div>

</div>



<!-- ================= REVIEW SECTION ================= -->

<div class="review-section">


<h2>
⭐ Rate & Review
</h2>


<?php if (isset($_SESSION["user_id"])): ?>


<!-- REVIEW FORM -->

<div class="review-form">

<form action="submit_review.php" method="POST">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">


<input
type="hidden"
name="anime_id"
value="<?php echo $anime_id; ?>"
>


<label>
Your Rating
</label>


<div class="rating-select">


<input
type="radio"
name="rating"
id="rating1"
value="1"
required
>

<label for="rating1">
1
</label>


<input
type="radio"
name="rating"
id="rating2"
value="2"
>

<label for="rating2">
2
</label>


<input
type="radio"
name="rating"
id="rating3"
value="3"
>

<label for="rating3">
3
</label>


<input
type="radio"
name="rating"
id="rating4"
value="4"
>

<label for="rating4">
4
</label>


<input
type="radio"
name="rating"
id="rating5"
value="5"
>

<label for="rating5">
5
</label>


</div>



<label>
Your Review
</label>


<textarea

name="review_text"

placeholder="What do you think about this anime?"

maxlength="1000"

></textarea>



<button
type="submit"
class="submit-btn"
>

⭐ Submit Rating & Review

</button>


</form>

</div>


<?php else: ?>


<!-- LOGIN MESSAGE -->

<div class="login-message">

<p>
You need to login before rating or reviewing an anime.
</p>

<a
href="login.php"
class="watch-btn"
>
🔐 Login
</a>

</div>


<?php endif; ?>



<!-- ================= USER REVIEWS ================= -->

<h2>
💬 User Reviews
</h2>


<?php if ($reviews->num_rows > 0): ?>


<?php while ($review = $reviews->fetch_assoc()): ?>


<div class="review-card">


<div class="review-header">


<div class="reviewer">

👤

<?php echo htmlspecialchars($review["name"]); ?>

</div>


<?php if (!empty($review["rating"])): ?>

<div class="review-rating">

⭐

<?php echo intval($review["rating"]); ?>/5

</div>

<?php endif; ?>


</div>


<p class="review-text">

<?php

echo nl2br(
    htmlspecialchars($review["review_text"])
);

?>

</p>


<div class="review-date">

<?php

echo date(
    "d M Y, h:i A",
    strtotime($review["created_at"])
);

?>

</div>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="login-message">

<p>
💬 No reviews yet. Be the first one to review this anime!
</p>

</div>


<?php endif; ?>


</div>

</section>



<!-- ================= FOOTER ================= -->

<footer class="footer">

<p>

© <?php echo date("Y"); ?> AniRate

| Anime Rating & Review System

</p>

</footer>


</body>

</html>