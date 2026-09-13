<?php

session_start();
require_once "../database.php";


/* ================= ADMIN CHECK ================= */

if (!isset($_SESSION["admin_id"])) {

    header("Location: login.php");
    exit();

}


/* ================= GET ANIME ID ================= */

$anime_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;

if ($anime_id <= 0) {

    header("Location: dashboard.php");
    exit();

}


/* ================= UPDATE ANIME ================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $genre = trim($_POST["genre"] ?? "");
    $episodes = trim($_POST["episodes"] ?? "");
    $release_year = trim($_POST["release_year"] ?? "");
    $studio = trim($_POST["studio"] ?? "");


    if ($title === "") {

        $error = "Anime title is required.";

    } else {

        $episodes_value =
            ($episodes !== "" && is_numeric($episodes))
            ? intval($episodes)
            : null;

        $year_value =
            ($release_year !== "" && is_numeric($release_year))
            ? intval($release_year)
            : null;


        $stmt = $conn->prepare("

            UPDATE anime

            SET
                title = ?,
                description = ?,
                genre = ?,
                episodes = ?,
                release_year = ?,
                studio = ?

            WHERE id = ?

        ");


        $stmt->bind_param(

            "sssissi",

            $title,
            $description,
            $genre,
            $episodes_value,
            $year_value,
            $studio,
            $anime_id

        );


        if ($stmt->execute()) {

            header(
                "Location: dashboard.php?updated=1"
            );

            exit();

        } else {

            $error = "Failed to update anime.";

        }

    }

}


/* ================= GET ANIME ================= */

$stmt = $conn->prepare("

    SELECT *

    FROM anime

    WHERE id = ?

    LIMIT 1

");

$stmt->bind_param("i", $anime_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: dashboard.php");
    exit();

}


$anime = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Anime - AniRate Admin
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #101010;

    color: white;

}


/* ================= NAVBAR ================= */

.navbar {

    background: #171717;

    border-bottom: 1px solid #292929;

    padding: 18px 30px;

}


.nav-container {

    max-width: 1100px;

    margin: auto;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.logo {

    color: #ff4d8d;

    font-size: 25px;

    font-weight: bold;

    text-decoration: none;

}


.nav-links {

    display: flex;

    gap: 20px;

}


.nav-links a {

    color: #ddd;

    text-decoration: none;

}


.nav-links a:hover {

    color: #ff4d8d;

}


/* ================= PAGE ================= */

.page {

    max-width: 800px;

    margin: auto;

    padding: 45px 20px;

}


.title {

    margin-bottom: 25px;

}


.title h1 {

    margin-bottom: 8px;

}


.title p {

    color: #888;

}


/* ================= FORM ================= */

.form-card {

    background: #171717;

    border: 1px solid #292929;

    border-radius: 18px;

    padding: 30px;

}


.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    color: #ddd;

    font-weight: bold;

}


.form-group input,
.form-group textarea {

    width: 100%;

    padding: 13px 15px;

    background: #222;

    color: white;

    border: 1px solid #444;

    border-radius: 9px;

    outline: none;

    font-family: inherit;

}


.form-group input:focus,
.form-group textarea:focus {

    border-color: #ff4d8d;

}


.form-group textarea {

    min-height: 150px;

    resize: vertical;

}


/* ================= ROW ================= */

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 15px;

}


/* ================= BUTTONS ================= */

.buttons {

    display: flex;

    gap: 10px;

    margin-top: 25px;

}


.save-btn {

    padding: 12px 20px;

    background: #ff4d8d;

    color: white;

    border: none;

    border-radius: 9px;

    font-weight: bold;

    cursor: pointer;

}


.save-btn:hover {

    background: #ff2f78;

}


.cancel-btn {

    padding: 12px 20px;

    background: #292929;

    color: #ddd;

    border-radius: 9px;

    text-decoration: none;

}


/* ================= POSTER ================= */

.poster-preview {

    width: 120px;

    height: 160px;

    object-fit: cover;

    border-radius: 10px;

    margin-bottom: 20px;

}


/* ================= ERROR ================= */

.error {

    background: #35191f;

    border: 1px solid #632b35;

    color: #ff7777;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

}


/* ================= MOBILE ================= */

@media(max-width:650px) {

    .nav-container {

        flex-direction: column;

        gap: 15px;

    }


    .nav-links {

        flex-wrap: wrap;

        justify-content: center;

    }


    .form-row {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="nav-container">


<a href="dashboard.php"
class="logo">

AniRate Admin

</a>


<div class="nav-links">

<a href="dashboard.php">
📊 Dashboard
</a>

<a href="users.php">
👥 Users
</a>

<a href="logout.php">
Logout
</a>

</div>


</div>

</nav>



<!-- ================= PAGE ================= -->

<div class="page">


<div class="title">

<h1>
✏️ Edit Anime
</h1>

<p>
Update anime information.
</p>

</div>



<?php if (isset($error)): ?>

<div class="error">

❌

<?php echo htmlspecialchars($error); ?>

</div>

<?php endif; ?>



<div class="form-card">


<!-- POSTER -->

<?php

$image_path =
    "../images/" .
    ($anime["image"] ?? "");

if (
    !empty($anime["image"]) &&
    file_exists($image_path)
):

?>

<img

src="<?php
echo htmlspecialchars($image_path);
?>"

alt="<?php
echo htmlspecialchars($anime["title"]);
?>"

class="poster-preview"

>

<?php endif; ?>



<form method="POST">


<!-- TITLE -->

<div class="form-group">

<label>
Anime Title
</label>

<input

type="text"

name="title"

value="<?php
echo htmlspecialchars(
    $anime["title"]
);
?>"

required

>

</div>



<!-- DESCRIPTION -->

<div class="form-group">

<label>
Description
</label>

<textarea

name="description"

><?php

echo htmlspecialchars(
    $anime["description"] ?? ""
);

?></textarea>

</div>



<!-- GENRE -->

<div class="form-group">

<label>
Genre
</label>

<input

type="text"

name="genre"

value="<?php
echo htmlspecialchars(
    $anime["genre"] ?? ""
);
?>"

placeholder="Action, Adventure, Fantasy"

>

</div>



<!-- EPISODES + YEAR -->

<div class="form-row">


<div class="form-group">

<label>
Episodes
</label>

<input

type="number"

name="episodes"

value="<?php
echo htmlspecialchars(
    $anime["episodes"] ?? ""
);
?>"

min="1"

>

</div>



<div class="form-group">

<label>
Release Year
</label>

<input

type="number"

name="release_year"

value="<?php
echo htmlspecialchars(
    $anime["release_year"] ?? ""
);
?>"

min="1900"

max="2100"

>

</div>


</div>



<!-- STUDIO -->

<div class="form-group">

<label>
Studio
</label>

<input

type="text"

name="studio"

value="<?php
echo htmlspecialchars(
    $anime["studio"] ?? ""
);
?>"

placeholder="Ufotable"

>

</div>



<!-- BUTTONS -->

<div class="buttons">


<button

type="submit"

class="save-btn"

>

💾 Save Changes

</button>


<a

href="dashboard.php"

class="cancel-btn"

>

Cancel

</a>


</div>


</form>


</div>


</div>


</body>

</html>