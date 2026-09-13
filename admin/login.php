<?php

session_start();

require_once "../database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin["password"])) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_name"] = $admin["name"];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Invalid password!";

        }

    } else {

        $error = "Admin account not found!";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - AniRate</title>

    <link rel="stylesheet" href="../css/style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, #32152d, transparent 35%),
                #09090f;
        }

        .login-container {
            width: 420px;
            max-width: 90%;
            background: #11111a;
            border: 1px solid #292936;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
        }

        .login-logo {
            text-align: center;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-logo span {
            color: #ff4d8d;
        }

        .login-title {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            color: #bbb;
        }

        .form-group input {
            width: 100%;
            padding: 13px 15px;
            background: #0b0b12;
            border: 1px solid #292936;
            border-radius: 9px;
            color: white;
            outline: none;
            font-family: Poppins;
        }

        .form-group input:focus {
            border-color: #ff4d8d;
        }

        .admin-login-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 9px;
            background: #ff4d8d;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .admin-login-btn:hover {
            opacity: 0.9;
        }

        .error {
            background: #351923;
            color: #ff7da9;
            padding: 11px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #777;
            text-decoration: none;
            font-size: 12px;
        }

        .back-home:hover {
            color: #ff4d8d;
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-logo">
            🎌 Ani<span>Rate</span>
        </div>

        <p class="login-title">
            Admin Control Panel
        </p>


        <?php if (!empty($error)): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label>
                    Admin Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter admin email"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>


            <button
                type="submit"
                class="admin-login-btn"
            >
                Login to Dashboard →
            </button>


        </form>


        <a
            href="../index.php"
            class="back-home"
        >
            ← Back to Website
        </a>

    </div>

</body>

</html>