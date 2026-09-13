<?php

session_start();

require_once "database.php";


/* =====================================================
   USER LOGIN CHECK
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


/* =====================================================
   GET USER DETAILS
===================================================== */

$stmt = $conn->prepare("

    SELECT
        id,
        name,
        email,
        created_at

    FROM users

    WHERE id = ?

    LIMIT 1

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$user_result = $stmt->get_result();


if ($user_result->num_rows === 0) {

    session_destroy();

    header("Location: login.php");

    exit();

}


$user = $user_result->fetch_assoc();



/* =====================================================
   TOTAL RATINGS
===================================================== */

$stmt = $conn->prepare("

    SELECT COUNT(*) AS total

    FROM ratings

    WHERE user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$ratings = $stmt->get_result()->fetch_assoc()["total"];



/* =====================================================
   TOTAL REVIEWS
===================================================== */

$stmt = $conn->prepare("

    SELECT COUNT(*) AS total

    FROM reviews

    WHERE user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$reviews = $stmt->get_result()->fetch_assoc()["total"];



/* =====================================================
   TOTAL WATCHLIST
===================================================== */

$stmt = $conn->prepare("

    SELECT COUNT(*) AS total

    FROM watchlist

    WHERE user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$watchlist = $stmt->get_result()->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
My Profile - AniRate
</title>

<link rel="stylesheet" href="css/style.css">


<style>

/* =====================================================
   PROFILE PAGE
===================================================== */

.profile-page {

    min-height: 70vh;

    padding: 60px 20px;

}


/* =====================================================
   PROFILE CARD
===================================================== */

.profile-card {

    max-width: 700px;

    margin: auto;

    background: #171717;

    border: 1px solid #292929;

    border-radius: 20px;

    padding: 40px;

    text-align: center;

    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.35);

}


/* =====================================================
   AVATAR
===================================================== */

.profile-avatar {

    width: 105px;

    height: 105px;

    margin: 0 auto 20px;

    border-radius: 50%;

    background: #ff4d8d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 48px;

    box-shadow:
        0 0 25px rgba(255, 77, 141, 0.25);

}


/* =====================================================
   USER NAME
===================================================== */

.profile-card h1 {

    margin: 0 0 8px;

    font-size: 32px;

}


.profile-email {

    color: #aaa;

    margin: 0;

}


.member-date {

    color: #666;

    margin-top: 10px;

}


/* =====================================================
   STATS
===================================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-top: 35px;

}


.stat {

    background: #222;

    border: 1px solid #303030;

    border-radius: 12px;

    padding: 22px 15px;

    transition: 0.2s;

}


.stat:hover {

    transform: translateY(-3px);

    border-color: #ff4d8d;

}


.stat h2 {

    margin: 0 0 7px;

    color: #ff4d8d;

    font-size: 28px;

}


.stat p {

    margin: 0;

    color: #aaa;

}


/* =====================================================
   BUTTONS
===================================================== */

.profile-buttons {

    margin-top: 35px;

    display: flex;

    justify-content: center;

    flex-wrap: wrap;

    gap: 8px;

}


.profile-buttons a {

    display: inline-block;

    padding: 12px 20px;

    border-radius: 9px;

    text-decoration: none;

    font-weight: bold;

    transition: 0.2s;

}


/* WATCHLIST */

.watch-btn {

    background: #ff4d8d;

    color: white;

}


.watch-btn:hover {

    background: #ff2f78;

    transform: translateY(-2px);

}


/* MY REVIEWS */

.review-btn {

    background: #292929;

    color: white;

    border: 1px solid #3a3a3a;

}


.review-btn:hover {

    background: #383838;

    color: #ff4d8d;

}


/* LOGOUT */

.logout-btn {

    background: #292929;

    color: #ddd;

    border: 1px solid #3a3a3a;

}


.logout-btn:hover {

    background: #422027;

    color: #ff6b6b;

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width: 600px) {

    .profile-page {

        padding: 40px 15px;

    }


    .profile-card {

        padding: 30px 20px;

    }


    .profile-card h1 {

        font-size: 26px;

    }


    .stats {

        grid-template-columns: 1fr;

    }


    .profile-buttons {

        flex-direction: column;

    }


    .profile-buttons a {

        width: 100%;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

<div class="nav-container">


<!-- LOGO -->

<a href="index.php"
class="logo">

AniRate

</a>


<!-- NAVIGATION -->

<div class="nav-links">


<a href="index.php">
🏠 Home
</a>


<a href="watchlist.php">
❤️ Watchlist
</a>


<a href="my_reviews.php">
⭐ My Reviews
</a>


<a href="profile.php">
👤 Profile
</a>


<a href="logout.php">
🚪 Logout
</a>


</div>


</div>

</nav>



<!-- =====================================================
     PROFILE
===================================================== -->

<section class="profile-page">


<div class="profile-card">


<!-- =================================================
     AVATAR
================================================= -->

<div class="profile-avatar">

👤

</div>



<!-- =================================================
     USER INFORMATION
================================================= -->

<h1>

<?php

echo htmlspecialchars(
    $user["name"]
);

?>

</h1>


<p class="profile-email">

<?php

echo htmlspecialchars(
    $user["email"]
);

?>

</p>


<p class="member-date">

Member since

<?php

echo date(

    "d M Y",

    strtotime(
        $user["created_at"]
    )

);

?>

</p>



<!-- =================================================
     STATISTICS
================================================= -->

<div class="stats">


<!-- RATINGS -->

<div class="stat">

<h2>

<?php

echo $ratings;

?>

</h2>

<p>
⭐ Ratings
</p>

</div>



<!-- REVIEWS -->

<div class="stat">

<h2>

<?php

echo $reviews;

?>

</h2>

<p>
💬 Reviews
</p>

</div>



<!-- WATCHLIST -->

<div class="stat">

<h2>

<?php

echo $watchlist;

?>

</h2>

<p>
❤️ Watchlist
</p>

</div>


</div>



<!-- =================================================
     PROFILE BUTTONS
================================================= -->

<div class="profile-buttons">


<!-- WATCHLIST -->

<a

href="watchlist.php"

class="watch-btn"

>

❤️ My Watchlist

</a>



<!-- MY REVIEWS -->

<a

href="my_reviews.php"

class="review-btn"

>

⭐ My Reviews

</a>



<!-- LOGOUT -->

<a

href="logout.php"

class="logout-btn"

>

🚪 Logout

</a>


</div>


</div>


</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

<p>

© <?php echo date("Y"); ?>

AniRate

| Anime Rating & Review System

</p>

</footer>


</body>

</html>