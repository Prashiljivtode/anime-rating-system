<?php
session_start();
require_once "database.php";

/* ---------------- SEARCH & FILTER ---------------- */

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$genre = isset($_GET["genre"]) ? trim($_GET["genre"]) : "";
$sort = isset($_GET["sort"]) ? $_GET["sort"] : "newest";

/* Get genres */
$genre_result = $conn->query("
    SELECT DISTINCT genre 
    FROM anime 
    WHERE genre IS NOT NULL AND genre != ''
    ORDER BY genre ASC
");

/* Sorting */
$order_by = "anime.created_at DESC";

if ($sort == "rating") {
    $order_by = "average_rating DESC";
} elseif ($sort == "title") {
    $order_by = "anime.title ASC";
} elseif ($sort == "year") {
    $order_by = "anime.release_year DESC";
}

/* Anime query */
$sql = "
    SELECT 
        anime.*,
        COALESCE(AVG(ratings.rating), 0) AS average_rating,
        COUNT(ratings.id) AS total_ratings
    FROM anime
    LEFT JOIN ratings 
        ON anime.id = ratings.anime_id
    WHERE 1=1
";

$params = [];
$types = "";

/* Search */
if ($search != "") {
    $sql .= " AND anime.title LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

/* Genre */
if ($genre != "") {
    $sql .= " AND anime.genre LIKE ?";
    $params[] = "%" . $genre . "%";
    $types .= "s";
}

$sql .= "
    GROUP BY anime.id
    ORDER BY $order_by
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AniRate - Anime Rating & Review</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* SEARCH SECTION */

        .search-section {
            padding: 35px 20px 10px;
            text-align: center;
        }

        .search-box {
            max-width: 900px;
            margin: auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .search-box input,
        .search-box select {
            padding: 13px 15px;
            border-radius: 10px;
            border: 1px solid #444;
            background: #171717;
            color: white;
            outline: none;
            font-size: 15px;
        }

        .search-box input {
            flex: 1;
            min-width: 250px;
        }

        .search-box select {
            min-width: 150px;
        }

        .search-btn {
            padding: 13px 22px;
            border: none;
            border-radius: 10px;
            background: #ff4d8d;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .search-btn:hover {
            background: #ff2f78;
        }

        .clear-btn {
            padding: 13px 18px;
            border-radius: 10px;
            background: #292929;
            color: white;
            text-decoration: none;
        }

        .clear-btn:hover {
            background: #3a3a3a;
        }

        .result-info {
            text-align: center;
            margin: 20px 0;
            color: #bbb;
        }

        /* POSTER */

        .anime-poster {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 12px;
        }

        .no-poster {
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            background: linear-gradient(135deg, #24101b, #111);
            border-radius: 12px;
        }

        /* DETAILS BUTTON */

        .details-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 16px;
            background: #ff4d8d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .details-btn:hover {
            background: #ff2f78;
        }

        /* EMPTY */

        .no-results {
            text-align: center;
            padding: 70px 20px;
            color: #aaa;
        }

        .no-results h2 {
            color: white;
            margin-bottom: 10px;
        }

        @media(max-width: 600px) {

            .search-box {
                flex-direction: column;
            }

            .search-box input,
            .search-box select,
            .search-btn,
            .clear-btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>


<!-- NAVBAR -->

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


<!-- HERO -->

<section class="hero">

    <div class="hero-content">

        <h1>
            Discover Your Next Anime
        </h1>

        <p>
            Rate, review and explore your favourite anime.
        </p>

        <a href="#anime" class="hero-btn">
            Explore Anime
        </a>

    </div>

</section>


<!-- SEARCH -->

<section class="search-section">

    <form method="GET" action="index.php" class="search-box">

        <input
            type="text"
            name="search"
            placeholder="🔍 Search anime..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <select name="genre">

            <option value="">
                All Genres
            </option>

            <?php while ($g = $genre_result->fetch_assoc()): ?>

                <option
                    value="<?php echo htmlspecialchars($g["genre"]); ?>"
                    <?php echo ($genre == $g["genre"]) ? "selected" : ""; ?>
                >
                    <?php echo htmlspecialchars($g["genre"]); ?>
                </option>

            <?php endwhile; ?>

        </select>


        <select name="sort">

            <option value="newest"
                <?php echo $sort == "newest" ? "selected" : ""; ?>>
                Newest
            </option>

            <option value="rating"
                <?php echo $sort == "rating" ? "selected" : ""; ?>>
                Highest Rated
            </option>

            <option value="title"
                <?php echo $sort == "title" ? "selected" : ""; ?>>
                A - Z
            </option>

            <option value="year"
                <?php echo $sort == "year" ? "selected" : ""; ?>>
                Latest Release
            </option>

        </select>


        <button type="submit" class="search-btn">
            Search
        </button>


        <?php if ($search != "" || $genre != "" || $sort != "newest"): ?>

            <a href="index.php" class="clear-btn">
                Clear
            </a>

        <?php endif; ?>

    </form>

</section>


<!-- RESULT INFO -->

<div class="result-info">

    <?php if ($search != ""): ?>

        Showing results for:
        <strong style="color:#ff4d8d;">
            <?php echo htmlspecialchars($search); ?>
        </strong>

    <?php elseif ($genre != ""): ?>

        Showing genre:
        <strong style="color:#ff4d8d;">
            <?php echo htmlspecialchars($genre); ?>
        </strong>

    <?php else: ?>

        Explore all anime

    <?php endif; ?>

</div>


<!-- ANIME -->

<section class="anime-section" id="anime">

    <div class="container">

        <div class="section-title">

            <h2>
                Anime Collection
            </h2>

            <p>
                Find something you will love ❤️
            </p>

        </div>


        <?php if ($result->num_rows > 0): ?>

            <div class="anime-grid">

                <?php while ($anime = $result->fetch_assoc()): ?>

                    <div class="anime-card">


                        <!-- POSTER -->

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


                        <!-- INFO -->

                        <div class="anime-info">

                            <h3>
                                <?php echo htmlspecialchars($anime["title"]); ?>
                            </h3>


                            <p class="genre">

                                <?php echo htmlspecialchars($anime["genre"]); ?>

                            </p>


                            <p>

                                🎬
                                <?php echo htmlspecialchars($anime["episodes"] ?? "N/A"); ?>
                                Episodes

                            </p>


                            <p>

                                🏢
                                <?php echo htmlspecialchars($anime["studio"] ?? "Unknown"); ?>

                            </p>


                            <p>

                                📅
                                <?php echo htmlspecialchars($anime["release_year"] ?? "N/A"); ?>

                            </p>


                            <!-- RATING -->

                            <div class="rating">

                                ⭐

                                <?php

                                echo number_format(
                                    (float)$anime["average_rating"],
                                    1
                                );

                                ?>

                                / 5

                                <span style="color:#888;">

                                    (<?php echo $anime["total_ratings"]; ?>)

                                </span>

                            </div>


                            <!-- DETAILS -->

                            <a
                                href="anime_details.php?id=<?php echo $anime["id"]; ?>"
                                class="details-btn"
                            >
                                View Details →
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="no-results">

                <h2>
                    😢 No Anime Found
                </h2>

                <p>
                    Try another anime name or genre.
                </p>

                <br>

                <a href="index.php" class="details-btn">
                    View All Anime
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- FEATURES -->

<section class="features">

    <div class="container">

        <div class="section-title">

            <h2>
                Why AniRate?
            </h2>

        </div>


        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">
                    ⭐
                </div>

                <h3>
                    Rate Anime
                </h3>

                <p>
                    Give your favourite anime a rating from 1 to 5.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    💬
                </div>

                <h3>
                    Write Reviews
                </h3>

                <p>
                    Share your opinion with other anime fans.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🔎
                </div>

                <h3>
                    Discover
                </h3>

                <p>
                    Search and discover new anime easily.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- FOOTER -->

<footer class="footer">

    <p>
        © <?php echo date("Y"); ?> AniRate | Anime Rating & Review System
    </p>

</footer>


</body>

</html>