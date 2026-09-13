<?php

session_start();

require_once "database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email == "" || $password == "") {

        $message = "Please enter email and password.";

    } else {

        $sql = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $sql->bind_param("s", $email);
        $sql->execute();

        $result = $sql->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                header("Location: index.php");
                exit();

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "Account not found.";

        }

        $sql->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - AniRate</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #111;
            color: white;
            font-family: Arial, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 400px;
            background: #1d1d1d;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .logo {
            text-align: center;
            color: #ff4d8d;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #ddd;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 18px;

            background: #292929;
            border: 1px solid #444;
            border-radius: 8px;

            color: white;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #ff4d8d;
        }

        button {
            width: 100%;
            padding: 14px;

            background: #ff4d8d;
            color: white;

            border: none;
            border-radius: 8px;

            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
        }

        button:hover {
            background: #ff246f;
        }

        .error {
            background: #3d1d25;
            color: #ff718f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }

        .register-link a {
            color: #ff4d8d;
            text-decoration: none;
        }

        .home-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        🎌 AniRate
    </div>

    <div class="subtitle">
        Login to your account
    </div>


    <?php if ($message != ""): ?>

        <div class="error">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            Email
        </label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
        >


        <button type="submit">
            Login 🔐
        </button>

    </form>


    <div class="register-link">

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </div>


    <a href="index.php" class="home-link">
        ← Back to Home
    </a>

</div>

</body>

</html>