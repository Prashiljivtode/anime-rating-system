<?php

session_start();

require_once "../database.php";

/* Check Admin Login */

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";


/* ================= ADD ANIME ================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $genre = trim($_POST["genre"]);
    $episodes = (int) $_POST["episodes"];
    $release_year = (int) $_POST["release_year"];
    $studio = trim($_POST["studio"]);
    $description = trim($_POST["description"]);

    $image_name = "";


    /* ================= IMAGE UPLOAD ================= */

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

        $extension = strtolower(
            pathinfo(
                $_FILES["image"]["name"],
                PATHINFO_EXTENSION
            )
        );

        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (in_array($extension, $allowed)) {

            $image_name =
                time() . "_" .
                uniqid() . "." .
                $extension;

            $upload_path =
                "../images/" . $image_name;

            move_uploaded_file(
                $_FILES["image"]["tmp_name"],
                $upload_path
            );
        }
    }


    /* ================= SAVE TO DATABASE ================= */

    $sql = "
        INSERT INTO anime
        (
            title,
            description,
            genre,
            episodes,
            release_year,
            studio,
            image
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssisss",
        $title,
        $description,
        $genre,
        $episodes,
        $release_year,
        $studio,
        $image_name
    );


    if ($stmt->execute()) {

        $message = "Anime added successfully! 🎌";
        $message_type = "success";

    } else {

        $message = "Something went wrong!";
        $message_type = "error";
    }


    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Anime - AniRate</title>


    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Poppins, sans-serif;

            background: #09090f;

            color: white;

        }


        /* ================= SIDEBAR ================= */

        .sidebar {

            position: fixed;

            width: 230px;

            height: 100vh;

            left: 0;
            top: 0;

            background: #11111a;

            border-right: 1px solid #252530;

            padding: 25px 15px;

        }


        .logo {

            font-size: 25px;

            font-weight: 800;

            padding: 0 15px;

            margin-bottom: 40px;

        }


        .logo span {

            color: #ff4d8d;

        }


        .menu-title {

            color: #666;

            font-size: 10px;

            letter-spacing: 2px;

            padding: 0 15px;

            margin-bottom: 12px;

        }


        .menu a {

            display: block;

            text-decoration: none;

            color: #999;

            padding: 12px 15px;

            margin-bottom: 5px;

            border-radius: 9px;

            font-size: 13px;

        }


        .menu a:hover,
        .menu a.active {

            color: #ff4d8d;

            background: #211321;

        }


        .logout {

            position: absolute;

            bottom: 25px;

            left: 15px;
            right: 15px;

        }


        .logout a {

            display: block;

            text-align: center;

            text-decoration: none;

            padding: 10px;

            border-radius: 8px;

            color: #ff6b9d;

            background: #21131c;

            font-size: 12px;

        }


        /* ================= MAIN ================= */

        .main {

            margin-left: 230px;

            padding: 40px;

            max-width: 1100px;

        }


        .header {

            margin-bottom: 25px;

        }


        .header h1 {

            font-size: 30px;

            margin-bottom: 5px;

        }


        .header p {

            color: #777;

            font-size: 13px;

        }


        /* ================= FORM CARD ================= */

        .form-card {

            background: #11111a;

            border: 1px solid #252530;

            border-radius: 16px;

            padding: 30px;

        }


        .form-card h2 {

            font-size: 19px;

            margin-bottom: 25px;

        }


        .form-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .full {

            grid-column: 1 / -1;

        }


        label {

            color: #bbb;

            font-size: 12px;

            margin-bottom: 8px;

        }


        input,
        select,
        textarea {

            width: 100%;

            background: #0b0b12;

            color: white;

            border: 1px solid #292936;

            border-radius: 8px;

            padding: 12px 13px;

            font-family: Poppins;

            outline: none;

        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #ff4d8d;

        }


        select {

            cursor: pointer;

        }


        textarea {

            height: 110px;

            resize: vertical;

        }


        input[type="file"] {

            padding: 10px;

        }


        /* ================= BUTTON ================= */

        .buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

        }


        .add-button {

            border: none;

            background: #ff4d8d;

            color: white;

            padding: 12px 25px;

            border-radius: 8px;

            font-family: Poppins;

            font-weight: 600;

            cursor: pointer;

        }


        .add-button:hover {

            opacity: 0.9;

        }


        .cancel-button {

            text-decoration: none;

            color: #aaa;

            border: 1px solid #292936;

            padding: 11px 22px;

            border-radius: 8px;

            font-size: 13px;

        }


        /* ================= MESSAGE ================= */

        .message {

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;

        }


        .success {

            background: #142b20;

            color: #72e2a1;

            border: 1px solid #245a3d;

        }


        .error {

            background: #351923;

            color: #ff7da9;

            border: 1px solid #61253a;

        }


        /* ================= MOBILE ================= */

        @media (max-width: 700px) {

            .sidebar {

                display: none;

            }

            .main {

                margin-left: 0;

                padding: 20px;

            }

            .form-grid {

                grid-template-columns: 1fr;

            }

            .full {

                grid-column: auto;

            }

        }

    </style>

