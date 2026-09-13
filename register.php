<?php

require_once "database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    /* Validation */

    if ($name == "" || $email == "" || $password == "") {

        $message = "Please fill all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } else {

        /* Check existing email */

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered.";
            $message_type = "error";

        } else {

            /* Hash password */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "user";

            /* Insert user */

            $insert = $conn->prepare(
                "INSERT INTO users
                (name, email, password, role)
                VALUES (?, ?, ?, ?)"
            );

            $insert->bind_param(
                "ssss",
                $name,
                $email,
                $hashed_password,
                $role
            );

            if ($insert->execute()) {

                $message = "Registration successful! 🎉 You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed.";
                $message_type = "error";
            }

            $insert->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - AniRate</title>

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

        .register-box {
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

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: #193d2a;
            color: #6cffaa;
        }

        .error {
            background: #3d1d25;
            color: #ff718f;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }

        .login-link a {
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

<div class="register-box">

    <div class="logo">
        🎌 AniRate
    </div>

    <div class="subtitle">
        Create your account
    </div>


    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            Name
        </label>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >


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
            placeholder="Minimum 6 characters"
            required
        >


        <button type="submit">
            Create Account 🚀
        </button>

    </form>


    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>


    <a href="index.php" class="home-link">
        ← Back to Home
    </a>

</div>

</body>

</html>