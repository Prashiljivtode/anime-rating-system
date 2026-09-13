<?php

session_start();
require_once "database.php";


/* ================= USER CHECK ================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


/* ================= GET USER REVIEWS ================= */

$stmt = $conn->prepare("

    SELECT

        reviews.review_text,
        reviews.created_at,

        anime.id AS anime_id,
        anime.title AS anime_title,
        anime.image AS anime_image,

        ratings.rating

    FROM reviews

    INNER JOIN anime
        ON reviews.anime_id = anime.id

    LEFT JOIN ratings
        ON ratings.user_id = reviews.user_id
        AND ratings.anime_id = reviews.anime_id

    WHERE reviews.user_id = ?

    ORDER BY reviews.created_at DESC

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
My Reviews - AniRate
</title>

<link rel="stylesheet" href="css/style.css">


<style>

/* ================= PAGE ================= */

.reviews-page {

    min-height: 70vh;

    padding: 50px 20px;

}


.reviews-container {

    max-width: 900px;

    margin: auto;

}


.page-title {

    text-align: center;

    margin-bottom: 35px;

}


.page-title h1 {

    margin-bottom: 8px;

}


.page-title p {

    color: #888;

}


/* ================= REVIEW CARD ================= */

.review-card {

    background: #171717;

    border: 1px solid #292929;

    border-radius: 15px;

    padding: 22px;

    margin-bottom: 18px;

    display: flex;

    gap: 20px;

}


.anime-poster {

    width: 90px;

    height: 120px;

    object-fit: cover;

    border-radius: 9px;

    flex-shrink: 0;

}


.no-poster {

    width: 90px;

    height: 120px;

    background: #292929;

    border-radius: 9px;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 35px;

    flex-shrink: 0;

}


.review-content {

    flex: 1;

}


.review-content h2 {

    margin: 0 0 8px;

    font-size: 22px;

}


.review-content h2 a {

    color: white;

    text-decoration: none;

}


.review-content h2 a:hover {

    color: #ff4d8d;

}


.rating {

    color: #ffc107;

    margin-bottom: 12px;

}


.review-text {

    color: #ccc;

    line-height: 1.6;

}


.review-date {

    color: #666;

    font-size: 13px;

    margin-top: 12px;

}


/* ================= EMPTY ================= */

.empty {

    text-align: center;

    background: #171717;

    border: 1px solid #292929;

    border-radius: 15px;

    padding: 60px 20px;

    color: #888;

}


.empty h2 {

    color: white;

    margin-bottom: 10px;

}


.btn {

    display: inline-block;

    margin-top: 18px;

    padding: 11px 18px;

    background: #ff4d8d;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

}


.btn:hover {

    background: #ff2f78;

}


/* ================= MOBILE ================= */

@media(max-width:600px) {

    .review-card {

        flex-direction: column;

    }


    .anime-poster,
    .no-poster {

        width: 120px;

        height: 160px;

    }

}

</style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="nav-container">


<a href="index.php"
class="logo">

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



<!-- ================= PAGE ================= -->

<section class="reviews-page">


<div class="reviews-container">


<div class="page-title">

<h1>
⭐ My Ratings & Reviews
</h1>

<p>
All the anime you have rated and reviewed.
</p>

</div>



<?php if ($result->num_rows > 0): ?>


<?php while ($review = $result->fetch_assoc()): ?>


<div class="review-card">


<!-- ================= POSTER ================= -->

<?php

$image_path =
    "images/" .
    ($review["anime_image"] ?? "");

if (

    !empty($review["anime_image"]) &&

    file_exists($image_path)

):

?>

<img

src="<?php

echo htmlspecialchars(
    $image_path
);

?>"

alt="<?php

echo htmlspecialchars(
    $review["anime_title"]
);

?>"

class="anime-poster"

>


<?php else: ?>


<div class="no-poster">

🎬

</div>


<?php endif; ?>



<!-- ================= CONTENT ================= -->

<div class="review-content">


<h2>

<a href="anime_details.php?id=<?php

echo intval(
    $review["anime_id"]
);

?>">

<?php

echo htmlspecialchars(
    $review["anime_title"]
);

?>

</a>

</h2>



<!-- RATING -->

<?php if (
    !empty($review["rating"])
): ?>

<div class="rating">

⭐

<?php

echo intval(
    $review["rating"]
);

?> / 5

</div>

<?php endif; ?>



<!-- REVIEW -->

<?php if (
    !empty(
        trim(
            $review["review_text"]
        )
    )
): ?>

<p class="review-text">

<?php

echo nl2br(
    htmlspecialchars(
        $review["review_text"]
    )
);

?>

</p>

<?php else: ?>

<p class="review-text">

You rated this anime but did not write a review.

</p>

<?php endif; ?>



<!-- DATE -->

<div class="review-date">

<?php

echo date(

    "d M Y, h:i A",

    strtotime(
        $review["created_at"]
    )

);

?>

</div>


</div>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="empty">

<h2>
💬 No Reviews Yet
</h2>

<p>
Rate and review an anime to see it here.
</p>


<a
href="index.php"
class="btn"
>

🎬 Explore Anime

</a>


</div>


<?php endif; ?>


</div>

</section>



<!-- ================= FOOTER ================= -->

<footer class="footer">

<p>

© <?php echo date("Y"); ?>

AniRate

| Anime Rating & Review System

</p>

</footer>


</body>

</html>