<?php
session_start();
require_once "../database.php";
require_once "../security.php";

// Admin login check
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

/* =========================
   DASHBOARD STATISTICS
========================= */

// Total Anime
$anime_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM anime");
if ($result) {
    $row = $result->fetch_assoc();
    $anime_count = (int)$row["total"];
}

// Total Users
$user_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
if ($result) {
    $row = $result->fetch_assoc();
    $user_count = (int)$row["total"];
}

// Total Ratings
$rating_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM ratings");
if ($result) {
    $row = $result->fetch_assoc();
    $rating_count = (int)$row["total"];
}

// Total Reviews
$review_count = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM reviews");
if ($result) {
    $row = $result->fetch_assoc();
    $review_count = (int)$row["total"];
}


/* =========================
   ANIME LIST
========================= */

$sql = "
    SELECT 
        anime.id,
        anime.title,
        anime.genre,
        anime.release_year,
        anime.image,
        COALESCE(AVG(ratings.rating), 0) AS average_rating,
        COUNT(DISTINCT ratings.id) AS total_ratings
    FROM anime
    LEFT JOIN ratings
        ON anime.id = ratings.anime_id
    GROUP BY
        anime.id,
        anime.title,
        anime.genre,
        anime.release_year,
        anime.image
    ORDER BY anime.id DESC
";