</head>


<body>


<!-- ================= SIDEBAR ================= -->

<aside class="sidebar">


    <div class="logo">

        🎌 Ani<span>Rate</span>

    </div>


    <div class="menu-title">

        ADMIN PANEL

    </div>


    <div class="menu">

        <a href="dashboard.php">
            📊 Dashboard
        </a>

        <a href="add_anime.php" class="active">
            🎌 Add Anime
        </a>

        <a href="#">
            📚 Manage Anime
        </a>

        <a href="#">
            👥 Users
        </a>

        <a href="#">
            💬 Reviews
        </a>

        <a href="../index.php">
            🌐 View Website
        </a>

    </div>


    <div class="logout">

        <a href="logout.php">
            🚪 Logout
        </a>

    </div>


</aside>



<!-- ================= MAIN ================= -->

<main class="main">


    <div class="header">

        <h1>
            Add Anime 🎌
        </h1>

        <p>
            Add a new anime to your collection.
        </p>

    </div>



    <div class="form-card">


        <?php if ($message != ""): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <h2>
            Anime Details
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="form-grid">


                <!-- ANIME NAME -->

                <div class="form-group full">

                    <label>
                        Anime Name *
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="e.g. Attack on Titan"
                        required
                    >

                </div>



                <!-- GENRE -->

                <div class="form-group">

                    <label>
                        Genre *
                    </label>

                    <select
                        name="genre"
                        required
                    >

                        <option value="">
                            Select Genre
                        </option>

                        <option value="Action">
                            Action
                        </option>

                        <option value="Adventure">
                            Adventure
                        </option>

                        <option value="Comedy">
                            Comedy
                        </option>

                        <option value="Romance">
                            Romance
                        </option>

                        <option value="Fantasy">
                            Fantasy
                        </option>

                        <option value="Horror">
                            Horror
                        </option>

                        <option value="Sports">
                            Sports
                        </option>

                        <option value="Mystery">
                            Mystery
                        </option>

                        <option value="Sci-Fi">
                            Sci-Fi
                        </option>

                        <option value="Isekai">
                            Isekai
                        </option>

                        <option value="Drama">
                            Drama
                        </option>

                        <option value="Supernatural">
                            Supernatural
                        </option>

                    </select>

                </div>



                <!-- STUDIO -->

                <div class="form-group">

                    <label>
                        Studio *
                    </label>

                    <input
                        type="text"
                        name="studio"
                        placeholder="e.g. MAPPA"
                        required
                    >

                </div>



                <!-- EPISODES -->

                <div class="form-group">

                    <label>
                        Episodes *
                    </label>

                    <input
                        type="number"
                        name="episodes"
                        min="1"
                        placeholder="e.g. 24"
                        required
                    >

                </div>



                <!-- YEAR -->

                <div class="form-group">

                    <label>
                        Release Year *
                    </label>

                    <input
                        type="number"
                        name="release_year"
                        min="1900"
                        max="2100"
                        placeholder="e.g. 2024"
                        required
                    >

                </div>



                <!-- POSTER -->

                <div class="form-group full">

                    <label>
                        Anime Poster
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>



                <!-- DESCRIPTION -->

                <div class="form-group full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Write a short description..."
                    ></textarea>

                </div>


            </div>



            <div class="buttons">

                <button
                    type="submit"
                    class="add-button"
                >
                    ➕ Add Anime
                </button>


                <a
                    href="dashboard.php"
                    class="cancel-button"
                >
                    Cancel
                </a>

            </div>


        </form>


    </div>


</main>


</body>

</html>