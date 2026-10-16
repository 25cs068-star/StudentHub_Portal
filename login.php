
<?php
session_start();

if (isset($_SESSION["user_id"])) {
    if (strtolower($_SESSION["role"] ?? "") === "admin") {
        header("Location: admin_dashboard.php");
        exit();
    }

    if (strtolower($_SESSION["role"] ?? "") === "student") {
        header("Location: dashboard.php");
        exit();
    }
}

if (isset($_COOKIE["remember_me"])) {
    $cookieParts = explode(":", $_COOKIE["remember_me"], 2);

    if (
        count($cookieParts) === 2 &&
        preg_match('/^[a-f0-9]{24}$/', $cookieParts[0]) &&
        preg_match('/^[a-f0-9]{64}$/', $cookieParts[1])
    ) {
        try {
            require_once __DIR__ . "/practical9_db.php";

            $stmt = $conn->prepare(
                "SELECT rt.user_id, rt.token_hash, rt.expires_at,
                        u.username, u.email, u.role
                 FROM remember_tokens rt
                 JOIN users u ON u.id = rt.user_id
                 WHERE rt.selector = ?
                 LIMIT 1"
            );

            $stmt->bind_param("s", $cookieParts[0]);
            $stmt->execute();

            $result = $stmt->get_result();
            $rememberUser = $result->fetch_assoc();
            $stmt->close();

            if (
                $rememberUser &&
                strtotime($rememberUser["expires_at"]) > time() &&
                hash_equals(
                    $rememberUser["token_hash"],
                    hash("sha256", $cookieParts[1])
                ) &&
                in_array(
                    strtolower($rememberUser["role"]),
                    ["admin", "student"],
                    true
                )
            ) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $rememberUser["user_id"];
                $_SESSION["username"] = $rememberUser["username"];
                $_SESSION["email"] = $rememberUser["email"];
                $_SESSION["role"] = strtolower($rememberUser["role"]);
                $_SESSION["last_activity"] = time();

                if ($_SESSION["role"] === "admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                }

                header("Location: dashboard.php");
                exit();
            }

            if ($rememberUser) {
                $deleteStmt = $conn->prepare(
                    "DELETE FROM remember_tokens WHERE selector = ?"
                );

                $deleteStmt->bind_param("s", $cookieParts[0]);
                $deleteStmt->execute();
                $deleteStmt->close();
            }

            setcookie("remember_me", "", [
                "expires" => time() - 3600,
                "path" => "/",
                "httponly" => true,
                "samesite" => "Lax"
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}

$error = "";

if (isset($_GET["error"]) && $_GET["error"] === "invalid") {
    $error = "Invalid email/enrollment number or password.";
} elseif (isset($_GET["error"]) && $_GET["error"] === "empty") {
    $error = "Please enter your login ID and password.";
} elseif (isset($_GET["error"]) && $_GET["error"] === "database") {
    $error = "Login could not be completed. Please check your database connection.";
} elseif (isset($_GET["error"]) && $_GET["error"] === "timeout") {
    $error = "Your session expired due to inactivity. Please login again.";
} elseif (isset($_GET["error"]) && $_GET["error"] === "loggedout") {
    $error = "You have logged out successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | StudentHub Portal</title>

    <link rel="stylesheet" href="csscommon.css">

    <style>
        .login-container {
            width: 90%;
            max-width: 450px;
            margin: 45px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            box-sizing: border-box;
        }

        .login-container h2 {
            text-align: center;
            color: #003566;
            margin-bottom: 10px;
        }

        .login-description {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-container label {
            display: block;
            margin-top: 16px;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .login-container input,
        .login-container select {
            width: 100%;
            padding: 12px;
            border: 1px solid #b8c7d9;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 70px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #0077b6;
            cursor: pointer;
            font-size: 13px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 18px 0;
        }

        .remember-row input {
            width: auto;
            margin: 0;
        }

        .remember-row label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }

        .login-buttons {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }

        .login-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .login-submit {
            background: #0077b6;
            color: #ffffff;
        }

        .login-submit:hover {
            background: #003566;
        }

        .login-reset {
            background: #e4eaf1;
            color: #003566;
        }

        .login-message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .login-error {
            background: #ffe6e6;
            color: #a00000;
        }

        .login-success {
            background: #e4f7e9;
            color: #17652b;
        }

        body.dark-mode .login-container {
            background: #263449;
            color: #ffffff;
        }

        body.dark-mode .login-container h2 {
            color: #8bdcff;
        }

        body.dark-mode .login-container input,
        body.dark-mode .login-container select {
            background: #182536;
            color: #ffffff;
            border-color: #53657b;
        }

        body.dark-mode .login-reset {
            background: #53657b;
            color: #ffffff;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 22px;
                margin: 25px auto;
            }

            .login-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo-section">
        <img src="logo.jfif" alt="StudentHub Logo">

        <div>
            <h1>StudentHub Portal</h1>
            <p>Your Digital Campus and Academic Companion</p>
        </div>
    </div>

    <button id="themeToggle" class="theme-btn" type="button">
        🌙 Dark Mode
    </button>

    <button id="menuToggle" class="menu-btn" type="button">
        ☰
    </button>

    <nav>
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="events.html">Events</a>
        <a href="faq.html">FAQ</a>
        <a href="contact.html">Contact</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main>
    <section class="login-container">

        <h2>Login to StudentHub</h2>

        <p class="login-description">
            Enter your details to access your dashboard.
        </p>

        <?php if ($error !== ""): ?>
            <p class="login-message login-error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form action="authenticate.php" method="POST" id="loginForm">

            <label for="role">Login As</label>

            <select name="role" id="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
            </select>

            <label for="loginId">Email or Enrollment Number</label>

            <input
                type="text"
                name="loginId"
                id="loginId"
                placeholder="Enter email or enrollment number"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>

            <div class="password-wrapper">
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    id="passwordToggle"
                >
                    Show
                </button>
            </div>

            <div class="remember-row">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    value="1"
                >

                <label for="remember">Remember Me for 30 days</label>
            </div>

            <div class="login-buttons">
                <button type="submit" class="login-submit">
                    Login
                </button>

                <button type="reset" class="login-reset">
                    Reset
                </button>
            </div>

        </form>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
</footer>

<script src="scriptcomman.js"></script>

<script>
    const passwordInput = document.getElementById("password");
    const passwordToggle = document.getElementById("passwordToggle");

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener("click", function () {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordToggle.textContent = "Hide";
            } else {
                passwordInput.type = "password";
                passwordToggle.textContent = "Show";
            }
        });
    }
</script>

</body>
</html>
