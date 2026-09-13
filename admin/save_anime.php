<?php

require_once "../database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: anime_search.php");
    exit();
}

$title = $_POST["title"] ?? "";
$description = $_POST["description"] ?? "";
$genre = $_POST["genre"] ?? "";
$episodes = $_POST["episodes"] ?? "";
$release_year = $_POST["release_year"] ?? "";
$studio = $_POST["studio"] ?? "";
$image_url = $_POST["image_url"] ?? "";

if (empty($title)) {
    die("Anime title is required.");
}


/* =========================
   CHECK DUPLICATE
========================= */

$check = $conn->prepare(
    "SELECT id FROM anime WHERE title = ? LIMIT 1"
);

$check->bind_param("s", $title);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "<script>
        alert('This anime is already added!');
        window.location.href='anime_search.php';
    </script>";
    exit();
}

$check->close();


/* =========================
   POSTER DOWNLOAD USING CURL
========================= */

$image_name = "";

if (!empty($image_url)) {

    $safe_name = preg_replace(
        "/[^a-zA-Z0-9_-]/",
        "_",
        strtolower($title)
    );

    $image_name = $safe_name . ".jpg";

    $image_path = "../images/" . $image_name;

    $ch = curl_init($image_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

    $image_data = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($image_data !== false && $http_code == 200) {

        file_put_contents($image_path, $image_data);

    } else {

        $image_name = "";
    }
}


/* =========================
   INTEGER VALUES
========================= */

if (!is_numeric($episodes)) {
    $episodes = null;
}

if (!is_numeric($release_year)) {
    $release_year = null;
}


/* =========================
   SAVE TO DATABASE
========================= */

$sql = $conn->prepare(
    "INSERT INTO anime
    (title, description, genre, episodes, release_year, studio, image)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$sql->bind_param(
    "sssiiss",
    $title,
    $description,
    $genre,
    $episodes,
    $release_year,
    $studio,
    $image_name
);


if ($sql->execute()) {

    if ($image_name != "") {

        echo "<script>
            alert('Anime + Poster added successfully! 🎉');
            window.location.href='dashboard.php';
        </script>";

    } else {

        echo "<script>
            alert('Anime added, but poster download failed.');
            window.location.href='dashboard.php';
        </script>";
    }

} else {

    echo "<script>
        alert('Database error while adding anime.');
        window.location.href='anime_search.php';
    </script>";
}


$sql->close();
$conn->close();

?>