<?php
session_start();
require_once "../database.php";
require_once "../security.php";

// Admin login check
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Delete User - POST Only
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {

    verify_csrf();

    if (!isset($_POST["user_id"]) || !is_numeric($_POST["user_id"])) {
        header("Location: users.php");
        exit();
    }

    $user_id = (int) $_POST["user_id"];

    // Make sure admin cannot be deleted
    $stmt = $conn->prepare(
        "DELETE FROM users WHERE id = ? AND role = 'user'"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: users.php?deleted=1");
    exit();
}


/*
|--------------------------------------------------------------------------
| Fetch Users
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.created_at,

        (
            SELECT COUNT(*)
            FROM ratings r
            WHERE r.user_id = u.id
        ) AS rating_count,

        (
            SELECT COUNT(*)
            FROM reviews rv
            WHERE rv.user_id = u.id
        ) AS review_count,

        (
            SELECT COUNT(*)
            FROM watchlist w
            WHERE w.user_id = u.id
        ) AS watchlist_count

    FROM users u
    WHERE u.role = 'user'
    ORDER BY u.created_at DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users | AniRate Admin</title>

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
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 8px;
            background: #292934;
        }

        .nav-links a:hover {
            background: #ff4d8d;
        }

        .logout {
            background: #ff3b6b !important;
        }

        .container {
            width: 94%;
            max-width: 1200px;
            margin: 35px auto;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #aaa;
            margin-bottom: 25px;
        }

        .success {
            background: #173d2c;
            color: #72f0a8;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
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
            min-width: 850px;
        }

        th {
            background: #22222d;
            color: #ff4d8d;
            text-align: left;
            padding: 15px;
        }

        td {
            padding: 15px;
            border-top: 1px solid #2b2b36;
            color: #ddd;
        }

        tr:hover td {
            background: #1e1e28;
        }

        .number {
            color: #ff4d8d;
            font-weight: bold;
        }

        .delete-btn {
            background: #ff3b6b;
            color: white;
            border: none;
            padding: 9px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .delete-btn:hover {
            background: #e52e5d;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #aaa;
        }

        @media (max-width: 700px) {

            .navbar {
                padding: 15px;
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                width: 96%;
                margin: 25px auto;
            }

            h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        🎌 AniRate Admin
    </div>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../index.php" target="_blank">
            Website
        </a>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>


<!-- MAIN -->

<div class="container">

    <h1>👥 Manage Users</h1>

    <p class="subtitle">
        View registered users and their activity.
    </p>


    <?php if (isset($_GET["deleted"])): ?>

        <div class="success">
            ✅ User deleted successfully.
        </div>

    <?php endif; ?>


    <div class="table-container">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Joined</th>

                        <th>Ratings</th>

                        <th>Reviews</th>

                        <th>Watchlist</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while ($user = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo (int) $user["id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($user["email"]); ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($user["created_at"])
                                );
                                ?>
                            </td>

                            <td class="number">
                                <?php echo (int) $user["rating_count"]; ?>
                            </td>

                            <td class="number">
                                <?php echo (int) $user["review_count"]; ?>
                            </td>

                            <td class="number">
                                <?php echo (int) $user["watchlist_count"]; ?>
                            </td>

                            <td>

                                <form
                                    method="POST"
                                    action="users.php"
                                    onsubmit="return confirm(
                                        'Are you sure you want to delete this user? This will also delete their ratings, reviews and watchlist.'
                                    );"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars(csrf_token()); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?php echo (int) $user["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_user"
                                        class="delete-btn"
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
                👤 No registered users found.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>