$anime_result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>AniRate Admin Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f0f14;
            color: white;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #171720;
            padding: 18px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            border-bottom: 1px solid #2b2b36;
        }

        .logo {
            color: #ff4d8d;
            font-size: 25px;
            font-weight: bold;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-name {
            color: #ddd;
        }

        .logout {
            background: #ff3b6b;
            color: white;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 8px;

            font-weight: bold;
        }

        .logout:hover {
            background: #e52d5c;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 94%;
            max-width: 1250px;

            margin: 35px auto;
        }

        .heading {
            margin-bottom: 25px;
        }

        .heading h1 {
            margin: 0 0 8px;
        }

        .heading p {
            color: #aaa;
            margin: 0;
        }


        /* =========================
           STATS
        ========================= */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 30px;
        }

        .stat-card {
            background: #191922;

            border: 1px solid #2b2b36;

            border-radius: 15px;

            padding: 22px;
        }

        .stat-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;

            color: #ff4d8d;
        }

        .stat-title {
            color: #aaa;
            margin-top: 5px;
        }


        /* =========================
           ACTION BUTTONS
        ========================= */

        .actions {
            display: flex;

            flex-wrap: wrap;

            gap: 12px;

            margin-bottom: 30px;
        }

        .action-btn {
            text-decoration: none;

            background: #ff4d8d;

            color: white;

            padding: 12px 18px;

            border-radius: 10px;

            font-weight: bold;
        }

        .action-btn:hover {
            background: #e83d7c;
        }

        .action-secondary {
            background: #292934;
        }

        .action-secondary:hover {
            background: #3a3a47;
        }


        /* =========================
           ANIME SECTION
        ========================= */

        .section-title {
            margin-bottom: 15px;
        }

        .section-title h2 {
            margin: 0;
        }

        .table-container {
            background: #191922;

            border: 1px solid #2b2b36;

            border-radius: 15px;

            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 900px;
        }

        th {
            background: #22222d;

            color: #ff4d8d;

            text-align: left;

            padding: 15px;
        }

        td {
            padding: 14px 15px;

            border-top: 1px solid #2b2b36;

            color: #ddd;
        }

        tr:hover td {
            background: #1e1e28;
        }


        /* =========================
           POSTER
        ========================= */

        .poster {
            width: 55px;
            height: 75px;

            object-fit: cover;

            border-radius: 7px;

            background: #292934;
        }

        .no-poster {
            width: 55px;
            height: 75px;

            border-radius: 7px;

            background: #292934;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;
        }


        /* =========================
           RATING
        ========================= */

        .rating {
            color: #ffd166;

            font-weight: bold;
        }


        /* =========================
           BUTTONS
        ========================= */

        .btn {
            display: inline-block;

            text-decoration: none;

            border: none;

            cursor: pointer;

            padding: 8px 12px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: bold;

            margin-right: 5px;
        }

        .edit-btn {
            background: #4d8cff;
            color: white;
        }

        .edit-btn:hover {
            background: #3575e5;
        }

        .delete-btn {
            background: #ff3b6b;
            color: white;
        }

        .delete-btn:hover {
            background: #e52d5c;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {
            padding: 45px;

            text-align: center;

            color: #aaa;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;

                gap: 12px;

                padding: 15px;
            }

            .container {
                width: 96%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .action-btn {
                text-align: center;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <div class="logo">
        🎌 AniRate Admin
    </div>

    <div class="nav-right">

        <span class="admin-name">
            👤
            <?php
            echo htmlspecialchars(
                $_SESSION["admin_name"] ?? "Admin"
            );
            ?>
        </span>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>


<!-- =========================
     MAIN CONTAINER
========================= -->

<div class="container">


    <!-- HEADING -->

    <div class="heading">

        <h1>
            📊 Admin Dashboard
        </h1>

        <p>
            Manage your Anime Rating & Review System.
        </p>

    </div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                🎌
            </div>

            <div class="stat-number">
                <?php echo $anime_count; ?>
            </div>

            <div class="stat-title">
                Total Anime
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                👥
            </div>

            <div class="stat-number">
                <?php echo $user_count; ?>
            </div>

            <div class="stat-title">
                Registered Users
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⭐
            </div>

            <div class="stat-number">
                <?php echo $rating_count; ?>
            </div>

            <div class="stat-title">
                Ratings
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                💬
            </div>

            <div class="stat-number">
                <?php echo $review_count; ?>
            </div>

            <div class="stat-title">
                Reviews
            </div>

        </div>

    </div>


    <!-- =========================
         ACTION BUTTONS
    ========================= -->

    <div class="actions">

        <a
            href="add_anime.php"
            class="action-btn"
        >
            ➕ Add Anime
        </a>

        <a
            href="anime_search.php"
            class="action-btn"
        >
            🔎 Search Anime
        </a>

        <a
            href="users.php"
            class="action-btn action-secondary"
        >
            👥 Manage Users
        </a>

        <a
            href="../index.php"
            target="_blank"
            class="action-btn action-secondary"
        >
            🌐 View Website
        </a>

    </div>


    <!-- =========================
         ANIME COLLECTION
    ========================= -->

    <div class="section-title">

        <h2>
            🎌 Anime Collection
        </h2>

    </div>


    <div class="table-container">


        <?php if ($anime_result && $anime_result->num_rows > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            Poster
                        </th>

                        <th>
                            Title
                        </th>

                        <th>
                            Genre
                        </th>

                        <th>
                            Year
                        </th>

                        <th>
                            Rating
                        </th>

                        <th>
                            Ratings
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($anime = $anime_result->fetch_assoc()): ?>


                    <tr>


                        <!-- POSTER -->

                        <td>

                            <?php

                            $image_file =
                                !empty($anime["image"])
                                ? "../images/" . $anime["image"]
                                : "";

                            if (
                                !empty($anime["image"]) &&
                                file_exists($image_file)
                            ):

                            ?>

                                <img
                                    src="<?php
                                    echo htmlspecialchars(
                                        $image_file
                                    );
                                    ?>"
                                    class="poster"
                                    alt="Anime Poster"
                                >

                            <?php else: ?>

                                <div class="no-poster">
                                    🎌
                                </div>

                            <?php endif; ?>

                        </td>


                        <!-- TITLE -->

                        <td>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $anime["title"]
                                );
                                ?>
                            </strong>

                        </td>


                        <!-- GENRE -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $anime["genre"] ?: "N/A"
                            );
                            ?>

                        </td>


                        <!-- YEAR -->

                        <td>

                            <?php
                            echo $anime["release_year"]
                                ? (int)$anime["release_year"]
                                : "N/A";
                            ?>

                        </td>


                        <!-- RATING -->

                        <td class="rating">

                            ⭐
                            <?php
                            echo number_format(
                                (float)$anime["average_rating"],
                                1
                            );
                            ?>

                        </td>


                        <!-- TOTAL RATINGS -->

                        <td>

                            <?php
                            echo (int)$anime["total_ratings"];
                            ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>


                            <!-- EDIT -->

                            <a
                                href="edit_anime.php?id=<?php
                                echo (int)$anime["id"];
                                ?>"
                                class="btn edit-btn"
                            >
                                ✏️ Edit
                            </a>


                            <!-- DELETE -->

                            <form
                                method="POST"
                                action="delete_anime.php"
                                style="display:inline;"
                                onsubmit="return confirm(
                                    'Are you sure you want to delete this anime? This will also delete its ratings, reviews and watchlist entries.'
                                );"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo htmlspecialchars(csrf_token()); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?php
                                    echo (int)$anime["id"];
                                    ?>"
                                >

                                <button
                                    type="submit"
                                    name="delete_anime"
                                    class="btn delete-btn"
                                >
                                    🗑️ Delete
                                </button>

                            </form>


                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="empty">

                🎌 No anime found.

                <br><br>

                <a
                    href="add_anime.php"
                    class="action-btn"
                >
                    ➕ Add First Anime
                </a>

            </div>


        <?php endif; ?>


    </div>


</div>

</body>

</html>